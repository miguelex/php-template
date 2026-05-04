<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/vendor/autoload.php';

use App\Core\App;
use App\Core\Router;
use App\Exceptions\RouteNotFoundException;

// ── Bootstrap ─────────────────────────────────────────────────────────────────
App::boot();

// ── Router ────────────────────────────────────────────────────────────────────
$router = new Router();

// Rutas definidas en config/routes.php
require BASE_PATH . '/config/routes.php';

// ── Dispatch ──────────────────────────────────────────────────────────────────
try {
    $router->dispatch();
} catch (RouteNotFoundException) {
    http_response_code(404);
    $router->render('errors/404', ['title' => 'Página no encontrada']);
} catch (\Throwable $e) {
    http_response_code(500);

    if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
        echo '<pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>';
    } else {
        $router->render('errors/500', ['title' => 'Error del servidor']);
    }
}
