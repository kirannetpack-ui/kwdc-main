<?php

use Illuminate\Support\Facades\Artisan;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

Artisan::call('migrate', ['--force' => true, '--path' => 'database/migrations/0001_01_01_000001_create_cache_table.php']);
Artisan::call('migrate', ['--force' => true, '--path' => 'database/migrations/0001_01_01_000001_create_sessions_table.php']);

echo "Sessions table migrated.\n";