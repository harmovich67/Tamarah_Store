<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\I18n;

class TranslationsController
{
    private string $arFile;
    private string $enFile;

    public function __construct()
    {
        $this->arFile = __DIR__ . '/../../../resources/lang/ar.php';
        $this->enFile = __DIR__ . '/../../../resources/lang/en.php';
    }

    public static function categorizeKey(string $key): string
    {
        // 1. Storefront & Hero & Banners & Brand Layout
        if (str_starts_with($key, 'hero_') ||
            str_starts_with($key, 'site_') ||
            str_starts_with($key, 'badge_') ||
            str_starts_with($key, 'feature_') ||
            str_starts_with($key, 'footer_') ||
            str_starts_with($key, 'tamrna_') ||
            str_starts_with($key, 'why_tumurna_') ||
            str_starts_with($key, 'story_') ||
            str_starts_with($key, 'announcement_') ||
            in_array($key, [
                'home', 'currency', 'browse_catalog', 'search_placeholder', 'lang_name', 
                'switch_lang', 'current_lang', 'all_rights_reserved', 'quick_links', 
                'visit_storefront', 'preview_live_store', 'quality_guarantee', 'luxury_packaging',
                'cold_shipping', 'royal_dates_tagline', 'one_year_warranty', 'welcome_to_tumurna',
                'admin_brand_title', 'admin_brand_subtitle'
            ])) {
            return 'storefront';
        }

        // 2. Dates Catalog, Varieties, Packages & Preorders
        if (str_starts_with($key, 'product_') ||
            str_starts_with($key, 'category_') ||
            str_starts_with($key, 'preorder_') ||
            str_starts_with($key, 'gift_') ||
            str_starts_with($key, 'dates_') ||
            str_starts_with($key, 'harvest_') ||
            str_starts_with($key, 'size_') ||
            str_starts_with($key, 'variant_') ||
            in_array($key, [
                'categories', 'all_categories', 'featured_dates', 'sukkari_dates', 
                'khalas_dates', 'ajwa_dates', 'sagae_dates', 'medjool_dates', 
                'view_product', 'from_price', 'select_size', 'sku', 'stock_status', 
                'in_stock', 'out_of_stock', 'pieces_left', 'quantity', 'add_to_cart', 
                'buy_now', 'description', 'products_found', 'no_products_found'
            ])) {
            return 'catalog';
        }

        // 3. Cart, Checkout, Coupons, Saudi Shipping & Payments
        if (str_starts_with($key, 'cart_') ||
            str_starts_with($key, 'checkout_') ||
            str_starts_with($key, 'pay_') ||
            str_starts_with($key, 'payment_') ||
            str_starts_with($key, 'mada_') ||
            str_starts_with($key, 'tamara_') ||
            str_starts_with($key, 'tabby_') ||
            str_starts_with($key, 'coupon_') ||
            str_starts_with($key, 'coupons') ||
            str_starts_with($key, 'shipping_') ||
            str_starts_with($key, 'saudi_') ||
            str_starts_with($key, 'city_') ||
            str_starts_with($key, 'cold_') ||
            str_starts_with($key, 'free_cold_') ||
            str_starts_with($key, 'allow_guest_') ||
            str_starts_with($key, 'require_login_') ||
            str_starts_with($key, 'guest_checkout_') ||
            in_array($key, [
                'cart', 'checkout', 'empty_cart', 'empty_cart_hint', 'start_shopping', 
                'subtotal', 'shipping', 'total', 'clear_cart', 'continue_shopping', 
                'proceed_to_checkout', 'item_added', 'item_removed', 'customer_info', 
                'full_name', 'phone_number', 'email_address', 'shipping_details', 
                'delivery_type', 'home_delivery', 'branch_pickup', 'city', 
                'detailed_address', 'order_notes', 'payment_method', 'order_summary', 
                'place_order', 'unit_price', 'free_cold_shipping_banner', 'order_success_title', 
                'order_success_subtitle', 'apply_coupon', 'remove_coupon', 'enter_coupon_code',
                'invalid_coupon', 'discount_percentage', 'discount_fixed', 'add_coupon', 'edit_coupon',
                'times_used', 'usage_limit', 'unlimited_usage', 'start_date', 'end_date', 'active_coupons',
                'shipping_rates', 'cold_shipping_rates', 'saudi_cities', 'shipping_fee', 'estimated_delivery',
                'is_cold_shipping', 'free_shipping_threshold', 'free_shipping_threshold_hint',
                'standard_shipping_fee', 'add_shipping_city', 'edit_shipping_city', 'checkout_policy'
            ])) {
            return 'checkout';
        }

        // 4. Customer Profile, National Addresses & Phone OTP
        if (str_starts_with($key, 'profile_') ||
            str_starts_with($key, 'otp_') ||
            str_starts_with($key, 'demo_otp') ||
            str_starts_with($key, 'my_') ||
            str_starts_with($key, 'address_') ||
            str_starts_with($key, 'addresses_') ||
            str_starts_with($key, 'customer_') ||
            in_array($key, [
                'my_profile', 'my_orders', 'shipping_addresses', 'add_new_address',
                'default_address', 'set_as_default', 'order_details', 'verify_otp', 
                'otp_code', 'resend_code', 'demo_otp_notice', 'login_with_phone_otp', 
                'otp_login_subtitle', 'send_otp_btn', 'phone_login_tab', 'password_login_tab', 
                'verified_account', 'login_for_faster_checkout', 'choose_from_saved_addresses',
                'no_addresses_yet', 'no_addresses_hint', 'add_first_address',
                'multiple_addresses_not_allowed', 'max_addresses_reached', 'new_customer_account',
                'saudi_phone_format', 'login_to_continue'
            ])) {
            return 'profile_otp';
        }

        // 5. ACF & Visual Repeater Builder
        if (str_starts_with($key, 'acf_') ||
            str_starts_with($key, 'repeater_') ||
            str_starts_with($key, 'sub_field') ||
            in_array($key, [
                'custom_fields_acf', 'field_groups', 'add_field_group', 'edit_field_group',
                'group_title_ar', 'group_title_en', 'group_key', 'target_type',
                'target_filter', 'target_page', 'target_post',
                'target_product', 'fields_builder', 'add_field', 'field_label_ar',
                'field_label_en', 'field_name_key', 'field_type', 'field_instructions',
                'field_required', 'field_default_value', 'repeater_subfields',
                'repeater_visual_builder', 'sub_field', 'sub_fields', 'delete_row',
                'add_repeater_row', 'fields_count', 'back_to_field_groups'
            ])) {
            return 'acf';
        }

        // 6. Orders, Cold Shipping Tracking & Invoices
        if (str_starts_with($key, 'order_') ||
            str_starts_with($key, 'step_') ||
            str_starts_with($key, 'tracking_') ||
            in_array($key, [
                'print_invoice', 'order_status', 'shipping_tracking_number', 'track_cold_shipment',
                'delivery_address_label', 
                'payment_method_and_status', 'payment_status_label', 
                'payment_paid_success', 'payment_awaiting_cod', 'items_and_sizes_ordered', 
                'grand_total', 'invoice_footer_thank_you', 'paid', 'pending', 'recent_orders', 'customer'
            ])) {
            return 'orders';
        }

        // 7. Admin Dashboard & Management
        if (str_starts_with($key, 'admin_') ||
            str_starts_with($key, 'role_') ||
            str_starts_with($key, 'status_') ||
            str_starts_with($key, 'menu_') ||
            str_starts_with($key, 'menus_') ||
            str_starts_with($key, 'home_section') ||
            str_starts_with($key, 'layout_') ||
            in_array($key, [
                'add_custom_section', 'custom_section_badge', 'builtin_section_badge',
                'display_style', 'style_grid', 'style_carousel', 'section_layout_style',
                'section_image_optional', 'button_text_ar', 'button_text_en', 'button_url'
            ]) ||
            str_starts_with($key, 'user_') ||
            str_starts_with($key, 'warehouse_') ||
            str_starts_with($key, 'warehouses_') ||
            str_starts_with($key, 'categories_') ||
            str_starts_with($key, 'users_') ||
            in_array($key, [
                'dashboard', 'login', 'register', 'logout', 'total_sales', 'total_orders', 
                'total_products', 'pending_approvals', 'actions', 
                'add_product', 'edit_product', 'delete', 'save_changes', 'variants_and_sizes', 
                'add_size_variant', 'size_title', 'price', 'sale_price', 'stock', 
                'update_settings', 'settings_updated', 'users_roles_management', 
                'structure_and_warehouses', 'system_settings', 'translations_management', 
                'gateways_and_policies', 'low_stock_alerts', 'items_count', 'size',
                'cancel', 'save_user', 'save_category'
            ])) {
            return 'admin';
        }

        // 8. Cache, Logs & Security Suite
        if (str_starts_with($key, 'cache_') ||
            str_starts_with($key, 'logs_') ||
            str_starts_with($key, 'security_') ||
            str_starts_with($key, 'backup_') ||
            str_starts_with($key, 'auto_backup_') ||
            str_starts_with($key, 'restore') ||
            str_starts_with($key, 'confirm_restore_') ||
            in_array($key, [
                'restore', 'choose_file_first', 'every_6_hours', 'every_12_hours',
                'every_3_days', 'daily', 'weekly', 'last_auto_backup', 'save_schedule',
                'preload_cache', 'clear_all_cache', 'total_cache_size', 'cached_items_count',
                'cache_storage_healthy', 'file_driver_active', 'cache_engine', 'in_memory_optimized',
                'response_speed', 'zero_db_overhead', 'cache_partitions', 'cache_partitions_hint',
                'cached_files', 'download_logs', 'clear_logs', 'total_log_entries', 'log_file_size',
                'error_events', 'errors_require_attention', 'brute_force_and_csrf', 'warnings_count',
                'system_warnings_logged', 'all_levels', 'error_level', 'warning_level', 'security_level',
                'info_level', 'search_logs_placeholder', 'logs_feed', 'records_shown',
                'logs_auto_rotated_notice', 'no_logs_found', 'no_logs_found_hint', 'show_context_payload',
                'confirm_clear_logs', 'rating', 'anti_brute_force_on', 'csrf_shield_on',
                'blocked_ips_count', 'firewall_active', 'headers_enforced', 'rate_limit_protection',
                'attempts_per_window', 'decay_window', 'sensitive_files_shield', 'automated_health_assessment',
                'block_ip_address', 'ip_address', 'block_reason', 'suspicious_activity_brute_force',
                'confirm_block_ip', 'blocked_ips_list', 'addresses_blocked', 'no_blocked_ips',
                'clean_firewall_status', 'blocked_at', 'unblock', 'confirm_unblock_ip',
                'max_login_otp_attempts', 'max_attempts_hint', 'rate_limit_lockout_seconds',
                'lockout_seconds_hint', 'save_security_policies'
            ])) {
            return 'security_cache';
        }

        return 'general';
    }

