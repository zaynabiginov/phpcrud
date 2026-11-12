<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array{0:class-string,1:string}> */
    private array $routes = [];

    public function get(string $path, array $handler): void { $this->map('GET', $path, $handler); }
    public function post(string $path, array $handler): void { $this->map('POST', $path, $handler); }
    public function any(string $path, array $handler): void { $this->map('ANY', $path, $handler); }

    private function map(string $method, string $path, array $handler): void
    {
        $this->routes[$method . ' ' . $this->norm($path)] = $handler;
    }

    private function norm(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = rtrim(base_href(), '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        $path = $this->norm($path);

        $handler = $this->routes[$method . ' ' . $path]
            ?? $this->routes['ANY ' . $path]
            ?? (($path === '/') ? ($this->routes['ANY /home'] ?? $this->routes['GET /home'] ?? null) : null);

        if ($handler === null) {
            http_response_code(404);
            header('Content-Type: text/html; charset=utf-8');
            View::render('errors.404', ['path' => $path], null);
            return;
        }
        [$class, $action] = $handler;
        (new $class())->$action();
    }
}