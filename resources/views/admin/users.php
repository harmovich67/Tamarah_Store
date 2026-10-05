<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = I18n::getLocale();
?>

<div class="w-full space-y-6 pb-12">
    
    <!-- Top Header Card: Tumurna Luxury Theme -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200/90 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-2xl bg-[#fbf5e9] text-[#8c5d25] border border-[#c49a52]/30 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5 text-[#8c5d25]"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-stone-900"><?= $locale === 'en' ? 'Users & Customers Management' : 'إدارة المستخدمين وحسابات العملاء' ?></h1>
            </div>
            <p class="text-xs sm:text-sm font-semibold text-stone-500 mt-1"><?= $locale === 'en' ? 'Manage store customer accounts, administrators, staff roles, and account permissions' : 'إدارة حسابات عملاء المتجر، وتعيين صلاحيات المدراء، ومتابعة الطلبات وتفعيل أو إيقاف الحسابات' ?></p>
        </div>

        <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="px-6 py-3.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-2xl text-xs sm:text-sm font-black flex items-center gap-2 shadow-lg shadow-[#315b2b]/20 transition">
            <i data-lucide="user-plus" class="w-4 h-4 text-[#c49a52]"></i>
            <span><?= $locale === 'en' ? 'Add New User / Staff' : 'إضافة عضو / مستخدم جديد' ?></span>
        </button>
    </div>

    <?php if (!empty($updated)): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs font-bold text-emerald-800 flex items-center gap-3 shadow-xs">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
            <span><?= $locale === 'en' ? 'User profile updated successfully!' : 'تم تحديث بيانات المستخدم وصلاحياته بنجاح!' ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($created)): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs font-bold text-emerald-800 flex items-center gap-3 shadow-xs">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
            <span><?= $locale === 'en' ? 'New user added successfully!' : 'تم إضافة المستخدم الجديد بنجاح!' ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($deleted)): ?>
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs font-bold text-amber-800 flex items-center gap-3 shadow-xs">
            <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0"></i>
            <span><?= $locale === 'en' ? 'User removed successfully' : 'تم حذف الحساب بنجاح' ?></span>
        </div>
    <?php endif; ?>

    <!-- Users Table (Full Width) -->
    <div class="bg-white rounded-3xl border border-stone-200/90 shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-xs text-<?= $isRtl ? 'right' : 'left' ?>">
                <thead>
                    <tr class="bg-[#fcfbf9] border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider text-[11px]">
                        <th class="p-4 whitespace-nowrap"><?= $locale === 'en' ? 'User / Customer' : 'المستخدم / العميل' ?></th>
                        <th class="p-4 whitespace-nowrap"><?= $locale === 'en' ? 'Saudi Mobile' : 'رقم الجوال' ?></th>
                        <th class="p-4 whitespace-nowrap text-center"><?= $locale === 'en' ? 'Role & Permission' : 'الدور والصلاحية' ?></th>
                        <th class="p-4 whitespace-nowrap text-center"><?= $locale === 'en' ? 'City & Activity' : 'المدينة والطلبات' ?></th>
                        <th class="p-4 whitespace-nowrap text-center"><?= $locale === 'en' ? 'Account Status' : 'حالة الحساب' ?></th>
                        <th class="p-4 whitespace-nowrap text-center"><?= $locale === 'en' ? 'Actions' : 'الإجراءات' ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-stone-50/70 transition">
                            <td class="p-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#fbf5e9] to-[#edf4e8] text-[#315b2b] font-black text-sm flex items-center justify-center border border-[#c49a52]/20 shadow-xs">
                                        <?= mb_substr($u['name'], 0, 1, 'UTF-8') ?>
                                    </div>
                                    <div>
                                        <span class="font-bold text-stone-900 block"><?= htmlspecialchars($u['name']) ?></span>
                                        <span class="text-[11px] text-stone-400 font-mono" dir="ltr"><?= htmlspecialchars($u['email']) ?></span>
                                    </div>
                                </div>
                            </td>

                            <td class="p-4 whitespace-nowrap text-stone-600 font-mono" dir="ltr">
                                <?= !empty($u['phone']) ? htmlspecialchars($u['phone']) : '<span class="text-stone-300">-</span>' ?>
                            </td>

                            <td class="p-4 whitespace-nowrap text-center">
                                <?php if ($u['role_name'] === 'super_admin'): ?>
                                    <span class="inline-flex items-center gap-1.5 text-[10px] font-black text-amber-900 bg-amber-50 px-3 py-1 rounded-full border border-amber-300 shadow-xs">
                                        <i data-lucide="crown" class="w-3.5 h-3.5 text-amber-600"></i>
                                        <span><?= $locale === 'en' ? 'Super Admin' : 'المدير العام للمتجر' ?></span>
                                    </span>
                                <?php elseif ($u['role_name'] === 'store_manager'): ?>
                                    <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-900 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-300 shadow-xs">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span><?= $locale === 'en' ? 'Store Manager' : 'مدير المتجر والمخزون' ?></span>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-stone-700 bg-stone-100 px-3 py-1 rounded-full border border-stone-200">
                                        <i data-lucide="user" class="w-3.5 h-3.5 text-stone-400"></i>
                                        <span><?= $locale === 'en' ? 'Store Customer' : 'عميل متجر تمور' ?></span>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="p-4 whitespace-nowrap text-center">
                                <div class="text-[11px]">
                                    <span class="font-bold text-stone-800"><?= htmlspecialchars($u['customer_city'] ?: ($locale === 'en' ? 'Saudi Arabia' : 'المملكة العربية السعودية')) ?></span>
                                    <span class="text-stone-400 block text-[10px]"><?= (int)($u['orders_count'] ?? 0) ?> <?= $locale === 'en' ? 'orders' : 'طلبات منفذة' ?></span>
                                </div>
                            </td>

                            <td class="p-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-3 py-1 rounded-full whitespace-nowrap <?= $u['status'] === 'active' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($u['status'] === 'pending' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-rose-50 text-rose-800 border border-rose-200') ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $u['status'] === 'active' ? 'bg-emerald-500' : ($u['status'] === 'pending' ? 'bg-amber-500' : 'bg-rose-500') ?>"></span>
                                    <span><?= $u['status'] === 'active' ? ($locale === 'en' ? 'Active' : 'نشط ومعتمد') : ($u['status'] === 'pending' ? ($locale === 'en' ? 'Pending' : 'قيد الانتظار') : ($locale === 'en' ? 'Suspended' : 'محظور / متوقف')) ?></span>
                                </span>
                            </td>

                            <td class="p-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button onclick="openEditUserModal(<?= htmlspecialchars(json_encode($u)) ?>)" class="p-2 bg-[#fbf5e9] hover:bg-[#315b2b] text-[#8c5d25] hover:text-white rounded-xl transition border border-[#c49a52]/20" title="<?= $locale === 'en' ? 'Edit User' : 'تعديل الصلاحيات' ?>">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>

                                    <form action="<?= url('/admin/users/delete') ?>" method="POST" onsubmit="return confirm('<?= $locale === 'en' ? 'Are you sure you want to delete this user?' : 'هل أنت متأكد من حذف هذا المستخدم؟' ?>');" class="inline">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="p-2 bg-stone-50 hover:bg-rose-50 text-stone-400 hover:text-rose-600 rounded-xl transition border border-stone-200" title="<?= $locale === 'en' ? 'Delete' : 'حذف' ?>">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-4 border border-stone-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-stone-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#edf4e8] text-[#315b2b] flex items-center justify-center">
                    <i data-lucide="user-cog" class="w-5 h-5"></i>
                </div>
                <h3 class="font-black text-stone-900 text-base"><?= $locale === 'en' ? 'Edit User & Permissions' : 'تعديل المستخدم والصلاحيات' ?></h3>
            </div>
            <button onclick="document.getElementById('editUserModal').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-stone-100 text-stone-500 hover:bg-stone-200 flex items-center justify-center transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="<?= url('/admin/users/update') ?>" method="POST" class="space-y-4 text-xs">
            <input type="hidden" id="editUserId" name="id">

            <div>
                <label class="block font-bold text-stone-700 mb-1.5"><?= $locale === 'en' ? 'Full Name' : 'اسم المستخدم / العميل' ?> *</label>
                <input type="text" id="editUserName" name="name" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-[#315b2b]">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= $locale === 'en' ? 'Email Address' : 'البريد الإلكتروني' ?> *</label>
                    <input type="email" id="editUserEmail" name="email" required dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs font-mono focus:outline-none focus:border-[#315b2b] text-<?= $isRtl ? 'right' : 'left' ?>">
                </div>

                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= $locale === 'en' ? 'Saudi Mobile' : 'رقم الجوال' ?></label>
                    <input type="tel" id="editUserPhone" name="phone" dir="ltr" placeholder="05XXXXXXXX" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs font-mono focus:outline-none focus:border-[#315b2b] text-<?= $isRtl ? 'right' : 'left' ?>">
                </div>
            </div>

            <div>
                <label class="block font-bold text-stone-700 mb-1.5"><?= $locale === 'en' ? 'New Password (leave empty to keep current)' : 'كلمة المرور الجديدة (اتركها فارغة إذا لم ترغب في التغيير)' ?></label>
                <input type="password" name="password" placeholder="••••••••" dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs font-mono focus:outline-none focus:border-[#315b2b] text-<?= $isRtl ? 'right' : 'left' ?>">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= $locale === 'en' ? 'Role & Permission' : 'الدور والصلاحية' ?> *</label>
                    <select id="editUserRole" name="role_id" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-[#315b2b]">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($locale === 'en' ? ($r['display_name_en'] ?? $r['name']) : $r['display_name_ar']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= $locale === 'en' ? 'Account Status' : 'حالة الحساب' ?> *</label>
                    <select id="editUserStatus" name="status" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-[#315b2b]">
                        <option value="active"><?= $locale === 'en' ? 'Active' : 'نشط ومعتمد' ?></option>
                        <option value="pending"><?= $locale === 'en' ? 'Pending' : 'قيد الانتظار' ?></option>
                        <option value="rejected"><?= $locale === 'en' ? 'Suspended' : 'محظور / متوقف' ?></option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-stone-100">
                <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')" class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl font-bold transition"><?= $locale === 'en' ? 'Cancel' : 'إلغاء' ?></button>
                <button type="submit" class="px-6 py-2.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-xl font-black shadow-md shadow-[#315b2b]/20 transition"><?= $locale === 'en' ? 'Save Changes' : 'حفظ التعديلات' ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-4 border border-stone-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-stone-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#fbf5e9] text-[#8c5d25] flex items-center justify-center">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </div>
                <h3 class="font-black text-stone-900 text-base"><?= $locale === 'en' ? 'Add New User or Admin' : 'إضافة مستخدم أو مدير جديد' ?></h3>
            </div>
            <button onclick="document.getElementById('addUserModal').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-stone-100 text-stone-500 hover:bg-stone-200 flex items-center justify-center transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="<?= url('/admin/users/store') ?>" method="POST" class="space-y-4 text-xs">
            <div>
                <label class="block font-bold text-stone-700 mb-1.5"><?= $locale === 'en' ? 'Full Name' : 'اسم المستخدم / العميل' ?> *</label>
                <input type="text" name="name" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-[#315b2b]">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= $locale === 'en' ? 'Email Address' : 'البريد الإلكتروني' ?> *</label>
                    <input type="email" name="email" required dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs font-mono focus:outline-none focus:border-[#315b2b] text-<?= $isRtl ? 'right' : 'left' ?>">
                </div>

                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= $locale === 'en' ? 'Saudi Mobile' : 'رقم الجوال' ?></label>
                    <input type="tel" name="phone" dir="ltr" placeholder="05XXXXXXXX" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs font-mono focus:outline-none focus:border-[#315b2b] text-<?= $isRtl ? 'right' : 'left' ?>">
                </div>
            </div>

            <div>
                <label class="block font-bold text-stone-700 mb-1.5"><?= $locale === 'en' ? 'Password' : 'كلمة المرور' ?> *</label>
                <input type="password" name="password" required dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs font-mono focus:outline-none focus:border-[#315b2b] text-<?= $isRtl ? 'right' : 'left' ?>">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= $locale === 'en' ? 'Role & Permission' : 'الدور والصلاحية' ?> *</label>
                    <select name="role_id" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-[#315b2b]">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= $r['name'] === 'customer' ? 'selected' : '' ?>>
                                <?= htmlspecialchars($locale === 'en' ? ($r['display_name_en'] ?? $r['name']) : $r['display_name_ar']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= $locale === 'en' ? 'Status' : 'الحالة' ?> *</label>
                    <select name="status" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-[#315b2b]">
                        <option value="active"><?= $locale === 'en' ? 'Active' : 'نشط ومعتمد' ?></option>
                        <option value="pending"><?= $locale === 'en' ? 'Pending' : 'قيد الانتظار' ?></option>
                        <option value="rejected"><?= $locale === 'en' ? 'Suspended' : 'محظور / متوقف' ?></option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-stone-100">
                <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl font-bold transition"><?= $locale === 'en' ? 'Cancel' : 'إلغاء' ?></button>
                <button type="submit" class="px-6 py-2.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-xl font-black shadow-md shadow-[#315b2b]/20 transition"><?= $locale === 'en' ? 'Create User' : 'إنشاء المستخدم' ?></button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditUserModal(user) {
        document.getElementById('editUserId').value = user.id;
        document.getElementById('editUserName').value = user.name;
        document.getElementById('editUserEmail').value = user.email;
        document.getElementById('editUserPhone').value = user.phone || '';
        document.getElementById('editUserRole').value = user.role_id;
        document.getElementById('editUserStatus').value = user.status;

        document.getElementById('editUserModal').classList.remove('hidden');
        if (window.lucide) {
            lucide.createIcons();
        }
    }
</script>
