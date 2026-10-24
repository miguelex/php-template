<?php

declare(strict_types=1);

/**
 * Definición de rutas de la aplicación.
 * $router está disponible desde public/index.php.
 *
 * Ejemplos:
 *   $router->get('/', [HomeController::class, 'index']);
 *   $router->post('/contacto', [ContactController::class, 'store']);
 */

use App\Core\Router;

/** @var Router $router */

// ── Ejemplo: ruta home ────────────────────────────────────────────────────────
$router->get('/', function (Router $router): void {
    $router->render('home', [
        'title'   => $_ENV['APP_NAME'] ?? 'PHP Template',
        'message' => '¡Plantilla PHP lista!',
    ]);
});

// ── Ejemplo: parámetros de ruta ────────────────────────────────────────────────
// Los {placeholders} llegan como segundo argumento asociativo del handler.
//
// $router->get('/users/{id}', function (Router $router, array $params): void {
//     $id = (int) $params['id'];
//     // ...
// });

// ── Ejemplo: middleware por ruta ───────────────────────────────────────────────
// $router->get('/dashboard', [DashboardController::class, 'index'])
//        ->middleware(App\Middleware\AuthMiddleware::check(...));
