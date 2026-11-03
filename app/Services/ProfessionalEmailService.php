<?php

namespace App\Services;

use App\Models\User;
use App\Models\DispatchOrder;
use App\Models\PickupRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProfessionalEmailService
{
    // Professional email templates with company branding
    public function sendDispatchCreated(DispatchOrder $dispatch, array $recipients)
    {
        $dispatch = $this->ensureTrackingToken($dispatch);
        $pdf = $this->generateDispatchSummary($dispatch);
        
        foreach ($recipients as $recipient) {
            Mail::send('emails.professional.dispatch_created', [
                'dispatch' => $dispatch,
                'recipient_name' => $recipient['name'],
                'tracking_url' => route('dispatch.track', [
                    'id' => $dispatch->id,
                    'token' => $dispatch->tracking_token,
                ]),
                'support_phone' => config('app.support_phone', '01-5551234'),
                'company_logo' => config('app.logo_url'),
            ], function ($message) use ($recipient, $pdf, $dispatch) {
                $message->to($recipient['email'], $recipient['name'])
                        ->subject('[KTM-WDC] Dispatch #' . $dispatch->invoice_no . ' Created')
                        ->attachData($pdf->output(), 'dispatch_summary_' . $dispatch->id . '.pdf', [
                            'mime' => 'application/pdf',
                        ]);
            });
        }
    }
    
    public function generateDispatchSummary(DispatchOrder $dispatch)
    {
        $data = [
            'dispatch' => $dispatch,
            'stops' => $dispatch->stops,
            'company' => [
                'name' => 'KTM Warehouse & Distribution Center',
                'pan' => '123456789',
                'address' => 'Kathmandu, Nepal',
                'phone' => '01-5551234',
                'email' => 'info@ktmwdc.com',
                'website' => 'www.ktmwdc.com'
            ],
            'generated_date' => now()->format('F d, Y H:i')
        ];
        
        return Pdf::loadView('pdf.dispatch_summary', $data);
    }
    
    public function sendPaymentReceipt(User $user, $transaction, $pdf)
    {
        Mail::send('emails.professional.payment_receipt', [
            'user' => $user,
            'transaction' => $transaction,
            'receipt_no' => $transaction->receipt_no,
        ], function ($message) use ($user, $transaction, $pdf) {
            $message->to($user->email, $user->name)
                    ->subject('Payment Receipt from KTM-WDC')
                    ->attachData($pdf->output(), 'receipt_' . $transaction->id . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
        });
    }

    private function ensureTrackingToken(DispatchOrder $dispatch): DispatchOrder
    {
        if (!$dispatch->tracking_enabled || blank($dispatch->tracking_token)) {
            $dispatch->forceFill([
                'tracking_enabled' => true,
                'tracking_token' => $dispatch->tracking_token ?: Str::random(40),
            ])->save();
        }

        return $dispatch->refresh();
    }
}
