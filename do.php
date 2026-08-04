<?php
/**
 * API Proxy - bypasses server-level WAF/mod_security
 * 
 * All /api/* requests are rewritten to this file by .htaccess
 * This file bootstraps Laravel and handles the request internally
 */

define('LARAVEL_START', microtime(true));
define('HOSTING_MODE', true);

// Preserve original request URI for Laravel routing
$origUri = $_SERVER['REQUEST_URI'] ?? '';
if (str_starts_with($origUri, '/do.php') || $origUri === '/do.php') {
    // Request was rewritten, check for original path in redirect
    $target = $_SERVER['REDIRECT_URL'] ?? $_SERVER['ORIG_REQUEST_URI'] ?? '';
    if ($target) {
        $_SERVER['REQUEST_URI'] = $target;
    }
}

// Load Laravel
require __DIR__.'/engine/vendor/autoload.php';
$app = require_once __DIR__.'/engine/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
