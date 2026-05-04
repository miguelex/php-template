<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\RouteNotFoundException;

/**
 * Router MVC sencillo.
 *
 * Uso en public/index.php:
 *   $router->get('/ruta', [MiController::class, 'metodo']);
 *   $router->post('/ruta', [MiController::class, 'metodo']);
 *   $router->dispatch();
 */
final class Router
{
    /** @var array<string, array<string, callable|array<int,string>>> */
    private array $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'PATCH'  => [],
        'DELETE' => [],
    ];

    /** @var list<callable> */
    private array $middleware = [];

    // ── Registro de rutas ────────────────────────────────────────────────────

    /** @param callable|array<int,string> $handler */
    public function get(string $uri, callable|array $handler): self
    {
        return $this->addRoute('GET', $uri, $handler);
    }

    /** @param callable|array<int,string> $handler */
    public function post(string $uri, callable|array $handler): self
    {
        return $this->addRoute('POST', $uri, $handler);
    }

    /** @param callable|array<int,string> $handler */
    public function put(string $uri, callable|array $handler): self
    {
        return $this->addRoute('PUT', $uri, $handler);
    }

    /** @param callable|array<int,string> $handler */
    public function patch(string $uri, callable|array $handler): self
    {
        return $this->addRoute('PATCH', $uri, $handler);
    }

    /** @param callable|array<int,string> $handler */
    public function delete(string $uri, callable|array $handler): self
    {
        return $this->addRoute('DELETE', $uri, $handler);
    }

    // ── Middleware global ────────────────────────────────────────────────────

    public function use(callable $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    // ── Dispatch ─────────────────────────────────────────────────────────────

    public function dispatch(): void
    {
        $method = $this->resolveMethod();
        $uri    = $this->resolveUri();

        $handler = $this->routes[$method][$uri] ?? null;

        if ($handler === null) {
            throw new RouteNotFoundException(
                "No route found for {$method} {$uri}",
            );
        }

        // Ejecutar middleware global
        foreach ($this->middleware as $mw) {
            $mw($this);
        }

        // Llamar al handler: closure o [Controller::class, 'method']
        if (is_array($handler)) {
            [$class, $methodName] = $handler;
            (new $class())->$methodName($this);
        } else {
            $handler($this);
        }
    }

    // ── Renderizado de vistas ─────────────────────────────────────────────────

    /**
     * Renderiza una vista dentro del layout por defecto.
     *
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data = [], string $layout = 'default'): void
    {
        // Extraer variables para la vista
        extract($data, EXTR_SKIP);

        ob_start();
        $viewFile = BASE_PATH . "/views/{$view}.php";

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$viewFile}");
        }

        include $viewFile;
        $content = ob_get_clean();

        $layoutFile = BASE_PATH . "/views/layouts/{$layout}.php";

        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Layout not found: {$layoutFile}");
        }

        include $layoutFile;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function redirect(string $uri, int $status = 302): never
    {
        http_response_code($status);
        header("Location: {$uri}");
        exit;
    }

    // ── Privados ─────────────────────────────────────────────────────────────

    /** @param callable|array<int,string> $handler */
    private function addRoute(string $method, string $uri, callable|array $handler): self
    {
        $this->routes[$method][$uri] = $handler;

        return $this;
    }

    private function resolveMethod(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Soporte para _method override (formularios HTML)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper($_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $override;
            }
        }

        return $method;
    }

    private function resolveUri(): string
    {
        $uri = $_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI'] ?? '/';

        // Eliminar query string
        $uri = strtok($uri, '?') ?: '/';

        // Normalizar trailing slash (salvo raíz)
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        return $uri;
    }
}