    public function index(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden: Super Admin only', 403);
            return;
        }

        $ar = file_exists($this->arFile) ? require $this->arFile : [];
        $en = file_exists($this->enFile) ? require $this->enFile : [];

        $allKeys = array_unique(array_merge(array_keys($ar), array_keys($en)));
        sort($allKeys);

        $search = trim((string)$request->get('search', ''));
        $currentCat = trim((string)$request->get('cat', 'all'));
        $missingOnly = $request->get('missing') == '1';

        // Categorize all keys and count
        $categoryCounts = [
            'all' => count($allKeys),
            'storefront' => 0,
            'catalog' => 0,
            'checkout' => 0,
            'orders' => 0,
            'profile_otp' => 0,
            'acf' => 0,
            'security_cache' => 0,
            'admin' => 0,
            'general' => 0,
            'missing' => 0,
        ];

        $filtered = [];

        foreach ($allKeys as $key) {
            $arVal = $ar[$key] ?? '';
            $enVal = $en[$key] ?? '';
            $cat = self::categorizeKey($key);

            if (isset($categoryCounts[$cat])) {
                $categoryCounts[$cat]++;
            }

            $isMissing = ($arVal === '' || $enVal === '');
            if ($isMissing) {
                $categoryCounts['missing']++;
            }

            // Category filter
            if ($currentCat !== 'all' && $cat !== $currentCat) {
                continue;
            }

            // Missing filter
            if ($missingOnly && !$isMissing) {
                continue;
            }

            // Search filter
            if (!empty($search)) {
                if (!str_contains(strtolower($key), strtolower($search)) &&
                    !str_contains($arVal, $search) &&
                    !str_contains(strtolower($enVal), strtolower($search))) {
                    continue;
                }
            }

            $filtered[] = [
                'key' => $key,
                'ar' => $arVal,
                'en' => $enVal,
                'category' => $cat,
                'is_missing' => $isMissing
            ];
        }

