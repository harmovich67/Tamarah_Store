<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use Database\Database;

class SettingsController
{
    public function index(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::redirect('/admin');
            return;
        }

        if (class_exists('\\App\\Core\\Cache')) {
            $settings = \App\Core\Cache::remember('system_settings', 86400, function () {
                $rows = Database::fetchAll("SELECT * FROM settings");
                $data = [];
                foreach ($rows as $row) {
                    $data[$row['key']] = $row['value'];
                }
                return $data;
            }, 'settings');
        } else {
            $settingsRows = Database::fetchAll("SELECT * FROM settings");
            $settings = [];
            foreach ($settingsRows as $row) {
                $settings[$row['key']] = $row['value'];
            }
        }

        Response::view('admin/settings', [
            'settings' => $settings,
            'saved' => $request->get('saved') == 1
        ], 'admin');
    }

    public function update(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden', 403);
            return;
        }

        $allowedKeys = [
            // Saudi & International Core Payment Gateways (Mada, Apple Pay, Cards)
            'gateway_provider', 'gateway_mode', 'gateway_publishable_key', 'gateway_secret_key',
            'enable_mada', 'enable_apple_pay', 'enable_credit_card',
            // Paymob Gateway (Egypt & Middle East)
            'enable_paymob', 'paymob_api_key', 'paymob_integration_id', 'paymob_iframe_id', 'paymob_hmac', 'paymob_mode',
            // Tamara BNPL
            'enable_tamara', 'tamara_merchant_token', 'tamara_public_key', 'tamara_mode',
            // Tabby BNPL
            'enable_tabby', 'tabby_public_key', 'tabby_secret_key', 'tabby_mode',
            // Cash on Delivery
            'enable_cod', 'cod_fee',
            // Direct Bank Transfer
            'enable_bank_transfer', 'bank_name', 'bank_account_name', 'bank_iban',
            // Shipping & Cold Freight
            'shipping_standard_fee', 'shipping_free_threshold', 'enable_branch_pickup',
            // General
            'allow_guest_checkout'
        ];

        foreach ($allowedKeys as $k) {
            $val = $request->get($k);
            if ($val !== null) {
                Database::execute("
                    INSERT INTO settings (`key`, `value`) VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
                ", [$k, $val]);
            }
        }

        if (class_exists('\\App\\Core\\Cache')) {
            \App\Core\Cache::flush('settings');
        }

        Response::redirect('/admin/settings?saved=1');
    }

    public function siteSettings(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::redirect('/admin');
            return;
        }

        $settingsRows = Database::fetchAll("SELECT * FROM settings");
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['key']] = $row['value'];
        }

        Response::view('admin/site_settings', [
            'settings' => $settings,
            'saved' => $request->get('saved') == 1
        ], 'admin');
    }

    public function updateSiteSettings(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden', 403);
            return;
        }

        $allowedKeys = [
            'site_name_ar', 'site_name_en', 'site_tagline_ar', 'site_tagline_en',
            'site_logo', 'announcement_text_ar', 'announcement_text_en',
            'contact_phone', 'contact_whatsapp', 'contact_email', 'contact_address',
            'contact_address_ar', 'contact_address_en',
            'social_facebook', 'social_instagram', 'footer_text_ar', 'footer_text_en'
        ];

        foreach ($allowedKeys as $k) {
            $val = $request->get($k);
            if ($val !== null) {
                Database::execute("
                    INSERT INTO settings (`key`, `value`) VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
                ", [$k, $val]);
            }
        }

        // Backward compatibility sync for contact_address
        if ($request->get('contact_address_ar') !== null && $request->get('contact_address') === null) {
            Database::execute("
                INSERT INTO settings (`key`, `value`) VALUES ('contact_address', ?)
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
            ", [$request->get('contact_address_ar')]);
        }

        // Handle direct file upload for site logo
        $uploadedLogo = \App\Core\Uploader::upload('logo_file', 'logos');
        if ($uploadedLogo) {
            Database::execute("
                INSERT INTO settings (`key`, `value`) VALUES ('site_logo', ?)
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
            ", [$uploadedLogo]);
        }

        if (class_exists('\\App\\Core\\Cache')) {
            \App\Core\Cache::flush('settings');
        }

        Response::redirect('/admin/site-settings?saved=1');
    }
}
