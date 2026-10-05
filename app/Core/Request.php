<?php

namespace App\Core;

class Request
{
    private array $params;
    private array $body;
    private string $method;
    private string $uri;

    public function __construct()
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->method = ($method === 'HEAD') ? 'GET' : $method;
        $this->uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $this->params = $_GET;

        // Parse JSON or form POST
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $this->body = json_decode($raw, true) ?: [];
        } else {
            $this->body = $_POST;
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isAjax(): bool
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $this->body[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->params, $this->body);
    }

    public function setRouteParams(array $params): void
    {
        $this->params = array_merge($this->params, $params);
    }

    public function ip(): string
    {
        return class_exists('App\\Core\\Logger') 
            ? Logger::getClientIp() 
            : ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    public static function csrfToken(): string
    {
        return Security::token();
    }

    public function validateCsrf(): bool
    {
        return Security::validateCsrf($this);
    }
}
