<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AdminEmailService
{
    protected $adminEmail;

    public function __construct()
    {
        $this->adminEmail = config('app.admin_email', 'admin@ktmwdc.com');
    }

    /**
     * Send admin notification email using HTML template
     */
    public function notifyAdmin($subject, $content, $data = [])
    {
        try {
            Log::info('Attempting to send admin email', [
                'to' => $this->adminEmail,
                'subject' => $subject,
            ]);

            // Send email with view
            Mail::send('emails.admin-notification', [
                'subject' => $subject,
                'content' => $content,
                'data' => $data,
                'adminEmail' => $this->adminEmail,
            ], function ($mail) use ($subject) {
                $mail->to($this->adminEmail)
                    ->subject('[KTM-WDC] ' . $subject);
            });

            Log::info('Admin notification sent successfully', ['subject' => $subject]);
            return true;

        } catch (\Exception $e) {
            Log::error('Admin notification failed: ' . $e->getMessage(), [
                'subject' => $subject,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send new user registration notification
     */
    public function notifyNewUser($user)
    {
        $subject = 'New User Registration';
        $content = "A new user has registered on KTM-WDC platform.";
        $data = [
            'user_name' => $user->name ?? 'N/A',
            'user_email' => $user->email ?? 'N/A',
            'user_role' => $user->role ?? 'N/A',
            'user_code' => $user->user_code ?? 'N/A',
            'registered_at' => now()->format('F d, Y H:i'),
        ];
        
        return $this->notifyAdmin($subject, $content, $data);
    }

    /**
     * Send new warehouse registration notification
     */
    public function notifyNewWarehouse($warehouse)
    {
        $subject = 'New Warehouse Registered';
        $content = "A new warehouse has been registered on KTM-WDC platform.";
        $data = [
            'warehouse_name' => $warehouse->name ?? 'N/A',
            'warehouse_location' => $warehouse->location ?? 'N/A',
            'owner_name' => $warehouse->user->name ?? 'N/A',
            'owner_email' => $warehouse->user->email ?? 'N/A',
            'area' => ($warehouse->area_sqft ?? 0) . ' sq ft',
            'status' => $warehouse->status ?? 'pending',
            'registered_at' => now()->format('F d, Y H:i'),
        ];
        
        return $this->notifyAdmin($subject, $content, $data);
    }

    /**
     * Send new dispatch notification
     */
    public function notifyNewDispatch($dispatch)
    {
        $subject = 'New Dispatch Created';
        $content = "A new dispatch has been created on KTM-WDC platform.";
        $data = [
            'dispatch_id' => $dispatch->id ?? 'N/A',
            'tracking_id' => $dispatch->tracking_id ?? 'N/A',
            'client_name' => $dispatch->client->name ?? 'N/A',
            'driver_name' => $dispatch->driver->name ?? 'N/A',
            'total_distance' => ($dispatch->total_distance ?? 0) . ' km',
            'total_price' => 'रू ' . number_format($dispatch->base_price ?? 0, 2),
            'status' => $dispatch->status ?? 'pending',
            'created_at' => $dispatch->created_at->format('F d, Y H:i') ?? now()->format('F d, Y H:i'),
        ];
        
        return $this->notifyAdmin($subject, $content, $data);
    }

    /**
     * Send new pickup notification
     */
    public function notifyNewPickup($pickup)
    {
        $subject = 'New Pickup Created';
        $content = "A new pickup request has been created on KTM-WDC platform.";
        $data = [
            'pickup_id' => $pickup->id ?? 'N/A',
            'tracking_id' => $pickup->tracking_id ?? 'N/A',
            'client_name' => $pickup->client->name ?? 'N/A',
            'driver_name' => $pickup->driver->name ?? 'N/A',
            'total_distance' => ($pickup->total_distance ?? 0) . ' km',
            'total_price' => 'रू ' . number_format($pickup->total_price ?? 0, 2),
            'status' => $pickup->status ?? 'pending',
            'created_at' => $pickup->created_at->format('F d, Y H:i') ?? now()->format('F d, Y H:i'),
        ];
        
        return $this->notifyAdmin($subject, $content, $data);
    }

    /**
     * Send payment received notification
     */
    public function notifyPaymentReceived($transaction, $invoice)
    {
        $subject = 'Payment Received';
        $content = "A payment has been received on KTM-WDC platform.";
        $data = [
            'transaction_id' => $transaction->id ?? 'N/A',
            'invoice_number' => $invoice->invoice_number ?? 'N/A',
            'amount' => 'रू ' . number_format($transaction->amount ?? 0, 2),
            'payment_method' => ucfirst($transaction->payment_method ?? 'N/A'),
            'user_name' => $transaction->user->name ?? 'N/A',
            'user_email' => $transaction->user->email ?? 'N/A',
            'payment_date' => now()->format('F d, Y H:i'),
        ];
        
        return $this->notifyAdmin($subject, $content, $data);
    }

    /**
     * Send test email
     */
    public function sendTestEmail()
    {
        $subject = 'Test Email - KTM-WDC Configuration';
        $content = "This is a test email from KTM-WDC platform. If you see this, your email configuration is working correctly.";
        $data = [
            'timestamp' => now()->format('F d, Y H:i:s'),
            'app_url' => config('app.url'),
            'environment' => config('app.env'),
            'server' => $_SERVER['SERVER_NAME'] ?? 'localhost',
        ];
        
        return $this->notifyAdmin($subject, $content, $data);
    }
}
