<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\RouteNotFoundException;

/**
 * Router MVC sencillo con soporte de parámetros y middleware por ruta.
 *
 * Uso en config/routes.php:
 *   $router->get('/', [HomeController::class, 'index']);
 *   $router->get('/users/{id}', [UserController::class, 'show']);
 *   $router->post('/posts', [PostController::class, 'store'])
 *          ->middleware(AuthMiddleware::check(...));
 *   $router->dispatch();
 *
 * Los parámetros ({id}, {slug}, ...) se pasan al handler como segundo
 * argumento asociativo:
 *   public function show(Router $router, array $params): void {
 *       $id = (int) $params['id'];
 *   }
 */
final class Router
{
    /**
     * @var array<string, list<array{
     *     regex: string,
     *     params: list<string>,
     *     handler: callable|array<int,string>,
     *     middleware: list<callable>
     * }>>
     */
    private array $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'PATCH'  => [],
        'DELETE' => [],
    ];

    /** @var list<callable> */
    private array $middleware = [];

    /** Referencia a la última ruta registrada, para ->middleware(). */
    private ?string $lastMethod = null;
    private ?int $lastIndex     = null;

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

    // ── Middleware ─────────────────────────────────────────────────────────────

    /** Middleware global: se ejecuta en todas las rutas. */
    public function use(callable $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Middleware para la última ruta registrada (encadenable).
     *
     *   $router->get('/admin', ...)->middleware(AuthMiddleware::check(...));
     */
    public function middleware(callable $middleware): self
    {
        if ($this->lastMethod === null || $this->lastIndex === null) {
            throw new \LogicException('middleware() must be called after registering a route.');
        }

        $this->routes[$this->lastMethod][$this->lastIndex]['middleware'][] = $middleware;

        return $this;
    }

    // ── Dispatch ─────────────────────────────────────────────────────────────

    public function dispatch(): void
    {
        $method = $this->resolveMethod();
        $uri    = $this->resolveUri();

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['regex'], $uri, $matches) !== 1) {
                continue;
            }

            // Asociar valores capturados con los nombres de parámetro.
            $params = [];
            foreach ($route['params'] as $i => $name) {
                $params[$name] = $matches[$i + 1] ?? null;
            }

            // Middleware global + específico de la ruta.
            foreach ([...$this->middleware, ...$route['middleware']] as $mw) {
                $mw($this);
            }

            $this->callHandler($route['handler'], $params);

            return;
        }

        throw new RouteNotFoundException("No route found for {$method} {$uri}");
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
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        [$regex, $params] = $this->compile($uri);

        $this->routes[$method][] = [
            'regex'      => $regex,
            'params'     => $params,
            'handler'    => $handler,
            'middleware' => [],
        ];

        $this->lastMethod = $method;
        $this->lastIndex  = array_key_last($this->routes[$method]);

        return $this;
    }

    /**
     * Compila una URI con placeholders ({id}) a una expresión regular.
     *
     * @return array{0: string, 1: list<string>}
     */
    private function compile(string $uri): array
    {
        $params   = [];
        $segments = preg_split('/(\{[a-zA-Z_][a-zA-Z0-9_]*\})/', $uri, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$uri];
        $regex    = '';

        foreach ($segments as $segment) {
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $segment, $m) === 1) {
                $params[] = $m[1];
                $regex   .= '([^/]+)';
            } else {
                $regex .= preg_quote($segment, '#');
            }
        }

        return ['#^' . $regex . '$#', $params];
    }

    /**
     * @param callable|array<int,string> $handler
     * @param array<string, string|null> $params
     */
    private function callHandler(callable|array $handler, array $params): void
    {
        if (is_array($handler)) {
            [$class, $methodName] = $handler;
            (new $class())->$methodName($this, $params);

            return;
        }

        $handler($this, $params);
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
            return rtrim($uri, '/');
        }

        return $uri;
    }
}
