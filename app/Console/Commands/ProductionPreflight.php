<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProductionPreflight extends Command
{
    protected $signature = 'app:production-preflight';

    protected $description = 'Validate required production configuration before the app starts.';

    public function handle(): int
    {
        $failures = [];
        $phaseOneDemo = (bool) config('kwdc.demo');
        if ($phaseOneDemo) {
            $this->warn('Running in PHASE_ONE_DEMO mode: skipping external service preflight checks.');
        }

        $this->requireExact($failures, 'APP_ENV', config('app.env'), 'production');
        $this->requireFalse($failures, 'APP_DEBUG', (bool) config('app.debug'));
        $this->requireFilled($failures, 'APP_KEY', config('app.key'));
        $this->requireHttpsUrl($failures, 'APP_URL', config('app.url'));

        $this->requireTrue($failures, 'SESSION_ENCRYPT', (bool) config('session.encrypt'));
        $this->requireTrue($failures, 'SESSION_SECURE_COOKIE', (bool) config('session.secure'));
        $this->requireTrue($failures, 'SESSION_HTTP_ONLY', (bool) config('session.http_only'));
        $this->requireIn($failures, 'SESSION_SAME_SITE', config('session.same_site'), ['lax', 'strict']);

        $this->validateDatabase($failures);
        $this->validateMail($failures, $phaseOneDemo);
        $this->validateMaps($failures, $phaseOneDemo);
        $this->validatePayments($failures, $phaseOneDemo);

        if ($failures !== []) {
            $this->error('Production preflight failed:');

            foreach ($failures as $failure) {
                $this->line(" - {$failure}");
            }

            return self::FAILURE;
        }

        $this->info('Production preflight passed.');

        return self::SUCCESS;
    }

    private function validateDatabase(array &$failures): void
    {
        $connection = config('database.default');
        $this->requireFilled($failures, 'DB_CONNECTION', $connection);

        $url = config("database.connections.{$connection}.url");
        if (! empty($url)) {
            return;
        }

        $database = config("database.connections.{$connection}.database");
        $this->requireFilled($failures, 'DB_DATABASE', $database);

        if (in_array($connection, ['mysql', 'pgsql'], true)) {
            $this->requireFilled($failures, 'DB_HOST', config("database.connections.{$connection}.host"));
            $this->requireFilled($failures, 'DB_USERNAME', config("database.connections.{$connection}.username"));
        }
    }

    private function validateMail(array &$failures, bool $phaseOneDemo): void
    {
        $mailer = config('mail.default');

        if ($phaseOneDemo && $mailer === 'log') {
            return;
        }

        $this->requireExact($failures, 'MAIL_MAILER', $mailer, 'smtp');
        $this->requireFilled($failures, 'MAIL_HOST', config('mail.mailers.smtp.host'));
        $this->requireFilled($failures, 'MAIL_USERNAME', config('mail.mailers.smtp.username'));
        $this->requireFilled($failures, 'MAIL_PASSWORD', config('mail.mailers.smtp.password'));
        $this->requireFilled($failures, 'MAIL_FROM_ADDRESS', config('mail.from.address'));
    }

    private function validatePayments(array &$failures, bool $phaseOneDemo): void
    {
        if ($phaseOneDemo) {
            return;
        }

        $this->requireFilled($failures, 'KHALTI_PUBLIC_KEY', config('payment.khalti.public_key'));
        $this->requireFilled($failures, 'KHALTI_SECRET_KEY', config('payment.khalti.secret_key'));
        $this->requireHttpsUrl($failures, 'KHALTI_BASE_URL', config('payment.khalti.base_url'));
        $this->requireHttpsUrl($failures, 'KHALTI_VERIFICATION_URL', config('payment.khalti.verification_url'));

        $this->requireFilled($failures, 'ESEWA_MERCHANT_CODE', config('payment.esewa.merchant_code'));
        $this->requireFilled($failures, 'ESEWA_SECRET_KEY', config('payment.esewa.secret_key'));
        $this->requireHttpsUrl($failures, 'ESEWA_PAYMENT_URL', config('payment.esewa.payment_url'));
        $this->requireHttpsUrl($failures, 'ESEWA_VERIFICATION_URL', config('payment.esewa.verification_url'));
    }

    private function validateMaps(array &$failures, bool $phaseOneDemo): void
    {
        if ($phaseOneDemo) {
            return;
        }

        $geocoderUrl = (string) config('maps.geocoder_url');
        $routerUrl = (string) config('maps.router_url');

        $this->requireHttpsUrl($failures, 'MAP_GEOCODER_URL', $geocoderUrl);
        $this->requireHttpsUrl($failures, 'MAP_ROUTER_URL', $routerUrl);

        if (str_contains($geocoderUrl, 'photon.komoot.io')) {
            $failures[] = 'MAP_GEOCODER_URL must use a dedicated production geocoder, not the public Photon demo endpoint.';
        }

        if (str_contains($routerUrl, 'router.project-osrm.org')) {
            $failures[] = 'MAP_ROUTER_URL must use a dedicated production router, not the public OSRM demo endpoint.';
        }
    }

    private function requireFilled(array &$failures, string $name, mixed $value): void
    {
        if (blank($value)) {
            $failures[] = "{$name} must be configured.";
        }
    }

    private function requireExact(array &$failures, string $name, mixed $actual, mixed $expected): void
    {
        if ($actual !== $expected) {
            $failures[] = "{$name} must be {$expected}.";
        }
    }

    private function requireFalse(array &$failures, string $name, bool $value): void
    {
        if ($value) {
            $failures[] = "{$name} must be false.";
        }
    }

    private function requireTrue(array &$failures, string $name, bool $value): void
    {
        if (! $value) {
            $failures[] = "{$name} must be true.";
        }
    }

    private function requireIn(array &$failures, string $name, mixed $actual, array $allowed): void
    {
        if (! in_array($actual, $allowed, true)) {
            $failures[] = "{$name} must be one of: " . implode(', ', $allowed) . '.';
        }
    }

    private function requireHttpsUrl(array &$failures, string $name, mixed $value): void
    {
        if (blank($value) || ! is_string($value) || ! str_starts_with($value, 'https://') || str_contains($value, 'your-domain.example')) {
            $failures[] = "{$name} must be a real https:// URL.";
        }
    }
}
