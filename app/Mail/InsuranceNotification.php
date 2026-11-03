<?php

namespace App\Mail;

use App\Models\WarehouseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InsuranceNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $request;

    public function __construct(WarehouseRequest $request)
    {
        $this->request = $request;
    }

    public function build()
    {
        $mail = $this->subject('New Warehouse Request – Insurance Documents')
                    ->view('emails.insurance-notification');

        // Attach invoice and packing list if they exist
        if ($this->request->invoice_path && Storage::disk('private_uploads')->exists($this->request->invoice_path)) {
            $mail->attach(Storage::disk('private_uploads')->path($this->request->invoice_path));
        }
        if ($this->request->packing_list_path && Storage::disk('private_uploads')->exists($this->request->packing_list_path)) {
            $mail->attach(Storage::disk('private_uploads')->path($this->request->packing_list_path));
        }

        return $mail;
    }
}
