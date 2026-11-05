<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// Ensure storage is mapped to writable /tmp on serverless environments (Vercel)
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || env('VERCEL')) {
    $storage = PHP_OS_FAMILY === 'Windows' ? (sys_get_temp_dir() . '/marmoyo_storage') : '/tmp/storage';
    foreach ([
        $storage . '/framework/views',
        $storage . '/framework/cache',
        $storage . '/framework/sessions',
        $storage . '/logs',
        $storage . '/app',
    ] as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
    $app->useStoragePath($storage);
}

return $app;
