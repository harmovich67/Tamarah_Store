<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center font-black shadow-inner">
                    <i data-lucide="zap" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight"><?= __('cache_management') ?></h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5"><?= __('cache_management_subtitle') ?></p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <button type="button" onclick="preloadCache()" id="btnPreload" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 transition font-bold text-sm shadow-sm">
                <i data-lucide="flame" class="w-4 h-4 text-amber-500"></i>
                <span><?= __('preload_cache') ?></span>
            </button>
            <button type="button" onclick="clearCacheGroup('all')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 text-white hover:bg-rose-700 transition font-bold text-sm shadow-lg shadow-rose-600/30">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                <span><?= __('clear_all_cache') ?></span>
            </button>
        </div>
    </div>

    <!-- Alert Banners -->
    <?php if (!empty($cleared)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 flex items-center justify-between gap-3 shadow-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0"></i>
                <p class="text-sm font-bold"><?= __('cache_cleared_success') ?> (<?= htmlspecialchars($cleared) ?>)</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:opacity-75"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($preloaded)): ?>
        <div class="p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60 text-indigo-800 dark:text-indigo-300 flex items-center justify-between gap-3 shadow-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <i data-lucide="sparkles" class="w-5 h-5 text-indigo-600 dark:text-indigo-400 flex-shrink-0"></i>
                <div>
                    <p class="text-sm font-bold"><?= __('cache_preloaded_success') ?></p>
                    <?php if (!empty($warmLog)): ?>
                        <ul class="text-xs text-indigo-600 dark:text-indigo-400 mt-1 list-disc list-inside">
                            <?php foreach ($warmLog as $w): ?>
                                <li><?= htmlspecialchars($w) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-indigo-600 hover:opacity-75"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    <?php endif; ?>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Size -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= __('total_cache_size') ?></span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1.5" id="statTotalSize"><?= $stats['total_size_formatted'] ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center">
                    <i data-lucide="hard-drive" class="w-6 h-6"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3 flex items-center gap-1.5">
                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500"></i>
                <span><?= __('cache_storage_healthy') ?></span>
            </p>
        </div>

        <!-- Total Files -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= __('cached_items_count') ?></span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1.5" id="statTotalFiles"><?= number_format($stats['total_files']) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center">
                    <i data-lucide="files" class="w-6 h-6"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3 flex items-center gap-1.5">
                <i data-lucide="database" class="w-3.5 h-3.5 text-amber-500"></i>
                <span><?= __('file_driver_active') ?></span>
            </p>
        </div>

        <!-- Preload Status -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= __('cache_engine') ?></span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1.5">High-Speed File</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center">
                    <i data-lucide="cpu" class="w-6 h-6"></i>
                </div>
            </div>
            <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-3 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span><?= __('in_memory_optimized') ?></span>
            </p>
        </div>

        <!-- Performance Boost -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= __('response_speed') ?></span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1.5">&lt; 15 ms</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 flex items-center justify-center">
                    <i data-lucide="gauge" class="w-6 h-6"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3 flex items-center gap-1.5">
                <i data-lucide="rocket" class="w-3.5 h-3.5 text-cyan-500"></i>
                <span><?= __('zero_db_overhead') ?></span>
            </p>
        </div>
    </div>

    <!-- Cache Categories Breakdown Grid -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i data-lucide="layers" class="w-5 h-5 text-indigo-500"></i>
                <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= __('cache_partitions') ?></h3>
            </div>
            <span class="text-xs text-slate-400"><?= __('cache_partitions_hint') ?></span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
            <?php
            $groupDefinitions = [
                'translations' => [
                    'title' => __('cache_translations_title'),
                    'desc' => __('cache_translations_desc'),
                    'icon' => 'languages',
                    'color' => 'indigo'
                ],
                'settings' => [
                    'title' => __('cache_settings_title'),
                    'desc' => __('cache_settings_desc'),
                    'icon' => 'sliders',
                    'color' => 'amber'
                ],
                'queries' => [
                    'title' => __('cache_queries_title'),
                    'desc' => __('cache_queries_desc'),
                    'icon' => 'database',
                    'color' => 'emerald'
                ],
                'views' => [
                    'title' => __('cache_views_title'),
                    'desc' => __('cache_views_desc'),
                    'icon' => 'layout-template',
                    'color' => 'purple'
                ],
                'data' => [
                    'title' => __('cache_data_title'),
                    'desc' => __('cache_data_desc'),
                    'icon' => 'server',
                    'color' => 'cyan'
                ],
                'security' => [
                    'title' => __('cache_security_title'),
                    'desc' => __('cache_security_desc'),
                    'icon' => 'shield',
                    'color' => 'rose'
                ]
            ];
            ?>

            <?php foreach ($groupDefinitions as $grpKey => $grpInfo): ?>
                <?php $grpStat = $stats['groups'][$grpKey] ?? ['count' => 0, 'size_formatted' => '0 B']; ?>
                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700/60 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-600 transition shadow-inner">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-<?= $grpInfo['color'] ?>-500/10 text-<?= $grpInfo['color'] ?>-600 dark:text-<?= $grpInfo['color'] ?>-400 flex items-center justify-center font-bold">
                                <i data-lucide="<?= $grpInfo['icon'] ?>" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700" id="stat-<?= $grpKey ?>-size">
                                <?= $grpStat['size_formatted'] ?>
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white"><?= $grpInfo['title'] ?></h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed"><?= $grpInfo['desc'] ?></p>
                    </div>

                    <div class="mt-5 pt-4 border-t border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between">
                        <span class="text-xs text-slate-400 font-medium" id="stat-<?= $grpKey ?>-count">
                            <span class="font-bold text-slate-700 dark:text-slate-300"><?= $grpStat['count'] ?></span> <?= __('cached_files') ?>
                        </span>
                        <button type="button" onclick="clearCacheGroup('<?= $grpKey ?>')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-600 hover:text-white transition">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span><?= __('clear') ?></span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Toast Feedback Container -->
<div id="cacheToast" class="fixed bottom-6 left-6 z-50 transform translate-y-20 opacity-0 pointer-events-none transition-all duration-300 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl bg-slate-900 text-white border border-slate-800">
    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400" id="toastIcon"></i>
    <span class="text-sm font-bold" id="toastMessage"></span>
</div>

<script>
    function showToast(msg, isSuccess = true) {
        const toast = document.getElementById('cacheToast');
        const text = document.getElementById('toastMessage');
        const icon = document.getElementById('toastIcon');
        text.innerText = msg;
        icon.setAttribute('data-lucide', isSuccess ? 'check-circle' : 'alert-triangle');
        icon.className = `w-5 h-5 ${isSuccess ? 'text-emerald-400' : 'text-rose-400'}`;
        lucide.createIcons();

        toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
        }, 3500);
    }

    async function clearCacheGroup(group) {
        if (group === 'all' && !confirm('<?= __('confirm_clear_all_cache') ?>')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('group', group);
            formData.append('_csrf', '<?= csrf_token() ?>');

            const res = await fetch('<?= url('/admin/cache/clear') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                showToast(data.message || '<?= __('cache_cleared_success') ?>', true);
                if (data.stats) {
                    updateStatsUI(data.stats);
                }
            } else {
                showToast(data.error || '<?= __('error_occurred') ?>', false);
            }
        } catch (e) {
            console.error(e);
            window.location.href = '<?= url('/admin/cache?cleared=') ?>' + group;
        }
    }

    async function preloadCache() {
        const btn = document.getElementById('btnPreload');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin text-amber-500"></i> <span><?= __('preloading') ?>...</span>`;
        lucide.createIcons();

        try {
            const formData = new FormData();
            formData.append('_csrf', '<?= csrf_token() ?>');

            const res = await fetch('<?= url('/admin/cache/preload') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                showToast(data.message || '<?= __('cache_preloaded_success') ?>', true);
                if (data.stats) {
                    updateStatsUI(data.stats);
                }
            } else {
                showToast(data.error || '<?= __('error_occurred') ?>', false);
            }
        } catch (e) {
            console.error(e);
            window.location.href = '<?= url('/admin/cache?preloaded=1') ?>';
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            lucide.createIcons();
        }
    }

    function updateStatsUI(stats) {
        const totalSize = document.getElementById('statTotalSize');
        const totalFiles = document.getElementById('statTotalFiles');
        if (totalSize) totalSize.innerText = stats.total_size_formatted;
        if (totalFiles) totalFiles.innerText = Number(stats.total_files).toLocaleString();

        for (const [key, val] of Object.entries(stats.groups || {})) {
            const sizeEl = document.getElementById(`stat-${key}-size`);
            const countEl = document.getElementById(`stat-${key}-count`);
            if (sizeEl) sizeEl.innerText = val.size_formatted;
            if (countEl) countEl.innerHTML = `<span class="font-bold text-slate-700 dark:text-slate-300">${val.count}</span> <?= __('cached_files') ?>`;
        }
    }
</script>
