<?php

namespace App\Services;

use App\Mail\ActivationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ActivationCodeService
{
    public function generateFor(User $user): string
    {
        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'activation_code_hash' => Hash::make($code),
            'activation_expires_at' => now()->addMinutes(30),
        ])->save();

        return $code;
    }

    public function send(User $user): bool
    {
        $previousHash = $user->activation_code_hash;
        $previousExpiry = $user->activation_expires_at;
        $code = $this->generateFor($user);

        try {
            if (! app()->environment('testing') && in_array(config('mail.default'), ['log', 'array', 'failover'], true)) {
                throw new \RuntimeException('A delivery transport is required for activation mail.');
            }
            Mail::to($user->email, $user->name)->send(new ActivationCodeMail($user, $code));

            return true;
        } catch (\Throwable $e) {
            // A failed resend must not invalidate the code already in the inbox.
            User::whereKey($user->id)
                ->where('activation_code_hash', $user->activation_code_hash)
                ->update([
                    'activation_code_hash' => $previousHash,
                    'activation_expires_at' => $previousExpiry,
                ]);
            Log::warning('Activation email delivery failed.', [
                'user_id' => $user->id,
                'exception_type' => get_class($e),
            ]);
            session()->flash('status', 'Email delivery is temporarily unavailable. Please try sending a new code shortly.');

            return false;
        }
    }

    public function verify(User $user, string $code): bool
    {
        if (!$user->activation_code_hash || !$user->activation_expires_at) {
            return false;
        }

        if ($user->activation_expires_at->isPast()) {
            return false;
        }

        return Hash::check($code, $user->activation_code_hash);
    }
}
