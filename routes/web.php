<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\PickupRequestController;
use App\Http\Controllers\ClientRequestHandler;
use App\Http\Controllers\StockController;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\AiVoiceController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\PrivateDocumentController;

// ================================================================
// 1. PUBLIC ROUTES
// ================================================================
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('landing');
Route::get('/ping', fn() => 'pong');
Route::get('/invoice/verify/{invoiceNumber}', [InvoiceController::class, 'verify'])->name('invoice.verify');

// Legal & Compliance Documentation
Route::get('/privacy-policy', fn() => view('legal.privacy'))->name('privacy-policy');
Route::get('/terms-of-service', fn() => view('legal.terms'))->name('terms-of-service');
Route::get('/compliance', fn() => view('legal.compliance'))->name('compliance');

// Language Switcher
Route::get('/locale/{lang}', function ($lang) {
    if (in_array($lang, ['en', 'ne'])) {
        session(['locale' => $lang]);
    }
    return redirect()->back();
})->name('locale.switch');

// PWA Static Endpoints
Route::get('/manifest.json', function () {
    return response(file_get_contents(public_path('manifest.json')), 200, ['Content-Type' => 'application/json']);
})->name('pwa.manifest');

Route::get('/offline.html', function () {
    return response(file_get_contents(public_path('offline.html')), 200, ['Content-Type' => 'text/html']);
})->name('pwa.offline');

// ================================================================
// 2. AUTHENTICATION ROUTES
// ================================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->middleware('throttle:5,1');
Route::get('/activate', [ActivationController::class, 'show'])->name('activation.notice');
Route::post('/activate', [ActivationController::class, 'activate'])
    ->name('activation.verify')
    ->middleware('throttle:5,1');
Route::post('/activate/resend', [ActivationController::class, 'resend'])->name('activation.resend')->middleware('throttle:3,1');

Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->middleware('guest')->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware(['guest', 'throttle:5,1'])->name('password.email');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->middleware('guest')->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware(['guest', 'throttle:5,1'])->name('password.store');
Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])->middleware('auth')->name('password.confirm');
Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store'])->middleware('auth');
Route::put('/password', [PasswordController::class, 'update'])->middleware('auth')->name('password.update');
Route::get('/verify-email', EmailVerificationPromptController::class)->middleware('auth')->name('verification.notice');
Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// Home & Dashboard
Route::get('/home', [HomeController::class, 'index'])->middleware(['auth', 'account.ready'])->name('home');
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'account.ready'])->name('dashboard');

