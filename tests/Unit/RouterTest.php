<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Router;
use App\Exceptions\RouteNotFoundException;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected function tearDown(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($_SERVER['PATH_INFO'], $_SERVER['REQUEST_URI'], $_POST['_method']);
        parent::tearDown();
    }

    private function request(string $method, string $uri): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI']    = $uri;
        unset($_SERVER['PATH_INFO']);
    }

    public function test_dispatches_exact_route(): void
    {
        $this->request('GET', '/');
        $called = false;

        $router = new Router();
        $router->get('/', function (Router $r) use (&$called): void {
            $called = true;
        });
        $router->dispatch();

        $this->assertTrue($called);
    }

    public function test_extracts_route_parameters(): void
    {
        $this->request('GET', '/users/42');
        $captured = [];

        $router = new Router();
        $router->get('/users/{id}', function (Router $r, array $params) use (&$captured): void {
            $captured = $params;
        });
        $router->dispatch();

        $this->assertSame(['id' => '42'], $captured);
    }

    public function test_multiple_parameters(): void
    {
        $this->request('GET', '/posts/php/comments/7');
        $captured = [];

        $router = new Router();
        $router->get('/posts/{slug}/comments/{commentId}', function (Router $r, array $params) use (&$captured): void {
            $captured = $params;
        });
        $router->dispatch();

        $this->assertSame(['slug' => 'php', 'commentId' => '7'], $captured);
    }

    public function test_unknown_route_throws(): void
    {
        $this->request('GET', '/nope');

        $router = new Router();
        $router->get('/', fn (Router $r): null => null);

        $this->expectException(RouteNotFoundException::class);
        $router->dispatch();
    }

    public function test_route_middleware_runs_before_handler(): void
    {
        $this->request('GET', '/admin');
        $order = [];

        $router = new Router();
        $router->get('/admin', function (Router $r) use (&$order): void {
            $order[] = 'handler';
        })->middleware(function (Router $r) use (&$order): void {
            $order[] = 'middleware';
        });
        $router->dispatch();

        $this->assertSame(['middleware', 'handler'], $order);
    }

    public function test_trailing_slash_is_normalized(): void
    {
        $this->request('GET', '/users/42/');
        $captured = [];

        $router = new Router();
        $router->get('/users/{id}', function (Router $r, array $params) use (&$captured): void {
            $captured = $params;
        });
        $router->dispatch();

        $this->assertSame(['id' => '42'], $captured);
    }

    public function test_parameter_does_not_match_across_slash(): void
    {
        $this->request('GET', '/users/1/2');

        $router = new Router();
        $router->get('/users/{id}', fn (Router $r, array $params): null => null);

        $this->expectException(RouteNotFoundException::class);
        $router->dispatch();
    }
}
