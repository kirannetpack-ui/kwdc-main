<?php

// Opt-in schema setup runs in Vercel's build environment, never in HTTP requests.
$runMigrations = getenv('VERCEL_RUN_MIGRATIONS') === 'true';
$seedDemo = getenv('KWDC_SEED_DEMO') === 'true';
$validateLaunch = filter_var(getenv('APP_LAUNCH_READY'), FILTER_VALIDATE_BOOLEAN)
    && ! filter_var(getenv('PHASE_ONE_DEMO'), FILTER_VALIDATE_BOOLEAN);
if (getenv('VERCEL_ENV') !== 'production' || (! $runMigrations && ! $seedDemo && ! $validateLaunch)) {
    exit(0);
}

if (! getenv('DATABASE_URL')) {
    fwrite(STDERR, "A production DATABASE_URL is required for schema setup.\n");
    exit(1);
}

// Schema changes use Neon's direct connection rather than its transaction pool.
if ($directUrl = getenv('DATABASE_URL_UNPOOLED')) {
    putenv('DATABASE_URL='.$directUrl);
    $_ENV['DATABASE_URL'] = $directUrl;
    $_SERVER['DATABASE_URL'] = $directUrl;
    putenv('DB_POOLED=false');
    $_ENV['DB_POOLED'] = 'false';
    $_SERVER['DB_POOLED'] = 'false';
}

foreach (['bootstrap/cache', 'storage/framework/views', 'storage/framework/cache/data', 'storage/logs'] as $path) {
    $directory = __DIR__.'/../../'.$path;
    if (! is_dir($directory)) {
        mkdir($directory, 0700, true);
    }
}

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if ($runMigrations && Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]) !== 0) {
    fwrite(STDERR, Illuminate\Support\Facades\Artisan::output());
    exit(1);
}

if ($validateLaunch) {
    if (Illuminate\Support\Facades\Artisan::call('app:production-preflight', ['--check-database' => true]) !== 0) {
        fwrite(STDERR, Illuminate\Support\Facades\Artisan::output());
        exit(1);
    }
}

if ($seedDemo) {
    if (! config('kwdc.demo') || App\Models\User::where('email', 'not like', '%@kwdc.test')->exists()) {
        fwrite(STDERR, "Demo seeding requires demo mode and a database without real accounts.\n");
        exit(1);
    }
    Illuminate\Support\Facades\DB::transaction(function () {
        $result = Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => Database\Seeders\NepaliDemoSeeder::class, '--force' => true,
        ]);
        if ($result !== 0) {
            throw new RuntimeException('Demo sample data setup failed.');
        }
    });
    echo "Demo sample accounts and records prepared.\n";
}

echo "Deployment database preparation complete.\n";
