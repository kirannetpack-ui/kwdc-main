<?php

namespace App\Services;

use App\Models\BusinessProfile;
use Illuminate\Support\Arr;

class BusinessSettings
{
    private ?array $stored = null;

    public function stored(): array
    {
        return $this->stored ??= BusinessProfile::find(1)?->details ?? [];
    }

    public function company(): array
    {
        return array_replace(config('company'), Arr::only($this->stored(), ['name', 'address', 'phone', 'email', 'pan']));
    }

    public function banking(): array
    {
        $details = $this->stored();
        if (!$details) {
            return config('payment.banking');
        }

        return [
            'accounts' => empty($details['bank_enabled']) ? [] : [[
                'bank_name' => $details['bank_name'],
                'account_name' => $details['account_name'],
                'account_number' => $details['account_number'],
                'branch' => $details['branch'] ?? null,
            ]],
            'qr' => [
                'image_url' => empty($details['qr_enabled']) ? null : $details['qr_url'],
                'merchant_name' => $details['merchant_name'] ?? null,
            ],
        ];
    }
}