// ================================================================
// 3. PROTECTED PLATFORM ROUTES (Authentication required)
// ================================================================
Route::middleware(['auth', 'account.ready'])->group(function () {
    Route::get('/maps/search', [\App\Http\Controllers\MapController::class, 'search'])->middleware('throttle:60,1')->name('maps.search');
    Route::get('/maps/reverse', [\App\Http\Controllers\MapController::class, 'reverse'])->middleware('throttle:60,1')->name('maps.reverse');
    Route::post('/maps/route', [\App\Http\Controllers\MapController::class, 'route'])->middleware('throttle:30,1')->name('maps.route');

    // ==================== VOICE ASSISTANT ====================
    Route::post('/ai/voice-assistant', [AiVoiceController::class, 'voiceAssistant'])->name('ai.voice.assistant');
    Route::post('/ai/transcribe', [AiVoiceController::class, 'transcribe'])->name('ai.transcribe');

    Route::get('/private-documents/{path}', [PrivateDocumentController::class, 'show'])
        ->where('path', '.*')
        ->name('documents.private.show');

    // ==================== PROFILE ====================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::get('/contacts', [ProfileController::class, 'contacts'])->name('contacts');
        Route::post('/contacts', [ProfileController::class, 'addContact'])->name('contacts.store');
        Route::delete('/contacts/{id}', [ProfileController::class, 'destroyContact'])->name('contacts.destroy');
        Route::post('/two-factor/toggle', [\App\Http\Controllers\TwoFactorController::class, 'toggle'])->name('two-factor.toggle');
    });

    // ==================== TWO FACTOR VERIFICATION ====================
    Route::get('/two-factor/challenge', [\App\Http\Controllers\TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/verify', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->name('two-factor.verify');

    // ==================== NOTIFICATIONS ====================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
    });

    // ==================== REMINDERS & WAREHOUSE REQUEST DECISION ====================
    Route::get('/warehouse-requests/{id}', [\App\Http\Controllers\WarehouseRequestDetailController::class, 'show'])->name('warehouse-requests.show');
    Route::post('/warehouse-requests/{id}/decision', [\App\Http\Controllers\WarehouseRequestDetailController::class, 'decide'])->name('warehouse-requests.decide');
    Route::resource('reminders', ReminderController::class)->only(['index', 'store', 'update', 'destroy']);

    // ==================== TRACKING ====================
    Route::prefix('tracking')->name('tracking.')->group(function () {
        Route::get('/', [TrackingController::class, 'index'])->name('index');
        Route::get('/incoming', [TrackingController::class, 'incoming'])->name('incoming');
        Route::get('/outgoing', [TrackingController::class, 'outgoing'])->name('outgoing');
        Route::get('/pickups', [TrackingController::class, 'pickups'])->name('pickups');
        Route::get('/shipment/{id}', [TrackingController::class, 'trackShipment'])->name('shipment');
        Route::post('/location/{id}', [TrackingController::class, 'updateLocation'])->name('location');
        Route::post('/status/{id}', [TrackingController::class, 'updateStatus'])->name('status');
    });

    // ==================== WAREHOUSES (Shared) ====================
    Route::resource('warehouses', WarehouseController::class);
    Route::post('/save-preferred-location', [ClientRequestHandler::class, 'savePreferredLocation'])->name('save-preferred-location');
    Route::post('/warehouses/verify-kataho', [WarehouseController::class, 'verifyKataho'])->name('warehouses.verify-kataho');
    Route::post('/warehouses/get-kataho-from-coords', [WarehouseController::class, 'getKatahoFromCoords'])->name('warehouses.get-kataho-from-coords');
    Route::get('/warehouses/search', [WarehouseController::class, 'search'])->name('warehouses.search');

    // ==================== STOCK ====================
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/create', [StockController::class, 'create'])->name('create');
        Route::post('/store', [StockController::class, 'store'])->name('store');
        Route::get('/{id}', [StockController::class, 'show'])->name('show');
        Route::get('/{id}/qr-code', [StockController::class, 'viewQrCode'])->name('qr-code');
        Route::get('/{id}/download-qr', [StockController::class, 'downloadQrCode'])->name('download-qr');
        Route::get('/{id}/document/{type}', [StockController::class, 'downloadDocument'])->name('download-document');
    });

    // ==================== BOXES (QR) ====================
    Route::prefix('boxes')->name('boxes.')->group(function () {
        Route::get('/', [BoxController::class, 'index'])->name('index');
        Route::get('/create', [BoxController::class, 'create'])->name('create');
        Route::post('/', [BoxController::class, 'store'])->name('store');
        Route::get('/{id}/qr', [BoxController::class, 'getQR'])->name('qr');
        Route::get('/{id}/track', [BoxController::class, 'track'])->name('track');
        Route::get('/{id}/print', [BoxController::class, 'printLabel'])->name('print');
        Route::get('/{id}/documents', [BoxController::class, 'getDocuments'])->name('documents');
    });

    // ==================== DISPATCH ====================
    Route::prefix('dispatch')->name('dispatch.')->group(function () {
        Route::get('/', [DispatchController::class, 'index'])->name('index');
        Route::get('/direct-create', [DispatchController::class, 'directCreate'])->name('direct-create');
        Route::get('/create/{requestId}', [DispatchController::class, 'create'])->name('create');
        Route::post('/store', [DispatchController::class, 'store'])->name('store');
        Route::get('/{id}', [DispatchController::class, 'show'])->name('show');
        Route::post('/calculate-price', [DispatchController::class, 'calculatePriceAjax'])->name('calculate-price');
        Route::get('/{id}/track', [DispatchController::class, 'track'])->name('track');
        Route::post('/{id}/status', [DispatchController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/rate', [DispatchController::class, 'rate'])->name('rate');
        Route::post('/{id}/enable-tracking', [DispatchController::class, 'enableTracking'])->name('enable-tracking');
        Route::post('/{id}/update-location', [DispatchController::class, 'updateLocation'])->name('update-location');
        Route::post('/stop/{id}/status', [DispatchController::class, 'updateStopStatus'])->name('stop-status');
    });

    // ==================== PICKUP ====================
    Route::prefix('pickup')->name('pickup.')->group(function () {
        Route::get('/', [PickupRequestController::class, 'index'])->name('index');
        Route::get('/create', [PickupRequestController::class, 'create'])->name('create');
        Route::get('/direct-create', [PickupRequestController::class, 'directCreate'])->name('direct-create');
        Route::post('/store', [PickupRequestController::class, 'store'])->name('store');
        Route::post('/calculate-price', [PickupRequestController::class, 'calculatePriceAjax'])->name('calculate-price');
        Route::get('/{id}', [PickupRequestController::class, 'show'])->name('show');
        Route::post('/{id}/status', [PickupRequestController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/cancel', [PickupRequestController::class, 'cancel'])->name('cancel');
    });

    // ==================== INVOICES ====================
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/client-index', [InvoiceController::class, 'clientIndex']);
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/download', [InvoiceController::class, 'download'])->name('download');
    });
    Route::get('/client/invoices', [InvoiceController::class, 'clientIndex'])->name('invoices.client-index');

    // ==================== PAYMENT ====================
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/khalti/init', [PaymentController::class, 'khaltiInit'])->name('khalti.init')->middleware('throttle:10,1');
        Route::get('/khalti/verify', [PaymentController::class, 'khaltiVerify'])->name('khalti.verify')->middleware('throttle:20,1');
        Route::post('/esewa/init', [PaymentController::class, 'esewaInit'])->name('esewa.init')->middleware('throttle:10,1');
        Route::get('/esewa/success', [PaymentController::class, 'esewaSuccess'])->name('esewa.success')->middleware('throttle:20,1');
        Route::get('/esewa/failure', [PaymentController::class, 'esewaFailure'])->name('esewa.failure')->middleware('throttle:20,1');
        Route::post('/stripe/init', [PaymentController::class, 'stripeInit'])->name('stripe.init')->middleware('throttle:10,1');
        Route::get('/stripe/success', [PaymentController::class, 'stripeSuccess'])->name('stripe.success')->middleware('throttle:20,1');
        Route::get('/stripe/cancel', [PaymentController::class, 'stripeCancel'])->name('stripe.cancel');
        Route::post('/bank/submit', [PaymentController::class, 'bankTransferSubmit'])->name('bank.submit')->middleware('throttle:10,1');
        Route::post('/banking-qr/submit', [PaymentController::class, 'bankingQrSubmit'])->name('banking-qr.submit')->middleware('throttle:10,1');
        Route::get('/success', [PaymentController::class, 'success'])->name('success');
        Route::get('/failure', [PaymentController::class, 'failure'])->name('failure');
        Route::get('/history', [PaymentController::class, 'history'])->name('history');
    });

    // ==================== AI ROUTES ====================
    Route::post('/ai/voice-command', [AIController::class, 'handleVoice'])->name('ai.voice.command');
    Route::post('/ai/chat-support', [AIController::class, 'chatSupport'])->name('ai.chat');
    Route::post('/ai/dispatch-advisor', [AIController::class, 'dispatchAdvisor'])->name('ai.dispatch-advisor');
    Route::post('/ai/pickup-advisor', [AIController::class, 'pickupAdvisor'])->name('ai.pickup-advisor');
    Route::post('/ai/warehouse-copy', [AIController::class, 'warehouseCopy'])->name('ai.warehouse-copy');
    Route::post('/ai/parse-reminder', [AIController::class, 'parseReminder'])->name('ai.parse-reminder');
    Route::post('/ai/dashboard-brief', [AIController::class, 'dashboardBrief'])->name('ai.dashboard-brief');
    Route::post('/dispatch/calculate-price', [DispatchController::class, 'calculatePriceAjax'])->name('dispatch.calculate-price');
    Route::post('/dispatch/recommend-drivers', [DispatchController::class, 'getDriverRecommendations'])->name('dispatch.recommend-drivers');
    Route::post('/pickup/recommend-drivers', [PickupRequestController::class, 'getDriverRecommendations'])->name('pickup.recommend-drivers');

    // ==================== PDF GENERATION ====================
    Route::prefix('pdf')->name('pdf.')->group(function () {
        Route::get('/warehouse/{id}', [PdfController::class, 'downloadWarehouse'])->name('warehouse');
        Route::get('/dispatch/{id}', [PdfController::class, 'downloadDispatch'])->name('dispatch');
    });
    Route::get('/warehouses/{id}/pdf', [PdfController::class, 'downloadWarehouse'])->name('warehouses.pdf');
    Route::get('/dispatch/{id}/pdf', [PdfController::class, 'downloadDispatch'])->name('dispatch.pdf');

    // ==================== ROLE-BASED MODULAR ROUTES ====================
    require __DIR__ . '/roles/security.php';
    require __DIR__ . '/roles/client.php';
    require __DIR__ . '/roles/driver.php';
    require __DIR__ . '/roles/equipment.php';
    require __DIR__ . '/roles/property.php';
    require __DIR__ . '/roles/admin.php';
});

// ================================================================
// 4. FALLBACK ROUTE
// ================================================================
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
