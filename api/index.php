<?php

/**
 * Vercel Serverless Entrypoint for Marmoyo Smart-Dine (Laravel)
 * Automatically prepares writable /tmp filesystem directories and forwards
 * incoming HTTP requests to Laravel front controller.
 */

$storagePath = '/tmp/storage';
$cachePath = $storagePath . '/framework/cache';

// Create ephemeral /tmp storage directories only on cold starts to save disk stat calls on warm lambdas
if (!is_dir($storagePath . '/framework/views')) {
    $dirs = [
        $storagePath . '/framework/views',
        $cachePath,
        $cachePath . '/data',
        $storagePath . '/framework/sessions',
        $storagePath . '/logs',
        $storagePath . '/app',
        $storagePath . '/app/public',
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}

// Set environment overrides for Vercel Serverless
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

// Relocate all bootstrap cache and view compilation files to writable /tmp
putenv('APP_CONFIG_CACHE=' . $cachePath . '/config.php');
$_ENV['APP_CONFIG_CACHE'] = $cachePath . '/config.php';
$_SERVER['APP_CONFIG_CACHE'] = $cachePath . '/config.php';

putenv('APP_EVENTS_CACHE=' . $cachePath . '/events.php');
$_ENV['APP_EVENTS_CACHE'] = $cachePath . '/events.php';
$_SERVER['APP_EVENTS_CACHE'] = $cachePath . '/events.php';

putenv('APP_PACKAGES_CACHE=' . $cachePath . '/packages.php');
$_ENV['APP_PACKAGES_CACHE'] = $cachePath . '/packages.php';
$_SERVER['APP_PACKAGES_CACHE'] = $cachePath . '/packages.php';

putenv('APP_ROUTES_CACHE=' . $cachePath . '/routes.php');
$_ENV['APP_ROUTES_CACHE'] = $cachePath . '/routes.php';
$_SERVER['APP_ROUTES_CACHE'] = $cachePath . '/routes.php';

putenv('APP_SERVICES_CACHE=' . $cachePath . '/services.php');
$_ENV['APP_SERVICES_CACHE'] = $cachePath . '/services.php';
$_SERVER['APP_SERVICES_CACHE'] = $cachePath . '/services.php';

putenv('VIEW_COMPILED_PATH=' . $storagePath . '/framework/views');
$_ENV['VIEW_COMPILED_PATH'] = $storagePath . '/framework/views';
$_SERVER['VIEW_COMPILED_PATH'] = $storagePath . '/framework/views';

// Forward to Laravel public/index.php with fallback exception handling
try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    $debug = getenv('APP_DEBUG') === 'true' || (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true');
    http_response_code(500);
    if ($debug) {
        header('Content-Type: text/plain; charset=UTF-8');
        echo "=== Vercel Serverless Error ===\n";
        echo $e->getMessage() . "\n\n";
        echo "File: " . $e->getFile() . " (Line " . $e->getLine() . ")\n\n";
        echo "Stack Trace:\n" . $e->getTraceAsString();
    } else {
        echo "<h1>500 Internal Server Error</h1><p>Terjadi kesalahan saat memproses permintaan pada server.</p>";
    }
    exit(1);
}

