<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

try {
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

    // Automatically purge stale CI cache files on shared hosting
    $staleCacheFiles = glob($basePath.'/bootstrap/cache/*.php');
    if ($staleCacheFiles) {
        foreach ($staleCacheFiles as $cacheFile) {
            @unlink($cacheFile);
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

    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    echo "<div style='font-family:sans-serif;max-width:800px;margin:50px auto;padding:24px;border:1px solid #e0e0e0;border-radius:8px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.1);'>";
    echo "<h2 style='color:#c62828;margin-top:0;'>Kenya Docs — Application Error</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (line " . $e->getLine() . ")</p>";
    echo "<pre style='background:#f5f5f5;padding:12px;border-radius:4px;overflow:auto;font-size:13px;max-height:400px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
    exit(1);
}
