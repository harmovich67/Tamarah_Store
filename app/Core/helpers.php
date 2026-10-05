<?php

// Global helper functions
if (!function_exists('__')) {
    function __(string $key, array $replace = [], string $default = ''): string
    {
        return \App\Core\I18n::trans($key, $replace, $default);
    }
}

if (!function_exists('env')) {
    /**
     * Get an environment variable with default fallback and auto .env loading
     */
    function env(string $key, mixed $default = null): mixed
    {
        static $envLoaded = false;
        if (!$envLoaded) {
            $envLoaded = true;
            $envFile = __DIR__ . '/../../.env';
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || str_starts_with($line, '#')) continue;
                    if (str_contains($line, '=')) {
                        [$k, $v] = explode('=', $line, 2);
                        $k = trim($k);
                        $v = trim($v);
                        // Strip quotes
                        if ((str_starts_with($v, '"') && str_ends_with($v, '"')) || (str_starts_with($v, "'") && str_ends_with($v, "'"))) {
                            $v = substr($v, 1, -1);
                        }
                        if (!isset($_ENV[$k]) && !isset($_SERVER[$k])) {
                            $_ENV[$k] = $v;
                            $_SERVER[$k] = $v;
                            putenv("{$k}={$v}");
                        }
                    }
                }
            }
        }

        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($val === false || $val === null) {
            return $default;
        }

        return match (strtolower((string)$val)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $val,
        };
    }
}

if (!function_exists('app_scheme')) {
    function app_scheme(): string
    {
        if ((!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
            || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)) {
            return 'https';
        }
        return 'http';
    }
}

if (!function_exists('app_host')) {
    function app_host(): string
    {
        return $_SERVER['HTTP_HOST'] ?? 'localhost';
    }
}

if (!function_exists('base_path_url')) {
    /**
     * Auto-detect the base path of the application regardless of subdirectory depth
     */
    function base_path_url(): string
    {
        static $base = null;
        if ($base === null) {
            // Check if explicit APP_URL is defined with a subpath in .env
            $configuredUrl = env('APP_URL');
            if (!empty($configuredUrl) && $configuredUrl !== 'http://localhost' && $configuredUrl !== 'https://localhost') {
                $parsed = parse_url($configuredUrl, PHP_URL_PATH);
                if (!empty($parsed) && $parsed !== '/' && $parsed !== '\\') {
                    $base = '/' . trim(str_replace('\\', '/', $parsed), '/');
                    return $base;
                }
            }

            // Auto-detect from SCRIPT_NAME (e.g. /alaz/index.php -> /alaz)
            $script = $_SERVER['SCRIPT_NAME'] ?? '';
            // PATH_INFO requests may expose /index.php/login as SCRIPT_NAME.
            $script = preg_replace('#/index\.php(?:/.*)?$#i', '/index.php', $script);
            $dir = str_replace('\\', '/', dirname($script));
            $base = ($dir === '/' || $dir === '.' || $dir === '' || $dir === '\\') ? '' : '/' . trim($dir, '/');
        }
        return $base;
    }
}

if (!function_exists('url')) {
    /**
     * Generate an application URL with proper subdirectory prefixing and no double slashes
     */
    function url(string $path = ''): string
    {
        // Don't modify absolute or external protocols
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')
            || str_starts_with($path, 'javascript:') || str_starts_with($path, 'mailto:')
            || str_starts_with($path, 'tel:') || str_starts_with($path, '#')) {
            return $path;
        }

        $base = base_path_url();
        $routeMode = strtolower((string) env('APP_ROUTE_MODE', 'rewrite'));
        $routeBase = $routeMode === 'path_info'
            ? $base . '/index.php'
            : $base;
        $trimmed = ltrim($path, '/');

        // Prevent duplicate prefixing if path already starts with base
        if (($base !== '' && (str_starts_with('/' . $trimmed, $base . '/') || '/' . $trimmed === $base))
            || ($routeBase !== '' && (str_starts_with('/' . $trimmed, $routeBase . '/') || '/' . $trimmed === $routeBase))) {
            return '/' . ltrim($trimmed, '/');
        }

        if ($trimmed === '') {
            return $routeBase !== '' ? $routeBase : '/';
        }

        if (str_starts_with($trimmed, '?')) {
            return ($routeBase !== '' ? $routeBase : '/') . $trimmed;
        }

        return ($routeBase !== '' ? $routeBase : '') . '/' . $trimmed;
    }
}

