<?php

require_once __DIR__ . '/../app/Core/helpers.php';

return [
    'name_ar' => env('APP_NAME_AR', 'متجر تَـمْرُنـا للتمور الفاخرة'),
    'name_en' => env('APP_NAME_EN', 'Tumurna Luxury Dates Store'),
    'url' => env('APP_URL') ?: (function_exists('full_url') ? full_url('/') : 'http://localhost'),
    'default_locale' => env('APP_LOCALE', 'ar'),
    'supported_locales' => ['ar', 'en'],
    'currency_ar' => env('APP_CURRENCY_AR', 'ر.س'),
    'currency_en' => env('APP_CURRENCY_EN', 'SAR'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Riyadh'),
];
