<?php
use App\Core\I18n;
use App\Core\Auth;
use App\Core\Backup;
use Database\Database;

$adminLocale = I18n::getAdminLocale();
$isRtl = I18n::isAdminRtl();
$locale = $adminLocale;
$user = Auth::user();
$isSuper = Auth::isSuperAdmin();

if ($isSuper) {
    Backup::maybeRunScheduledBackup();
}
?>
<!DOCTYPE html>
<html lang="<?= $adminLocale ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('admin_dashboard') ?> - تَـمْرُنـا للتمور الفاخرة</title>
    
    <link rel="icon" type="image/png" href="<?= asset('assets/images/logo.png') ?>" />

    <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>?v=<?= @filemtime(__DIR__ . '/../../../assets/css/admin.css') ?>">
    <script src="<?= asset('assets/js/lucide.min.js') ?>"></script>

    <script>
        window.APP_URL = <?= json_encode(base_path_url()) ?>;
        window.appUrl = function(path) {
            path = path || '';
            if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('//')) return path;
            return window.APP_URL + (path.startsWith('/') ? path : '/' + path);
        };

    </script>

    <style>
        * {
            scrollbar-width: thin;
            scrollbar-color: #a37b34 #0c190f;
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #08120a;
        }
        ::-webkit-scrollbar-thumb {
            background-color: #23531e;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background-color: #c49a52;
        }
    </style>
