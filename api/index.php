<?php

/**
 * Vercel Serverless Entrypoint for Marmoyo Smart-Dine (Laravel)
 * Automatically prepares writable /tmp filesystem directories and forwards
 * incoming HTTP requests to Laravel front controller.
 */

$storagePath = '/tmp/storage';
$dirs = [
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
    $storagePath . '/app',
    $storagePath . '/app/public',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Set environment overrides for Vercel Serverless
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

if (!getenv('VIEW_COMPILED_PATH')) {
    putenv('VIEW_COMPILED_PATH=' . $storagePath . '/framework/views');
}

// Forward to Laravel public/index.php
require __DIR__ . '/../public/index.php';

