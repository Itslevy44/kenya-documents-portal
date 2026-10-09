<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Support both InfinityFree subfolder structure (/htdocs/laravel_app) and standard local structure
$basePath = file_exists(__DIR__.'/laravel_app/vendor/autoload.php')
    ? __DIR__.'/laravel_app'
    : __DIR__.'/..';

// Ensure required runtime storage directories exist on shared hosting
$storageDirs = [
    $basePath.'/storage/framework/views',
    $basePath.'/storage/framework/sessions',
    $basePath.'/storage/framework/cache/data',
    $basePath.'/storage/app/documents',
    $basePath.'/storage/logs',
];
foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $basePath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$app = require_once $basePath.'/bootstrap/app.php';

// When deployed inside htdocs, the public directory is the webroot (__DIR__)
if ($basePath === __DIR__.'/laravel_app') {
    $app->usePublicPath(__DIR__);
}

$app->handleRequest(Request::capture());
