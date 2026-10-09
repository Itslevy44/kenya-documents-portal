<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Register fatal shutdown handler to catch uncaught PHP errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo "<div style='font-family:sans-serif;max-width:900px;margin:30px auto;padding:24px;border:2px solid #c62828;border-radius:8px;background:#fff;'>";
        echo "<h2 style='color:#c62828;margin-top:0;'>PHP Fatal Shutdown Error</h2>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($error['message']) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($error['file']) . " (line " . $error['line'] . ")</p>";
        echo "</div>";
    }
});

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Quick diagnostic endpoint: /?ping=1
if (isset($_GET['ping'])) {
    header('Content-Type: text/plain');
    echo "PONG: PHP " . PHP_VERSION . "\n";
    echo "Base dir: " . __DIR__ . "\n";
    echo "laravel_app exists: " . (is_dir(__DIR__ . '/laravel_app') ? 'YES' : 'NO') . "\n";
    echo "vendor autoload exists: " . (file_exists(__DIR__ . '/laravel_app/vendor/autoload.php') ? 'YES' : 'NO') . "\n";
    echo "bootstrap app exists: " . (file_exists(__DIR__ . '/laravel_app/bootstrap/app.php') ? 'YES' : 'NO') . "\n";
    echo ".env exists: " . (file_exists(__DIR__ . '/laravel_app/.env') ? 'YES' : 'NO') . "\n";
    exit;
}

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

    // Ensure APP_KEY exists and is a valid base64 key
    $envFile = $basePath . '/.env';
    if (file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        if (!preg_match('/^APP_KEY=base64:[A-Za-z0-9+\/]{43}=/m', $envContent)) {
            $newKey = 'base64:' . base64_encode(random_bytes(32));
            if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
                $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $newKey, $envContent);
            } else {
                $envContent .= "\nAPP_KEY=" . $newKey . "\n";
            }
            @file_put_contents($envFile, $envContent);
            putenv("APP_KEY={$newKey}");
            $_ENV['APP_KEY'] = $newKey;
            $_SERVER['APP_KEY'] = $newKey;
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
    echo "<div style='font-family:sans-serif;max-width:900px;margin:30px auto;padding:24px;border:1px solid #e0e0e0;border-radius:8px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.1);'>";
    echo "<h2 style='color:#c62828;margin-top:0;'>Kenya Docs — Application Error</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (line " . $e->getLine() . ")</p>";
    echo "<pre style='background:#f5f5f5;padding:12px;border-radius:4px;overflow:auto;font-size:13px;max-height:400px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
    exit(1);
}