        Response::view('admin/translations', [
            'translations' => $filtered,
            'totalCount' => count($allKeys),
            'categoryCounts' => $categoryCounts,
            'currentCat' => $currentCat,
            'search' => $search,
            'missingOnly' => $missingOnly,
            'saved' => $request->get('saved') == 1,
            'locale' => I18n::getLocale(),
            'isRtl' => I18n::isRtl()
        ], 'admin');
    }

    public function update(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::json(['success' => false, 'error' => 'Forbidden'], 403);
            return;
        }

        $ar = file_exists($this->arFile) ? require $this->arFile : [];
        $en = file_exists($this->enFile) ? require $this->enFile : [];

        // Check if JSON request or standard POST
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson = str_contains($contentType, 'application/json');

        if ($isJson) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $key = trim((string)($input['key'] ?? ''));
            $arVal = trim((string)($input['ar_val'] ?? ''));
            $enVal = trim((string)($input['en_val'] ?? ''));
        } else {
            $key = trim((string)$request->get('key'));
            $arVal = trim((string)$request->get('ar_val'));
            $enVal = trim((string)$request->get('en_val'));
        }

        if (!empty($key)) {
            $ar[$key] = $arVal;
            $en[$key] = $enVal;

            $this->saveLangFile($this->arFile, $ar);
            $this->saveLangFile($this->enFile, $en);
        }

        if ($isJson || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Response::json([
                'success' => true,
                'message' => 'Translation updated successfully',
                'key' => $key,
                'ar' => $arVal,
                'en' => $enVal
            ]);
            return;
        }

        Response::redirect('/admin/translations?saved=1');
    }

    public function delete(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::json(['success' => false, 'error' => 'Forbidden'], 403);
            return;
        }

        $ar = file_exists($this->arFile) ? require $this->arFile : [];
        $en = file_exists($this->enFile) ? require $this->enFile : [];

        $key = trim((string)$request->get('key'));

        if (!empty($key)) {
            unset($ar[$key]);
            unset($en[$key]);

            $this->saveLangFile($this->arFile, $ar);
            $this->saveLangFile($this->enFile, $en);
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            Response::json(['success' => true, 'message' => 'Key deleted successfully', 'key' => $key]);
            return;
        }

        Response::redirect('/admin/translations?saved=1');
    }

    private function saveLangFile(string $filePath, array $data): void
    {
        $content = "<?php\n\nreturn " . var_export($data, true) . ";\n";
        file_put_contents($filePath, $content, LOCK_EX);
        if (class_exists('\\App\\Core\\Cache')) {
            \App\Core\Cache::flush('translations');
        }
    }
}
