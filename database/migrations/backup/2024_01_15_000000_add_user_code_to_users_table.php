<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_code')->unique()->nullable()->after('id');
            $table->index('user_code');
        });

        // Backfill existing users with generated codes
        $users = DB::table('users')->whereNull('user_code')->get();
        
        foreach ($users as $user) {
            $prefix = match($user->role) {
                'client', 'is_client' => 'CLT',
                'driver', 'is_driver' => 'DRV',
                'equipment_owner', 'is_equipment_owner' => 'EQO',
                default => 'USR',
            };
            
            $year = date('Y');
            
            // Get the highest sequence number for this prefix and year
            $lastCode = DB::table('users')
                ->where('user_code', 'like', "{$prefix}-{$year}-%")
                ->orderBy('user_code', 'desc')
                ->value('user_code');
            
            if ($lastCode) {
                $lastSeq = intval(substr($lastCode, -4));
                $newSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newSeq = '0001';
            }
            
            $userCode = "{$prefix}-{$year}-{$newSeq}";
            
            DB::table('users')
                ->where('id', $user->id)
                ->update(['user_code' => $userCode]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_code');
        });
    }
};