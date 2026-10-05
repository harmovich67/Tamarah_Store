<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 flex items-center justify-center font-black shadow-inner">
                    <i data-lucide="archive-restore" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight"><?= __('backup_management') ?></h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5"><?= __('backup_management_subtitle') ?></p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <button type="button" onclick="generateBackup()" id="btnGenerateBackup" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition font-bold text-sm shadow-lg shadow-indigo-600/30">
                <i data-lucide="download-cloud" class="w-4 h-4"></i>
                <span><?= __('generate_full_backup') ?></span>
            </button>
        </div>
    </div>

    <!-- Alert Banners -->
    <?php if (!empty($generated)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 flex items-center justify-between gap-3 shadow-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0"></i>
                <p class="text-sm font-bold"><?= __('backup_generated_success') ?></p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:opacity-75"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($deleted)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 flex items-center justify-between gap-3 shadow-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0"></i>
                <p class="text-sm font-bold"><?= __('backup_deleted_success') ?></p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:opacity-75"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($restored)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 flex items-center justify-between gap-3 shadow-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0"></i>
                <p class="text-sm font-bold"><?= __('backup_restored_success') ?></p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:opacity-75"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($autoSaved)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 flex items-center justify-between gap-3 shadow-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0"></i>
                <p class="text-sm font-bold"><?= __('auto_backup_settings_saved') ?></p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:opacity-75"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    <?php endif; ?>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= __('database_size') ?></span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1.5"><?= htmlspecialchars($dbSizeFormatted) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center">
                    <i data-lucide="database" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= __('uploads_size') ?></span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1.5"><?= htmlspecialchars($uploadsSizeFormatted) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center">
                    <i data-lucide="images" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= __('available_backups') ?></span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1.5" id="backupsCount"><?= count($backups) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center">
                    <i data-lucide="archive" class="w-6 h-6"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Automatic Backup Schedule -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= __('auto_backup_title') ?></h3>
                    <p class="text-xs text-slate-400"><?= __('auto_backup_subtitle') ?></p>
                </div>
            </div>

            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" id="autoEnabledHidden" value="<?= $autoEnabled ? '1' : '0' ?>">
                <input type="checkbox" id="autoEnabledCheckbox" <?= $autoEnabled ? 'checked' : '' ?> class="sr-only peer" onchange="onAutoBackupToggle(this)">
                <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                <span class="ms-3 text-xs font-bold text-slate-600 dark:text-slate-300"><?= __('enabled_active') ?></span>
            </label>
        </div>

        <div id="autoBackupOptions" class="<?= $autoEnabled ? '' : 'hidden' ?> mt-5 pt-5 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5"><?= __('auto_backup_interval') ?></label>
                <select id="autoIntervalSelect" class="w-full sm:w-64 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm font-semibold">
                    <option value="6" <?= $autoIntervalHours === 6 ? 'selected' : '' ?>><?= __('every_6_hours') ?></option>
                    <option value="12" <?= $autoIntervalHours === 12 ? 'selected' : '' ?>><?= __('every_12_hours') ?></option>
                    <option value="24" <?= $autoIntervalHours === 24 ? 'selected' : '' ?>><?= __('daily') ?></option>
                    <option value="72" <?= $autoIntervalHours === 72 ? 'selected' : '' ?>><?= __('every_3_days') ?></option>
                    <option value="168" <?= $autoIntervalHours === 168 ? 'selected' : '' ?>><?= __('weekly') ?></option>
                </select>
            </div>
            <?php if (!empty($autoLastRunAt)): ?>
                <p class="text-xs text-slate-400 flex items-center gap-1.5 pb-2.5">
                    <i data-lucide="history" class="w-3.5 h-3.5"></i>
                    <span><?= __('last_auto_backup') ?>: <b class="text-slate-600 dark:text-slate-300"><?= htmlspecialchars($autoLastRunAt) ?></b></span>
                </p>
            <?php endif; ?>
            <button type="button" onclick="saveAutoBackupSettings()" id="btnSaveAutoBackup" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-sm transition">
                <?= __('save_schedule') ?>
            </button>
        </div>
        <p class="text-[11px] text-slate-400 mt-4"><?= __('auto_backup_note') ?></p>
    </div>

    <!-- What's Included / How To Use -->
    <div class="bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/70 dark:border-indigo-800/50 rounded-3xl p-6 flex flex-col sm:flex-row gap-4">
        <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0">
            <i data-lucide="info" class="w-6 h-6"></i>
        </div>
        <div class="text-sm text-indigo-900 dark:text-indigo-200 leading-relaxed">
            <p class="font-black mb-1"><?= $locale === 'ar' ? 'ماذا تحتوي النسخة الاحتياطية الكاملة؟' : "What's inside the full backup?" ?></p>
            <p>
                <?= $locale === 'ar'
                    ? 'عند الضغط على "إنشاء نسخة احتياطية كاملة"، يقوم النظام بتجميع نسخة حديثة من قاعدة البيانات بالكامل (منتجات التمور، الأقسام، الطلبات، الكوبونات، مناطق الشحن، الترجمات، الإعدادات) مع كل الصور المرفوعة داخل مجلد uploads، وضغطها في ملف ZIP واحد جاهز للتنزيل. يمكنك بعدها نقل أو استعادة هذا الملف على أي استضافة تدعم PHP و MySQL، واستيراد قاعدة البيانات ومجلد uploads كما هو.'
                    : 'Clicking "Generate Full Backup" bundles a fresh dump of the entire database (date products, categories, orders, coupons, shipping zones, translations, settings) together with every uploaded image inside the uploads folder into a single downloadable .zip. You can then restore this package on any host supporting PHP and MySQL.'
                ?>
            </p>
        </div>
    </div>

    <!-- Upload & Restore -->
    <div class="bg-rose-50/60 dark:bg-rose-950/20 border border-rose-200/70 dark:border-rose-900/40 rounded-3xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-11 h-11 rounded-2xl bg-rose-600 text-white flex items-center justify-center flex-shrink-0">
                <i data-lucide="upload-cloud" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= __('restore_from_upload_title') ?></h3>
                <p class="text-xs text-slate-500 dark:text-slate-400"><?= __('restore_from_upload_subtitle') ?></p>
            </div>
        </div>

        <form id="uploadRestoreForm" action="<?= url('/admin/backup/restore') ?>" method="POST" enctype="multipart/form-data" onsubmit="return false;" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <input type="file" id="restoreFileInput" name="restore_file" accept=".zip,.sql" required class="flex-1 text-xs text-slate-600 dark:text-slate-300 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-rose-600 file:text-white hover:file:bg-rose-700 transition cursor-pointer bg-white dark:bg-slate-900 border border-rose-200 dark:border-rose-900/50 rounded-xl px-2 py-1.5">
            <button type="button" onclick="confirmRestore(null, 'upload')" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-black shadow-sm transition whitespace-nowrap">
                <?= __('restore_now') ?>
            </button>
        </form>
    </div>

    <!-- Backups List -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
            <i data-lucide="folder-archive" class="w-5 h-5 text-indigo-500"></i>
            <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= __('available_backups') ?></h3>
        </div>

        <div id="backupsListContainer">
            <?php if (empty($backups)): ?>
                <div class="p-10 text-center text-slate-400" id="noBackupsState">
                    <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="package-open" class="w-8 h-8 text-slate-300 dark:text-slate-600"></i>
                    </div>
                    <h4 class="font-bold text-slate-700 dark:text-slate-300 mb-1"><?= __('no_backups_yet') ?></h4>
                    <p class="text-xs text-slate-400"><?= __('no_backups_yet_hint') ?></p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-slate-100 dark:divide-slate-800" id="backupsList">
                    <?php foreach ($backups as $b): ?>
                        <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3" data-backup-row="<?= htmlspecialchars($b['name']) ?>">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="file-archive" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white truncate" dir="ltr"><?= htmlspecialchars($b['name']) ?></p>
                                    <p class="text-xs text-slate-400"><?= htmlspecialchars($b['size_formatted']) ?> &middot; <?= date('Y-m-d H:i', $b['created_at']) ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a href="<?= url('/admin/backup/download?file=' . urlencode($b['name'])) ?>" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-600 hover:text-white transition">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                    <span><?= __('download') ?></span>
                                </a>
                                <button type="button" onclick="confirmRestore('<?= htmlspecialchars($b['name'], ENT_QUOTES) ?>', 'existing')" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-600 hover:text-white transition">
                                    <i data-lucide="history" class="w-3.5 h-3.5"></i>
                                    <span><?= __('restore') ?></span>
                                </button>
                                <button type="button" onclick="deleteBackup('<?= htmlspecialchars($b['name'], ENT_QUOTES) ?>')" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-600 hover:text-white transition">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span><?= __('delete') ?></span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div id="restoreConfirmModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-rose-100 dark:border-rose-900/40 space-y-5">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center flex-shrink-0">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="font-black text-slate-900 dark:text-white text-lg"><?= __('confirm_restore_title') ?></h3>
                <p class="text-xs text-slate-400" id="restoreConfirmFileLabel"></p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 text-xs text-rose-800 dark:text-rose-300 leading-relaxed">
            <?= __('confirm_restore_warning') ?>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                <?= __('confirm_restore_type_hint', ['word' => '<b class="font-mono text-rose-600">' . __('restore_confirm_word') . '</b>']) ?>
            </label>
            <input type="text" id="restoreConfirmInput" oninput="onRestoreConfirmInput()" dir="ltr" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm font-bold text-center focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20">
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" onclick="closeRestoreModal()" class="px-5 py-3 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-sm"><?= __('cancel') ?></button>
            <button type="button" id="btnConfirmRestore" onclick="performRestore()" disabled class="px-6 py-3 bg-rose-300 text-white rounded-xl font-black text-sm shadow-md transition cursor-not-allowed">
                <?= __('restore_now') ?>
            </button>
        </div>
    </div>
</div>

<!-- Toast Feedback Container -->
<div id="backupToast" class="fixed bottom-6 left-6 z-50 transform translate-y-20 opacity-0 pointer-events-none transition-all duration-300 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl bg-slate-900 text-white border border-slate-800">
    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400" id="backupToastIcon"></i>
    <span class="text-sm font-bold" id="backupToastMessage"></span>
</div>

<script>
    function showBackupToast(msg, isSuccess = true) {
        const toast = document.getElementById('backupToast');
        const text = document.getElementById('backupToastMessage');
        const icon = document.getElementById('backupToastIcon');
        text.innerText = msg;
        icon.setAttribute('data-lucide', isSuccess ? 'check-circle' : 'alert-triangle');
        icon.className = `w-5 h-5 ${isSuccess ? 'text-emerald-400' : 'text-rose-400'}`;
        lucide.createIcons();

        toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
        }, 3500);
    }

    async function generateBackup() {
        const btn = document.getElementById('btnGenerateBackup');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> <span><?= __('generating_backup') ?>...</span>`;
        lucide.createIcons();

        try {
            const formData = new FormData();
            formData.append('_csrf', '<?= csrf_token() ?>');

            const res = await fetch('<?= url('/admin/backup/generate') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                showBackupToast(data.message || '<?= __('backup_generated_success') ?>', true);
                setTimeout(() => window.location.href = '<?= url('/admin/backup?generated=1') ?>', 600);
            } else {
                showBackupToast(data.error || '<?= __('error_occurred') ?>', false);
            }
        } catch (e) {
            console.error(e);
            showBackupToast('<?= __('error_occurred') ?>', false);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            lucide.createIcons();
        }
    }

    async function deleteBackup(fileName) {
        if (!confirm('<?= __('confirm_delete_backup') ?>')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('file', fileName);
            formData.append('_csrf', '<?= csrf_token() ?>');

            const res = await fetch('<?= url('/admin/backup/delete') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                const row = document.querySelector(`[data-backup-row="${fileName}"]`);
                if (row) row.remove();
                showBackupToast('<?= __('backup_deleted_success') ?>', true);
                const countEl = document.getElementById('backupsCount');
                if (countEl) countEl.innerText = Math.max(0, parseInt(countEl.innerText, 10) - 1);
            } else {
                showBackupToast(data.error || '<?= __('error_occurred') ?>', false);
            }
        } catch (e) {
            console.error(e);
            window.location.href = '<?= url('/admin/backup?deleted=1') ?>';
        }
    }

    // ==========================================
    // Restore (from an existing backup, or an uploaded file)
    // ==========================================
    const RESTORE_CONFIRM_WORD = <?= json_encode(__('restore_confirm_word')) ?>;
    let pendingRestore = { mode: null, fileName: null };

    function confirmRestore(fileName, mode) {
        if (mode === 'upload') {
            const input = document.getElementById('restoreFileInput');
            if (!input.files || !input.files.length) {
                showBackupToast('<?= __('choose_file_first') ?>', false);
                return;
            }
        }

        pendingRestore = { mode, fileName };
        const label = document.getElementById('restoreConfirmFileLabel');
        label.textContent = mode === 'upload'
            ? document.getElementById('restoreFileInput').files[0].name
            : fileName;

        document.getElementById('restoreConfirmInput').value = '';
        onRestoreConfirmInput();
        document.getElementById('restoreConfirmModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeRestoreModal() {
        document.getElementById('restoreConfirmModal').classList.add('hidden');
    }

    function onRestoreConfirmInput() {
        const val = document.getElementById('restoreConfirmInput').value.trim();
        const btn = document.getElementById('btnConfirmRestore');
        const matches = val === RESTORE_CONFIRM_WORD;
        btn.disabled = !matches;
        btn.classList.toggle('bg-rose-300', !matches);
        btn.classList.toggle('cursor-not-allowed', !matches);
        btn.classList.toggle('bg-rose-600', matches);
        btn.classList.toggle('hover:bg-rose-700', matches);
    }

    async function performRestore() {
        const btn = document.getElementById('btnConfirmRestore');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin inline-block"></i> <?= __('restoring') ?>...`;
        lucide.createIcons();

        try {
            const formData = new FormData();
            formData.append('_csrf', '<?= csrf_token() ?>');

            if (pendingRestore.mode === 'upload') {
                const fileInput = document.getElementById('restoreFileInput');
                formData.append('restore_file', fileInput.files[0]);
            } else {
                formData.append('file', pendingRestore.fileName);
            }

            const res = await fetch('<?= url('/admin/backup/restore') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                showBackupToast('<?= __('backup_restored_success') ?>', true);
                setTimeout(() => window.location.href = '<?= url('/admin/backup?restored=1') ?>', 800);
            } else {
                showBackupToast(data.error || '<?= __('error_occurred') ?>', false);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        } catch (e) {
            console.error(e);
            showBackupToast('<?= __('error_occurred') ?>', false);
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }

    // ==========================================
    // Automatic Backup Schedule
    // ==========================================
    function onAutoBackupToggle(checkbox) {
        document.getElementById('autoBackupOptions').classList.toggle('hidden', !checkbox.checked);
    }

    async function saveAutoBackupSettings() {
        const btn = document.getElementById('btnSaveAutoBackup');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin inline-block"></i>`;
        lucide.createIcons();

        try {
            const formData = new FormData();
            formData.append('_csrf', '<?= csrf_token() ?>');
            formData.append('auto_enabled', document.getElementById('autoEnabledCheckbox').checked ? '1' : '');
            formData.append('interval_hours', document.getElementById('autoIntervalSelect').value);

            const res = await fetch('<?= url('/admin/backup/auto-settings') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                showBackupToast('<?= __('auto_backup_settings_saved') ?>', true);
            } else {
                showBackupToast(data.error || '<?= __('error_occurred') ?>', false);
            }
        } catch (e) {
            console.error(e);
            showBackupToast('<?= __('error_occurred') ?>', false);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            lucide.createIcons();
        }
    }
</script>
