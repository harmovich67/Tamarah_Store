<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Cache;
use App\Core\Request;
use App\Core\Response;
use App\Core\Uploader;
use Database\Database;

class HomeSectionsController
{
    public function index(Request $request): void
    {
        Auth::requireSuperAdmin();

        $sections = Database::fetchAll("SELECT * FROM home_sections ORDER BY order_index ASC, id ASC");
        $banners = Database::fetchAll("SELECT * FROM banners ORDER BY order_index ASC, id ASC");

        // Decode settings_json for easy access in view
        foreach ($sections as &$sec) {
            $sec['settings'] = !empty($sec['settings_json']) ? json_decode($sec['settings_json'], true) : [];
        }
        unset($sec);

        $tab = $request->get('tab', 'sections');

        Response::view('admin/home_sections', [
            'sections' => $sections,
            'banners' => $banners,
            'currentTab' => $tab,
            'saved' => $request->get('saved') == 1,
            'deleted' => $request->get('deleted') == 1,
            'error' => $request->get('error')
        ], 'admin');
    }

    public function updateSection(Request $request): void
    {
        Auth::requireSuperAdmin();

        $id = (int)$request->get('id');
        $sec = Database::fetchOne("SELECT * FROM home_sections WHERE id = ?", [$id]);
        if (!$sec) {
            Response::redirect('/admin/home-sections');
            return;
        }

        $titleAr = trim((string)$request->get('title_ar'));
        $titleEn = trim((string)$request->get('title_en'));
        $subtitleAr = trim((string)$request->get('subtitle_ar'));
        $subtitleEn = trim((string)$request->get('subtitle_en'));
        $badgeAr = trim((string)$request->get('badge_ar'));
        $badgeEn = trim((string)$request->get('badge_en'));
        $buttonTextAr = trim((string)$request->get('button_text_ar'));
        $buttonTextEn = trim((string)$request->get('button_text_en'));
        $buttonUrl = trim((string)$request->get('button_url'));
        $style = trim((string)$request->get('style', $sec['style'] ?? 'default'));
        $contentAr = trim((string)$request->get('content_ar'));
        $contentEn = trim((string)$request->get('content_en'));

        // Handle image upload if provided
        $uploaded = Uploader::upload('image_file', 'home_sections');
        $image = $uploaded ?: (trim((string)$request->get('image_url')) ?: $sec['image']);

        Database::execute("
            UPDATE home_sections SET
                title_ar = ?, title_en = ?,
                subtitle_ar = ?, subtitle_en = ?,
                badge_ar = ?, badge_en = ?,
                button_text_ar = ?, button_text_en = ?,
                button_url = ?, style = ?,
                image = ?, custom_content_ar = ?, custom_content_en = ?
            WHERE id = ?
        ", [
            $titleAr, $titleEn,
            $subtitleAr, $subtitleEn,
            $badgeAr, $badgeEn,
            $buttonTextAr, $buttonTextEn,
            $buttonUrl, $style,
            $image, $contentAr, $contentEn,
            $id
        ]);

