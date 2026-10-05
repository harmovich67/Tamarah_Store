<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Cache;
use App\Core\Request;
use App\Core\Response;
use App\Core\Uploader;
use Database\Database;

class BannersController
{
    public function index(Request $request): void
    {
        Auth::requireSuperAdmin();
        Response::redirect('/admin/home-sections?tab=banners');
    }

    public function store(Request $request): void
    {
        Auth::requireSuperAdmin();

        $badgeAr = trim((string)$request->get('badge_ar'));
        $badgeEn = trim((string)$request->get('badge_en'));
        $titleAr = trim((string)$request->get('title_ar'));
        $titleEn = trim((string)$request->get('title_en'));
        $subtitleAr = trim((string)$request->get('subtitle_ar'));
        $subtitleEn = trim((string)$request->get('subtitle_en'));
        $ctaAr = trim((string)$request->get('cta_ar', 'تسوق الآن'));
        $ctaEn = trim((string)$request->get('cta_en', 'Shop Now'));
        $link = trim((string)$request->get('link', '/catalog'));

        // Handle uploaded image or fallback image URL
        $uploaded = Uploader::upload('image_file', 'banners');
        $image = $uploaded ?: trim((string)$request->get('image_url', 'assets/images/saudi_dates_hero_1787053884267.jpg'));

        $maxOrder = (int)Database::fetchOne("SELECT COALESCE(MAX(order_index), 0) as m FROM banners")['m'];

        Database::execute("
            INSERT INTO banners (
                badge_ar, badge_en, title_ar, title_en, subtitle_ar, subtitle_en,
                cta_ar, cta_en, link, image, order_index, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ", [
            $badgeAr, $badgeEn, $titleAr, $titleEn, $subtitleAr, $subtitleEn,
            $ctaAr, $ctaEn, $link, $image, $maxOrder + 1
        ]);

        $this->flushCache();
        Response::redirect('/admin/home-sections?tab=banners&saved=1');
    }

    public function update(Request $request): void
    {
        Auth::requireSuperAdmin();

        $id = (int)$request->get('id');
        $banner = Database::fetchOne("SELECT * FROM banners WHERE id = ?", [$id]);
        if (!$banner) {
            Response::redirect('/admin/home-sections?tab=banners');
            return;
        }

        $badgeAr = trim((string)$request->get('badge_ar'));
        $badgeEn = trim((string)$request->get('badge_en'));
        $titleAr = trim((string)$request->get('title_ar'));
        $titleEn = trim((string)$request->get('title_en'));
        $subtitleAr = trim((string)$request->get('subtitle_ar'));
        $subtitleEn = trim((string)$request->get('subtitle_en'));
        $ctaAr = trim((string)$request->get('cta_ar', 'تسوق الآن'));
        $ctaEn = trim((string)$request->get('cta_en', 'Shop Now'));
        $link = trim((string)$request->get('link', '/catalog'));

        $uploaded = Uploader::upload('image_file', 'banners');
        $image = $uploaded ?: (trim((string)$request->get('image_url')) ?: $banner['image']);

        Database::execute("
            UPDATE banners SET
                badge_ar = ?, badge_en = ?, title_ar = ?, title_en = ?,
                subtitle_ar = ?, subtitle_en = ?, cta_ar = ?, cta_en = ?,
                link = ?, image = ?
            WHERE id = ?
        ", [
            $badgeAr, $badgeEn, $titleAr, $titleEn,
            $subtitleAr, $subtitleEn, $ctaAr, $ctaEn,
            $link, $image, $id
        ]);

        $this->flushCache();
        Response::redirect('/admin/home-sections?tab=banners&saved=1');
    }

    public function delete(Request $request): void
    {
        Auth::requireSuperAdmin();

        $id = (int)$request->get('id');
        Database::execute("DELETE FROM banners WHERE id = ?", [$id]);

        $this->flushCache();
        Response::redirect('/admin/home-sections?tab=banners&deleted=1');
    }

    public function toggle(Request $request): void
    {
        Auth::requireSuperAdmin();

        $id = (int)$request->get('id');
        $banner = Database::fetchOne("SELECT status FROM banners WHERE id = ?", [$id]);
        if ($banner) {
            $newStatus = $banner['status'] === 'active' ? 'inactive' : 'active';
            Database::execute("UPDATE banners SET status = ? WHERE id = ?", [$newStatus, $id]);
            $this->flushCache();
        }

        Response::redirect('/admin/home-sections?tab=banners');
    }

    public function moveUp(Request $request): void
    {
        Auth::requireSuperAdmin();
        $this->swapOrder((int)$request->get('id'), 'up');
        Response::redirect('/admin/home-sections?tab=banners');
    }

    public function moveDown(Request $request): void
    {
        Auth::requireSuperAdmin();
        $this->swapOrder((int)$request->get('id'), 'down');
        Response::redirect('/admin/home-sections?tab=banners');
    }

    private function swapOrder(int $id, string $direction): void
    {
        $banners = Database::fetchAll("SELECT id, order_index FROM banners ORDER BY order_index ASC, id ASC");
        $index = null;
        foreach ($banners as $i => $b) {
            if ((int)$b['id'] === $id) {
                $index = $i;
                break;
            }
        }
        if ($index === null) return;

        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;
        if ($swapWith < 0 || $swapWith >= count($banners)) return;

        $a = $banners[$index];
        $b = $banners[$swapWith];

        Database::execute("UPDATE banners SET order_index = ? WHERE id = ?", [$b['order_index'], $a['id']]);
        Database::execute("UPDATE banners SET order_index = ? WHERE id = ?", [$a['order_index'], $b['id']]);

        $this->flushCache();
    }

    private function flushCache(): void
    {
        if (class_exists('\\App\\Core\\Cache')) {
            Cache::flush('data');
        }
    }
}
