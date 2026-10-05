<?php

namespace Database;

use PDO;

class Migrator
{
    public static function run(): void
    {
        $pdo = Database::getConnection();
        $isSqlite = Database::getDriver() === 'sqlite';

        $autoInc = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $decimal = $isSqlite ? 'NUMERIC' : 'DECIMAL(10,2)';
        $timestamp = 'DATETIME DEFAULT CURRENT_TIMESTAMP';

        // 1. Roles table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS roles (
                id $autoInc,
                name VARCHAR(50) NOT NULL UNIQUE,
                display_name_ar VARCHAR(100) NOT NULL,
                display_name_en VARCHAR(100) NOT NULL,
                permissions TEXT NULL
            );
        ");

        // 2. Users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id $autoInc,
                role_id INT NOT NULL,
                school_id INT NULL DEFAULT 0,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NULL,
                status VARCHAR(20) DEFAULT 'active',
                phone_verified TINYINT(1) DEFAULT 0,
                otp_code VARCHAR(10) NULL,
                otp_expires_at $timestamp,
                created_at $timestamp
            );
        ");

        // 3. Categories (Tumurna Dates Categories)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS categories (
                id $autoInc,
                stage_id INT NULL DEFAULT 0,
                name_ar VARCHAR(150) NOT NULL,
                name_en VARCHAR(150) NOT NULL,
                slug VARCHAR(120) NOT NULL UNIQUE,
                description_ar TEXT NULL,
                description_en TEXT NULL,
                icon VARCHAR(50) NULL,
                image VARCHAR(255) NULL,
                order_index INT DEFAULT 0,
                created_at $timestamp
            );
        ");

        // 4. Products (Tumurna Dates & Packages)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS products (
                id $autoInc,
                category_id INT NOT NULL DEFAULT 1,
                school_id INT NULL DEFAULT 0,
                stage_id INT NULL DEFAULT 0,
                name_ar VARCHAR(250) NOT NULL,
                name_en VARCHAR(250) NOT NULL,
                slug VARCHAR(200) NOT NULL UNIQUE,
                description_ar MEDIUMTEXT NULL,
                description_en MEDIUMTEXT NULL,
                price $decimal NOT NULL DEFAULT 0,
                sale_price $decimal NULL,
                weight VARCHAR(80) DEFAULT '1 كجم',
                stock_quantity INT DEFAULT 50,
                sku VARCHAR(100) NULL,
                badge VARCHAR(80) NULL,
                badge_en VARCHAR(80) NULL,
                is_preorder TINYINT(1) DEFAULT 0,
                preorder_date VARCHAR(100) NULL,
                is_fragile TINYINT(1) DEFAULT 0,
                rating $decimal DEFAULT 4.9,
                rating_count INT DEFAULT 45,
                sales_count INT DEFAULT 120,
                is_featured TINYINT(1) DEFAULT 1,
                is_bestseller TINYINT(1) DEFAULT 0,
                featured_image VARCHAR(255) NULL,
                gallery TEXT NULL,
                status VARCHAR(20) DEFAULT 'published',
                created_at $timestamp
            );
        ");

        // 5. Product Variants (Pack sizes, weights, packaging)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS product_variants (
                id $autoInc,
                product_id INT NOT NULL,
                size_name VARCHAR(100) NOT NULL,
                size_name_en VARCHAR(100) NULL,
                sku VARCHAR(100) NULL,
                price $decimal NOT NULL,
                sale_price $decimal NULL,
                stock_quantity INT DEFAULT 50,
                weight_kg $decimal DEFAULT 1.0,
                created_at $timestamp
            );
        ");

        // 6. Banners (Hero slider & promotional)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS banners (
                id $autoInc,
                badge_ar VARCHAR(100) NULL,
                badge_en VARCHAR(100) NULL,
                title_ar VARCHAR(255) NOT NULL,
                title_en VARCHAR(255) NULL,
                subtitle_ar VARCHAR(255) NULL,
                subtitle_en VARCHAR(255) NULL,
                cta_ar VARCHAR(100) DEFAULT 'تسوق الآن',
                cta_en VARCHAR(100) DEFAULT 'Shop Now',
                link VARCHAR(255) DEFAULT '/catalog',
                image VARCHAR(255) NOT NULL,
                order_index INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'active',
                created_at $timestamp
            );
        ");

        // 7. Orders
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id $autoInc,
                order_number VARCHAR(50) NOT NULL UNIQUE,
                user_id INT NULL,
                customer_name VARCHAR(150) NOT NULL,
                customer_phone VARCHAR(50) NOT NULL,
                customer_email VARCHAR(150) NULL,
                shipping_address TEXT NOT NULL,
                governorate VARCHAR(100) NULL DEFAULT 'الرياض',
                city VARCHAR(100) NOT NULL DEFAULT 'الرياض',
                district VARCHAR(100) NULL,
                shipping_type VARCHAR(50) DEFAULT 'home_delivery',
                subtotal $decimal NOT NULL,
                shipping_fee $decimal DEFAULT 0,
                discount $decimal DEFAULT 0,
                total $decimal NOT NULL,
                payment_method VARCHAR(50) DEFAULT 'mada',
                payment_status VARCHAR(50) DEFAULT 'paid',
                payment_reference VARCHAR(100) NULL,
                shipping_service VARCHAR(50) DEFAULT 'cold_freight',
                shipping_status VARCHAR(50) DEFAULT 'processing',
                notes TEXT NULL,
                created_at $timestamp
            );
        ");

        // 8. Order Items
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS order_items (
                id $autoInc,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                variant_id INT NULL,
                school_id INT NULL DEFAULT 0,
                product_name VARCHAR(200) NOT NULL,
                size_name VARCHAR(100) NULL,
                unit_price $decimal NOT NULL,
                quantity INT NOT NULL,
                subtotal $decimal NOT NULL
            );
        ");

        // 9. Gift Cards (Royal Tumurna Gift Cards)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS gift_cards (
                id $autoInc,
                code VARCHAR(50) NOT NULL UNIQUE,
                card_type VARCHAR(30) DEFAULT 'digital',
                amount $decimal NOT NULL,
                balance $decimal NOT NULL,
                sender_name VARCHAR(150) NOT NULL,
                recipient_name VARCHAR(150) NOT NULL,
                recipient_phone VARCHAR(50) NULL,
                recipient_email VARCHAR(150) NULL,
                message TEXT NULL,
                delivery_date VARCHAR(50) NULL,
                status VARCHAR(20) DEFAULT 'active',
                created_at $timestamp
            );
        ");

        // 10. Wishlists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS wishlists (
                id $autoInc,
                user_id INT NULL,
                session_id VARCHAR(100) NULL,
                product_id INT NOT NULL,
                created_at $timestamp
            );
        ");

        // 11. User Addresses
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_addresses (
                id $autoInc,
                user_id INT NOT NULL,
                title VARCHAR(100) NULL,
                recipient_name VARCHAR(100) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                governorate VARCHAR(100) NULL,
                city VARCHAR(100) NOT NULL,
                district VARCHAR(100) NULL,
                address TEXT NULL,
                street_address VARCHAR(255) NULL,
                building_floor VARCHAR(100) NULL,
                landmark VARCHAR(255) NULL,
                postal_code VARCHAR(20) NULL,
                is_default TINYINT(1) DEFAULT 0,
                created_at $timestamp
            );
        ");

        // 12. Settings
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS settings (
                `key` VARCHAR(100) PRIMARY KEY,
                `value` TEXT NULL
            );
        ");

        // 13. Pages (CMS)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS pages (
                id $autoInc,
                title_ar VARCHAR(255) NOT NULL,
                title_en VARCHAR(255) NOT NULL,
                slug VARCHAR(200) NOT NULL UNIQUE,
                content_ar MEDIUMTEXT NULL,
                content_en MEDIUMTEXT NULL,
                excerpt_ar TEXT NULL,
                excerpt_en TEXT NULL,
                featured_image VARCHAR(255) NULL,
                template VARCHAR(50) DEFAULT 'default',
                status VARCHAR(20) DEFAULT 'published',
                meta_title_ar VARCHAR(255) NULL,
                meta_title_en VARCHAR(255) NULL,
                meta_desc_ar TEXT NULL,
                meta_desc_en TEXT NULL,
                created_at $timestamp,
                updated_at $timestamp
            );
        ");

        // 14. Posts (Blog & Stories)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS posts (
                id $autoInc,
                post_type VARCHAR(50) DEFAULT 'post',
                title_ar VARCHAR(255) NOT NULL,
                title_en VARCHAR(255) NOT NULL,
                slug VARCHAR(200) NOT NULL UNIQUE,
                content_ar MEDIUMTEXT NULL,
                content_en MEDIUMTEXT NULL,
                excerpt_ar TEXT NULL,
                excerpt_en TEXT NULL,
                featured_image VARCHAR(255) NULL,
                status VARCHAR(20) DEFAULT 'published',
                school_id INT NULL DEFAULT 0,
                author_id INT NULL DEFAULT 1,
                created_at $timestamp,
                updated_at $timestamp
            );
        ");

        // 15. ACF Field Groups & Fields
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS custom_field_groups (
                id $autoInc,
                title_ar VARCHAR(150) NOT NULL,
                title_en VARCHAR(150) NOT NULL,
                key_name VARCHAR(100) NOT NULL UNIQUE,
                target_type VARCHAR(50) DEFAULT 'page',
                target_filter VARCHAR(100) DEFAULT 'all',
                position VARCHAR(20) DEFAULT 'normal',
                order_index INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'active',
                created_at $timestamp
            );
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS custom_fields (
                id $autoInc,
                group_id INT NOT NULL,
                label_ar VARCHAR(150) NOT NULL,
                label_en VARCHAR(150) NOT NULL,
                name VARCHAR(100) NOT NULL,
                type VARCHAR(50) NOT NULL,
                instructions_ar TEXT NULL,
                instructions_en TEXT NULL,
                is_required TINYINT(1) DEFAULT 0,
                default_value TEXT NULL,
                placeholder_ar VARCHAR(255) NULL,
                placeholder_en VARCHAR(255) NULL,
                options_data TEXT NULL,
                parent_field_id INT NULL,
                order_index INT DEFAULT 0,
                created_at $timestamp
            );
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS custom_field_values (
                id $autoInc,
                field_id INT NOT NULL,
                entity_type VARCHAR(50) NOT NULL,
                entity_id INT NOT NULL,
                row_index INT DEFAULT 0,
                sub_field_name VARCHAR(100) NULL,
                value_text MEDIUMTEXT NULL,
                created_at $timestamp
            );
        ");

        // 16. Menu Items
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS menu_items (
                id $autoInc,
                location VARCHAR(50) DEFAULT 'header',
                title_ar VARCHAR(100) NOT NULL,
                title_en VARCHAR(100) NOT NULL,
                link_type VARCHAR(50) DEFAULT 'custom',
                custom_url VARCHAR(255) NULL,
                page_id INT NULL,
                order_index INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'active'
            );
        ");

        // 17. Homepage Sections
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS home_sections (
                id $autoInc,
                section_key VARCHAR(100) NOT NULL UNIQUE,
                name_ar VARCHAR(150) NOT NULL,
                name_en VARCHAR(150) NOT NULL,
                title_ar VARCHAR(255) NULL,
                title_en VARCHAR(255) NULL,
                subtitle_ar VARCHAR(255) NULL,
                subtitle_en VARCHAR(255) NULL,
                badge_ar VARCHAR(100) NULL,
                badge_en VARCHAR(100) NULL,
                image VARCHAR(255) NULL,
                button_text_ar VARCHAR(100) NULL,
                button_text_en VARCHAR(100) NULL,
                button_url VARCHAR(255) NULL,
                style VARCHAR(50) DEFAULT 'default',
                is_custom TINYINT(1) DEFAULT 0,
                order_index INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'active',
                style_options TEXT NULL,
                custom_content_ar MEDIUMTEXT NULL,
                custom_content_en MEDIUMTEXT NULL,
                settings_json LONGTEXT NULL
            );
        ");

        // Older installations created this table before the homepage CMS fields existed.
        $sectionColumns = [
            'title_ar' => 'VARCHAR(255) NULL',
            'title_en' => 'VARCHAR(255) NULL',
            'subtitle_ar' => 'VARCHAR(255) NULL',
            'subtitle_en' => 'VARCHAR(255) NULL',
            'badge_ar' => 'VARCHAR(100) NULL',
            'badge_en' => 'VARCHAR(100) NULL',
            'image' => 'VARCHAR(255) NULL',
            'button_text_ar' => 'VARCHAR(100) NULL',
            'button_text_en' => 'VARCHAR(100) NULL',
            'button_url' => 'VARCHAR(255) NULL',
            'style' => "VARCHAR(50) DEFAULT 'default'",
            'is_custom' => 'TINYINT(1) DEFAULT 0',
            'settings_json' => 'LONGTEXT NULL',
        ];
        $existingSectionColumns = $isSqlite
            ? array_column($pdo->query('PRAGMA table_info(home_sections)')->fetchAll(PDO::FETCH_ASSOC), 'name')
            : array_column($pdo->query('SHOW COLUMNS FROM home_sections')->fetchAll(PDO::FETCH_ASSOC), 'Field');
        foreach ($sectionColumns as $column => $definition) {
            if (!in_array($column, $existingSectionColumns, true)) {
                $pdo->exec("ALTER TABLE home_sections ADD COLUMN `$column` $definition");
            }
        }

        // 18. Live Translations
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS translations (
                id $autoInc,
                `key` VARCHAR(150) NOT NULL UNIQUE,
                ar TEXT NOT NULL,
                en TEXT NOT NULL
            );
        ");

        // 19. Security & Logs
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS security_logs (
                id $autoInc,
                ip_address VARCHAR(50) NOT NULL,
                event_type VARCHAR(100) NOT NULL,
                severity VARCHAR(20) DEFAULT 'warning',
                details TEXT NULL,
                request_uri VARCHAR(255) NULL,
                user_agent VARCHAR(255) NULL,
                created_at $timestamp
            );
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS blocked_ips (
                id $autoInc,
                ip_address VARCHAR(50) NOT NULL UNIQUE,
                reason VARCHAR(255) NULL,
                blocked_by VARCHAR(50) DEFAULT 'firewall',
                expires_at DATETIME NULL,
                created_at $timestamp
            );
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS otp_codes (
                id $autoInc,
                phone VARCHAR(50) NOT NULL,
                code VARCHAR(10) NOT NULL,
                action VARCHAR(50) DEFAULT 'login',
                expires_at DATETIME NOT NULL,
                created_at $timestamp
            );
        ");

        // 20. Warehouses
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS warehouses (
                id $autoInc,
                name_ar VARCHAR(150) NOT NULL,
                name_en VARCHAR(150) NOT NULL,
                code VARCHAR(50) NULL,
                location VARCHAR(255) NULL,
                phone VARCHAR(50) NULL,
                status VARCHAR(20) DEFAULT 'active',
                created_at $timestamp
            );
        ");

        // 21. Compatibility Stubs (Schools & Stages to ensure zero runtime SQL errors)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS schools (
                id $autoInc,
                name_ar VARCHAR(150) NOT NULL DEFAULT 'مزارع ومستودعات تمرنا',
                name_en VARCHAR(150) NOT NULL DEFAULT 'Tumurna Warehouses & Farms',
                slug VARCHAR(150) NOT NULL UNIQUE DEFAULT 'tumurna-main',
                code VARCHAR(50) NULL,
                logo VARCHAR(255) NULL,
                banner VARCHAR(255) NULL,
                phone VARCHAR(50) NULL,
                email VARCHAR(100) NULL,
                address VARCHAR(255) NULL,
                address_en VARCHAR(255) NULL,
                city VARCHAR(100) NULL,
                city_en VARCHAR(100) NULL,
                warehouse_name VARCHAR(150) NULL,
                warehouse_name_en VARCHAR(150) NULL,
                status VARCHAR(20) DEFAULT 'active',
                created_at $timestamp
            );
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS educational_stages (
                id $autoInc,
                school_id INT NOT NULL DEFAULT 1,
                name_ar VARCHAR(100) NOT NULL DEFAULT 'تمور عامة',
                name_en VARCHAR(100) NOT NULL DEFAULT 'General Dates',
                slug VARCHAR(100) NOT NULL DEFAULT 'general',
                order_index INT DEFAULT 0,
                created_at $timestamp
            );
        ");

        // 23. Coupons Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS coupons (
                id $autoInc,
                code VARCHAR(50) NOT NULL UNIQUE,
                discount_type VARCHAR(20) DEFAULT 'percentage',
                discount_value $decimal NOT NULL,
                min_order_amount $decimal DEFAULT 0.00,
                usage_limit INT NULL,
                times_used INT DEFAULT 0,
                start_date DATE NULL,
                end_date DATE NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at $timestamp,
                updated_at $timestamp
            );
        ");

        // 24. Shipping Zones Table (Saudi Arabia Cities)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS shipping_zones (
                id $autoInc,
                city_name_ar VARCHAR(100) NOT NULL UNIQUE,
                city_name_en VARCHAR(100) NOT NULL,
                shipping_fee $decimal NOT NULL DEFAULT 25.00,
                estimated_delivery VARCHAR(150) DEFAULT 'توصيل مبرد خلال 24-48 ساعة',
                is_cold_shipping TINYINT(1) DEFAULT 1,
                is_active TINYINT(1) DEFAULT 1,
                created_at $timestamp
            );
        ");

        // Ensure coupon_code column exists in orders & OTP columns in users
        try {
            $isSqlite = Database::getDriver() === 'sqlite';
            if (!$isSqlite) {
                $orderCols = array_column(Database::fetchAll("SHOW COLUMNS FROM orders"), 'Field');
                if (!in_array('coupon_code', $orderCols)) {
                    $pdo->exec("ALTER TABLE orders ADD COLUMN coupon_code VARCHAR(50) NULL AFTER discount");
                }

                $userCols = array_column(Database::fetchAll("SHOW COLUMNS FROM users"), 'Field');
                if (!in_array('phone_verified', $userCols)) {
                    $pdo->exec("ALTER TABLE users ADD COLUMN phone_verified TINYINT(1) DEFAULT 0 AFTER status");
                }
                if (!in_array('otp_code', $userCols)) {
                    $pdo->exec("ALTER TABLE users ADD COLUMN otp_code VARCHAR(10) NULL AFTER phone_verified");
                }
                if (!in_array('otp_expires_at', $userCols)) {
                    $pdo->exec("ALTER TABLE users ADD COLUMN otp_expires_at DATETIME NULL AFTER otp_code");
                }

                $addrCols = array_column(Database::fetchAll("SHOW COLUMNS FROM user_addresses"), 'Field');
                if (!in_array('title', $addrCols)) {
                    $pdo->exec("ALTER TABLE user_addresses ADD COLUMN title VARCHAR(100) NULL AFTER user_id");
                }
                if (!in_array('street_address', $addrCols)) {
                    $pdo->exec("ALTER TABLE user_addresses ADD COLUMN street_address VARCHAR(255) NULL AFTER address");
                }
                if (!in_array('building_floor', $addrCols)) {
                    $pdo->exec("ALTER TABLE user_addresses ADD COLUMN building_floor VARCHAR(100) NULL AFTER street_address");
                }
                if (!in_array('landmark', $addrCols)) {
                    $pdo->exec("ALTER TABLE user_addresses ADD COLUMN landmark VARCHAR(255) NULL AFTER building_floor");
                }

                // Gift cards bought through checkout are linked to their order
                $gcCols = array_column(Database::fetchAll("SHOW COLUMNS FROM gift_cards"), 'Field');
                if (!in_array('order_id', $gcCols)) {
                    $pdo->exec("ALTER TABLE gift_cards ADD COLUMN order_id INT NULL AFTER status");
                }
                if (!in_array('sender_phone', $gcCols)) {
                    $pdo->exec("ALTER TABLE gift_cards ADD COLUMN sender_phone VARCHAR(50) NULL AFTER sender_name");
                }

                // Menu items: the admin menu manager writes label_*/icon/open_new_tab
                $menuColRows = Database::fetchAll("SHOW COLUMNS FROM menu_items");
                $menuCols = array_column($menuColRows, 'Field');
                $menuNullable = array_column($menuColRows, 'Null', 'Field');
                if (!in_array('label_ar', $menuCols)) {
                    $pdo->exec("ALTER TABLE menu_items ADD COLUMN label_ar VARCHAR(100) NULL AFTER title_en");
                    $pdo->exec("UPDATE menu_items SET label_ar = title_ar WHERE label_ar IS NULL");
                }
                if (!in_array('label_en', $menuCols)) {
                    $pdo->exec("ALTER TABLE menu_items ADD COLUMN label_en VARCHAR(100) NULL AFTER label_ar");
                    $pdo->exec("UPDATE menu_items SET label_en = title_en WHERE label_en IS NULL");
                }
                if (!in_array('icon', $menuCols)) {
                    $pdo->exec("ALTER TABLE menu_items ADD COLUMN icon VARCHAR(50) NULL AFTER page_id");
                }
                if (!in_array('open_new_tab', $menuCols)) {
                    $pdo->exec("ALTER TABLE menu_items ADD COLUMN open_new_tab TINYINT(1) DEFAULT 0 AFTER icon");
                }
                if (($menuNullable['title_ar'] ?? 'YES') === 'NO') {
                    $pdo->exec("ALTER TABLE menu_items MODIFY title_ar VARCHAR(100) NULL, MODIFY title_en VARCHAR(100) NULL");
                }
            }
        } catch (\Throwable $e) {}
    }

    public static function seed(): void

    {
        $pdo = Database::getConnection();

        // 1. Seed Roles
        $rolesCount = (int)Database::fetchOne("SELECT COUNT(*) as c FROM roles")['c'];
        if ($rolesCount === 0) {
            $pdo->exec("
                INSERT INTO roles (name, display_name_ar, display_name_en, permissions) VALUES
                ('super_admin', 'المدير العام للمتجر', 'Super Admin', '*'),
                ('store_manager', 'مدير المتجر والمخزون', 'Store Manager', 'products,categories,orders,stock'),
                ('customer', 'عميل مسجل', 'Customer', 'account,orders')
            ");
        }

        // 2. Seed / Ensure Primary Admin User
        $adminPassword = trim((string) env('ADMIN_PASSWORD', 'Admin@12345'));
        if ($adminPassword === '' || $adminPassword === 'replace_with_a_strong_unique_password') {
            $adminPassword = 'Admin@12345';
        }
        $adminHash = password_hash($adminPassword, PASSWORD_DEFAULT);

        $superAdminRole = Database::fetchOne("SELECT id FROM roles WHERE name = 'super_admin'");
        $superAdminRoleId = $superAdminRole ? (int)$superAdminRole['id'] : 1;

        $adminUser = Database::fetchOne("SELECT id, password FROM users WHERE email = 'admin@tumurna.com'");
        if (!$adminUser) {
            $stmt = $pdo->prepare("
                INSERT INTO users (role_id, school_id, name, email, password, phone, status, phone_verified)
                VALUES (?, 1, 'مدير متجر تمرنا', 'admin@tumurna.com', ?, '0555000000', 'active', 1)
            ");
            $stmt->execute([$superAdminRoleId, $adminHash]);
        } else {
            // Update password to match configured ADMIN_PASSWORD / Admin@12345 if needed
            if (!password_verify($adminPassword, $adminUser['password'])) {
                $stmt = $pdo->prepare("UPDATE users SET password = ?, role_id = ?, status = 'active' WHERE id = ?");
                $stmt->execute([$adminHash, $superAdminRoleId, $adminUser['id']]);
            }
        }

        // 3. Seed Tumurna Categories
        $categoriesCount = (int)Database::fetchOne("SELECT COUNT(*) as c FROM categories")['c'];
        if ($categoriesCount === 0) {
            $categories = [
                [
                    'id' => 1,
                    'name_ar' => 'عجوة المدينة الفاخرة',
                    'name_en' => 'Luxury Madinah Ajwa',
                    'slug' => 'ajwa',
                    'description_ar' => 'عجوة المدينة المنورة الأصلية الفاخرة منتقاة بعناية فائقة من مزارع العالية المباركة.',
                    'description_en' => 'Authentic Madinah Ajwa dates handpicked from blessed farms.',
                    'image' => 'assets/images/ajwa_luxury_box_1787053900509.jpg',
                    'icon' => 'sparkles',
                    'order_index' => 1
                ],
                [
                    'id' => 2,
                    'name_ar' => 'سكري القصيم الملكي',
                    'name_en' => 'Royal Qassim Sukari',
                    'slug' => 'sukari',
                    'description_ar' => 'سكري مفتل ومكنوز فاخر بقوام ذهبي ومذاق غني ساحر، رمز الكرم الأصيل.',
                    'description_en' => 'Golden crunchy and soft Sukari dates with rich natural sweetness.',
                    'image' => 'assets/images/golden_sukari_dates_1787053915087.jpg',
                    'icon' => 'award',
                    'order_index' => 2
                ],
                [
                    'id' => 3,
                    'name_ar' => 'خلاص الأحساء والخرج',
                    'name_en' => 'Premium Khalas Dates',
                    'slug' => 'khalas',
                    'description_ar' => 'أجود حبات الخلاص الفاخر مكنوز بحرفية ومغلف بأعلى معايير الجودة العالمية.',
                    'description_en' => 'Traditional pressed Khalas dates with amber hue and caramel notes.',
                    'image' => 'assets/images/fresh_ruthab_harvest_1787053947518.jpg',
                    'icon' => 'star',
                    'order_index' => 3
                ],
                [
                    'id' => 4,
                    'name_ar' => 'صقعي ومحشيات فاخرة',
                    'name_en' => 'Sagae & Stuffed Luxury',
                    'slug' => 'sagae',
                    'description_ar' => 'تمور صقعي ممتازة وتشكيلات تمور محشية بأفخر أنواع اللوز والبيكان والفستق البلجيكي.',
                    'description_en' => 'Artisanal stuffed dates with roasted almonds, pecans, and Belgian chocolate.',
                    'image' => 'assets/images/golden_sukari_dates_1787053915087.jpg',
                    'icon' => 'shield-check',
                    'order_index' => 4
                ],
                [
                    'id' => 5,
                    'name_ar' => 'مجدول ملكي سوبر جامبو',
                    'name_en' => 'Royal Medjool Super Jumbo',
                    'slug' => 'medjool',
                    'description_ar' => 'حبات مجدول ضخمة طرية ذات جودة تصديرية استثنائية تناسب المناسبات الكبرى.',
                    'description_en' => 'Giant succulent Medjool dates with soft velvety texture.',
                    'image' => 'assets/images/ajwa_luxury_box_1787053900509.jpg',
                    'icon' => 'award',
                    'order_index' => 5
                ],
                [
                    'id' => 6,
                    'name_ar' => 'بوكسات وباقات الإهداء',
                    'name_en' => 'Luxury Gift Boxes',
                    'slug' => 'gift-boxes',
                    'description_ar' => 'صناديق خشبية ومخملية فاخرة تناسب كبار الشخصيات وحفلات الاستقبال الملكية.',
                    'description_en' => 'Handcrafted wooden trunks and velvet boxes perfect for VIP gifting.',
                    'image' => 'assets/images/royal_dates_gift_package_1787053933879.jpg',
                    'icon' => 'gift',
                    'order_index' => 6
                ]
            ];

            $catStmt = $pdo->prepare("
                INSERT INTO categories (id, name_ar, name_en, slug, description_ar, description_en, image, icon, order_index)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($categories as $c) {
                $catStmt->execute([
                    $c['id'], $c['name_ar'], $c['name_en'], $c['slug'],
                    $c['description_ar'], $c['description_en'], $c['image'], $c['icon'], $c['order_index']
                ]);
            }
        }

        // 4. Seed Tumurna Products
        $productsCount = (int)Database::fetchOne("SELECT COUNT(*) as c FROM products")['c'];
        if ($productsCount === 0) {
            $products = [
                [
                    'id' => 1,
                    'category_id' => 1,
                    'slug' => 'madinah-ajwa-luxury',
                    'name_ar' => 'عجوة المدينة المنورة العالية الملكية',
                    'name_en' => 'Royal Madinah Al-Aliyah Ajwa',
                    'description_ar' => 'عجوة حبة كبيرة درجة أولى فاخرة من مزارع العالية بالمدينة المنورة، حبات سوداء طرية غنية بالفوائد ومطابقة لمواصفات الهيئة العامة للغذاء والدواء.',
                    'description_en' => 'First-grade large Al-Aliyah Ajwa from Madinah, known for high quality and authentic flavor.',
                    'price' => 180.00,
                    'sale_price' => 220.00,
                    'weight' => '1 كجم',
                    'stock_quantity' => 45,
                    'sku' => 'TUM-AJW-01',
                    'badge' => 'الأكثر طلباً',
                    'badge_en' => 'Bestseller',
                    'is_preorder' => 0,
                    'preorder_date' => null,
                    'is_fragile' => 0,
                    'rating' => 4.9,
                    'rating_count' => 158,
                    'sales_count' => 342,
                    'is_featured' => 1,
                    'is_bestseller' => 1,
                    'featured_image' => 'assets/images/ajwa_luxury_box_1787053900509.jpg',
                    'variants' => [
                        ['size_name' => 'عبوة 500 جرام', 'size_name_en' => '500g Pack', 'price' => 95.00, 'sale_price' => null, 'stock' => 50, 'weight' => 0.5],
                        ['size_name' => 'صندوق 1 كجم فاخر', 'size_name_en' => '1kg Luxury Box', 'price' => 180.00, 'sale_price' => 220.00, 'stock' => 45, 'weight' => 1.0],
                        ['size_name' => 'كرتون ملكي 3 كجم', 'size_name_en' => '3kg Royal Carton', 'price' => 510.00, 'sale_price' => 590.00, 'stock' => 20, 'weight' => 3.0]
                    ]
                ],
                [
                    'id' => 2,
                    'category_id' => 2,
                    'slug' => 'qassim-sukari-muftal',
                    'name_ar' => 'سكري القصيم مفتل درجة أولى - صندوق فاخر',
                    'name_en' => 'Qassim Muftal Sukari - Luxury Box',
                    'description_ar' => 'سكري القصيم المفتل حبة شقراء ذهبية مقرمشة ولذيذة، معبأة في عبوة محكمة الإغلاق لحفظ النكهة والرطوبة المثالية.',
                    'description_en' => 'Golden crunchy Sukari dates from the heart of Al-Qassim palm groves.',
                    'price' => 95.00,
                    'sale_price' => 120.00,
                    'weight' => '1 كجم',
                    'stock_quantity' => 60,
                    'sku' => 'TUM-SUK-02',
                    'badge' => 'خصم 20%',
                    'badge_en' => '20% Off',
                    'is_preorder' => 0,
                    'preorder_date' => null,
                    'is_fragile' => 0,
                    'rating' => 4.8,
                    'rating_count' => 203,
                    'sales_count' => 512,
                    'is_featured' => 1,
                    'is_bestseller' => 1,
                    'featured_image' => 'assets/images/golden_sukari_dates_1787053915087.jpg',
                    'variants' => [
                        ['size_name' => 'عبوة 1 كجم', 'size_name_en' => '1kg Pack', 'price' => 95.00, 'sale_price' => 120.00, 'stock' => 60, 'weight' => 1.0],
                        ['size_name' => 'كرتون ضيافة 3 كجم', 'size_name_en' => '3kg Hospitality Box', 'price' => 260.00, 'sale_price' => 310.00, 'stock' => 35, 'weight' => 3.0]
                    ]
                ],
                [
                    'id' => 3,
                    'category_id' => 3,
                    'slug' => 'al-ahsa-khalas-pressed',
                    'name_ar' => 'خلاص الأحساء الفاخر مكنوز يدوي',
                    'name_en' => 'Hand-pressed Al-Ahsa Khalas',
                    'description_ar' => 'خلاص أحسائي تقليدي مكنوز بالدبس الطبيعي بدون إضافات صناعية، طعم أصيل ولون عنبري يجسد كرم الضيافة السعودية.',
                    'description_en' => 'Traditional pressed Khalas with natural date molasses and rich amber flavor.',
                    'price' => 65.00,
                    'sale_price' => null,
                    'weight' => '1 كجم',
                    'stock_quantity' => 80,
                    'sku' => 'TUM-KHL-03',
                    'badge' => 'طبيعي 100%',
                    'badge_en' => '100% Natural',
                    'is_preorder' => 0,
                    'preorder_date' => null,
                    'is_fragile' => 0,
                    'rating' => 4.7,
                    'rating_count' => 120,
                    'sales_count' => 290,
                    'is_featured' => 0,
                    'is_bestseller' => 1,
                    'featured_image' => 'assets/images/fresh_ruthab_harvest_1787053947518.jpg',
                    'variants' => [
                        ['size_name' => 'كيس مكنوز 1 كجم', 'size_name_en' => '1kg Vacuum Bag', 'price' => 65.00, 'sale_price' => null, 'stock' => 80, 'weight' => 1.0],
                        ['size_name' => 'كرتون 8 أكياس (8 كجم)', 'size_name_en' => 'Carton 8x1kg', 'price' => 480.00, 'sale_price' => 520.00, 'stock' => 25, 'weight' => 8.0]
                    ]
                ],
                [
                    'id' => 4,
                    'category_id' => 4,
                    'slug' => 'stuffed-sagae-dates-platter',
                    'name_ar' => 'صندوق تمور صقعي محشوة باللوز والبيكان والكراميل (طبق كريستال)',
                    'name_en' => 'Stuffed Sagae Dates in Artisanal Glass Platter',
                    'description_ar' => 'طبق زجاجي كريستالي فاخر يحتوي على تشكيلة صقعي منتقاة بحبات اللوز المحمص والبيكان والكراميل البلجيكي. تنبيه: هذا المنتج حساس وقابل للكسر ويوصى بالاستلام من الفرع أو التوصيل المحلي.',
                    'description_en' => 'Artisanal stuffed Sagae dates with premium nuts in an elegant crystal tray.',
                    'price' => 240.00,
                    'sale_price' => 290.00,
                    'weight' => '1.2 كجم',
                    'stock_quantity' => 18,
                    'sku' => 'TUM-SAG-04',
                    'badge' => 'إهداء فاخر',
                    'badge_en' => 'Luxury Gift',
                    'is_preorder' => 0,
                    'preorder_date' => null,
                    'is_fragile' => 1,
                    'rating' => 4.9,
                    'rating_count' => 39,
                    'sales_count' => 88,
                    'is_featured' => 1,
                    'is_bestseller' => 0,
                    'featured_image' => 'assets/images/golden_sukari_dates_1787053915087.jpg',
                    'variants' => [
                        ['size_name' => 'طبق كريستال 1.2 كجم', 'size_name_en' => 'Crystal Platter 1.2kg', 'price' => 240.00, 'sale_price' => 290.00, 'stock' => 18, 'weight' => 1.2]
                    ]
                ],
                [
                    'id' => 5,
                    'category_id' => 5,
                    'slug' => 'super-jumbo-medjool',
                    'name_ar' => 'مجدول ملكي سوبر جامبو - تصدير خاص',
                    'name_en' => 'Super Jumbo Medjool - Exclusive Selection',
                    'description_ar' => 'تمور المجدول الفاخرة بحجم سوبر جامبو، ملمس مخملي وقوام كراميلي غني، منتقاة حبة بحبة لتلائم أرقى المناسبات والضيافة.',
                    'description_en' => 'Extra large Medjool dates with soft chewy texture and natural caramel richness.',
                    'price' => 135.00,
                    'sale_price' => null,
                    'weight' => '1 كجم',
                    'stock_quantity' => 30,
                    'sku' => 'TUM-MED-05',
                    'badge' => 'سوبر جامبو',
                    'badge_en' => 'Super Jumbo',
                    'is_preorder' => 0,
                    'preorder_date' => null,
                    'is_fragile' => 0,
                    'rating' => 4.8,
                    'rating_count' => 77,
                    'sales_count' => 140,
                    'is_featured' => 1,
                    'is_bestseller' => 0,
                    'featured_image' => 'assets/images/ajwa_luxury_box_1787053900509.jpg',
                    'variants' => [
                        ['size_name' => 'عبوة 1 كجم جامبو', 'size_name_en' => '1kg Jumbo Box', 'price' => 135.00, 'sale_price' => null, 'stock' => 30, 'weight' => 1.0],
                        ['size_name' => 'صندوق إهداء 2 كجم', 'size_name_en' => '2kg Gift Box', 'price' => 260.00, 'sale_price' => 280.00, 'stock' => 15, 'weight' => 2.0]
                    ]
                ],
                [
                    'id' => 6,
                    'category_id' => 1,
                    'slug' => 'fresh-madinah-ruthana-preorder',
                    'name_ar' => 'رطب روثانة المدينة المنورة الطازج (موسم الحصاد القادم)',
                    'name_en' => 'Fresh Madinah Ruthana Harvest (Pre-Order)',
                    'description_ar' => 'رطب روثانة المدينة المشهورة بطراوتها وحلاوتها الفائقة. هذا المنتج متاح للحجز المسبق لموسم الجني وسيتم شحنه مبرداً فور اكتمال النضج مباشرة من النخلة إلى بابك.',
                    'description_en' => 'Sweet, fresh Ruthana dates harvested directly and delivered cold to your doorstep.',
                    'price' => 110.00,
                    'sale_price' => null,
                    'weight' => '2 كجم (كرتون مبرد)',
                    'stock_quantity' => 100,
                    'sku' => 'TUM-RUT-06',
                    'badge' => 'حجز مسبق للحصاد',
                    'badge_en' => 'Pre-order',
                    'is_preorder' => 1,
                    'preorder_date' => '15 سبتمبر 2026',
                    'is_fragile' => 0,
                    'rating' => 5.0,
                    'rating_count' => 23,
                    'sales_count' => 65,
                    'is_featured' => 1,
                    'is_bestseller' => 0,
                    'featured_image' => 'assets/images/fresh_ruthab_harvest_1787053947518.jpg',
                    'variants' => [
                        ['size_name' => 'كرتون مبرد 2 كجم', 'size_name_en' => '2kg Chilled Box', 'price' => 110.00, 'sale_price' => null, 'stock' => 100, 'weight' => 2.0]
                    ]
                ],
                [
                    'id' => 7,
                    'category_id' => 6,
                    'slug' => 'royal-wooden-gift-trunk',
                    'name_ar' => 'صندوق تمرنا الملكي الخشبي الفاخر المزدوج',
                    'name_en' => 'Tumurna Royal Wooden Gift Trunk',
                    'description_ar' => 'صندوق خشبي منحوت يدوياً ومبطن بالمخمل يتضمن 4 تشكيلات مختلفة: عجوة عالية، سكري مفتل، صقعي محشي بالفستق، ومجدول جامبو مع مبخرة نحاسية وتغليف ملكي.',
                    'description_en' => 'Handcrafted royal wooden gift box containing 4 premium date varieties with brass incense burner.',
                    'price' => 380.00,
                    'sale_price' => 450.00,
                    'weight' => '2.5 كجم',
                    'stock_quantity' => 25,
                    'sku' => 'TUM-BOX-07',
                    'badge' => 'هدية الملوك',
                    'badge_en' => 'Royal Gift',
                    'is_preorder' => 0,
                    'preorder_date' => null,
                    'is_fragile' => 0,
                    'rating' => 4.9,
                    'rating_count' => 98,
                    'sales_count' => 195,
                    'is_featured' => 1,
                    'is_bestseller' => 1,
                    'featured_image' => 'assets/images/royal_dates_gift_package_1787053933879.jpg',
                    'variants' => [
                        ['size_name' => 'صندوق ملكي متكامل 2.5 كجم', 'size_name_en' => 'Full Royal Trunk 2.5kg', 'price' => 380.00, 'sale_price' => 450.00, 'stock' => 25, 'weight' => 2.5]
                    ]
                ],
                [
                    'id' => 8,
                    'category_id' => 1,
                    'slug' => 'madinah-anbarah-prime',
                    'name_ar' => 'عنبرة المدينة المنورة الفاخرة - نخب أول',
                    'name_en' => 'Madinah Anbarah Dates - Prime Grade',
                    'description_ar' => 'عنبرة المدينة النادرة بحباتها الطويلة ولونها العنابي الجذاب، مناسبة للإهداء الراقي وكبار الضيوف ومحبي التمور النادرة.',
                    'description_en' => 'Rare and highly prized Madinah Anbarah dates with distinct elongated shape.',
                    'price' => 195.00,
                    'sale_price' => null,
                    'weight' => '1 كجم',
                    'stock_quantity' => 15,
                    'sku' => 'TUM-ANB-08',
                    'badge' => 'نادر وفاخر',
                    'badge_en' => 'Rare Prime',
                    'is_preorder' => 0,
                    'preorder_date' => null,
                    'is_fragile' => 0,
                    'rating' => 4.8,
                    'rating_count' => 21,
                    'sales_count' => 54,
                    'is_featured' => 0,
                    'is_bestseller' => 0,
                    'featured_image' => 'assets/images/ajwa_luxury_box_1787053900509.jpg',
                    'variants' => [
                        ['size_name' => 'عبوة فاخرة 1 كجم', 'size_name_en' => '1kg Prime Box', 'price' => 195.00, 'sale_price' => null, 'stock' => 15, 'weight' => 1.0]
                    ]
                ]
            ];

            $prodStmt = $pdo->prepare("
                INSERT INTO products (
                    id, category_id, school_id, stage_id, slug, name_ar, name_en, description_ar, description_en,
                    price, sale_price, weight, stock_quantity, sku, badge, badge_en,
                    is_preorder, preorder_date, is_fragile, rating, rating_count, sales_count,
                    is_featured, is_bestseller, featured_image, status
                ) VALUES (?, ?, 1, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published')
            ");

            $varStmt = $pdo->prepare("
                INSERT INTO product_variants (product_id, size_name, size_name_en, sku, price, sale_price, stock_quantity, weight_kg)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($products as $p) {
                $prodStmt->execute([
                    $p['id'], $p['category_id'], $p['slug'], $p['name_ar'], $p['name_en'],
                    $p['description_ar'], $p['description_en'], $p['price'], $p['sale_price'],
                    $p['weight'], $p['stock_quantity'], $p['sku'], $p['badge'], $p['badge_en'],
                    $p['is_preorder'], $p['preorder_date'], $p['is_fragile'], $p['rating'],
                    $p['rating_count'], $p['sales_count'], $p['is_featured'], $p['is_bestseller'],
                    $p['featured_image']
                ]);

                foreach ($p['variants'] as $v) {
                    $varStmt->execute([
                        $p['id'], $v['size_name'], $v['size_name_en'],
                        $p['sku'] . '-' . rand(10, 99), $v['price'], $v['sale_price'],
                        $v['stock'], $v['weight']
                    ]);
                }
            }
        }

        // 5. Seed Banners
        $bannersCount = (int)Database::fetchOne("SELECT COUNT(*) as c FROM banners")['c'];
        if ($bannersCount === 0) {
            $banners = [
                [
                    'badge_ar' => 'تشكيلة التمور السعودية الملكية',
                    'badge_en' => 'Royal Saudi Dates Selection',
                    'title_ar' => 'تمور سعودية مختارة بعناية — مذاق أصيل لكل مناسبة',
                    'title_en' => 'Carefully Selected Saudi Dates — Authentic Taste For Every Occasion',
                    'subtitle_ar' => 'من نخيل المدينة والقصيم مباشرة إلى ضيافتكم بأعلى معايير الجودة',
                    'subtitle_en' => 'Direct from Madinah and Qassim palm groves to your hospitality',
                    'cta_ar' => 'تصفح أقسام التمور',
                    'cta_en' => 'Browse Collection',
                    'link' => '/catalog',
                    'image' => 'assets/images/saudi_dates_hero_1787053884267.jpg',
                    'order_index' => 1
                ],
                [
                    'badge_ar' => 'هدايا تُسعد من تُحب',
                    'badge_en' => 'Gifts They Will Cherish',
                    'title_ar' => 'أهدِ لحظات حلوة وفاخرة مع بطاقات تمرنا الملكية',
                    'title_en' => 'Gift Sweet Royal Moments With Tumurna Luxury Gift Cards',
                    'subtitle_ar' => 'بطاقة رقمية فورية أو مطبوعة داخل صندوق خشبي أنيق',
                    'subtitle_en' => 'Instant digital delivery or handcrafted wooden box',
                    'cta_ar' => 'اكتشف بطاقات الإهداء',
                    'cta_en' => 'Explore Gift Cards',
                    'link' => '/gift-cards',
                    'image' => 'assets/images/royal_dates_gift_package_1787053933879.jpg',
                    'order_index' => 2
                ],
                [
                    'badge_ar' => 'حصاد المدينة المنورة القادم',
                    'badge_en' => 'Upcoming Madinah Harvest',
                    'title_ar' => 'احجز حصتك الآن من رطب وحصاد المدينة القادم',
                    'title_en' => 'Reserve Your Fresh Madinah Harvest Pre-Order Now',
                    'subtitle_ar' => 'شحن مبرد فائق السرعة فور الجني لضمان طراوة قطاف النخلة',
                    'subtitle_en' => 'Refrigerated express shipping straight from the harvest',
                    'cta_ar' => 'تصفح تشكيلة الحجز المسبق',
                    'cta_en' => 'Pre-order Now',
                    'link' => '/catalog?filter=preorder',
                    'image' => 'assets/images/fresh_ruthab_harvest_1787053947518.jpg',
                    'order_index' => 3
                ]
            ];

            $bannerStmt = $pdo->prepare("
                INSERT INTO banners (badge_ar, badge_en, title_ar, title_en, subtitle_ar, subtitle_en, cta_ar, cta_en, link, image, order_index, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            foreach ($banners as $b) {
                $bannerStmt->execute([
                    $b['badge_ar'], $b['badge_en'], $b['title_ar'], $b['title_en'],
                    $b['subtitle_ar'], $b['subtitle_en'], $b['cta_ar'], $b['cta_en'],
                    $b['link'], $b['image'], $b['order_index']
                ]);
            }
        }

        // Homepage CMS sections are required for the storefront to render its content.
        // Insert only missing built-in sections so existing visibility, order, and edits survive.
        $existingHomeKeys = array_column(Database::fetchAll('SELECT section_key FROM home_sections'), 'section_key');
        $defaultHomeSections = [
            ['hero', 'السلايدر الرئيسي', 'Hero Slider', 'السلايدر الرئيسي', 'Hero Slider', null, 1],
            ['categories', 'أقسام التمور', 'Date Categories', 'أقسام التمور', 'Date Categories', null, 2],
            ['featured_products', 'المنتجات المميزة', 'Featured Products', 'منتجات مميزة', 'Featured Products', null, 3],
            ['preorder', 'الحجز المسبق', 'Harvest Pre-Order', 'احجز حصاد التمور القادم', 'Reserve the Next Harvest', 'assets/images/fresh_ruthab_harvest_1787053947518.jpg', 4],
            ['features', 'مميزات تمرنا', 'Why Choose Tamrna', 'لماذا تختار تمرنا؟', 'Why Choose Tamrna?', null, 5],
            ['testimonials', 'آراء العملاء', 'Customer Testimonials', 'آراء عملائنا', 'What Our Customers Say', null, 6],
            ['newsletter', 'النشرة البريدية', 'Newsletter', 'ابق على اطلاع', 'Stay in the Loop', null, 7],
        ];
        $homeSectionInsert = $pdo->prepare('
            INSERT INTO home_sections (section_key, name_ar, name_en, title_ar, title_en, image, order_index, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, \'active\')
        ');
        foreach ($defaultHomeSections as $section) {
            if (!in_array($section[0], $existingHomeKeys, true)) {
                $homeSectionInsert->execute($section);
            }
        }

        // 6. Seed Site Settings
        $settings = [
            'site_name_ar' => 'تَـمْرُنـا للتمور الفاخرة',
            'site_name_en' => 'Tumurna Luxury Dates',
            'site_tagline_ar' => 'أفخر أنواع التمور الملكية وحلول الضيافة والهدايا',
            'site_tagline_en' => 'Finest Saudi Dates & Luxury Hospitality Gifts',
            'site_logo' => 'assets/images/logo.png',
            'currency_ar' => 'ر.س',
            'currency_en' => 'SAR',
            'contact_phone' => '8001248888',
            'contact_email' => 'care@tumurna.com',
            'whatsapp_number' => '966500000000',
            'contact_address_ar' => 'المملكة العربية السعودية - الرياض، جدة، المدينة المنورة',
            'contact_address_en' => 'Kingdom of Saudi Arabia - Riyadh, Jeddah, Al-Madinah',
            'tax_number' => '310294857200003',
            'cr_number' => '1010894723',
            'shipping_free_threshold' => '300',
            'shipping_standard_fee' => '25',
            'announcement_text_ar' => 'شحن مبرد فاخر وسريع مجاناً للطلبات فوق ٣٠٠ ر.س داخل كافة مدن المملكة 🌴',
            'announcement_text_en' => 'Free fast refrigerated shipping across KSA on orders over 300 SAR 🌴',
            'footer_text_ar' => 'جميع الحقوق محفوظة لمتجر تَـمْرُنـا للتمور الفاخرة © 2026',
            'footer_text_en' => 'All rights reserved for Tumurna Luxury Dates © 2026',
            'allow_guest_checkout' => '1',
            'enable_mada' => '1',
            'enable_apple_pay' => '1',
            'enable_tabby' => '1',
            'enable_tamara' => '1',
            'enable_cod' => '1',
            'instagram_url' => 'https://instagram.com/tumurna_dates',
            'snapchat_url' => 'https://snapchat.com/add/tumurna',
            'tiktok_url' => 'https://tiktok.com/@tumurna_dates'
        ];

        $settingsInsert = Database::getDriver() === 'sqlite' ? 'INSERT OR IGNORE' : 'INSERT IGNORE';
        $settStmt = $pdo->prepare("{$settingsInsert} INTO settings (`key`, `value`) VALUES (?, ?)");
        foreach ($settings as $k => $v) {
            $settStmt->execute([$k, $v]);
        }

        // 7. Seed Navigation Menu Items
        $menuCount = (int)Database::fetchOne("SELECT COUNT(*) as c FROM menu_items")['c'];
        if ($menuCount === 0) {
            $menuItems = [
                ['location' => 'header', 'title_ar' => 'الرئيسية', 'title_en' => 'Home', 'custom_url' => '/', 'order' => 1],
                ['location' => 'header', 'title_ar' => 'أقسام التمور', 'title_en' => 'Catalog', 'custom_url' => '/catalog', 'order' => 2],
                ['location' => 'header', 'title_ar' => 'الحجز المسبق للحصاد', 'title_en' => 'Pre-order', 'custom_url' => '/catalog?filter=preorder', 'order' => 3],
                ['location' => 'header', 'title_ar' => 'بطاقات الإهداء', 'title_en' => 'Gift Cards', 'custom_url' => '/gift-cards', 'order' => 4],
                ['location' => 'header', 'title_ar' => 'عن تمرنا', 'title_en' => 'About', 'custom_url' => '/about', 'order' => 5],
                ['location' => 'header', 'title_ar' => 'فروعنا وتواصل معنا', 'title_en' => 'Contact', 'custom_url' => '/contact', 'order' => 6],

                ['location' => 'footer', 'title_ar' => 'الصفحة الرئيسية', 'title_en' => 'Home', 'custom_url' => '/', 'order' => 1],
                ['location' => 'footer', 'title_ar' => 'كافة أقسام التمور', 'title_en' => 'All Dates', 'custom_url' => '/catalog', 'order' => 2],
                ['location' => 'footer', 'title_ar' => 'تشكيلة الحجز المسبق', 'title_en' => 'Pre-order Harvest', 'custom_url' => '/catalog?filter=preorder', 'order' => 3],
                ['location' => 'footer', 'title_ar' => 'بطاقات الإهداء الفاخرة', 'title_en' => 'Royal Gift Cards', 'custom_url' => '/gift-cards', 'order' => 4],
                ['location' => 'footer', 'title_ar' => 'عن تمرنا وقصة الحصاد', 'title_en' => 'Our Story', 'custom_url' => '/about', 'order' => 5],
                ['location' => 'footer', 'title_ar' => 'فروعنا بالمملكة', 'title_en' => 'Our Branches', 'custom_url' => '/contact', 'order' => 6]
            ];

            $menuStmt = $pdo->prepare("
                INSERT INTO menu_items (location, title_ar, title_en, link_type, custom_url, order_index, status)
                VALUES (?, ?, ?, 'custom', ?, ?, 'active')
            ");
            foreach ($menuItems as $m) {
                $menuStmt->execute([$m['location'], $m['title_ar'], $m['title_en'], $m['custom_url'], $m['order']]);
            }
        }

        // 8. Seed Sample Orders
        $ordersCount = (int)Database::fetchOne("SELECT COUNT(*) as c FROM orders")['c'];
        if ($ordersCount === 0) {
            $pdo->exec("
                INSERT INTO orders (
                    order_number, user_id, customer_name, customer_phone, customer_email, shipping_address,
                    city, district, shipping_type, subtotal, shipping_fee, discount, total,
                    payment_method, payment_status, shipping_status, notes
                ) VALUES
                ('TUM-2026-8910', NULL, 'فهد الناصر', '0555123456', 'fahad@example.com', 'حي النرجس، شارع الأمير فيصل بن بندر', 'الرياض', 'النرجس', 'home_delivery', 420.00, 0.00, 40.00, 380.00, 'mada', 'paid', 'shipped', 'يرجى الاتصال قبل التوصيل'),
                ('TUM-2026-8911', NULL, 'سارة العتيبي', '0555987654', 'sara@example.com', 'حي الروضة، طريق الكورنيش', 'جدة', 'الروضة', 'home_delivery', 180.00, 25.00, 0.00, 205.00, 'apple_pay', 'paid', 'processing', 'تغليف إهداء فاخر'),
                ('TUM-2026-8912', NULL, 'عبدالله الحربي', '0555443322', 'harbi@example.com', 'استلام من فرع سلطانة', 'المدينة المنورة', 'سلطانة', 'branch_pickup', 240.00, 0.00, 0.00, 240.00, 'cod', 'pending', 'pending', 'استلام مسائي من الفرع')
            ");

            $pdo->exec("
                INSERT INTO order_items (order_id, product_id, variant_id, product_name, size_name, unit_price, quantity, subtotal) VALUES
                (1, 7, 1, 'صندوق تمرنا الملكي الخشبي الفاخر المزدوج', 'صندوق ملكي متكامل 2.5 كجم', 380.00, 1, 380.00),
                (2, 1, 2, 'عجوة المدينة المنورة العالية الملكية', 'صندوق 1 كجم فاخر', 180.00, 1, 180.00),
                (3, 4, 1, 'صندوق تمور صقعي محشوة باللوز والبيكان والكراميل', 'طبق كريستال 1.2 كجم', 240.00, 1, 240.00)
            ");
        }

        // 9. Seed Sample Gift Cards
        $gcCount = (int)Database::fetchOne("SELECT COUNT(*) as c FROM gift_cards")['c'];
        if ($gcCount === 0) {
            $pdo->exec("
                INSERT INTO gift_cards (code, card_type, amount, balance, sender_name, recipient_name, recipient_phone, recipient_email, message, status) VALUES
                ('TUM-GIFT-500-ROYAL', 'digital', 500.00, 500.00, 'فهد الناصر', 'خالد السعدون', '0555778899', 'khaled@example.com', 'كل عام وأنتم بخير بمناسبة قدوم شهر الخير والبركة', 'active'),
                ('TUM-GIFT-250-WOOD', 'printed', 250.00, 250.00, 'سلطان المقرن', 'محمد القحطاني', '0555332211', 'mohammed@example.com', 'أجمل التهاني والتبريكات، ضيافة ملكية تليق بمقامك الكريم', 'active')
            ");
        }

        // 10. Seed Warehouses
        $warehousesCount = (int)Database::fetchOne("SELECT COUNT(*) as c FROM warehouses")['c'];
        if ($warehousesCount === 0) {
            $pdo->exec("
                INSERT INTO warehouses (name_ar, name_en, code, location, phone, status) VALUES
                ('مستودع الرياض المركزي المبرد', 'Riyadh Central Cold Warehouse', 'WH-RUH-01', 'الرياض - السلي', '0114000000', 'active'),
                ('مستودع مزارع المدينة المنورة', 'Madinah Farms Hub', 'WH-MED-02', 'المدينة المنورة - العالية', '0148000000', 'active'),
                ('مستودع بريدة وعنيزة المبرد', 'Qassim Hub', 'WH-QAS-03', 'القصيم - بريدة', '0163000000', 'active')
            ");
        }

        // 11. Seed Coupons
        $couponCount = (int)Database::fetchOne("SELECT COUNT(*) as c FROM coupons")['c'];
        if ($couponCount === 0) {
            $sampleCoupons = [
                ['TAMRNA10', 'percentage', 10.00, 100.00, 500, '2026-01-01', '2026-12-31'],
                ['RAMADAN50', 'fixed', 50.00, 300.00, 200, '2026-01-01', '2026-12-31'],
                ['WELCOME', 'percentage', 15.00, 50.00, 1000, '2026-01-01', '2026-12-31'],
                ['VIPGIFT', 'fixed', 100.00, 500.00, 100, '2026-01-01', '2026-12-31'],
            ];
            $stmtC = $pdo->prepare("
                INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, usage_limit, start_date, end_date, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
            ");
            foreach ($sampleCoupons as $c) {
                $stmtC->execute($c);
            }
        }

        // 12. Seed Saudi Shipping Zones
        $shippingCount = (int)Database::fetchOne("SELECT COUNT(*) as c FROM shipping_zones")['c'];
        if ($shippingCount === 0) {
            $cities = [
                ['الرياض', 'Riyadh', 20.00, 'توصيل مبرد نفس اليوم (خلال ساعات)'],
                ['جدة', 'Jeddah', 25.00, 'توصيل مبرد خلال 24-48 ساعة'],
                ['مكة المكرمة', 'Makkah', 25.00, 'توصيل مبرد خلال 24-48 ساعة'],
                ['المدينة المنورة', 'Madinah', 20.00, 'توصيل مبرد سريع (نفس اليوم / 24 ساعة)'],
                ['القصيم / بريدة', 'Buraidah / Qassim', 20.00, 'توصيل مبرد سريع (نفس اليوم / 24 ساعة)'],
                ['عنيزة', 'Onaizah', 20.00, 'توصيل مبرد سريع (24 ساعة)'],
                ['الدمام', 'Dammam', 25.00, 'توصيل مبرد خلال 24-48 ساعة'],
                ['الخبر', 'Khobar', 25.00, 'توصيل مبرد خلال 24-48 ساعة'],
                ['الأحساء', 'Al-Ahsa', 20.00, 'توصيل مبرد سريع (24 ساعة)'],
                ['الطائف', 'Taif', 25.00, 'توصيل مبرد خلال 24-48 ساعة'],
                ['تبوك', 'Tabuk', 30.00, 'توصيل مبرد خلال 48 ساعة'],
                ['حائل', 'Hail', 25.00, 'توصيل مبرد خلال 24-48 ساعة'],
                ['أبها', 'Abha', 30.00, 'توصيل مبرد خلال 48 ساعة'],
                ['خميس مشيط', 'Khamis Mushait', 30.00, 'توصيل مبرد خلال 48 ساعة'],
                ['جازان', 'Jazan', 35.00, 'توصيل مبرد خلال 48-72 ساعة'],
                ['نجران', 'Najran', 35.00, 'توصيل مبرد خلال 48-72 ساعة'],
                ['ينبع', 'Yanbu', 25.00, 'توصيل مبرد خلال 24-48 ساعة'],
                ['الجبيل', 'Jubail', 25.00, 'توصيل مبرد خلال 24-48 ساعة'],
                ['حفر الباطن', 'Hafar Al-Batin', 30.00, 'توصيل مبرد خلال 48 ساعة'],
                ['الخرج', 'Al-Kharj', 20.00, 'توصيل مبرد خلال 24 ساعة']
            ];
            $stmtZ = $pdo->prepare("
                INSERT INTO shipping_zones (city_name_ar, city_name_en, shipping_fee, estimated_delivery, is_cold_shipping, is_active)
                VALUES (?, ?, ?, ?, 1, 1)
            ");
            foreach ($cities as $c) {
                $stmtZ->execute($c);
            }
        }
    }
}

