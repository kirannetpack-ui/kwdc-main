<?php

// Opt-in schema setup runs in Vercel's build environment, never in HTTP requests.
if (getenv('VERCEL_ENV') !== 'production' || getenv('VERCEL_RUN_MIGRATIONS') !== 'true') {
    exit(0);
}

if (! getenv('DATABASE_URL')) {
    fwrite(STDERR, "A production DATABASE_URL is required for schema setup.\n");
    exit(1);
}

foreach (['bootstrap/cache', 'storage/framework/views', 'storage/framework/cache/data', 'storage/logs'] as $path) {
    $directory = __DIR__.'/../../'.$path;
    if (! is_dir($directory)) {
        mkdir($directory, 0700, true);
    }
}

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
exit($app->handleCommand(new Symfony\Component\Console\Input\ArrayInput([
    'command' => 'migrate', '--force' => true,
])));
