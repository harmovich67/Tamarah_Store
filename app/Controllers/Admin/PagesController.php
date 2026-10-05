<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Uploader;
use App\Core\CustomFields;
use Database\Database;
use PDO;

class PagesController
{
    public function index(Request $request): void
    {
        Auth::requireSuperAdmin();

        $search = trim((string)$request->get('q', ''));
        $status = trim((string)$request->get('status', ''));

        $sql = "SELECT * FROM pages WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (title_ar LIKE ? OR title_en LIKE ? OR slug LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status !== '' && in_array($status, ['published', 'draft'])) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY id DESC";
        $pages = Database::fetchAll($sql, $params);

        Response::view('admin/pages/index', [
            'pages' => $pages,
            'search' => $search,
            'status' => $status
        ], 'admin');
    }

    public function create(Request $request): void
    {
        Auth::requireSuperAdmin();

        Response::view('admin/pages/form', [
            'page' => null,
            'isEdit' => false
        ], 'admin');
    }

    public function store(Request $request): void
    {
        Auth::requireSuperAdmin();

        $titleAr = trim((string)$request->get('title_ar'));
        $titleEn = trim((string)$request->get('title_en'));
        $slug = trim((string)$request->get('slug'));
        $contentAr = (string)$request->get('content_ar');
        $contentEn = (string)$request->get('content_en');
        $excerptAr = (string)$request->get('excerpt_ar');
        $excerptEn = (string)$request->get('excerpt_en');
        $template = (string)$request->get('template', 'default');
        $status = (string)$request->get('status', 'published');
        $metaTitleAr = (string)$request->get('meta_title_ar');
        $metaTitleEn = (string)$request->get('meta_title_en');
        $metaDescAr = (string)$request->get('meta_desc_ar');
        $metaDescEn = (string)$request->get('meta_desc_en');

        if (empty($titleAr) && empty($titleEn)) {
            $_SESSION['error'] = 'يرجى إدخال عنوان الصفحة بالعربية أو الإنجليزية';
            Response::redirect('/admin/pages/create');
            return;
        }

        // Auto slug if empty
        if (empty($slug)) {
            $baseSlug = !empty($titleEn) ? $titleEn : $titleAr;
            $slug = preg_replace('/[^a-zA-Z0-9\x{0621}-\x{064A}-]+/u', '-', strtolower($baseSlug));
            $slug = trim($slug, '-');
        }

        // Ensure unique slug
        $existing = Database::fetchOne("SELECT id FROM pages WHERE slug = ?", [$slug]);
        if ($existing) {
            $slug .= '-' . rand(100, 999);
        }

        // Handle featured image upload
        $featuredImage = null;
        $uploaded = Uploader::upload('featured_image_file', 'pages');
        if ($uploaded) {
            $featuredImage = $uploaded;
        } else {
            $featuredImage = trim((string)$request->get('featured_image'));
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO pages (title_ar, title_en, slug, content_ar, content_en, excerpt_ar, excerpt_en, featured_image, template, status, meta_title_ar, meta_title_en, meta_desc_ar, meta_desc_en, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");

        $stmt->execute([
            $titleAr ?: $titleEn,
            $titleEn ?: $titleAr,
            $slug,
            $contentAr,
            $contentEn,
            $excerptAr,
            $excerptEn,
            $featuredImage ?: null,
            $template,
            $status,
            $metaTitleAr,
            $metaTitleEn,
            $metaDescAr,
            $metaDescEn
        ]);

        $pageId = (int)$pdo->lastInsertId();

        // Save ACF Custom Fields
        CustomFields::saveEntityFields('page', $pageId, $request->all(), $_FILES);

        $_SESSION['success'] = __('page_saved_success');
        Response::redirect('/admin/pages');
    }

    public function edit(Request $request): void
    {
        Auth::requireSuperAdmin();
        $id = (int)$request->get('id');

        $page = Database::fetchOne("SELECT * FROM pages WHERE id = ?", [$id]);
        if (!$page) {
            $_SESSION['error'] = 'الصفحة المطلوبة غير موجودة';
            Response::redirect('/admin/pages');
            return;
        }

        Response::view('admin/pages/form', [
            'page' => $page,
            'isEdit' => true
        ], 'admin');
    }

    public function update(Request $request): void
    {
        Auth::requireSuperAdmin();
        $id = (int)$request->get('id');

        $page = Database::fetchOne("SELECT * FROM pages WHERE id = ?", [$id]);
        if (!$page) {
            $_SESSION['error'] = 'الصفحة غير موجودة';
            Response::redirect('/admin/pages');
            return;
        }

        $titleAr = trim((string)$request->get('title_ar'));
        $titleEn = trim((string)$request->get('title_en'));
        $slug = trim((string)$request->get('slug'));
        $contentAr = (string)$request->get('content_ar');
        $contentEn = (string)$request->get('content_en');
        $excerptAr = (string)$request->get('excerpt_ar');
        $excerptEn = (string)$request->get('excerpt_en');
        $template = (string)$request->get('template', 'default');
        $status = (string)$request->get('status', 'published');
        $metaTitleAr = (string)$request->get('meta_title_ar');
        $metaTitleEn = (string)$request->get('meta_title_en');
        $metaDescAr = (string)$request->get('meta_desc_ar');
        $metaDescEn = (string)$request->get('meta_desc_en');

        // Check slug uniqueness
        $existing = Database::fetchOne("SELECT id FROM pages WHERE slug = ? AND id != ?", [$slug, $id]);
        if ($existing) {
            $slug .= '-' . rand(100, 999);
        }

        // Image upload
        $featuredImage = $page['featured_image'];
        $uploaded = Uploader::upload('featured_image_file', 'pages');
        if ($uploaded) {
            $featuredImage = $uploaded;
        } elseif ($request->get('featured_image') !== null) {
            $featuredImage = trim((string)$request->get('featured_image'));
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE pages SET 
                title_ar = ?, title_en = ?, slug = ?, content_ar = ?, content_en = ?,
                excerpt_ar = ?, excerpt_en = ?, featured_image = ?, template = ?,
                status = ?, meta_title_ar = ?, meta_title_en = ?, meta_desc_ar = ?,
                meta_desc_en = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $stmt->execute([
            $titleAr ?: $titleEn,
            $titleEn ?: $titleAr,
            $slug ?: $page['slug'],
            $contentAr,
            $contentEn,
            $excerptAr,
            $excerptEn,
            $featuredImage ?: null,
            $template,
            $status,
            $metaTitleAr,
            $metaTitleEn,
            $metaDescAr,
            $metaDescEn,
            $id
        ]);

        // Save ACF Custom Fields
        CustomFields::saveEntityFields('page', $id, $request->all(), $_FILES);

        $_SESSION['success'] = __('page_saved_success');
        Response::redirect('/admin/pages');
    }

    public function delete(Request $request): void
    {
        Auth::requireSuperAdmin();
        $id = (int)$request->get('id');

        $pdo = Database::getConnection();
        $pdo->prepare("DELETE FROM pages WHERE id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM custom_field_values WHERE entity_type = 'page' AND entity_id = ?")->execute([$id]);

        $_SESSION['success'] = __('page_deleted_success');
        Response::redirect('/admin/pages');
    }

    public function toggleStatus(Request $request): void
    {
        Auth::requireSuperAdmin();
        $id = (int)$request->get('id');

        $page = Database::fetchOne("SELECT status FROM pages WHERE id = ?", [$id]);
        if ($page) {
            $newStatus = $page['status'] === 'published' ? 'draft' : 'published';
            Database::getConnection()->prepare("UPDATE pages SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$newStatus, $id]);
            Response::json(['success' => true, 'new_status' => $newStatus]);
            return;
        }

        Response::json(['success' => false, 'message' => 'Page not found'], 404);
    }
}
