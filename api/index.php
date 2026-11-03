<?php

// Only temporary caches belong on the function's writable filesystem.
$runtime = sys_get_temp_dir().'/kwdc';
foreach (['views', 'fonts', 'framework/cache/data', 'framework/sessions', 'logs'] as $directory) {
    $path = $runtime.'/'.$directory;
    if (! is_dir($path)) {
        mkdir($path, 0700, true);
    }
}

putenv('VIEW_COMPILED_PATH='.$runtime.'/views');
putenv('APP_PACKAGES_CACHE='.$runtime.'/packages.php');
putenv('APP_SERVICES_CACHE='.$runtime.'/services.php');
$_ENV['LARAVEL_STORAGE_PATH'] = $runtime;
$_SERVER['LARAVEL_STORAGE_PATH'] = $runtime;

require __DIR__.'/../public/index.php';