if (!function_exists('full_url')) {
    /**
     * Generate a full absolute URL including scheme and host
     */
    function full_url(string $path = ''): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $scheme = app_scheme();
        $host = app_host();
        $relative = url($path);
        return "{$scheme}://{$host}" . (str_starts_with($relative, '/') ? $relative : "/{$relative}");
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL with dynamic subfolder resolution
     */
    function asset(?string $path): string
    {
        if (empty($path)) {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            || str_starts_with($path, '//') || str_starts_with($path, 'data:')) {
            return $path;
        }
        $base = base_path_url();
        $relative = '/' . ltrim($path, '/');
        if ($base !== '' && ($relative === $base || str_starts_with($relative, $base . '/'))) {
            return $relative;
        }
        return $base . $relative;
    }
}

if (!function_exists('localized')) {
    /**
     * Pick the Arabic/English value matching the current locale, falling back
     * to the other language when the preferred one is empty.
     */
    function localized(?string $ar, ?string $en): string
    {
        $ar = (string)($ar ?? '');
        $en = (string)($en ?? '');
        if (\App\Core\I18n::getLocale() === 'en') {
            return $en !== '' ? $en : $ar;
        }
        return $ar !== '' ? $ar : $en;
    }
}

if (!function_exists('is_active_url')) {
    function is_active_url(string $path, bool $exact = false): bool
    {
        $reqUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $targetUrl = url($path);
        if ($exact) {
            return $reqUri === $targetUrl || ($targetUrl !== '/' && rtrim($reqUri, '/') === rtrim($targetUrl, '/'));
        }
        return str_starts_with($reqUri, $targetUrl);
    }
}


// Native ACF Pro Helpers
if (!function_exists('get_field')) {
    function get_field(string $name, ?int $entityId = null, ?string $entityType = null, mixed $default = null): mixed
    {
        return \App\Core\CustomFields::getField($name, $entityId, $entityType, $default);
    }
}

if (!function_exists('the_field')) {
    function the_field(string $name, ?int $entityId = null, ?string $entityType = null, mixed $default = null): void
    {
        \App\Core\CustomFields::theField($name, $entityId, $entityType, $default);
    }
}

if (!function_exists('get_fields')) {
    function get_fields(?int $entityId = null, ?string $entityType = null): array
    {
        return \App\Core\CustomFields::getAllFields($entityId, $entityType);
    }
}

if (!function_exists('have_rows')) {
    function have_rows(string $repeaterName, ?int $entityId = null, ?string $entityType = null): bool
    {
        return \App\Core\CustomFields::haveRows($repeaterName, $entityId, $entityType);
    }
}

if (!function_exists('the_row')) {
    function the_row(string $repeaterName): ?array
    {
        return \App\Core\CustomFields::theRow($repeaterName);
    }
}

if (!function_exists('get_sub_field')) {
    function get_sub_field(string $subFieldName, ?string $repeaterName = null): mixed
    {
        return \App\Core\CustomFields::getSubField($subFieldName, $repeaterName);
    }
}

if (!function_exists('has_field')) {
    function has_field(string $name, ?int $entityId = null, ?string $entityType = null): bool
    {
        $val = \App\Core\CustomFields::getField($name, $entityId, $entityType);
        return $val !== null && $val !== '' && $val !== [];
    }
}

// CSRF & Security Helpers
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \App\Core\Security::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return \App\Core\Security::field();
    }
}

// Cache & Logger Helpers
if (!function_exists('app_cache')) {
    function app_cache(?string $key = null, mixed $default = null, int $ttl = 3600, string $group = 'data'): mixed
    {
        if ($key === null) {
            return new \App\Core\Cache();
        }
        return \App\Core\Cache::remember($key, $ttl, fn() => $default, $group);
    }
}

if (!function_exists('app_logger')) {
    function app_logger(): \App\Core\Logger
    {
        return new \App\Core\Logger();
    }
}

