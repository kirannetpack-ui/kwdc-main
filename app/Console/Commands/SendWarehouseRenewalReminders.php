<?php

namespace App\Console\Commands;

use App\Mail\ContractExpiryReminder;
use App\Models\WarehouseRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendWarehouseRenewalReminders extends Command
{
    protected $signature = 'warehouse:send-renewal-reminders';

    protected $description = 'Send warehouse agreement renewal notices two calendar months before expiry';

    public function handle(): int
    {
        $failed = false;
        $today = today();

        WarehouseRequest::where('status', 'assigned')
            ->where(fn ($query) => $query->whereDate('contract_end_date', '>', $today)
                ->orWhere(fn ($legacy) => $legacy->whereNull('contract_end_date')->whereDate('contract_expires_at', '>', $today)))
            ->chunkById(100, function ($agreements) use ($today, &$failed) {
                foreach ($agreements as $agreement) {
                    try {
                        Cache::lock("warehouse-renewal:{$agreement->id}", 300)->get(function () use ($agreement, $today, &$failed) {
                            $agreement = $agreement->fresh('client');
                            $end = $agreement?->agreementEnd();

                            if (!$end || $agreement->status !== 'assigned' || $agreement->renewalChoice()
                                || $agreement->renewal_reminded_for === $end->toDateString()
                                || $today->lt($end->copy()->subMonthsNoOverflow(2))
                                || $today->gte($agreement->renewalDeadline())) {
                                return;
                            }

                            if (!$agreement->client?->email) {
                                $this->warn("Warehouse agreement {$agreement->id} has no client email.");
                                $failed = true;
                                return;
                            }

                            Mail::to($agreement->client->email)->send(new ContractExpiryReminder(
                                $agreement,
                                (int) $today->diffInDays($end),
                                true
                            ));

                            $agreement->forceFill(['renewal_reminded_for' => $end->toDateString()])->save();
                        });
                    } catch (\Throwable $exception) {
                        report($exception);
                        $this->error("Could not send the renewal reminder for agreement {$agreement->id}.");
                        $failed = true;
                    }
                }
            });

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
