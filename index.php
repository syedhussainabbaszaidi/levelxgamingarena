<?php

/**
 * Front controller for the flat cPanel layout.
 *
 * On the server the whole Laravel application lives in public_html/ (see
 * .github/workflows/deploy.yml), so the paths below point at __DIR__ instead
 * of __DIR__.'/..'. Local development keeps using public/index.php via
 * `php artisan serve`.
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