// WordPress & Template Polyfills for Standalone Theme Compatibility
if (!function_exists('esc_html')) {
    function esc_html($s): string {
        return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($s): string {
        return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url($s): string {
        return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('currency')) {
    function currency(): string {
        return \App\Core\I18n::isRtl() ? 'ر.س' : 'SAR';
    }
}

if (!function_exists('lang_get')) {
    /**
     * Get localized value from an array or object based on active locale
     * e.g. lang_get($product, 'name') returns $product['name_en'] if locale is 'en', falling back to name_ar or name
     */
    function lang_get(mixed $data, string $field, mixed $default = ''): mixed {
        if (empty($data)) return $default;
        $locale = \App\Core\I18n::getLocale();
        $isObj = is_object($data);
        
        $primaryKey = "{$field}_{$locale}";
        $fallbackLocale = ($locale === 'en') ? 'ar' : 'en';
        $fallbackKey = "{$field}_{$fallbackLocale}";
        
        if ($isObj) {
            if (!empty($data->$primaryKey)) return $data->$primaryKey;
            if (!empty($data->$field)) return $data->$field;
            if (!empty($data->$fallbackKey)) return $data->$fallbackKey;
        } elseif (is_array($data)) {
            if (!empty($data[$primaryKey])) return $data[$primaryKey];
            if (!empty($data[$field])) return $data[$field];
            if (!empty($data[$fallbackKey])) return $data[$fallbackKey];
        }
        
        return $default;
    }
}

if (!function_exists('tumurna_icon')) {
    function tumurna_icon(string $name, string $classes = 'w-5 h-5'): string {
        $name = strtolower(trim($name));
        $icons = [
            'sparkles' => '<path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>',
            'arrow-left' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
            'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
            'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
            'gift' => '<rect width="18" height="14" x="3" y="8" rx="2"/><path d="M12 5a3 3 0 1 0-3 3h6a3 3 0 1 0-3-3Z"/><path d="M12 8v14"/><path d="M3 12h18"/>',
            'calendar' => '<rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>',
            'award' => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
            'flame' => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
            'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
            'truck' => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
            'store' => '<path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2v0a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12v0a2 2 0 0 1-2-2V7"/>',
            'credit-card' => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
            'shield-check' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/>',
            'shield-alert' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
            'heart' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
            'shopping-bag' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
            'check' => '<path d="M20 6 9 17l-5-5"/>',
            'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'menu' => '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>',
            'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
            'message-circle' => '<path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z"/>',
            'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
            'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
            'map-pin' => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
            'ghost' => '<path d="M9 10h.01"/><path d="M15 10h.01"/><path d="M12 2a8 8 0 0 0-8 8v12l3-3 2.5 2.5L12 19l2.5 2.5L17 19l3 3V10a8 8 0 0 0-8-8z"/>',
            'instagram' => '<rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>',
            'music' => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
            'globe' => '<circle cx="12" cy="12" r="10"/><line x1="2" x2="22" y1="12" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
            'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'user-pen' => '<path d="M11.5 15H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L13.842 6.17a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'package' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
            'package-open' => '<path d="M12 22v-9"/><path d="M15.6 3.6 21 6.5l-9 5.2-9-5.2 5.4-2.9"/><path d="M21 16.5V6.5"/><path d="M3 6.5v10"/><path d="m3.3 16.5 8.7 5 8.7-5"/>',
            'map-pin-off' => '<path d="M12.75 4.07A8 8 0 0 1 20 10c0 1.95-.88 3.96-2.12 5.88"/><path d="M2 2l20 20"/><path d="M8.2 8.2a8 8 0 0 0-4.2 1.8c0 4.99 5.54 10.2 7.4 11.8a1.2 1.2 0 0 0 1.2 0c.93-.8 2.38-2.29 3.65-4.1"/><circle cx="12" cy="10" r="3"/>',
            'log-out' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>',
            'snowflake' => '<line x1="2" x2="22" y1="12" y2="12"/><line x1="12" x2="12" y1="2" y2="22"/><path d="m20 16-4-4 4-4"/><path d="m4 8 4 4-4 4"/><path d="m16 4-4 4-4-4"/><path d="m8 20 4-4 4 4"/>',
            'navigation' => '<polygon points="3 11 22 2 13 21 11 13 3 11"/>',
            'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>',
            'check-check' => '<path d="M18 6 7 17l-5-5"/><path d="m22 10-7.5 7.5L13 16"/>',
            'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
            'alert-circle' => '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
            'info' => '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/>',
            'pencil' => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>',
            'edit-3' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>',
            'trash-2' => '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/>',
            'home' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'building' => '<rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/>',
            'palmtree' => '<path d="M13 8c0-2.76-2.46-5-5.5-5S2 5.24 2 8h2s1-2 4-2 3 2 5 2"/><path d="M13 7.14A5.82 5.82 0 0 1 16.5 6c3.04 0 5.5 2.24 5.5 5h-2s-1-2-4-2-2.5 1.5-3 2"/><path d="M5.8 15.5c1.5 1.5 3.2 2.5 5.2 2.5s3.7-1 5.2-2.5"/><path d="M11 22h2s-1-10 3-14"/>',
            'flag' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" x2="4" y1="22" y2="15"/>',
            'plus' => '<line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/>',
            'plus-circle' => '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="16"/><line x1="8" x2="16" y1="12" y2="12"/>',
            'compass' => '<circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>',
            'boxes' => '<path d="M2.97 12.92A2 2 0 0 0 2 14.63v3.24a2 2 0 0 0 .97 1.71l6 3.43a2 2 0 0 0 1.03.29 2 2 0 0 0 1.03-.29l6-3.43a2 2 0 0 0 .97-1.71v-3.24a2 2 0 0 0-.97-1.71L12 9.49l-6.03 3.43Z"/><path d="m12 9.49 6.03 3.43"/><path d="M12 22.14v-9.22"/>',
            'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
            'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
            'x-circle' => '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>',
            'printer' => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/>',
            'alert-octagon' => '<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
            'box' => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
            'receipt' => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/>',
        ];

        // Extra Lucide paths used by the Tamrna Foundation redesign
        static $extraIcons = null;
        if ($extraIcons === null) {
            $extraFile = __DIR__ . '/icons_extra.php';
            $extraIcons = is_file($extraFile) ? (array)require $extraFile : [];
        }

        $inner = $icons[$name] ?? ($extraIcons[$name] ?? '<circle cx="12" cy="12" r="10"/>');
        return sprintf(
            '<svg class="%s inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">%s</svg>',
            htmlspecialchars($classes, ENT_QUOTES, 'UTF-8'),
            $inner
        );
    }
}

if (!function_exists('fnd_icon')) {
    /**
     * Foundation-style icon: fixed pixel size (width/height attributes), no utility classes needed.
     */
    function fnd_icon(string $name, int $size = 20, string $class = '', float $stroke = 2.0): string {
        $svg = tumurna_icon($name, '');
        $attrs = sprintf(
            '<svg width="%d" height="%d" class="lucide lucide-%s %s" aria-hidden="true"',
            $size,
            $size,
            htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($class, ENT_QUOTES, 'UTF-8')
        );
        $svg = preg_replace('/^<svg class="[^"]*"/', $attrs, $svg, 1);
        if ($stroke !== 2.0) {
            $svg = str_replace('stroke-width="2"', 'stroke-width="' . $stroke . '"', $svg);
        }
        return $svg;
    }
}

if (!function_exists('fnd_divider')) {
    /** Heritage divider ornament used in hero/promo sections. */
    function fnd_divider(): string {
        return '<span class="heritage-divider" aria-hidden="true"><i></i><svg viewBox="0 0 40 24" width="40" height="24" fill="none"><path d="M20 2 30 12 20 22 10 12 20 2ZM20 7l5 5-5 5-5-5 5-5ZM2 12h8m20 0h8" stroke="currentColor"></path></svg><i></i></span>';
    }
}

if (!function_exists('fnd_money')) {
    /** Price formatted for display, e.g. "240.00 ر.س" */
    function fnd_money($amount): string {
        $n = (float)$amount;
        $decimals = (floor($n) == $n) ? 0 : 2;
        return number_format($n, $decimals) . ' ' . currency();
    }
}

if (!function_exists('tumurna_section_ornament')) {
    function tumurna_section_ornament(string $classes = 'mt-2 mb-2'): string {
        return sprintf(
            '<div class="flex items-center justify-center gap-2 %s">
                <span class="w-8 h-[1px] bg-gradient-to-r from-transparent to-[#c49a52]"></span>
                <span class="w-2 h-2 rotate-45 border border-[#c49a52] bg-[#fbf5e9]"></span>
                <span class="w-8 h-[1px] bg-gradient-to-l from-transparent to-[#c49a52]"></span>
            </div>',
            htmlspecialchars($classes, ENT_QUOTES, 'UTF-8')
        );
    }
}


