<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center font-black shadow-inner">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight"><?= __('logs_management') ?></h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5"><?= __('logs_management_subtitle') ?></p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="<?= url('/admin/logs/download') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 transition font-bold text-sm shadow-sm">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span><?= __('download_logs') ?> (<?= $stats['total_size_formatted'] ?>)</span>
            </a>
            <button type="button" onclick="clearLogs()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 text-white hover:bg-rose-700 transition font-bold text-sm shadow-lg shadow-rose-600/30">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                <span><?= __('clear_logs') ?></span>
            </button>
        </div>
    </div>

    <!-- Alert Banners -->
    <?php if (!empty($cleared)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 flex items-center justify-between gap-3 shadow-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0"></i>
                <p class="text-sm font-bold"><?= __('logs_cleared_success') ?></p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:opacity-75"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    <?php endif; ?>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Events -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= __('total_log_entries') ?></span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1.5"><?= number_format($stats['total_events']) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center">
                    <i data-lucide="activity" class="w-6 h-6"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3 flex items-center gap-1.5">
                <i data-lucide="hard-drive" class="w-3.5 h-3.5 text-indigo-500"></i>
                <span><?= __('log_file_size') ?>: <?= $stats['total_size_formatted'] ?></span>
            </p>
        </div>

        <!-- Errors Count -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-rose-500 uppercase tracking-wider"><?= __('error_events') ?></span>
                    <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1.5"><?= number_format($stats['errors_count']) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center">
                    <i data-lucide="alert-octagon" class="w-6 h-6"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3 flex items-center gap-1.5">
                <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-rose-500"></i>
                <span><?= __('errors_require_attention') ?></span>
            </p>
        </div>

        <!-- Security Incidents -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-purple-500 uppercase tracking-wider"><?= __('security_events') ?></span>
                    <h3 class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1.5"><?= number_format($stats['security_count']) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 flex items-center justify-center">
                    <i data-lucide="shield-alert" class="w-6 h-6"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3 flex items-center gap-1.5">
                <i data-lucide="lock" class="w-3.5 h-3.5 text-purple-500"></i>
                <span><?= __('brute_force_and_csrf') ?></span>
            </p>
        </div>

        <!-- Warnings & Notices -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-amber-500 uppercase tracking-wider"><?= __('warnings_count') ?></span>
                    <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1.5"><?= number_format($stats['warnings_count']) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3 flex items-center gap-1.5">
                <i data-lucide="info" class="w-3.5 h-3.5 text-amber-500"></i>
                <span><?= __('system_warnings_logged') ?></span>
            </p>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <form method="GET" action="<?= url('/admin/logs') ?>" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Level Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-2 md:pb-0 scrollbar-none">
                <?php
                $levels = [
                    'all' => ['label' => __('all_levels'), 'icon' => 'list'],
                    'error' => ['label' => __('error_level'), 'icon' => 'alert-octagon'],
                    'warning' => ['label' => __('warning_level'), 'icon' => 'alert-triangle'],
                    'security' => ['label' => __('security_level'), 'icon' => 'shield-alert'],
                    'info' => ['label' => __('info_level'), 'icon' => 'info']
                ];
                ?>
                <?php foreach ($levels as $lKey => $lInfo): ?>
                    <a href="<?= url('/admin/logs?level=' . $lKey . ($search ? '&q=' . urlencode($search) : '')) ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition flex-shrink-0 <?= $currentLevel === $lKey ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' ?>">
                        <i data-lucide="<?= $lInfo['icon'] ?>" class="w-3.5 h-3.5"></i>
                        <span><?= $lInfo['label'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search & Limit -->
            <div class="flex items-center gap-3">
                <input type="hidden" name="level" value="<?= htmlspecialchars($currentLevel) ?>">
                <div class="relative flex-1 md:w-64">
                    <i data-lucide="search" class="w-4 h-4 absolute top-3 right-3 text-slate-400"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="<?= __('search_logs_placeholder') ?>" class="w-full pr-9 pl-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <select name="limit" onchange="this.form.submit()" class="py-2 px-3 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                    <option value="150" <?= $limit == 150 ? 'selected' : '' ?>>150</option>
                    <option value="300" <?= $limit == 300 ? 'selected' : '' ?>>300</option>
                    <option value="500" <?= $limit == 500 ? 'selected' : '' ?>>500</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 font-bold text-xs rounded-xl hover:opacity-90 transition">
                    <?= __('filter') ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Logs Stream / Feed -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i data-lucide="terminal" class="w-5 h-5 text-indigo-500"></i>
                <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= __('logs_feed') ?></h3>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    <?= count($logs) ?> <?= __('records_shown') ?>
                </span>
            </div>
            <span class="text-xs text-slate-400"><?= __('logs_auto_rotated_notice') ?></span>
        </div>

        <?php if (empty($logs)): ?>
            <div class="p-16 text-center">
                <div class="w-16 h-16 rounded-3xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mx-auto mb-4">
                    <i data-lucide="check-circle" class="w-8 h-8 text-emerald-500"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900 dark:text-white"><?= __('no_logs_found') ?></h4>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto"><?= __('no_logs_found_hint') ?></p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php foreach ($logs as $idx => $log): ?>
                    <?php
                    $isError = in_array($log['level'], ['error', 'critical', 'emergency']);
                    $isWarning = $log['level'] === 'warning';
                    $isSecurity = $log['level'] === 'security' || $log['channel'] === 'security';
                    
                    $badgeClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                    $iconName = 'info';

                    if ($isError) {
                        $badgeClass = 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200 dark:border-rose-900';
                        $iconName = 'alert-octagon';
                    } elseif ($isWarning) {
                        $badgeClass = 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 border border-amber-200 dark:border-amber-900';
                        $iconName = 'alert-triangle';
                    } elseif ($isSecurity) {
                        $badgeClass = 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400 border border-purple-200 dark:border-purple-900';
                        $iconName = 'shield-alert';
                    }
                    ?>
                    <div class="p-5 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1.5 text-xs font-black px-2.5 py-1 rounded-lg uppercase tracking-wider <?= $badgeClass ?>">
                                    <i data-lucide="<?= $iconName ?>" class="w-3.5 h-3.5"></i>
                                    <span><?= htmlspecialchars($log['channel']) ?>.<?= htmlspecialchars($log['level']) ?></span>
                                </span>
                                <span class="text-xs font-mono text-slate-500 dark:text-slate-400"><?= htmlspecialchars($log['timestamp']) ?></span>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-slate-400 font-mono">
                                <span><i data-lucide="globe" class="w-3 h-3 inline"></i> <?= htmlspecialchars($log['ip']) ?></span>
                                <span><i data-lucide="user" class="w-3 h-3 inline"></i> <?= htmlspecialchars($log['user']) ?></span>
                            </div>
                        </div>

                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 break-words leading-relaxed">
                            <?= htmlspecialchars($log['message']) ?>
                        </p>

                        <?php if (!empty($log['context'])): ?>
                            <div class="mt-3">
                                <button type="button" onclick="toggleContext('ctx-<?= $idx ?>')" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                    <i data-lucide="code" class="w-3.5 h-3.5"></i>
                                    <span><?= __('show_context_payload') ?></span>
                                </button>
                                <div id="ctx-<?= $idx ?>" class="hidden mt-2 p-4 rounded-xl bg-slate-950 text-slate-200 text-xs font-mono overflow-x-auto border border-slate-800">
                                    <pre><?= htmlspecialchars(json_encode($log['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleContext(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.toggle('hidden');
        }
    }

    async function clearLogs() {
        if (!confirm('<?= __('confirm_clear_logs') ?>')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('_csrf', '<?= csrf_token() ?>');

            const res = await fetch('<?= url('/admin/logs/clear') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                window.location.href = '<?= url('/admin/logs?cleared=1') ?>';
            }
        } catch (e) {
            window.location.href = '<?= url('/admin/logs?cleared=1') ?>';
        }
    }
</script>
