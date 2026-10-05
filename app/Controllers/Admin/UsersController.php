<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use Database\Database;

class UsersController
{
    public function index(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden: Super Admin only', 403);
            return;
        }

        $users = Database::fetchAll("
            SELECT u.*, r.name as role_name, r.display_name_ar as role_ar, r.display_name_en as role_en,
                   (SELECT city FROM user_addresses ua WHERE ua.user_id = u.id AND ua.is_default = 1 LIMIT 1) as customer_city,
                   (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) as orders_count
            FROM users u
            JOIN roles r ON u.role_id = r.id
            ORDER BY u.id DESC
        ");

        $roles = Database::fetchAll("SELECT * FROM roles ORDER BY id ASC");

        Response::view('admin/users', [
            'users' => $users,
            'roles' => $roles,
            'updated' => $request->get('updated') == 1,
            'created' => $request->get('created') == 1,
            'deleted' => $request->get('deleted') == 1,
        ], 'admin');
    }

    public function store(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden', 403);
            return;
        }

        $name = trim((string)$request->get('name'));
        $email = trim((string)$request->get('email'));
        $phone = trim((string)$request->get('phone'));
        $password = (string)$request->get('password');
        $roleId = (int)$request->get('role_id', 3);
        $status = trim((string)$request->get('status', 'active'));

        if (empty($name) || empty($email) || empty($password)) {
            Response::error('جميع الحقول الأساسية مطلوبة');
            return;
        }

        // Check uniqueness
        $exists = Database::fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($exists) {
            Response::error('البريد الإلكتروني مسجل بالفعل');
            return;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        Database::execute("
            INSERT INTO users (role_id, name, email, password, phone, status, phone_verified)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ", [$roleId, $name, $email, $hash, $phone, $status]);

        Response::redirect('/admin/users?created=1');
    }

    public function update(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden', 403);
            return;
        }

        $id = (int)$request->get('id');
        $user = Database::fetchOne("SELECT * FROM users WHERE id = ?", [$id]);

        if (!$user) {
            Response::error('المستخدم غير موجود', 404);
            return;
        }

        $name = trim((string)$request->get('name'));
        $email = trim((string)$request->get('email'));
        $phone = trim((string)$request->get('phone'));
        $password = (string)$request->get('password');
        $roleId = (int)$request->get('role_id');
        $status = trim((string)$request->get('status', 'active'));

        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            Database::execute("
                UPDATE users 
                SET name = ?, email = ?, phone = ?, password = ?, role_id = ?, status = ?
                WHERE id = ?
            ", [$name, $email, $phone, $hash, $roleId, $status, $id]);
        } else {
            Database::execute("
                UPDATE users 
                SET name = ?, email = ?, phone = ?, role_id = ?, status = ?
                WHERE id = ?
            ", [$name, $email, $phone, $roleId, $status, $id]);
        }

        Response::redirect('/admin/users?updated=1');
    }

    public function delete(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden', 403);
            return;
        }

        $id = (int)$request->get('id');
        if ($id === Auth::id()) {
            Response::error('لا يمكنك حذف حسابك الحالي المسجل به الدخول');
            return;
        }

        Database::execute("DELETE FROM users WHERE id = ?", [$id]);
        Response::redirect('/admin/users?deleted=1');
    }
}
