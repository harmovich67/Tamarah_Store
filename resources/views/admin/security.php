<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center font-black shadow-inner">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight"><?= __('security_center') ?></h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5"><?= __('security_center_subtitle') ?></p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold text-xs border border-emerald-200 dark:border-emerald-800/60 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span><?= __('security_active_shield') ?></span>
            </span>
        </div>
    </div>

    <!-- Alert Notifications -->
    <?php if (!empty($saved)): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 flex items-center justify-between gap-3 shadow-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0"></i>
                <p class="text-sm font-bold"><?= __('security_settings_saved') ?></p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:opacity-75"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    <?php endif; ?>

    <!-- Security Health Score & Metrics Hero Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Score Card -->
        <div class="lg:col-span-1 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white p-8 rounded-3xl border border-slate-800 shadow-xl relative overflow-hidden flex flex-col justify-between">
            <div class="relative z-10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-300"><?= __('security_health_score') ?></span>
                    <span class="px-2.5 py-1 rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 text-xs font-black"><?= $audit['grade'] ?> <?= __('rating') ?></span>
                </div>
                <div class="mt-6 flex items-baseline gap-3">
                    <h2 class="text-5xl font-black tracking-tight text-white"><?= $audit['score'] ?></h2>
                    <span class="text-lg font-bold text-indigo-300">/ 100</span>
                </div>
                <!-- Progress bar -->
                <div class="w-full bg-slate-800/80 rounded-full h-3 mt-4 overflow-hidden p-0.5 border border-slate-700/50">
                    <div class="bg-gradient-to-r from-emerald-400 to-indigo-400 h-full rounded-full transition-all duration-1000" style="width: <?= $audit['score'] ?>%;"></div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-800/80 relative z-10 flex items-center justify-between text-xs text-slate-300">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield" class="w-4 h-4 text-emerald-400"></i>
                    <span><?= __('anti_brute_force_on') ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4 text-indigo-400"></i>
                    <span><?= __('csrf_shield_on') ?></span>
                </div>
            </div>

            <!-- Background subtle glow -->
            <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- Key Pillars Summary (2 Cols) -->
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Blocked IPs Count -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider"><?= __('blocked_ips_count') ?></span>
                        <h3 class="text-3xl font-black text-slate-900 dark:text-white mt-1.5" id="statBlockedIps"><?= count($blockedIps) ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center">
                        <i data-lucide="ban" class="w-6 h-6"></i>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-4 flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                    <span><?= __('firewall_active') ?></span>
                </p>
            </div>

            <!-- Headers Status -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider"><?= __('security_headers_status') ?></span>
                        <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5">5 <?= __('headers_enforced') ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center">
                        <i data-lucide="file-check" class="w-6 h-6"></i>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-4 flex items-center gap-1.5">
                    <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500"></i>
                    <span>X-Frame, Nosniff, Referrer</span>
                </p>
            </div>

            <!-- Rate Limiter Status -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider"><?= __('rate_limit_protection') ?></span>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white mt-1.5"><?= $secSettings['security_rate_limit_max'] ?? '5' ?> <?= __('attempts_per_window') ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center">
                        <i data-lucide="timer" class="w-6 h-6"></i>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-4 flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span><?= __('decay_window') ?>: <?= $secSettings['security_rate_limit_decay'] ?? '300' ?>s (5m)</span>
                </p>
            </div>

            <!-- Sensitive Files Shield -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider"><?= __('sensitive_files_shield') ?></span>
                        <h3 class="text-xl font-black text-indigo-600 dark:text-indigo-400 mt-1.5">.env & Logs 100%</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center">
                        <i data-lucide="key" class="w-6 h-6"></i>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-4 flex items-center gap-1.5">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5 text-indigo-500"></i>
                    <span>Apache mod_rewrite 403</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Security Audit Checklist -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i data-lucide="clipboard-check" class="w-5 h-5 text-indigo-500"></i>
                <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= __('security_audit_checklist') ?></h3>
            </div>
            <span class="text-xs text-slate-400"><?= __('automated_health_assessment') ?></span>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php foreach ($audit['checks'] as $check): ?>
                <?php
                $isPass = $check['status'] === 'pass';
                $isWarning = $check['status'] === 'warning';
                $badgeBg = $isPass ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200' : ($isWarning ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-200');
                $icon = $isPass ? 'check-circle-2' : ($isWarning ? 'alert-triangle' : 'x-circle');
                $title = ($locale === 'en' && !empty($check['title_en'])) ? $check['title_en'] : $check['title'];
                $desc = ($locale === 'en' && !empty($check['desc_en'])) ? $check['desc_en'] : $check['desc'];
                ?>
                <div class="p-5 flex items-start justify-between gap-4 hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 <?= $badgeBg ?> border">
                            <i data-lucide="<?= $icon ?>" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($title) ?></h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed"><?= htmlspecialchars($desc) ?></p>
                        </div>
                    </div>
                    <span class="text-xs font-black px-3 py-1 rounded-xl border flex-shrink-0 uppercase <?= $badgeBg ?>">
                        <?= $isPass ? __('status_pass') : ($isWarning ? __('status_warning') : __('status_fail')) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- IP Firewall & Blacklist Management -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add IP Form -->
        <div class="lg:col-span-1 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-3 mb-5">
                <i data-lucide="shield-ban" class="w-5 h-5 text-rose-500"></i>
                <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= __('block_ip_address') ?></h3>
            </div>
            <form onsubmit="handleBlockIp(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5"><?= __('ip_address') ?></label>
                    <input type="text" id="inputBlockIp" required placeholder="e.g. 192.168.1.100" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5"><?= __('block_reason') ?></label>
                    <input type="text" id="inputBlockReason" placeholder="<?= __('suspicious_activity_brute_force') ?>" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>
                <button type="submit" id="btnBlockIp" class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/30 transition flex items-center justify-center gap-2">
                    <i data-lucide="ban" class="w-4 h-4"></i>
                    <span><?= __('confirm_block_ip') ?></span>
                </button>
            </form>
        </div>

        <!-- Blocked IPs Table -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i data-lucide="list-filter" class="w-5 h-5 text-indigo-500"></i>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= __('blocked_ips_list') ?></h3>
                    </div>
                    <span class="text-xs text-slate-400"><?= count($blockedIps) ?> <?= __('addresses_blocked') ?></span>
                </div>

                <div class="overflow-x-auto">
                    <?php if (empty($blockedIps)): ?>
                        <div class="p-12 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mx-auto mb-3">
                                <i data-lucide="shield-check" class="w-6 h-6 text-emerald-500"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300"><?= __('no_blocked_ips') ?></p>
                            <p class="text-xs text-slate-400 mt-1"><?= __('clean_firewall_status') ?></p>
                        </div>
                    <?php else: ?>
                        <table class="w-full text-right text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200/60 dark:border-slate-700">
                                <tr>
                                    <th class="p-4 font-bold text-slate-600 dark:text-slate-300"><?= __('ip_address') ?></th>
                                    <th class="p-4 font-bold text-slate-600 dark:text-slate-300"><?= __('block_reason') ?></th>
                                    <th class="p-4 font-bold text-slate-600 dark:text-slate-300"><?= __('blocked_at') ?></th>
                                    <th class="p-4 font-bold text-slate-600 dark:text-slate-300 text-left"><?= __('action') ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800" id="blockedIpsBody">
                                <?php foreach ($blockedIps as $ip => $data): ?>
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                        <td class="p-4 font-mono font-bold text-rose-600 dark:text-rose-400"><?= htmlspecialchars($ip) ?></td>
                                        <td class="p-4 text-slate-700 dark:text-slate-300"><?= htmlspecialchars($data['reason'] ?? '-') ?></td>
                                        <td class="p-4 text-slate-400 font-mono"><?= htmlspecialchars($data['blocked_at'] ?? '-') ?></td>
                                        <td class="p-4 text-left">
                                            <button type="button" onclick="handleUnblockIp('<?= htmlspecialchars($ip) ?>')" class="px-3 py-1 rounded-lg text-xs font-bold text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 transition">
                                                <?= __('unblock') ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Policy Settings Form -->
    <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div class="flex items-center gap-3 mb-6">
            <i data-lucide="sliders" class="w-5 h-5 text-indigo-500"></i>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= __('security_policy_settings') ?></h3>
                <p class="text-xs text-slate-500 mt-0.5"><?= __('security_policy_settings_desc') ?></p>
            </div>
        </div>

        <form method="POST" action="<?= url('/admin/security/settings') ?>" class="space-y-6">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Rate Limit Max Attempts -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2"><?= __('max_login_otp_attempts') ?></label>
                    <input type="number" name="security_rate_limit_max" min="3" max="20" value="<?= htmlspecialchars($secSettings['security_rate_limit_max'] ?? '5') ?>" class="w-full px-4 py-2.5 text-sm rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <span class="text-[11px] text-slate-400 mt-1 block"><?= __('max_attempts_hint') ?></span>
                </div>

                <!-- Rate Limit Decay Seconds -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2"><?= __('rate_limit_lockout_seconds') ?></label>
                    <input type="number" name="security_rate_limit_decay" min="60" max="3600" step="60" value="<?= htmlspecialchars($secSettings['security_rate_limit_decay'] ?? '300') ?>" class="w-full px-4 py-2.5 text-sm rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <span class="text-[11px] text-slate-400 mt-1 block"><?= __('lockout_seconds_hint') ?> (300s = 5 دقائق)</span>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-600/30 transition flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span><?= __('save_security_policies') ?></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    async function handleBlockIp(e) {
        e.preventDefault();
        const ipInput = document.getElementById('inputBlockIp');
        const reasonInput = document.getElementById('inputBlockReason');
        const ip = ipInput.value.trim();
        const reason = reasonInput.value.trim();

        if (!ip) return;

        try {
            const formData = new FormData();
            formData.append('ip', ip);
            formData.append('reason', reason);
            formData.append('_csrf', '<?= csrf_token() ?>');

            const res = await fetch('<?= url('/admin/security/block-ip') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.error || '<?= __('error_occurred') ?>');
            }
        } catch (err) {
            console.error(err);
            window.location.reload();
        }
    }

    async function handleUnblockIp(ip) {
        if (!confirm('<?= __('confirm_unblock_ip') ?> ' + ip + '?')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('ip', ip);
            formData.append('_csrf', '<?= csrf_token() ?>');

            const res = await fetch('<?= url('/admin/security/unblock-ip') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || '<?= __('error_occurred') ?>');
            }
        } catch (err) {
            console.error(err);
            window.location.reload();
        }
    }
</script>
