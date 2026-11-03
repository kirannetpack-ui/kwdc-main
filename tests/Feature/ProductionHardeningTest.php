<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_debug_routes_are_not_registered(): void
    {
        $this->get('/test-login')->assertNotFound();
        $this->get('/clear-cache')->assertNotFound();
        $this->get('/test-email')->assertNotFound();
    }

    public function test_notification_email_delivery_fields_are_mass_assignable(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);

        $notification = Notification::create([
            'notification_number' => 'NOT-TEST-001',
            'type' => 'dispatch_created',
            'title' => 'Dispatch created',
            'subject' => 'Dispatch created',
            'message' => 'A dispatch was created.',
            'user_id' => $user->id,
            'recipient_email' => $user->email,
            'recipient_name' => $user->name,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->assertSame('sent', $notification->status);
        $this->assertDatabaseHas('notifications', [
            'notification_number' => 'NOT-TEST-001',
            'recipient_email' => $user->email,
        ]);
    }

    public function test_source_views_and_scripts_do_not_ship_console_debug_logs(): void
    {
        $paths = [
            public_path('js/voice-assistant.js'),
            resource_path('views/warehouses/create.blade.php'),
            resource_path('views/equipment-requests/create.blade.php'),
        ];

        foreach ($paths as $path) {
            $this->assertStringNotContainsString('console.log', file_get_contents($path), $path);
        }
    }

    public function test_authentication_logs_do_not_include_session_tokens_or_raw_login_email(): void
    {
        $loginController = file_get_contents(app_path('Http/Controllers/Auth/LoginController.php'));

        $this->assertStringNotContainsString('session()->token()', $loginController);
        $this->assertStringNotContainsString('session_token', $loginController);
        $this->assertStringNotContainsString("'email' => \$request->email", $loginController);
        $this->assertStringNotContainsString('"email" => $request->email', $loginController);
    }

    public function test_security_headers_are_applied_to_https_responses(): void
    {
        $response = $this->withServerVariables(['HTTPS' => 'on'])->get('/ping');

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $this->assertStringContainsString("frame-ancestors 'none'", $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('microphone=(self)', $response->headers->get('Permissions-Policy'));
    }

    public function test_production_requests_are_forced_to_https(): void
    {
        Config::set('app.env', 'production');

        $this->get('http://localhost/ping')
            ->assertRedirect('https://localhost/ping');
    }

    public function test_production_environment_template_documents_secure_defaults(): void
    {
        $template = file_get_contents(base_path('.env.production.example'));

        $this->assertStringContainsString('APP_ENV=production', $template);
        $this->assertStringContainsString('APP_DEBUG=false', $template);
        $this->assertStringContainsString('SESSION_ENCRYPT=true', $template);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $template);
        $this->assertStringContainsString('MAIL_MAILER=smtp', $template);
        $this->assertStringContainsString('KHALTI_SECRET_KEY=', $template);
        $this->assertStringContainsString('ESEWA_VERIFICATION_URL=https://esewa.com.np/epay/transrec', $template);
        $this->assertStringContainsString('PAYMENT_HTTP_TIMEOUT=10', $template);
    }

    public function test_deployment_config_does_not_generate_app_key_at_build_time(): void
    {
        $nixpacks = file_get_contents(base_path('nixpacks.toml'));
        $startScript = file_get_contents(base_path('scripts/deploy/start-production.sh'));
        $procfile = file_get_contents(base_path('Procfile'));

        $this->assertStringNotContainsString('key:generate', $nixpacks);
        $this->assertStringContainsString('npm run build', $nixpacks);
        $this->assertStringContainsString('APP_KEY must be configured', $startScript);
        $this->assertStringContainsString('php artisan app:production-preflight', $startScript);
        $this->assertStringContainsString('php artisan migrate --force', $startScript);
        $this->assertLessThan(
            strpos($startScript, 'php artisan optimize:clear'),
            strpos($startScript, 'php artisan migrate --force')
        );
        $this->assertLessThan(
            strpos($startScript, 'php artisan app:production-preflight'),
            strpos($startScript, 'php artisan optimize:clear')
        );
        $this->assertLessThan(
            strpos($startScript, 'php artisan config:cache'),
            strpos($startScript, 'php artisan app:production-preflight')
        );
        $this->assertStringContainsString('worker: php artisan queue:work', $procfile);
        $this->assertStringContainsString('scheduler: php artisan schedule:work', $procfile);
    }

    public function test_apache_deployments_serve_laravel_public_directory(): void
    {
        $dockerfile = file_get_contents(base_path('dockerfile'));
        $htaccess = file_get_contents(public_path('.htaccess'));

        $this->assertStringContainsString('APACHE_DOCUMENT_ROOT=/var/www/html/public', $dockerfile);
        $this->assertStringContainsString('a2enmod rewrite headers', $dockerfile);
        $this->assertStringContainsString('RewriteRule ^ index.php [L]', $htaccess);
    }

    public function test_generated_private_artifacts_are_not_tracked(): void
    {
        exec('git ls-files', $trackedFiles, $exitCode);

        $this->assertSame(0, $exitCode);

        $forbiddenPatterns = [
            '#^archive/frontend-backup/node_modules/#',
            '#^database/backups/#',
            '#^archive/root-junk/cookies\.txt$#',
            '#^archive/root-junk/login\.html$#',
            '#^archive/root-junk/.+\.bak$#',
            '#^app/Http/Controllers/.+\.txt$#',
            '#^scripts/maintenance/test-login\.bat$#',
            '#^resources/views/auth/simple-login\.blade\.php$#',
            '#^resources/views/warehouses/(simple|simple-create|test)\.blade\.php$#',
        ];

        foreach ($forbiddenPatterns as $pattern) {
            $matches = preg_grep($pattern, $trackedFiles);

            $this->assertSame([], array_values($matches), 'Forbidden tracked files matched ' . $pattern);
        }
    }

    public function test_mail_configuration_does_not_commit_smtp_credentials(): void
    {
        $mailConfig = file_get_contents(config_path('mail.php'));
        $adminEmailService = file_get_contents(app_path('Services/AdminEmailService.php'));

        $this->assertStringContainsString("'host' => env('MAIL_HOST'", $mailConfig);
        $this->assertStringContainsString("'username' => env('MAIL_USERNAME')", $mailConfig);
        $this->assertStringContainsString("'password' => env('MAIL_PASSWORD')", $mailConfig);
        $this->assertStringContainsString("'address' => env('MAIL_FROM_ADDRESS'", $mailConfig);
        $this->assertStringNotContainsString('smtp.gmail.com', $mailConfig);
        $this->assertStringNotContainsString('kiran.kwdc@gmail.com', $mailConfig);
        $this->assertStringNotContainsString('nuzpnwuaavxynsdg', $mailConfig);
        $this->assertStringNotContainsString('kiran.kwdc@gmail.com', $adminEmailService);
    }

    public function test_inactive_auth_scaffolding_is_not_tracked(): void
    {
        exec('git ls-files', $trackedFiles, $exitCode);

        $this->assertSame(0, $exitCode);

        $forbiddenPatterns = [
            '#^routes/auth\.php$#',
            '#^app/Http/Controllers/Auth/(AuthenticatedSessionController|RegisteredUserController|ForgotPasswordController|ResetPasswordController|ConfirmPasswordController)\.php$#',
            '#^resources/views/auth/passwords/#',
        ];

        foreach ($forbiddenPatterns as $pattern) {
            $matches = preg_grep($pattern, $trackedFiles);

            $this->assertSame([], array_values($matches), 'Forbidden tracked files matched ' . $pattern);
        }
    }

    public function test_admin_warehouse_review_links_sensitive_documents_through_private_route(): void
    {
        $view = file_get_contents(resource_path('views/admin/warehouses/show.blade.php'));

        $this->assertStringContainsString('$warehouse->tax_document', $view);
        $this->assertStringContainsString('$warehouse->fire_safety_document', $view);
        $this->assertStringContainsString("route('documents.private.show', ['path' => \$warehouse->tax_document])", $view);
        $this->assertStringContainsString("route('documents.private.show', ['path' => \$warehouse->fire_safety_document])", $view);

        $this->assertStringNotContainsString('tax_clearance_document', $view);
        $this->assertStringNotContainsString('fire_safety_certificate', $view);
        $this->assertStringNotContainsString('Storage::url($warehouse->tax_', $view);
        $this->assertStringNotContainsString('Storage::url($warehouse->fire_safety_', $view);
    }

    public function test_box_document_surfaces_do_not_use_public_storage_urls(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/BoxController.php'));
        $indexView = file_get_contents(resource_path('views/client/boxes/index.blade.php'));
        $trackView = file_get_contents(resource_path('views/client/boxes/track.blade.php'));

        $this->assertStringContainsString("'private_uploads'", $controller);
        $this->assertStringContainsString("route('documents.private.show'", $trackView);

        $this->assertStringNotContainsString("store('boxes/documents/invoices', 'public')", $controller);
        $this->assertStringNotContainsString("store('boxes/documents/packing_lists', 'public')", $controller);
        $this->assertStringNotContainsString("store('boxes/documents/insurance', 'public')", $controller);
        $this->assertStringNotContainsString("store('boxes/documents/others', 'public')", $controller);
        $this->assertStringNotContainsString('/storage/${data.', $indexView);
        $this->assertStringNotContainsString('Storage::url($box->', $trackView);
    }

    public function test_dispatch_stop_invoice_documents_use_private_route(): void
    {
        $privateDocumentController = file_get_contents(app_path('Http/Controllers/PrivateDocumentController.php'));
        $dispatchShowView = file_get_contents(resource_path('views/dispatch/show.blade.php'));

        $this->assertStringContainsString('canAccessDeliveryStopDocument', $privateDocumentController);
        $this->assertStringContainsString("DeliveryStop::where('invoice_document', \$path)", $privateDocumentController);
        $this->assertStringContainsString("route('documents.private.show', ['path' => \$stop->invoice_document])", $dispatchShowView);
        $this->assertStringNotContainsString('Storage::url($stop->invoice_document)', $dispatchShowView);
    }

    public function test_stock_documents_are_private_uploads(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/StockController.php'));

        $this->assertStringContainsString("store('stock-documents/invoices', 'private_uploads')", $controller);
        $this->assertStringContainsString("store('stock-documents/grns', 'private_uploads')", $controller);
        $this->assertStringContainsString("store('stock-documents/quality-certificates', 'private_uploads')", $controller);
        $this->assertStringContainsString("store('stock-documents/others', 'private_uploads')", $controller);
        $this->assertStringContainsString("Storage::disk('private_uploads')->download", $controller);
        $this->assertStringNotContainsString("stock_documents/invoices', 'public'", $controller);
        $this->assertStringNotContainsString("Storage::disk('public')->download(\$path)", $controller);
    }

    public function test_dispatch_actions_use_centralized_access_guards(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/DispatchController.php'));
        $emailService = file_get_contents(app_path('Services/ProfessionalEmailService.php'));

        $this->assertStringContainsString('private function dispatchClientId', $controller);
        $this->assertStringContainsString('$clientId = $this->dispatchClientId($request);', $controller);
        $this->assertStringContainsString('return $user->id;', $controller);
        $this->assertStringContainsString('private function canTrackDispatch', $controller);
        $this->assertStringContainsString('hash_equals($dispatch->tracking_token, $token)', $controller);
        $this->assertStringContainsString("'token' => \$dispatch->tracking_token", $controller);
        $this->assertStringContainsString('private function canViewDispatch', $controller);
        $this->assertStringContainsString('private function canManageDispatch', $controller);
        $this->assertStringContainsString('private function canRateDispatch', $controller);
        $this->assertStringContainsString('abort_unless($this->canViewDispatch($dispatch), 403)', $controller);
        $this->assertStringContainsString('abort_unless($this->canManageDispatch($dispatch), 403)', $controller);
        $this->assertStringContainsString('abort_unless($this->canRateDispatch($dispatch), 403)', $controller);
        $this->assertStringContainsString('abort_unless($this->canManageDispatch($stop->dispatchOrder), 403)', $controller);
        $this->assertStringContainsString('ensureTrackingToken', $emailService);
        $this->assertStringContainsString("'id' => \$dispatch->id", $emailService);
        $this->assertStringContainsString("'token' => \$dispatch->tracking_token", $emailService);
        $this->assertStringNotContainsString("route('dispatch.track', \$dispatch->tracking_id)", $emailService);
    }

    public function test_admin_update_endpoints_do_not_mass_assign_raw_request_data(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/AdminController.php'));

        $this->assertStringNotContainsString('$equipment->update($request->all())', $controller);
        $this->assertStringNotContainsString('$driver->update($request->all())', $controller);
        $this->assertStringNotContainsString('$tier->update($request->all())', $controller);
        $this->assertStringContainsString('$equipment->update($validated)', $controller);
        $this->assertStringContainsString('$driver->update($validated)', $controller);
        $this->assertStringContainsString('$tier->update($validated)', $controller);
    }

    public function test_equipment_compliance_documents_are_private_uploads(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/EquipmentController.php'));
        $privateDocumentController = file_get_contents(app_path('Http/Controllers/PrivateDocumentController.php'));

        $this->assertStringContainsString("store('equipment/documents', 'private_uploads')", $controller);
        $this->assertStringNotContainsString("store('equipment/documents', 'public')", $controller);
        $this->assertStringContainsString('whereEquipmentDocumentPath', $privateDocumentController);
        $this->assertStringContainsString("->orWhere('insurance_doc', \$path)", $privateDocumentController);
    }

    public function test_driver_vehicle_compliance_documents_are_private_uploads(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/DriverVehicleController.php'));
        $privateDocumentController = file_get_contents(app_path('Http/Controllers/PrivateDocumentController.php'));

        $this->assertStringContainsString("storeAs('vehicle-documents/' . \$folder, \$filename, 'private_uploads')", $controller);
        $this->assertStringContainsString("'insurance_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'", $controller);
        $this->assertStringContainsString("storeAs('vehicle-photos/' . \$folder, \$filename, 'public')", $controller);
        $this->assertStringContainsString("Storage::disk('private_uploads')->delete", $controller);
        $this->assertStringContainsString('whereVehicleDocumentPath', $privateDocumentController);
        $this->assertStringContainsString("->orWhere('blue_book_file_path', \$path)", $privateDocumentController);
        $this->assertStringNotContainsString("storeAs('vehicle_documents/' . \$folder, \$filename, 'public')", $controller);
    }

    public function test_warehouse_request_insurance_documents_are_private_uploads(): void
    {
        $clientRequestController = file_get_contents(app_path('Http/Controllers/ClientRequestHandler.php'));
        $privateDocumentController = file_get_contents(app_path('Http/Controllers/PrivateDocumentController.php'));
        $insuranceView = file_get_contents(resource_path('views/admin/insurance/show.blade.php'));
        $insuranceMail = file_get_contents(app_path('Mail/InsuranceNotification.php'));

        $this->assertStringContainsString("store('warehouse-requests/documents/invoices', 'private_uploads')", $clientRequestController);
        $this->assertStringContainsString("store('warehouse-requests/documents/packing-lists', 'private_uploads')", $clientRequestController);
        $this->assertStringContainsString("store('warehouse-requests/documents/insurance', 'private_uploads')", $clientRequestController);
        $this->assertStringContainsString('whereWarehouseRequestDocumentPath', $privateDocumentController);
        $this->assertStringContainsString("route('documents.private.show', ['path' => \$warehouseRequest->invoice_path])", $insuranceView);
        $this->assertStringNotContainsString("asset('storage/'.\$warehouseRequest->", $insuranceView);
        $this->assertStringContainsString("Storage::disk('private_uploads')->path", $insuranceMail);
        $this->assertStringNotContainsString("storage_path('app/public/'", $insuranceMail);
    }

    public function test_warehouse_request_schema_supports_live_operations(): void
    {
        $this->assertTrue(Schema::hasColumns('warehouse_requests', [
            'required_area',
            'space_required',
            'duration_months',
            'assigned_warehouse_id',
            'invoice_path',
            'packing_list_path',
            'insurance_path',
            'agreed_price',
            'agreed_price_per_unit',
            'monthly_rent',
            'last_invoice_date',
            'goods_auctioned',
            'assigned_at',
            'completed_at',
            'cancelled_at',
            'cancellation_notes',
            'contract_end_date',
            'contract_signed_at',
            'contract_expires_at',
        ]));
    }

    public function test_public_realtime_views_use_cached_config_not_raw_env(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $dispatchShow = file_get_contents(resource_path('views/dispatch/show.blade.php'));

        $this->assertStringContainsString("config('broadcasting.connections.reverb.key')", $layout);
        $this->assertStringContainsString("config('broadcasting.connections.reverb.options.host'", $layout);
        $this->assertStringContainsString("config('broadcasting.connections.reverb.key')", $dispatchShow);
        $this->assertStringNotContainsString("env('REVERB_", $layout);
        $this->assertStringNotContainsString("env('REVERB_", $dispatchShow);
    }

    public function test_payment_provider_sessions_fail_closed_on_malformed_provider_payloads(): void
    {
        $service = file_get_contents(app_path('Services/PaymentService.php'));

        $this->assertStringContainsString('(int) round($amount * 100)', $service);
        $this->assertStringContainsString("blank(\$data['pidx'] ?? null)", $service);
        $this->assertStringContainsString("blank(\$data['payment_url'] ?? null)", $service);
        $this->assertStringContainsString('Str::uuid()', $service);
    }

    public function test_payment_provider_calls_have_timeout_retry_and_config_guards(): void
    {
        $service = file_get_contents(app_path('Services/PaymentService.php'));
        $paymentConfig = file_get_contents(config_path('payment.php'));

        $this->assertStringContainsString("'timeout' => (int) env('PAYMENT_HTTP_TIMEOUT'", $paymentConfig);
        $this->assertStringContainsString('Http::timeout(config(\'payment.http.timeout\'))', $service);
        $this->assertStringContainsString('->retry(', $service);
        $this->assertStringContainsString("blank(config('payment.khalti.secret_key'))", $service);
        $this->assertStringContainsString("blank(config('payment.esewa.merchant_code'))", $service);
    }

    public function test_auth_routes_use_single_canonical_password_reset_surface(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));
        $uris = collect(Route::getRoutes())->map(fn ($route) => $route->uri())->all();

        $this->assertStringNotContainsString('Auth::routes()', $routes);
        $this->assertNotContains('password/reset', $uris);
        $this->assertNotContains('password/email', $uris);
        $this->assertContains('forgot-password', $uris);
        $this->assertContains('reset-password/{token}', $uris);
    }

    public function test_password_reset_uses_branded_security_email(): void
    {
        $userModel = file_get_contents(app_path('Models/User.php'));
        $emailView = file_get_contents(resource_path('views/emails/password-reset.blade.php'));

        $this->assertStringContainsString('ProfessionalResetPasswordNotification', $userModel);
        $this->assertStringContainsString('Reset your KTM-WDC password', $emailView);
        $this->assertStringContainsString('KTM-WDC will never ask you to share your password', $emailView);
        $this->assertStringContainsString('This password reset link expires in', $emailView);
    }

    public function test_production_preflight_fails_for_incomplete_configuration(): void
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', false);
        Config::set('app.key', '');
        Config::set('app.url', 'http://ktm-wdc.example');

        $exitCode = Artisan::call('app:production-preflight');
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('APP_KEY must be configured.', $output);
        $this->assertStringContainsString('APP_URL must be a real https:// URL.', $output);
    }

    public function test_production_preflight_passes_with_required_configuration(): void
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', false);
        Config::set('app.key', 'base64:production-secret-key');
        Config::set('app.url', 'https://ktm-wdc.example');
        Config::set('session.encrypt', true);
        Config::set('session.secure', true);
        Config::set('session.http_only', true);
        Config::set('session.same_site', 'lax');
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', 'smtp.example.com');
        Config::set('mail.mailers.smtp.username', 'mail-user');
        Config::set('mail.mailers.smtp.password', 'mail-password');
        Config::set('mail.from.address', 'no-reply@ktm-wdc.example');
        Config::set('maps.geocoder_url', 'https://maps.ktm-wdc.example/geocoder');
        Config::set('maps.router_url', 'https://maps.ktm-wdc.example/router');
        Config::set('payment.khalti.public_key', 'khalti-public');
        Config::set('payment.khalti.secret_key', 'khalti-secret');
        Config::set('payment.khalti.base_url', 'https://a.khalti.com/api/v2/');
        Config::set('payment.khalti.verification_url', 'https://a.khalti.com/api/v2/epayment/lookup/');
        Config::set('payment.esewa.merchant_code', 'esewa-merchant');
        Config::set('payment.esewa.secret_key', 'esewa-secret');
        Config::set('payment.esewa.payment_url', 'https://esewa.com.np/epay/main');
        Config::set('payment.esewa.verification_url', 'https://esewa.com.np/epay/transrec');

        $exitCode = Artisan::call('app:production-preflight');

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Production preflight passed.', Artisan::output());
    }
}
