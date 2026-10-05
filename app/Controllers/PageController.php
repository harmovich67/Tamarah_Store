<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\I18n;
use App\Core\Auth;
use App\Core\CustomFields;
use Database\Database;

class PageController
{
    public function show(Request $request): void
    {
        $slug = (string)$request->get('slug');

        // Check if there is a dedicated view for this page
        if ($slug === 'about') {
            $this->about($request);
            return;
        }
        if ($slug === 'contact') {
            $this->contact($request);
            return;
        }

        $page = Database::fetchOne("SELECT * FROM pages WHERE slug = ?", [$slug]);

        if (!$page) {
            Response::error('الصفحة المطلوبة غير موجودة (404 Not Found)', 404);
            return;
        }

        if ($page['status'] !== 'published' && !Auth::isSuperAdmin()) {
            Response::error('الصفحة غير منشورة (403 Forbidden)', 403);
            return;
        }

        CustomFields::setCurrentEntity('page', (int)$page['id']);

        $locale = I18n::getLocale();
        $title = $locale === 'en' && !empty($page['title_en']) ? $page['title_en'] : $page['title_ar'];
        $metaTitle = ($locale === 'en' && !empty($page['meta_title_en'])) ? $page['meta_title_en'] : ($page['meta_title_ar'] ?: $title);
        $metaDesc = ($locale === 'en' && !empty($page['meta_desc_en'])) ? $page['meta_desc_en'] : $page['meta_desc_ar'];

        Response::view('storefront/page', [
            'page' => $page,
            'pageTitle' => $title,
            'metaTitle' => $metaTitle,
            'metaDesc' => $metaDesc
        ]);
    }

    public function showPost(Request $request): void
    {
        $slug = (string)$request->get('slug');
        $post = Database::fetchOne("SELECT * FROM posts WHERE slug = ?", [$slug]);

        if (!$post) {
            Response::error('المنشور المطلوب غير موجود (404 Not Found)', 404);
            return;
        }

        if ($post['status'] !== 'published' && !Auth::isSuperAdmin()) {
            Response::error('المنشور غير متاح حالياً (403 Forbidden)', 403);
            return;
        }

        CustomFields::setCurrentEntity('post', (int)$post['id']);

        $locale = I18n::getLocale();
        $title = $locale === 'en' && !empty($post['title_en']) ? $post['title_en'] : $post['title_ar'];

        Response::view('storefront/page', [
            'page' => $post,
            'pageTitle' => $title,
            'isPost' => true
        ]);
    }

    public function giftCards(Request $request): void
    {
        Response::view('storefront/gift_cards', [
            'page_title' => 'بطاقات الإهداء الملكية'
        ]);
    }

    public function wishlist(Request $request): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $locale = \App\Core\I18n::getLocale();

        // Wishlist is restricted to registered users only
        if (!\App\Core\Auth::check()) {
            $_SESSION['flash_notice'] = ($locale === 'en')
                ? 'Please sign in or register to manage your wishlist.'
                : 'يرجى تسجيل الدخول أو إنشاء حساب لإدارة قائمتك المفضلة.';
            Response::redirect('/login?redirect=' . urlencode('/wishlist'));
            return;
        }

        $userId = \App\Core\Auth::id();
        $dbWishlist = Database::fetchAll("SELECT product_id FROM wishlists WHERE user_id = ?", [$userId]);
        $wishlistIds = array_column($dbWishlist, 'product_id');
        $_SESSION['wishlist'] = $wishlistIds;

        $products = [];
        if (!empty($wishlistIds)) {
            $placeholders = implode(',', array_fill(0, count($wishlistIds), '?'));
            $products = Database::fetchAll("
                SELECT p.*, c.name_ar as category_name_ar, c.name_en as category_name_en 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.id IN ($placeholders) AND p.status = 'published'
            ", $wishlistIds);
        }

        Response::view('storefront/wishlist', [
            'products' => $products,
            'page_title' => $locale === 'en' ? 'My Wishlist' : 'قائمة رغباتي والمفضلة'
        ]);
    }

    public function apiWishlistToggle(Request $request): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $locale = \App\Core\I18n::getLocale();

        // Only logged in users can favorite
        if (!\App\Core\Auth::check()) {
            Response::json([
                'success' => false,
                'requires_login' => true,
                'message' => ($locale === 'en') 
                    ? 'Please sign in or create an account to save dates to your wishlist.' 
                    : 'يرجى تسجيل الدخول أو إنشاء حساب لإضافة المنتجات إلى المفضلة.',
                'redirect' => url('/login?redirect=' . urlencode($_SERVER['HTTP_REFERER'] ?? '/catalog'))
            ]);
            return;
        }

        $userId = \App\Core\Auth::id();
        $productId = (int)$request->get('product_id');

        if (!isset($_SESSION['wishlist'])) {
            $dbWishlist = Database::fetchAll("SELECT product_id FROM wishlists WHERE user_id = ?", [$userId]);
            $_SESSION['wishlist'] = array_column($dbWishlist, 'product_id');
        }

        $key = array_search($productId, $_SESSION['wishlist']);

        if ($key !== false) {
            unset($_SESSION['wishlist'][$key]);
            $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
            Database::execute("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
            $inWishlist = false;
            $msg = ($locale === 'en') ? 'Item removed from your wishlist' : 'تمت إزالة المنتج من المفضلة';
        } else {
            $_SESSION['wishlist'][] = $productId;
            try {
                Database::execute("INSERT INTO wishlists (user_id, product_id, created_at) VALUES (?, ?, NOW())", [$userId, $productId]);
            } catch (\Throwable $e) {
                // Ignore duplicate insert if any
            }
            $inWishlist = true;
            $msg = ($locale === 'en') ? 'Item added to your wishlist' : 'تمت إضافة المنتج إلى المفضلة';
        }

        Response::json([
            'success' => true,
            'in_wishlist' => $inWishlist,
            'count' => count($_SESSION['wishlist']),
            'message' => $msg
        ]);
    }

    public function about(Request $request): void
    {
        Response::view('storefront/about', [
            'page_title' => 'عن تمرنا وقصة الحصاد'
        ]);
    }

    public function contact(Request $request): void
    {
        Response::view('storefront/contact', [
            'page_title' => 'فروعنا وتواصل معنا'
        ]);
    }
}