        $this->flushHomeCache();
        Response::redirect('/admin/home-sections?saved=1');
    }

    public function updateFeatures(Request $request): void
    {
        Auth::requireSuperAdmin();

        $id = (int)$request->get('id');
        $sec = Database::fetchOne("SELECT * FROM home_sections WHERE id = ?", [$id]);
        if (!$sec) {
            Response::redirect('/admin/home-sections');
            return;
        }

        $titleAr = trim((string)$request->get('title_ar', 'لماذا يختار عملاؤنا تمرنا؟'));
        $titleEn = trim((string)$request->get('title_en', 'Why Our Customers Choose Tumurna'));
        $badgeAr = trim((string)$request->get('badge_ar', 'الجودة السعودية الخالصة'));
        $badgeEn = trim((string)$request->get('badge_en', 'Pure Saudi Quality'));

        // Extract 4 feature cards
        $cards = [];
        $rawCards = $request->get('cards');
        if (is_array($rawCards)) {
            foreach ($rawCards as $c) {
                if (!empty($c['title_ar']) || !empty($c['title_en'])) {
                    $cards[] = [
                        'icon' => trim((string)($c['icon'] ?? 'award')),
                        'title_ar' => trim((string)($c['title_ar'] ?? '')),
                        'title_en' => trim((string)($c['title_en'] ?? '')),
                        'desc_ar' => trim((string)($c['desc_ar'] ?? '')),
                        'desc_en' => trim((string)($c['desc_en'] ?? '')),
                    ];
                }
            }
        }

        $settings = !empty($sec['settings_json']) ? json_decode($sec['settings_json'], true) : [];
        $settings['cards'] = $cards;

        Database::execute("
            UPDATE home_sections SET
                title_ar = ?, title_en = ?, badge_ar = ?, badge_en = ?, settings_json = ?
            WHERE id = ?
        ", [$titleAr, $titleEn, $badgeAr, $badgeEn, json_encode($settings, JSON_UNESCAPED_UNICODE), $id]);

        $this->flushHomeCache();
        Response::redirect('/admin/home-sections?saved=1');
    }

    public function updateTestimonials(Request $request): void
    {
        Auth::requireSuperAdmin();

        $id = (int)$request->get('id');
        $sec = Database::fetchOne("SELECT * FROM home_sections WHERE id = ?", [$id]);
        if (!$sec) {
            Response::redirect('/admin/home-sections');
            return;
        }

        $titleAr = trim((string)$request->get('title_ar', 'ماذا يقول عملاؤنا عن تمورنا؟'));
        $titleEn = trim((string)$request->get('title_en', 'What Customers Say About Our Dates'));
        $badgeAr = trim((string)$request->get('badge_ar', 'تجارب حقيقية'));
        $badgeEn = trim((string)$request->get('badge_en', 'Real Experiences'));

        $reviews = [];
        $rawReviews = $request->get('reviews');
        if (is_array($rawReviews)) {
            foreach ($rawReviews as $r) {
                if ((!empty($r['name']) || !empty($r['name_en'])) && (!empty($r['comment']) || !empty($r['comment_en']))) {
                    $reviews[] = [
                        'name' => trim((string)($r['name'] ?? '')),
                        'name_en' => trim((string)($r['name_en'] ?? '')),
                        'city' => trim((string)($r['city'] ?? 'المملكة')),
                        'city_en' => trim((string)($r['city_en'] ?? 'KSA')),
                        'rating' => max(1, min(5, (int)($r['rating'] ?? 5))),
                        'comment' => trim((string)($r['comment'] ?? '')),
                        'comment_en' => trim((string)($r['comment_en'] ?? '')),
                        'product_name' => trim((string)($r['product_name'] ?? 'تمور ملكية فاخرة')),
                        'product_name_en' => trim((string)($r['product_name_en'] ?? 'Royal Luxury Dates')),
                    ];
                }
            }
        }

        $settings = !empty($sec['settings_json']) ? json_decode($sec['settings_json'], true) : [];
        $settings['reviews'] = $reviews;

        Database::execute("
            UPDATE home_sections SET
                title_ar = ?, title_en = ?, badge_ar = ?, badge_en = ?, settings_json = ?
            WHERE id = ?
        ", [$titleAr, $titleEn, $badgeAr, $badgeEn, json_encode($settings, JSON_UNESCAPED_UNICODE), $id]);

        $this->flushHomeCache();
        Response::redirect('/admin/home-sections?saved=1');
    }

    public function updateNewsletter(Request $request): void
    {
        Auth::requireSuperAdmin();

        $id = (int)$request->get('id');
        $sec = Database::fetchOne("SELECT * FROM home_sections WHERE id = ?", [$id]);
        if (!$sec) {
            Response::redirect('/admin/home-sections');
            return;
        }

        $titleAr = trim((string)$request->get('title_ar'));
        $titleEn = trim((string)$request->get('title_en'));
        $subtitleAr = trim((string)$request->get('subtitle_ar'));
        $subtitleEn = trim((string)$request->get('subtitle_en'));
        $badgeAr = trim((string)$request->get('badge_ar'));
        $badgeEn = trim((string)$request->get('badge_en'));
        $buttonTextAr = trim((string)$request->get('button_text_ar'));
        $buttonTextEn = trim((string)$request->get('button_text_en'));
        $couponCode = trim((string)$request->get('coupon_code', 'TAMRNA10'));
        $discountPercent = trim((string)$request->get('discount_percent', '10'));

        $settings = !empty($sec['settings_json']) ? json_decode($sec['settings_json'], true) : [];
        $settings['coupon_code'] = $couponCode;
        $settings['discount_percent'] = $discountPercent;

        Database::execute("
            UPDATE home_sections SET
                title_ar = ?, title_en = ?,
                subtitle_ar = ?, subtitle_en = ?,
                badge_ar = ?, badge_en = ?,
                button_text_ar = ?, button_text_en = ?,
                settings_json = ?
            WHERE id = ?
        ", [$titleAr, $titleEn, $subtitleAr, $subtitleEn, $badgeAr, $badgeEn, $buttonTextAr, $buttonTextEn, json_encode($settings, JSON_UNESCAPED_UNICODE), $id]);

        $this->flushHomeCache();
        Response::redirect('/admin/home-sections?saved=1');
    }

    public function storeCustom(Request $request): void
    {
        Auth::requireSuperAdmin();

        $titleAr = trim((string)$request->get('title_ar'));
        $titleEn = trim((string)$request->get('title_en'));
        $subtitleAr = trim((string)$request->get('subtitle_ar'));
        $subtitleEn = trim((string)$request->get('subtitle_en'));
        $style = trim((string)$request->get('style', 'centered'));
        $contentAr = (string)$request->get('content_ar');
        $contentEn = (string)$request->get('content_en');
        $buttonTextAr = trim((string)$request->get('button_text_ar'));
        $buttonTextEn = trim((string)$request->get('button_text_en'));
        $buttonUrl = trim((string)$request->get('button_url'));

        if (empty($titleAr) && empty($titleEn)) {
            Response::redirect('/admin/home-sections?error=empty_title');
            return;
        }

        $uploadedImage = Uploader::upload('image_file', 'home_sections');
        $image = $uploadedImage ?: trim((string)$request->get('image_url'));

        $maxOrder = (int)Database::fetchOne("SELECT COALESCE(MAX(order_index), 0) as m FROM home_sections")['m'];

        Database::execute("
            INSERT INTO home_sections (
                section_key, name_ar, name_en, is_custom, title_ar, title_en, subtitle_ar, subtitle_en, style,
                custom_content_ar, custom_content_en, image, button_text_ar, button_text_en, button_url,
                order_index, status
            ) VALUES (?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ", [
            'custom_' . bin2hex(random_bytes(8)),
            $titleAr ?: $titleEn, $titleEn ?: $titleAr,
            $titleAr ?: $titleEn, $titleEn ?: $titleAr,
            $subtitleAr, $subtitleEn, $style,
            $contentAr, $contentEn, $image,
            $buttonTextAr, $buttonTextEn, $buttonUrl,
            $maxOrder + 1
        ]);

        $this->flushHomeCache();
        Response::redirect('/admin/home-sections?saved=1');
    }

    public function deleteCustom(Request $request): void
    {
        Auth::requireSuperAdmin();

        $id = (int)$request->get('id');
        Database::execute("DELETE FROM home_sections WHERE id = ? AND is_custom = 1", [$id]);
        $this->flushHomeCache();
        Response::redirect('/admin/home-sections?deleted=1');
    }

    public function toggleStatus(Request $request): void
    {
        Auth::requireSuperAdmin();

        $id = (int)$request->get('id');
        $section = Database::fetchOne("SELECT status FROM home_sections WHERE id = ?", [$id]);
        if ($section) {
            $newStatus = $section['status'] === 'active' ? 'inactive' : 'active';
            Database::execute("UPDATE home_sections SET status = ? WHERE id = ?", [$newStatus, $id]);
            $this->flushHomeCache();
        }
        Response::redirect('/admin/home-sections');
    }

    public function moveUp(Request $request): void
    {
        Auth::requireSuperAdmin();
        $this->swapOrder((int)$request->get('id'), 'up');
        Response::redirect('/admin/home-sections');
    }

    public function moveDown(Request $request): void
    {
        Auth::requireSuperAdmin();
        $this->swapOrder((int)$request->get('id'), 'down');
        Response::redirect('/admin/home-sections');
    }

    private function swapOrder(int $id, string $direction): void
    {
        $sections = Database::fetchAll("SELECT id, order_index FROM home_sections ORDER BY order_index ASC, id ASC");
        $index = null;
        foreach ($sections as $i => $s) {
            if ((int)$s['id'] === $id) {
                $index = $i;
                break;
            }
        }
        if ($index === null) return;

        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;
        if ($swapWith < 0 || $swapWith >= count($sections)) return;

        $a = $sections[$index];
        $b = $sections[$swapWith];

        Database::execute("UPDATE home_sections SET order_index = ? WHERE id = ?", [$b['order_index'], $a['id']]);
        Database::execute("UPDATE home_sections SET order_index = ? WHERE id = ?", [$a['order_index'], $b['id']]);

        $this->flushHomeCache();
    }

    private function flushHomeCache(): void
    {
        if (class_exists('\\App\\Core\\Cache')) {
            Cache::flush('data');
        }
    }
}
