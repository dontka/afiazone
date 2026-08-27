<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function patch(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    public function add(string $method, string $path, callable|array $handler, array $middleware = []): void
    {
        $path = $this->normalizePath($path);
        $this->routes[strtoupper($method)][] = compact('path', 'handler', 'middleware');
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes[$method] ?? [] as $route) {
            $params = $this->match($route['path'], $path);

            if ($params === null) {
                continue;
            }

            return $this->execute($route['handler'], $route['middleware'], $params, $request);
        }

        return new Response(
            View::render('Shared::errors/404', [
                'title' => 'Page introuvable',
                'path' => $path,
            ]),
            404
        );
    }

    private function execute(callable|array $handler, array $middleware, array $params, Request $request): Response
    {
        $next = function (Request $request) use ($handler, $params): Response {
            $resolvedHandler = $handler;
            if (is_array($resolvedHandler) && is_string($resolvedHandler[0])) {
                $resolvedHandler[0] = new $resolvedHandler[0]();
            }

            $result = $resolvedHandler(...array_values($params));

            return $result instanceof Response ? $result : new Response((string) $result);
        };

        foreach (array_reverse($middleware) as $item) {
            $middlewareHandler = is_string($item) ? new $item() : $item;
            $next = fn (Request $request): Response => $middlewareHandler($request, $next);
        }

        return $next($request);
    }

    private function match(string $routePath, string $requestPath): ?array
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (! preg_match($pattern, $requestPath, $matches)) {
            return null;
        }

        return array_filter($matches, static fn ($key) => is_string($key), ARRAY_FILTER_USE_KEY);
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }
}