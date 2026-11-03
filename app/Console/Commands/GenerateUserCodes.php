<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateUserCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:generate-codes {--force : Force regenerate even if code exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate unique user codes for all users (CLT-YEAR-0000 format)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        
        $users = $force ? User::all() : User::whereNull('user_code')->get();
        
        if ($users->isEmpty()) {
            $this->info('No users need user codes.');
            return 0;
        }
        
        $this->info("Generating codes for {$users->count()} users...");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        $counts = [
            'CLT' => 0,
            'DRV' => 0,
            'EQO' => 0,
            'USR' => 0,
        ];
        
        foreach ($users as $user) {
            // Temporarily disable auto-generation to avoid conflicts
            $user->user_code = $this->generateCodeForUser($user);
            $user->saveQuietly(); // Save without firing events
            
            $prefix = substr($user->user_code, 0, 3);
            $counts[$prefix]++;
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->table(
            ['User Type', 'Codes Generated'],
            [
                ['Clients (CLT)', $counts['CLT']],
                ['Drivers (DRV)', $counts['DRV']],
                ['Equipment Owners (EQO)', $counts['EQO']],
                ['Others (USR)', $counts['USR']],
            ]
        );
        
        $this->info('User codes generated successfully!');
        
        return 0;
    }
    
    /**
     * Generate a code for a specific user.
     */
    private function generateCodeForUser($user)
    {
        $prefix = match(true) {
            $user->is_client || $user->role === 'client' => 'CLT',
            $user->is_driver || $user->role === 'driver' => 'DRV',
            $user->is_equipment_owner || $user->role === 'equipment_owner' => 'EQO',
            default => 'USR',
        };
        
        $year = now()->year;
        
        // Get the highest sequence number for this prefix and year
        $lastCode = DB::table('users')
            ->where('user_code', 'like', "{$prefix}-{$year}-%")
            ->orderBy('user_code', 'desc')
            ->value('user_code');
        
        if ($lastCode) {
            $lastSeq = (int) substr($lastCode, -4);
            $newSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newSeq = '0001';
        }
        
        return "{$prefix}-{$year}-{$newSeq}";
    }
}