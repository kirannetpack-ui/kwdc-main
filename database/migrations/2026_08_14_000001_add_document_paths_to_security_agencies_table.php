<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('security_agencies', function (Blueprint $table) {
            $table->string('registration_certificate_path')->nullable()->after('certifications');
            $table->string('license_certificate_path')->nullable()->after('registration_certificate_path');
            $table->string('pan_vat_certificate_path')->nullable()->after('license_certificate_path');
        });
    }

    public function down(): void
    {
        Schema::table('security_agencies', function (Blueprint $table) {
            $table->dropColumn([
                'registration_certificate_path',
                'license_certificate_path',
                'pan_vat_certificate_path',
            ]);
        });
    }
};
