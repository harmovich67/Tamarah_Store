<?php

namespace App\Core;

class Response
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function redirect(string $url): void
    {
        if (function_exists('url')) {
            $url = url($url);
        }
        header("Location: {$url}");
        exit;
    }


    public static function view(string $viewPath, array $data = [], ?string $layout = 'app'): void
    {
        $isAdmin = ($layout === 'admin' || str_starts_with($viewPath, 'admin/'));
        \App\Core\I18n::setContext($isAdmin ? 'admin' : 'storefront');

        $locale = \App\Core\I18n::getLocale();
        $isRtl = \App\Core\I18n::isRtl();
        $data['locale'] = $data['locale'] ?? $locale;
        $data['isRtl'] = $data['isRtl'] ?? $isRtl;
        extract($data);
        $locale = \App\Core\I18n::getLocale();
        $isRtl = \App\Core\I18n::isRtl();

        // Render inner view
        $file = __DIR__ . '/../../resources/views/' . $viewPath . '.php';
        if (!file_exists($file)) {
            http_response_code(500);
            die("View file not found: {$viewPath}");
        }

        ob_start();
        require $file;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            exit;
        }

        // Wrap in layout
        $layoutFile = __DIR__ . '/../../resources/views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            echo $content;
            exit;
        }

        require $layoutFile;
        exit;
    }

    public static function error(string $message, int $code = 400): void
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            self::json(['success' => false, 'error' => $message], $code);
        } else {
            http_response_code($code);
            self::view('storefront/error', ['message' => $message, 'code' => $code]);
        }
    }
}
