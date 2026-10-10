<?php

/**
 * Entry point for Vercel Serverless Function
 * Forwards requests to Laravel's public/index.php with /tmp writable storage fix
 */

// Initialize writable storage directory structure in /tmp
$storagePath = '/tmp/storage';
$subDirectories = [
    '/app',
    '/app/public',
    '/framework',
    '/framework/views',
    '/framework/cache',
    '/framework/cache/data',
    '/framework/sessions',
    '/logs',
];

foreach ($subDirectories as $subDir) {
    $dir = $storagePath . $subDir;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Ensure compiled views directory exists
if (!is_dir('/tmp/views')) {
    mkdir('/tmp/views', 0755, true);
}

// Forward to Laravel public/index.php
require __DIR__ . '/../public/index.php';
