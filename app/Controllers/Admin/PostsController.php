<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Uploader;
use App\Core\CustomFields;
use Database\Database;
use PDO;

class PostsController
{
    public function index(Request $request): void
    {
        Auth::requireSuperAdmin();

        $type = trim((string)$request->get('type', ''));
        $search = trim((string)$request->get('q', ''));

        $sql = "SELECT p.* FROM posts p WHERE 1=1";
        $params = [];

        if ($type !== '') {
            $sql .= " AND p.post_type = ?";
            $params[] = $type;
        }

        if ($search !== '') {
            $sql .= " AND (p.title_ar LIKE ? OR p.title_en LIKE ? OR p.slug LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY p.id DESC";
        $posts = Database::fetchAll($sql, $params);

        Response::view('admin/posts/index', [
            'posts' => $posts,
            'currentType' => $type,
            'search' => $search
        ], 'admin');
    }

    public function create(Request $request): void
    {
        Auth::requireSuperAdmin();

        Response::view('admin/posts/form', [
            'post' => null,
            'isEdit' => false
        ], 'admin');
    }

    public function store(Request $request): void
    {
        Auth::requireSuperAdmin();

        $postType = trim((string)$request->get('post_type', 'post'));
        $titleAr = trim((string)$request->get('title_ar'));
        $titleEn = trim((string)$request->get('title_en'));
        $slug = trim((string)$request->get('slug'));
        $contentAr = (string)$request->get('content_ar');
        $contentEn = (string)$request->get('content_en');
        $excerptAr = (string)$request->get('excerpt_ar');
        $excerptEn = (string)$request->get('excerpt_en');
        $status = (string)$request->get('status', 'published');

        if (empty($titleAr) && empty($titleEn)) {
            $_SESSION['error'] = 'يرجى إدخال عنوان المقال';
            Response::redirect('/admin/posts/create');
            return;
        }

        if (empty($slug)) {
            $baseSlug = !empty($titleEn) ? $titleEn : $titleAr;
            $slug = preg_replace('/[^a-zA-Z0-9\x{0621}-\x{064A}-]+/u', '-', strtolower($baseSlug));
            $slug = trim($slug, '-') . '-' . rand(100, 999);
        }

        $featuredImage = null;
        $uploaded = Uploader::upload('featured_image_file', 'posts');
        if ($uploaded) {
            $featuredImage = $uploaded;
        } else {
            $featuredImage = trim((string)$request->get('featured_image'));
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO posts (post_type, title_ar, title_en, slug, content_ar, content_en, excerpt_ar, excerpt_en, featured_image, status, author_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");

        $stmt->execute([
            $postType,
            $titleAr ?: $titleEn,
            $titleEn ?: $titleAr,
            $slug,
            $contentAr,
            $contentEn,
            $excerptAr,
            $excerptEn,
            $featuredImage ?: null,
            $status,
            Auth::id()
        ]);

        $postId = (int)$pdo->lastInsertId();

        // Save ACF fields
        CustomFields::saveEntityFields('post', $postId, $request->all(), $_FILES, $postType);

        $_SESSION['success'] = __('post_saved_success');
        Response::redirect('/admin/posts');
    }

    public function edit(Request $request): void
    {
        Auth::requireSuperAdmin();
        $id = (int)$request->get('id');

        $post = Database::fetchOne("SELECT * FROM posts WHERE id = ?", [$id]);
        if (!$post) {
            $_SESSION['error'] = 'المقال غير موجود';
            Response::redirect('/admin/posts');
            return;
        }

        Response::view('admin/posts/form', [
            'post' => $post,
            'isEdit' => true
        ], 'admin');
    }

    public function update(Request $request): void
    {
        Auth::requireSuperAdmin();
        $id = (int)$request->get('id');

        $post = Database::fetchOne("SELECT * FROM posts WHERE id = ?", [$id]);
        if (!$post) {
            $_SESSION['error'] = 'المقال غير موجود';
            Response::redirect('/admin/posts');
            return;
        }

        $postType = trim((string)$request->get('post_type', 'post'));
        $titleAr = trim((string)$request->get('title_ar'));
        $titleEn = trim((string)$request->get('title_en'));
        $slug = trim((string)$request->get('slug'));
        $contentAr = (string)$request->get('content_ar');
        $contentEn = (string)$request->get('content_en');
        $excerptAr = (string)$request->get('excerpt_ar');
        $excerptEn = (string)$request->get('excerpt_en');
        $status = (string)$request->get('status', 'published');

        $featuredImage = $post['featured_image'];
        $uploaded = Uploader::upload('featured_image_file', 'posts');
        if ($uploaded) {
            $featuredImage = $uploaded;
        } elseif ($request->get('featured_image') !== null) {
            $featuredImage = trim((string)$request->get('featured_image'));
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE posts SET 
                post_type = ?, title_ar = ?, title_en = ?, slug = ?, content_ar = ?,
                content_en = ?, excerpt_ar = ?, excerpt_en = ?, featured_image = ?,
                status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $stmt->execute([
            $postType,
            $titleAr ?: $titleEn,
            $titleEn ?: $titleAr,
            $slug ?: $post['slug'],
            $contentAr,
            $contentEn,
            $excerptAr,
            $excerptEn,
            $featuredImage ?: null,
            $status,
            $id
        ]);

        // Save ACF fields
        CustomFields::saveEntityFields('post', $id, $request->all(), $_FILES, $postType);

        $_SESSION['success'] = __('post_saved_success');
        Response::redirect('/admin/posts');
    }

    public function delete(Request $request): void
    {
        Auth::requireSuperAdmin();
        $id = (int)$request->get('id');

        $pdo = Database::getConnection();
        $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM custom_field_values WHERE entity_type = 'post' AND entity_id = ?")->execute([$id]);

        $_SESSION['success'] = __('post_deleted_success');
        Response::redirect('/admin/posts');
    }
}
