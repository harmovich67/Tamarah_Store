<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use Database\Database;
use PDO;

class FieldGroupsController
{
    public function index(Request $request): void
    {
        Auth::requireSuperAdmin();

        $groups = Database::fetchAll("
            SELECT cfg.*, COUNT(cf.id) as fields_count 
            FROM custom_field_groups cfg
            LEFT JOIN custom_fields cf ON cf.group_id = cfg.id
            GROUP BY cfg.id
            ORDER BY cfg.order_index ASC, cfg.id DESC
        ");

        Response::view('admin/custom_fields/index', [
            'groups' => $groups
        ], 'admin');
    }

    public function create(Request $request): void
    {
        Auth::requireSuperAdmin();

        Response::view('admin/custom_fields/form', [
            'group' => null,
            'fields' => [],
            'isEdit' => false
        ], 'admin');
    }

    public function store(Request $request): void
    {
        Auth::requireSuperAdmin();

        $titleAr = trim((string)$request->get('title_ar'));
        $titleEn = trim((string)$request->get('title_en'));
        $keyName = trim((string)$request->get('key_name'));
        $targetType = trim((string)$request->get('target_type', 'page'));
        $targetFilter = trim((string)$request->get('target_filter', 'all'));
        $position = trim((string)$request->get('position', 'normal'));
        $orderIndex = (int)$request->get('order_index', 0);
        $status = trim((string)$request->get('status', 'active'));

        if (empty($titleAr) && empty($titleEn)) {
            $_SESSION['error'] = 'يرجى إدخال اسم المجموعة';
            Response::redirect('/admin/custom-fields/create');
            return;
        }

        if (empty($keyName)) {
            $keyName = 'group_' . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($titleEn ?: $titleAr)) . '_' . rand(100, 999);
        }

        // Uniqueness
        $existing = Database::fetchOne("SELECT id FROM custom_field_groups WHERE key_name = ?", [$keyName]);
        if ($existing) {
            $keyName .= '_' . rand(10, 99);
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO custom_field_groups (title_ar, title_en, key_name, target_type, target_filter, position, order_index, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([
                $titleAr ?: $titleEn,
                $titleEn ?: $titleAr,
                $keyName,
                $targetType,
                $targetFilter,
                $position,
                $orderIndex,
                $status
            ]);

            $groupId = (int)$pdo->lastInsertId();

            // Insert Fields
            $rawFields = $request->get('fields');
            if (is_array($rawFields)) {
                $stmtFld = $pdo->prepare("
                    INSERT INTO custom_fields (group_id, label_ar, label_en, name, type, instructions_ar, instructions_en, required, default_value, options, order_index)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $idx = 0;
                foreach ($rawFields as $f) {
                    if (empty($f['name'])) continue;
                    $idx++;
                    $fName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower(trim($f['name'])));
                    $fLabelAr = trim((string)($f['label_ar'] ?? $fName));
                    $fLabelEn = trim((string)($f['label_en'] ?? $fLabelAr));
                    $fType = trim((string)($f['type'] ?? 'text'));
                    $fInstAr = trim((string)($f['instructions_ar'] ?? ''));
                    $fInstEn = trim((string)($f['instructions_en'] ?? ''));
                    $fReq = !empty($f['required']) ? 1 : 0;
                    $fDef = trim((string)($f['default_value'] ?? ''));
                    $fOptions = null;
                    if ($fType === 'repeater' && !empty($f['sub_fields']) && is_array($f['sub_fields'])) {
                        $cleanSub = [];
                        foreach ($f['sub_fields'] as $sf) {
                            if (!empty($sf['name'])) {
                                $cleanSub[] = [
                                    'name' => preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower(trim($sf['name']))),
                                    'label' => trim((string)($sf['label'] ?? ($sf['label_ar'] ?? $sf['name']))),
                                    'label_ar' => trim((string)($sf['label_ar'] ?? ($sf['label'] ?? $sf['name']))),
                                    'label_en' => trim((string)($sf['label_en'] ?? '')),
                                    'type' => trim((string)($sf['type'] ?? 'text'))
                                ];
                            }
                        }
                        $fOptions = !empty($cleanSub) ? json_encode($cleanSub, JSON_UNESCAPED_UNICODE) : null;
                    } elseif (!empty($f['options'])) {
                        $fOptions = is_array($f['options']) ? json_encode($f['options'], JSON_UNESCAPED_UNICODE) : trim((string)$f['options']);
                    }

                    $stmtFld->execute([
                        $groupId, $fLabelAr, $fLabelEn, $fName, $fType,
                        $fInstAr, $fInstEn, $fReq, $fDef, $fOptions, $idx
                    ]);
                }
            }

            $pdo->commit();
            $_SESSION['success'] = 'تم حفظ مجموعة الحقول بنجاح!';
            Response::redirect('/admin/custom-fields');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'حدث خطأ أثناء الحفظ: ' . $e->getMessage();
            Response::redirect('/admin/custom-fields/create');
        }
    }

