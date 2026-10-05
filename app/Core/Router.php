<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middlewares): void
    {
        // Convert /product/{slug} into regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#u';

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        // Strip subfolder if running in subdirectory
        $base = base_path_url();
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        if ($uri === '/index.php' || str_starts_with($uri, '/index.php/')) {
            $uri = substr($uri, strlen('/index.php'));
        }
        $uri = '/' . trim($uri, '/');


        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                // Extract named params
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = urldecode($value);
                    }
                }
                $request->setRouteParams($params);

                // Run middlewares
                foreach ($route['middlewares'] as $mw) {
                    if (!$this->handleMiddleware($mw, $request)) {
                        return; // Stopped by middleware
                    }
                }

                // Call handler
                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func($handler, $request, ...array_values($params));
                } elseif (is_array($handler)) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    $controller->$action($request, ...array_values($params));
                }
                return;
            }
        }

        // Not Found
        Response::error("الصفحة المطلوبة غير موجودة (404 Not Found)", 404);
    }

    private function handleMiddleware(string $name, Request $request): bool
    {
        if ($name === 'auth') {
            if (!Auth::check()) {
                if ($request->isAjax()) {
                    Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
                } else {
                    Response::redirect('/login');
                }
                return false;
            }
        }

        if ($name === 'super_admin') {
            if (!Auth::check() || !Auth::isSuperAdmin()) {
                if ($request->isAjax()) {
                    Response::json(['success' => false, 'message' => 'Forbidden: Super Admin only'], 403);
                } else {
                    Response::redirect('/admin');
                }
                return false;
            }
        }

        if ($name === 'store_manager' || $name === 'school_admin' || $name === 'admin') {
            if (!Auth::check() || (!Auth::isStoreManager() && !Auth::isSuperAdmin())) {
                if ($request->isAjax()) {
                    Response::json(['success' => false, 'message' => 'Forbidden: Store Manager only'], 403);
                } else {
                    Response::redirect('/login');
                }
                return false;
            }
        }

        return true;
    }
}
