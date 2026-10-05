<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Curated list of Font Awesome 6 Free "solid" icons used by the ACF "icon" field type.
 * Values are the icon slug only (without the "fa-solid fa-" prefix); the stored field
 * value is always the full class string, e.g. "fa-solid fa-star".
 */
class FontAwesomeIcons
{
    public static function catalog(): array
    {
        return [
            'عام (General)' => [
                'house', 'user', 'user-group', 'gear', 'magnifying-glass', 'bell', 'heart', 'star',
                'star-half-stroke', 'check', 'xmark', 'plus', 'minus', 'trash', 'pen', 'pen-to-square',
                'floppy-disk', 'print', 'download', 'upload', 'link', 'lock', 'lock-open', 'eye',
                'eye-slash', 'flag', 'bookmark', 'tag', 'tags', 'filter', 'list', 'table-cells',
                'table-cells-large', 'bars', 'ellipsis', 'ellipsis-vertical', 'circle-question',
                'circle-info', 'triangle-exclamation', 'circle-check', 'circle-xmark', 'thumbs-up',
                'thumbs-down', 'face-smile', 'gem', 'crown', 'trophy', 'medal', 'award',
            ],
            'اتصال (Communication)' => [
                'envelope', 'envelope-open', 'phone', 'phone-volume', 'comment', 'comments',
                'paper-plane', 'at', 'address-book', 'address-card', 'share', 'share-nodes', 'rss',
                'headset', 'handshake', 'video',
            ],
            'تسوق وتجارة (Shopping & Business)' => [
                'cart-shopping', 'bag-shopping', 'money-bill', 'money-bill-wave', 'credit-card',
                'wallet', 'gift', 'percent', 'receipt', 'store', 'box', 'boxes-stacked', 'truck',
                'truck-fast', 'warehouse', 'chart-line', 'chart-pie', 'briefcase', 'building',
                'shop', 'basket-shopping',
            ],
            'ملفات ووسائط (Files & Media)' => [
                'file', 'file-lines', 'file-pdf', 'file-image', 'file-word', 'file-excel', 'folder',
                'folder-open', 'clipboard', 'clipboard-list', 'image', 'images', 'camera', 'music',
                'headphones', 'play', 'pause', 'microphone',
            ],
            'جودة وطعام وضيافة (Food & Hospitality)' => [
                'apple-whole', 'leaf', 'mug-hot', 'utensils', 'bowl-food', 'cake-candles',
                'gift', 'box-archive', 'certificate', 'medal', 'award', 'shield-heart',
                'star', 'crown', 'gem', 'check-double',
            ],
            'طقس وطبيعة (Weather & Nature)' => [
                'cloud', 'cloud-sun', 'sun', 'moon', 'umbrella', 'snowflake', 'wind', 'tree', 'leaf',
                'paw', 'droplet', 'fire', 'bolt', 'recycle',
            ],
            'أجهزة وتقنية (Devices & Security)' => [
                'mobile', 'mobile-screen', 'laptop', 'desktop', 'tablet', 'wifi', 'database',
                'server', 'shield', 'shield-halved', 'key', 'fingerprint', 'user-shield', 'globe',
            ],
            'أماكن ومواصلات (Places & Transport)' => [
                'map', 'map-pin', 'location-dot', 'compass', 'car', 'bus', 'plane', 'ship',
                'bicycle', 'motorcycle', 'train', 'gas-pump', 'road',
            ],
            'وقت وتقويم (Time & Calendar)' => [
                'calendar', 'calendar-days', 'clock', 'hourglass', 'stopwatch',
            ],
            'رياضة (Sports)' => [
                'dumbbell', 'futbol', 'basketball', 'volleyball', 'medal',
            ],
        ];
    }

    /**
     * Flat list of every icon slug in the catalog, for quick lookups
     */
    public static function allSlugs(): array
    {
        return array_merge(...array_values(self::catalog()));
    }
}
