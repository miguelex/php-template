<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/vendor/autoload.php';

use App\Core\App;
use App\Core\Log;
use App\Core\Router;
use App\Exceptions\RouteNotFoundException;

// ── Bootstrap ─────────────────────────────────────────────────────────────────
App::boot();

$debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

// Páginas de error enriquecidas en desarrollo (Whoops, solo require-dev).
if ($debug && class_exists(\Whoops\Run::class)) {
    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    $whoops->register();
}

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
    Log::error($e->getMessage(), ['exception' => $e]);

    // En desarrollo, dejar que Whoops renderice el error completo.
    if ($debug) {
        throw $e;
    }

    http_response_code(500);
    $router->render('errors/500', ['title' => 'Error del servidor']);
}
