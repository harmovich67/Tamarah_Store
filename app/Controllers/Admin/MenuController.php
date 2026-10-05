<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use Database\Database;

class MenuController
{
    public function index(Request $request): void
    {
        Auth::requireSuperAdmin();

        $items = Database::fetchAll("
            SELECT m.*, p.title_ar as page_title_ar, p.title_en as page_title_en, p.slug as page_slug
            FROM menu_items m
            LEFT JOIN pages p ON m.link_type = 'page' AND m.page_id = p.id
            ORDER BY m.location ASC, m.order_index ASC, m.id ASC
        ");

        $headerItems = array_values(array_filter($items, fn($i) => $i['location'] === 'header'));
        $footerItems = array_values(array_filter($items, fn($i) => $i['location'] === 'footer'));

        $pages = Database::fetchAll("SELECT id, title_ar, title_en, slug FROM pages ORDER BY title_ar ASC");

        Response::view('admin/menus', [
            'headerItems' => $headerItems,
            'footerItems' => $footerItems,
            'pages' => $pages,
            'saved' => $request->get('saved') == 1,
            'deleted' => $request->get('deleted') == 1,
        ], 'admin');
    }

    public function store(Request $request): void
    {
        Auth::requireSuperAdmin();

        $location = trim((string)$request->get('location', 'header')) === 'footer' ? 'footer' : 'header';
        $labelAr = trim((string)$request->get('label_ar'));
        $labelEn = trim((string)$request->get('label_en'));
        $linkType = trim((string)$request->get('link_type', 'custom')) === 'page' ? 'page' : 'custom';
        $pageId = $linkType === 'page' ? (int)$request->get('page_id') : null;
        $customUrl = $linkType === 'custom' ? trim((string)$request->get('custom_url')) : null;
        $icon = trim((string)$request->get('icon', ''));
        $openNewTab = $request->get('open_new_tab') ? 1 : 0;
        $orderIndex = (int)$request->get('order_index', 0);
        $status = trim((string)$request->get('status', 'active'));

        if (empty($labelAr) && empty($labelEn)) {
            Response::redirect('/admin/menus');
            return;
        }

        Database::execute("
            INSERT INTO menu_items (location, label_ar, label_en, link_type, page_id, custom_url, icon, open_new_tab, order_index, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $location, $labelAr ?: $labelEn, $labelEn ?: $labelAr, $linkType,
            $pageId, $customUrl, $icon ?: null, $openNewTab, $orderIndex, $status
        ]);

        Response::redirect('/admin/menus?saved=1');
    }

    public function update(Request $request): void
    {
        Auth::requireSuperAdmin();

        $id = (int)$request->get('id');
        $location = trim((string)$request->get('location', 'header')) === 'footer' ? 'footer' : 'header';
        $labelAr = trim((string)$request->get('label_ar'));
        $labelEn = trim((string)$request->get('label_en'));
        $linkType = trim((string)$request->get('link_type', 'custom')) === 'page' ? 'page' : 'custom';
        $pageId = $linkType === 'page' ? (int)$request->get('page_id') : null;
        $customUrl = $linkType === 'custom' ? trim((string)$request->get('custom_url')) : null;
        $icon = trim((string)$request->get('icon', ''));
        $openNewTab = $request->get('open_new_tab') ? 1 : 0;
        $orderIndex = (int)$request->get('order_index', 0);
        $status = trim((string)$request->get('status', 'active'));

        Database::execute("
            UPDATE menu_items
            SET location = ?, label_ar = ?, label_en = ?, link_type = ?, page_id = ?,
                custom_url = ?, icon = ?, open_new_tab = ?, order_index = ?, status = ?
            WHERE id = ?
        ", [
            $location, $labelAr ?: $labelEn, $labelEn ?: $labelAr, $linkType,
            $pageId, $customUrl, $icon ?: null, $openNewTab, $orderIndex, $status, $id
        ]);

        Response::redirect('/admin/menus?saved=1');
    }

    public function delete(Request $request): void
    {
        Auth::requireSuperAdmin();
        $id = (int)$request->get('id');
        Database::execute("DELETE FROM menu_items WHERE id = ?", [$id]);
        Response::redirect('/admin/menus?deleted=1');
    }
}
