<?php

namespace App\Core;

class I18n
{
    private static ?string $context = null;
    private static array $translations = [];

    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize separate session keys for storefront and admin dashboard
        if (!isset($_SESSION['locale'])) {
            $_SESSION['locale'] = 'ar';
        }
        if (!isset($_SESSION['admin_locale'])) {
            $_SESSION['admin_locale'] = 'ar';
        }

        // Check query param for explicit change ?lang=en or ?lang=ar
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'])) {
            if (self::isAdminRequest()) {
                $_SESSION['admin_locale'] = $_GET['lang'];
            } else {
                $_SESSION['locale'] = $_GET['lang'];
            }
        }

        self::loadTranslations(self::getStoreLocale());
        self::loadTranslations(self::getAdminLocale());
    }

    public static function isAdminRequest(): bool
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        $base = function_exists('base_path_url') ? base_path_url() : '';
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        $uri = '/' . ltrim($uri, '/');
        return str_starts_with($uri, '/admin');
    }

    public static function setContext(?string $context): void
    {
        self::$context = $context;
    }

    public static function getContext(): string
    {
        if (self::$context !== null) {
            return self::$context;
        }
        return self::isAdminRequest() ? 'admin' : 'storefront';
    }

    public static function getStoreLocale(): string
    {
        return $_SESSION['locale'] ?? 'ar';
    }

    public static function getAdminLocale(): string
    {
        return $_SESSION['admin_locale'] ?? 'ar';
    }

    public static function setStoreLocale(string $locale): void
    {
        if (in_array($locale, ['ar', 'en'])) {
            $_SESSION['locale'] = $locale;
            self::loadTranslations($locale);
        }
    }

    public static function setAdminLocale(string $locale): void
    {
        if (in_array($locale, ['ar', 'en'])) {
            $_SESSION['admin_locale'] = $locale;
            self::loadTranslations($locale);
        }
    }

    public static function getLocale(): string
    {
        return self::getContext() === 'admin' ? self::getAdminLocale() : self::getStoreLocale();
    }

    public static function setLocale(string $locale, ?string $context = null): void
    {
        $target = $context ?? self::getContext();
        if ($target === 'admin') {
            self::setAdminLocale($locale);
        } else {
            self::setStoreLocale($locale);
        }
    }

    public static function isRtl(?string $context = null): bool
    {
        $target = $context ?? self::getContext();
        $locale = ($target === 'admin') ? self::getAdminLocale() : self::getStoreLocale();
        return $locale === 'ar';
    }

    public static function isAdminRtl(): bool
    {
        return self::getAdminLocale() === 'ar';
    }

    public static function isStoreRtl(): bool
    {
        return self::getStoreLocale() === 'ar';
    }

    private static function loadTranslations(string $locale): void
    {
        if (isset(self::$translations[$locale])) {
            return;
        }
        $langFile = __DIR__ . '/../../resources/lang/' . $locale . '.php';
        if (file_exists($langFile)) {
            $mtime = filemtime($langFile);
            $cacheKey = 'translations_' . $locale . '_' . $mtime;
            if (class_exists('App\\Core\\Cache')) {
                self::$translations[$locale] = Cache::remember($cacheKey, 86400, function () use ($langFile) {
                    return require $langFile;
                }, 'translations');
            } else {
                self::$translations[$locale] = require $langFile;
            }
        } else {
            self::$translations[$locale] = [];
        }
    }

    public static function clearCache(): void
    {
        if (class_exists('App\\Core\\Cache')) {
            Cache::flush('translations');
        }
        self::$translations = [];
    }

    public static function trans(string $key, array $replace = [], string $default = '', ?string $locale = null): string
    {
        $activeLocale = $locale ?? self::getLocale();
        if (!isset(self::$translations[$activeLocale])) {
            self::loadTranslations($activeLocale);
        }
        $text = self::$translations[$activeLocale][$key] ?? ($default ?: $key);
        foreach ($replace as $placeholder => $value) {
            $text = str_replace(':' . $placeholder, (string)$value, $text);
        }
        return $text;
    }

    public static function formatPrice(float|int|string|null $amount = 0.0, ?string $locale = null): string
    {
        $amount = (float)($amount ?? 0.0);
        $formatted = number_format($amount, 2);
        $activeLocale = $locale ?? self::getLocale();
        return $activeLocale === 'ar' ? "{$formatted} ر.س" : "{$formatted} SAR";
    }
}
