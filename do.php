<?php
/**
 * API Proxy - bypasses server-level WAF/mod_security
 * 
 * Frontend sends requests to /do.php/api/* instead of /api/*
 * This file strips /do.php prefix and bootstraps Laravel
 */

define('LARAVEL_START', microtime(true));
define('HOSTING_MODE', true);

// Strip /do.php prefix from REQUEST_URI so Laravel routes correctly
// e.g. /do.php/api/posts → /api/posts
$uri = $_SERVER['REQUEST_URI'] ?? '';
if (preg_match('#^/do\.php(/.*)?$#', $uri, $m)) {
    $_SERVER['REQUEST_URI'] = $m[1] ?? '/';
}

require __DIR__.'/engine/vendor/autoload.php';
$app = require_once __DIR__.'/engine/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