</head>
<body class="bg-[#fcfbfa] text-[#1c1917] font-sans antialiased flex h-screen overflow-hidden selection:bg-[#23531e] selection:text-white" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">

    <!-- Mobile Sidebar Backdrop -->
    <div id="adminSidebarBackdrop" onclick="toggleAdminSidebar()" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-40 hidden md:hidden transition-all duration-300"></div>

    <!-- Sidebar -->
    <aside id="adminSidebar" class="fixed inset-y-0 <?= $isRtl ? 'right-0 translate-x-full' : 'left-0 -translate-x-full' ?> md:relative md:translate-x-0 w-72 bg-[#0c190f] text-white flex flex-col flex-shrink-0 z-50 md:z-30 shadow-2xl transition-transform duration-300 ease-in-out border-e border-[#19321e]">
        
        <!-- Brand Header -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-[#19321e] bg-[#08120a]">
            <a href="<?= url('/admin') ?>" class="flex items-center gap-3">
                <img src="<?= asset('assets/images/logo.png') ?>" alt="تمرنا" class="w-10 h-10 object-contain brightness-110">
                <div>
                    <h1 class="text-base font-black text-white tracking-wide leading-none"><?= __('admin_brand_title') ?></h1>
                    <span class="text-[10px] text-[#c49a52] font-bold mt-0.5 block tracking-wider"><?= __('admin_brand_subtitle') ?></span>
                </div>
            </a>
            <button type="button" onclick="toggleAdminSidebar()" class="md:hidden w-8 h-8 rounded-lg bg-[#19321e] text-stone-300 hover:text-white flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Current User Role -->
        <div class="p-3.5 mx-4 mt-4 rounded-2xl bg-[#132717] border border-[#1e3d24] shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-[#23531e] text-white flex items-center justify-center font-black text-sm border border-[#3e7836]">
                    <?= mb_substr($user['name'] ?? 'م', 0, 1, 'UTF-8') ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-white truncate"><?= htmlspecialchars($user['name'] ?? 'المدير') ?></p>
                    <span class="inline-flex items-center gap-1 text-[10px] font-black text-[#c49a52] bg-[#08120a] px-2 py-0.5 rounded-md mt-0.5 border border-[#19321e]">
                        <i data-lucide="crown" class="w-2.5 h-2.5"></i> <?= htmlspecialchars($isRtl ? ($user['role_ar'] ?? '') : ($user['role_en'] ?? '')) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1.5">
            
            <a href="<?= url('/admin') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin', true) ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_dashboard') ?></span>
                </div>
            </a>

            <!-- Store Management -->
            <div class="pt-3 pb-1">
                <p class="px-3.5 text-[10px] font-bold uppercase tracking-wider text-[#c49a52]/80"><?= __('admin_nav_store_mgmt') ?></p>
            </div>

            <a href="<?= url('/admin/products') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin/products') ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="box" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_products') ?></span>
                </div>
            </a>

            <a href="<?= url('/admin/categories') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin/categories') ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="tags" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_categories') ?></span>
                </div>
            </a>

            <a href="<?= url('/admin/orders') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin/orders') ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="truck" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_orders') ?></span>
                </div>
            </a>

            <!-- Coupons Management -->
            <a href="<?= url('/admin/coupons') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin/coupons') ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="ticket" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_coupons') ?></span>
                </div>
            </a>

            <!-- Saudi Shipping Zones -->
            <a href="<?= url('/admin/shipping-zones') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin/shipping-zones') ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="map-pin" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_shipping') ?></span>
                </div>
            </a>

            <a href="<?= url('/admin/settings') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin/settings') ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="credit-card" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_payments') ?></span>
                </div>
            </a>

            <a href="<?= url('/gift-cards') ?>" target="_blank" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold text-stone-300 hover:text-white hover:bg-[#132717] transition">
                <div class="flex items-center gap-3">
                    <i data-lucide="gift" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_gift_cards') ?></span>
                </div>
                <i data-lucide="external-link" class="w-3 h-3 text-stone-500"></i>
            </a>

            <!-- Content & Brand -->
            <div class="pt-3 pb-1">
                <p class="px-3.5 text-[10px] font-bold uppercase tracking-wider text-[#c49a52]/80"><?= __('admin_nav_branding') ?></p>
            </div>

            <a href="<?= url('/admin/home-sections') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin/home-sections') ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="layout-panel-top" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_home_sections') ?></span>
                </div>
            </a>

            <a href="<?= url('/admin/site-settings') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin/site-settings') ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="settings" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_site_settings') ?></span>
                </div>
            </a>

            <a href="<?= url('/admin/pages') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin/pages') ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="file-text" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_pages') ?></span>
                </div>
            </a>

            <!-- Customers & Users -->
            <div class="pt-3 pb-1">
                <p class="px-3.5 text-[10px] font-bold uppercase tracking-wider text-[#c49a52]/80"><?= __('admin_nav_users_section') ?></p>
            </div>

            <a href="<?= url('/admin/users') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin/users') ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="users" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_users') ?></span>
                </div>
            </a>

            <a href="<?= url('/admin/translations') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin/translations') ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="languages" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_translations') ?></span>
                </div>
            </a>

            <a href="<?= url('/admin/cache') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin/cache') ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="zap" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_cache') ?></span>
                </div>
            </a>

            <a href="<?= url('/admin/security') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin/security') ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="shield" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_security') ?></span>
                </div>
            </a>

            <a href="<?= url('/admin/backup') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= is_active_url('/admin/backup') ? 'bg-[#1c3821] text-white shadow-md border border-[#2f5c37]' : 'text-stone-300 hover:text-white hover:bg-[#132717]' ?>">
                <div class="flex items-center gap-3">
                    <i data-lucide="database" class="w-4 h-4 text-[#c49a52]"></i>
                    <span><?= __('admin_nav_backup') ?></span>
                </div>
            </a>

        </nav>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-[#19321e] bg-[#08120a] flex items-center justify-between">
            <a href="<?= url('/') ?>" target="_blank" class="text-xs font-bold text-[#c49a52] hover:underline flex items-center gap-1.5">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                <span><?= __('admin_visit_store') ?></span>
            </a>
            <a href="<?= url('/logout') ?>" class="text-xs font-bold text-rose-400 hover:text-rose-300 flex items-center gap-1">
                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                <span><?= __('admin_logout') ?></span>
            </a>
        </div>

    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Top Navbar -->
        <header class="h-20 bg-white border-b border-stone-200 px-6 flex items-center justify-between z-10 shadow-xs">
            
            <div class="flex items-center gap-4">
                <button type="button" onclick="toggleAdminSidebar()" class="md:hidden p-2 rounded-xl bg-stone-100 text-stone-700 hover:bg-stone-200">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-stone-700 hidden sm:inline"><?= __('admin_store_status_active') ?></span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                
                <!-- Language Switcher Button (Dashboard Only) -->
                <a href="<?= url('/admin/lang/' . ($adminLocale === 'ar' ? 'en' : 'ar')) ?>" class="px-2 sm:px-3 py-1.5 rounded-xl border border-stone-200 hover:border-[#c49a52] bg-[#fbf5e9] hover:bg-[#f3e7d1] text-[#6f431b] text-xs font-bold transition flex items-center gap-1.5 shadow-xs whitespace-nowrap" title="<?= $adminLocale === 'ar' ? 'Switch Dashboard to English' : 'تحويل الداشبورد إلى العربية' ?>">
                    <i data-lucide="globe" class="w-3.5 h-3.5 text-[#8c5d25]"></i>
                    <span class="hidden sm:inline"><?= $adminLocale === 'ar' ? 'English (EN)' : 'عربي (AR)' ?></span>
                </a>

                <!-- Visit Store Button -->
                <a href="<?= url('/') ?>" target="_blank" class="px-3.5 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold transition-all border border-stone-200 flex items-center gap-1.5">
                    <i data-lucide="store" class="w-3.5 h-3.5 text-[#568d43]"></i>
                    <span class="hidden sm:inline"><?= __('admin_visit_store') ?></span>
                </a>

                <!-- Clear Cache Quick Button -->
                <form method="post" action="<?= url('/admin/cache/clear') ?>" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="p-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 transition-colors" title="<?= __('admin_clear_cache') ?>">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    </button>
                </form>

                <!-- Profile Dropdown -->
                <div class="flex items-center gap-2.5 ps-3 border-s border-stone-200">
                    <div class="w-8 h-8 rounded-full bg-[#568d43] text-white flex items-center justify-center text-xs font-black">
                        <?= mb_substr($user['name'] ?? 'A', 0, 1, 'UTF-8') ?>
                    </div>
                    <span class="text-xs font-bold text-stone-800 hidden md:inline"><?= htmlspecialchars($user['name'] ?? 'Admin') ?></span>
                </div>

            </div>

        </header>

        <!-- Main Workspace -->
        <main class="flex-1 overflow-y-auto p-6 sm:p-8">
            <?= $content ?? '' ?>
        </main>

    </div>

    <script>
        lucide.createIcons();

        function toggleAdminSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('adminSidebarBackdrop');
            const isRtl = document.documentElement.dir === 'rtl';
            if (sidebar) {
                if (isRtl) {
                    sidebar.classList.toggle('translate-x-full');
                } else {
                    sidebar.classList.toggle('-translate-x-full');
                }
            }
            if (backdrop) {
                backdrop.classList.toggle('hidden');
            }
        }
    </script>
</body>
</html>