    public function edit(Request $request): void
    {
        Auth::requireSuperAdmin();
        $id = (int)$request->get('id');

        $group = Database::fetchOne("SELECT * FROM custom_field_groups WHERE id = ?", [$id]);
        if (!$group) {
            $_SESSION['error'] = 'المجموعة غير موجودة';
            Response::redirect('/admin/custom-fields');
            return;
        }

        $fields = Database::fetchAll("SELECT * FROM custom_fields WHERE group_id = ? ORDER BY order_index ASC, id ASC", [$id]);

        Response::view('admin/custom_fields/form', [
            'group' => $group,
            'fields' => $fields,
            'isEdit' => true
        ], 'admin');
    }

    public function update(Request $request): void
    {
        Auth::requireSuperAdmin();
        $id = (int)$request->get('id');

        $group = Database::fetchOne("SELECT * FROM custom_field_groups WHERE id = ?", [$id]);
        if (!$group) {
            $_SESSION['error'] = 'المجموعة غير موجودة';
            Response::redirect('/admin/custom-fields');
            return;
        }

        $titleAr = trim((string)$request->get('title_ar'));
        $titleEn = trim((string)$request->get('title_en'));
        $keyName = trim((string)$request->get('key_name'));
        $targetType = trim((string)$request->get('target_type', 'page'));
        $targetFilter = trim((string)$request->get('target_filter', 'all'));
        $position = trim((string)$request->get('position', 'normal'));
        $orderIndex = (int)$request->get('order_index', 0);
        $status = trim((string)$request->get('status', 'active'));

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                UPDATE custom_field_groups SET
                    title_ar = ?, title_en = ?, key_name = ?, target_type = ?,
                    target_filter = ?, position = ?, order_index = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $titleAr ?: $titleEn,
                $titleEn ?: $titleAr,
                $keyName ?: $group['key_name'],
                $targetType,
                $targetFilter,
                $position,
                $orderIndex,
                $status,
                $id
            ]);

            // Sync Fields: Delete and reinsert
            $pdo->prepare("DELETE FROM custom_fields WHERE group_id = ?")->execute([$id]);

            $rawFields = $request->get('fields');
            if (is_array($rawFields)) {
                $stmtFld = $pdo->prepare("
                    INSERT INTO custom_fields (group_id, label_ar, label_en, name, type, instructions_ar, instructions_en, required, default_value, options, order_index)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $idx = 0;
                foreach ($rawFields as $f) {
                    if (empty($f['name'])) continue;
                    $idx++;
                    $fName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower(trim($f['name'])));
                    $fLabelAr = trim((string)($f['label_ar'] ?? $fName));
                    $fLabelEn = trim((string)($f['label_en'] ?? $fLabelAr));
                    $fType = trim((string)($f['type'] ?? 'text'));
                    $fInstAr = trim((string)($f['instructions_ar'] ?? ''));
                    $fInstEn = trim((string)($f['instructions_en'] ?? ''));
                    $fReq = !empty($f['required']) ? 1 : 0;
                    $fDef = trim((string)($f['default_value'] ?? ''));
                    $fOptions = null;
                    if ($fType === 'repeater' && !empty($f['sub_fields']) && is_array($f['sub_fields'])) {
                        $cleanSub = [];
                        foreach ($f['sub_fields'] as $sf) {
                            if (!empty($sf['name'])) {
                                $cleanSub[] = [
                                    'name' => preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower(trim($sf['name']))),
                                    'label' => trim((string)($sf['label'] ?? ($sf['label_ar'] ?? $sf['name']))),
                                    'label_ar' => trim((string)($sf['label_ar'] ?? ($sf['label'] ?? $sf['name']))),
                                    'label_en' => trim((string)($sf['label_en'] ?? '')),
                                    'type' => trim((string)($sf['type'] ?? 'text'))
                                ];
                            }
                        }
                        $fOptions = !empty($cleanSub) ? json_encode($cleanSub, JSON_UNESCAPED_UNICODE) : null;
                    } elseif (!empty($f['options'])) {
                        $fOptions = is_array($f['options']) ? json_encode($f['options'], JSON_UNESCAPED_UNICODE) : trim((string)$f['options']);
                    }

                    $stmtFld->execute([
                        $id, $fLabelAr, $fLabelEn, $fName, $fType,
                        $fInstAr, $fInstEn, $fReq, $fDef, $fOptions, $idx
                    ]);
                }
            }

            $pdo->commit();
            $_SESSION['success'] = 'تم تحديث مجموعة الحقول بنجاح!';
            Response::redirect('/admin/custom-fields');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'حدث خطأ أثناء التحديث: ' . $e->getMessage();
            Response::redirect('/admin/custom-fields/' . $id . '/edit');
        }
    }

    public function delete(Request $request): void
    {
        Auth::requireSuperAdmin();
        $id = (int)$request->get('id');

        $pdo = Database::getConnection();
        $pdo->prepare("DELETE FROM custom_fields WHERE group_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM custom_field_groups WHERE id = ?")->execute([$id]);

        $_SESSION['success'] = 'تم حذف مجموعة الحقول بنجاح.';
        Response::redirect('/admin/custom-fields');
    }

    /**
     * AJAX: list assignable targets (individual entities + sub-types) for the
     * target_filter dropdown, based on the selected target_type.
     */
    public function apiTargets(Request $request): void
    {
        Auth::requireSuperAdmin();

        $type = trim((string)$request->get('type', 'page'));
        $locale = \App\Core\I18n::getLocale();
        $options = [];

        if ($type === 'page') {
            $options[] = ['value' => 'all', 'label' => $locale === 'ar' ? 'الكل (كل الصفحات)' : 'All Pages'];
            $rows = Database::fetchAll("SELECT id, title_ar, title_en FROM pages ORDER BY id DESC");
            foreach ($rows as $r) {
                $label = $locale === 'ar' ? (($r['title_ar'] ?: $r['title_en']) ?: ('#' . $r['id'])) : (($r['title_en'] ?: $r['title_ar']) ?: ('#' . $r['id']));
                $options[] = ['value' => 'id:' . $r['id'], 'label' => $label];
            }
        } elseif ($type === 'post') {
            $options[] = ['value' => 'all', 'label' => $locale === 'ar' ? 'الكل (كل أنواع المنشورات)' : 'All Posts (any type)'];

            $typeLabels = [
                'post' => $locale === 'ar' ? 'كل مقالات المدونة' : 'All Blog Posts',
                'announcement' => $locale === 'ar' ? 'كل الإعلانات' : 'All Announcements',
                'faq' => $locale === 'ar' ? 'كل الأسئلة الشائعة' : 'All FAQs',
            ];
            $groupLabel = $locale === 'ar' ? 'أنواع المنشورات' : 'Post Types';
            foreach ($typeLabels as $pt => $label) {
                $options[] = ['value' => 'type:' . $pt, 'label' => $label, 'group' => $groupLabel];
            }

            $postTypeNames = [
                'post' => $locale === 'ar' ? 'مدونة' : 'Blog',
                'announcement' => $locale === 'ar' ? 'إعلان' : 'Announcement',
                'faq' => $locale === 'ar' ? 'سؤال شائع' : 'FAQ',
            ];
            $individualGroup = $locale === 'ar' ? 'منشورات فردية' : 'Individual Posts';
            $rows = Database::fetchAll("SELECT id, title_ar, title_en, post_type FROM posts ORDER BY id DESC LIMIT 300");
            foreach ($rows as $r) {
                $title = $locale === 'ar' ? (($r['title_ar'] ?: $r['title_en']) ?: ('#' . $r['id'])) : (($r['title_en'] ?: $r['title_ar']) ?: ('#' . $r['id']));
                $typeName = $postTypeNames[$r['post_type']] ?? $r['post_type'];
                $options[] = [
                    'value' => 'id:' . $r['id'],
                    'label' => $title . ' (' . $typeName . ')',
                    'group' => $individualGroup
                ];
            }
        } elseif ($type === 'product') {
            $options[] = ['value' => 'all', 'label' => $locale === 'ar' ? 'الكل (كل المنتجات)' : 'All Products'];
            $rows = Database::fetchAll("SELECT id, name_ar, name_en FROM products ORDER BY id DESC LIMIT 500");
            foreach ($rows as $r) {
                $label = $locale === 'ar' ? (($r['name_ar'] ?: $r['name_en']) ?: ('#' . $r['id'])) : (($r['name_en'] ?: $r['name_ar']) ?: ('#' . $r['id']));
                $options[] = ['value' => 'id:' . $r['id'], 'label' => $label];
            }
        } elseif ($type === 'category') {
            $options[] = ['value' => 'all', 'label' => $locale === 'ar' ? 'الكل (كل أصناف التمور)' : 'All Date Categories'];
            $rows = Database::fetchAll("SELECT id, name_ar, name_en FROM categories ORDER BY id ASC");
            foreach ($rows as $r) {
                $label = $locale === 'ar' ? (($r['name_ar'] ?: $r['name_en']) ?: ('#' . $r['id'])) : (($r['name_en'] ?: $r['name_ar']) ?: ('#' . $r['id']));
                $options[] = ['value' => 'id:' . $r['id'], 'label' => $label];
            }
        } else {
            $options[] = ['value' => 'all', 'label' => $locale === 'ar' ? 'الكل' : 'All'];
        }

        Response::json(['success' => true, 'options' => $options]);
    }
}
