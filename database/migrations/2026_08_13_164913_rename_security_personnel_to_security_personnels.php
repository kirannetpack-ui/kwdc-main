<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::rename('security_personnel', 'security_personnels');
    }

    public function down()
    {
        Schema::rename('security_personnels', 'security_personnel');
    }
};