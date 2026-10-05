<?php

namespace App\Core;

use Database\Database;
use PDO;

class CustomFields
{
    private static ?string $currentEntityType = null;
    private static ?int $currentEntityId = null;

    // Repeater traversal state stacks
    private static array $repeaterStates = [];

    /**
     * Set current entity context (e.g. when rendering a storefront page)
     */
    public static function setCurrentEntity(string $entityType, int $entityId): void
    {
        self::$currentEntityType = $entityType;
        self::$currentEntityId = $entityId;
    }

    public static function getCurrentEntity(): array
    {
        return [
            'type' => self::$currentEntityType,
            'id' => self::$currentEntityId
        ];
    }

    /**
     * Get field groups matching an entity type, always including groups scoped to
     * 'all' plus any groups scoped to one of the given specific target filters
     * (e.g. 'id:5' for a single page/post/product, 'type:faq' for a post sub-type).
     */
    public static function getGroupsFor(string $targetType, array $targetFilters = []): array
    {
        $filters = array_values(array_unique(array_filter(
            array_merge(['all'], $targetFilters),
            fn($f) => $f !== null && $f !== ''
        )));

        $placeholders = implode(',', array_fill(0, count($filters), '?'));
        $sql = "SELECT * FROM custom_field_groups WHERE status = 'active' AND target_type = ? AND target_filter IN ($placeholders)";
        $params = array_merge([$targetType], $filters);
        $sql .= " ORDER BY order_index ASC, id ASC";

        return Database::fetchAll($sql, $params);
    }

    /**
     * Build the list of target_filter values that apply to a given entity: its own
     * id ('id:5') and, for sub-typed entities like posts, its sub-type ('type:faq').
     * Groups scoped to 'all' always match regardless of what this returns.
     */
    public static function buildTargetFilters(?int $entityId, ?string $subType = null): array
    {
        $filters = [];
        if (!empty($entityId)) {
            $filters[] = 'id:' . $entityId;
        }
        if (!empty($subType)) {
            $filters[] = 'type:' . $subType;
        }
        return $filters;
    }

    /**
     * Get all fields for a group
     */
    public static function getFieldsForGroup(int $groupId): array
    {
        return Database::fetchAll("
            SELECT * FROM custom_fields
            WHERE group_id = ?
            ORDER BY order_index ASC, id ASC
        ", [$groupId]);
    }

    /**
     * Get all fields matching entity type with their group info
     */
    public static function getFieldsForEntity(string $targetType, array $targetFilters = []): array
    {
        $groups = self::getGroupsFor($targetType, $targetFilters);
        if (empty($groups)) {
            return [];
        }

        $result = [];
        foreach ($groups as $grp) {
            $fields = self::getFieldsForGroup((int)$grp['id']);
            $grp['fields'] = $fields;
            $result[] = $grp;
        }

        return $result;
    }

    /**
     * Fetch a custom field value (like ACF get_field)
     */
    public static function getField(string $fieldName, ?int $entityId = null, ?string $entityType = null, mixed $default = null): mixed
    {
        $entityType = $entityType ?: (self::$currentEntityType ?: 'page');
        $entityId = $entityId ?: self::$currentEntityId;

        if (!$entityId) {
            return $default;
        }

        $row = Database::fetchOne("
            SELECT value FROM custom_field_values 
            WHERE entity_type = ? AND entity_id = ? AND field_name = ?
            LIMIT 1
        ", [$entityType, $entityId, $fieldName]);

        if (!$row || $row['value'] === null || $row['value'] === '') {
            return $default;
        }

        $val = $row['value'];

        // If JSON formatted (arrays, repeaters, selects)
        if (is_string($val) && (str_starts_with($val, '[') || str_starts_with($val, '{'))) {
            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $val;
    }

    /**
     * Echo a field value safely
     */
    public static function theField(string $fieldName, ?int $entityId = null, ?string $entityType = null, mixed $default = null): void
    {
        $val = self::getField($fieldName, $entityId, $entityType, $default);
        if (is_array($val)) {
            echo htmlspecialchars(json_encode($val, JSON_UNESCAPED_UNICODE));
        } else {
            echo htmlspecialchars((string)$val);
        }
    }

    /**
     * Get all custom field values for an entity as key => value map
     */
    public static function getAllFields(?int $entityId = null, ?string $entityType = null): array
    {
        $entityType = $entityType ?: (self::$currentEntityType ?: 'page');
        $entityId = $entityId ?: self::$currentEntityId;

        if (!$entityId) {
            return [];
        }

        $rows = Database::fetchAll("
            SELECT field_name, value FROM custom_field_values 
            WHERE entity_type = ? AND entity_id = ?
        ", [$entityType, $entityId]);

        $result = [];
        foreach ($rows as $r) {
            $val = $r['value'];
            if (is_string($val) && (str_starts_with($val, '[') || str_starts_with($val, '{'))) {
                $decoded = json_decode($val, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $val = $decoded;
                }
            }
            $result[$r['field_name']] = $val;
        }

        return $result;
    }

    // ==========================================
    // Repeater Traversal Methods (ACF style)
    // ==========================================

    public static function haveRows(string $repeaterName, ?int $entityId = null, ?string $entityType = null): bool
    {
        if (!isset(self::$repeaterStates[$repeaterName])) {
            $rows = self::getField($repeaterName, $entityId, $entityType, []);
            if (!is_array($rows) || empty($rows)) {
                return false;
            }
            self::$repeaterStates[$repeaterName] = [
                'rows' => array_values($rows),
                'index' => -1,
                'total' => count($rows)
            ];
        }

        $state = &self::$repeaterStates[$repeaterName];
        if ($state['index'] + 1 < $state['total']) {
            return true;
        }

        // Reached end, reset
        unset(self::$repeaterStates[$repeaterName]);
        return false;
    }

    public static function theRow(string $repeaterName): ?array
    {
        if (!isset(self::$repeaterStates[$repeaterName])) {
            return null;
        }

        $state = &self::$repeaterStates[$repeaterName];
        $state['index']++;
        return $state['rows'][$state['index']] ?? null;
    }

    public static function getSubField(string $subFieldName, ?string $repeaterName = null): mixed
    {
        if ($repeaterName !== null) {
            $state = self::$repeaterStates[$repeaterName] ?? null;
        } else {
            // Pick most recent active repeater
            $state = !empty(self::$repeaterStates) ? end(self::$repeaterStates) : null;
        }

        if (!$state || $state['index'] < 0 || !isset($state['rows'][$state['index']])) {
            return null;
        }

        $currentRow = $state['rows'][$state['index']];
        return $currentRow[$subFieldName] ?? null;
    }

    // ==========================================
    // Save Entity Custom Fields (Admin Form Handler)
    // ==========================================

    public static function saveEntityFields(string $entityType, int $entityId, array $requestData, array $files = [], ?string $subType = null): void
    {
        $acfInputs = $requestData['acf'] ?? [];
        if (!is_array($acfInputs) && empty($files['acf'])) {
            return;
        }

        $pdo = Database::getConnection();

        // 1. Fetch all expected fields for this entity (groups scoped to 'all', this entity's id, or its sub-type)
        $groups = self::getFieldsForEntity($entityType, self::buildTargetFilters($entityId, $subType));
        $fieldsDefinitions = [];
        foreach ($groups as $grp) {
            foreach ($grp['fields'] as $fld) {
                $fieldsDefinitions[$fld['name']] = $fld;
            }
        }

        // 2. Handle File Uploads in ACF fields
        if (isset($_FILES['acf']['name']) && is_array($_FILES['acf']['name'])) {
            foreach ($_FILES['acf']['name'] as $fldName => $filename) {
                if (!empty($filename)) {
                    // Create mock single file structure for Uploader
                    $fileKey = "acf_upload_{$fldName}";
                    $_FILES[$fileKey] = [
                        'name' => $_FILES['acf']['name'][$fldName],
                        'type' => $_FILES['acf']['type'][$fldName],
                        'tmp_name' => $_FILES['acf']['tmp_name'][$fldName],
                        'error' => $_FILES['acf']['error'][$fldName],
                        'size' => $_FILES['acf']['size'][$fldName],
                    ];
                    $uploadedPath = Uploader::upload($fileKey, 'custom_fields');
                    if ($uploadedPath) {
                        $acfInputs[$fldName] = $uploadedPath;
                    }
                }
            }
        }

        // 3. Process and persist each field
        $stmtDelete = $pdo->prepare("DELETE FROM custom_field_values WHERE entity_type = ? AND entity_id = ? AND field_name = ?");
        $stmtInsert = $pdo->prepare("INSERT INTO custom_field_values (entity_type, entity_id, field_name, value) VALUES (?, ?, ?, ?)");

        foreach ($fieldsDefinitions as $fieldName => $fld) {
            $type = $fld['type'];
            $val = $acfInputs[$fieldName] ?? null;

            // Handle Repeater
            if ($type === 'repeater') {
                if (is_array($val)) {
                    // Clean empty rows
                    $cleanedRows = [];
                    foreach ($val as $row) {
                        if (is_array($row) && !empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                            $cleanedRows[] = $row;
                        }
                    }
                    $storedVal = !empty($cleanedRows) ? json_encode(array_values($cleanedRows), JSON_UNESCAPED_UNICODE) : null;
                } else {
                    $storedVal = null;
                }
            } elseif ($type === 'boolean') {
                $storedVal = (!empty($val) && $val !== '0') ? '1' : '0';
            } elseif (is_array($val)) {
                $storedVal = json_encode($val, JSON_UNESCAPED_UNICODE);
            } else {
                $storedVal = $val !== null ? trim((string)$val) : null;
            }

            // Always update value
            $stmtDelete->execute([$entityType, $entityId, $fieldName]);
            if ($storedVal !== null && $storedVal !== '') {
                $stmtInsert->execute([$entityType, $entityId, $fieldName, $storedVal]);
            }
        }
    }

    // ==========================================
    // Render Meta Box HTML for Admin Forms
    // ==========================================

    public static function renderMetaBox(string $entityType, ?int $entityId = null, ?string $subType = null): string
    {
        $groups = self::getFieldsForEntity($entityType, self::buildTargetFilters($entityId, $subType));
        if (empty($groups)) {
            return '';
        }

        $savedValues = $entityId ? self::getAllFields($entityId, $entityType) : [];
        $locale = I18n::getLocale();

        ob_start();
        ?>
        <div class="mt-8 space-y-6" id="acf-metabox-wrapper">
            <?php foreach ($groups as $grp): ?>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-slate-900 to-indigo-950 flex items-center justify-between text-white">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-indigo-500/30 border border-indigo-400/30 flex items-center justify-center text-indigo-300 font-bold text-xs">
                                <i data-lucide="sliders" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h3 class="font-extrabold text-base text-white">
                                    <?= htmlspecialchars($locale === 'en' ? $grp['title_en'] : $grp['title_ar']) ?>
                                </h3>
                                <span class="text-[11px] font-mono text-indigo-300">
                                    key: <?= htmlspecialchars($grp['key_name']) ?>
                                </span>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white/10 text-slate-200 border border-white/10">
                            <?= count($grp['fields']) ?> <?= __('fields_count') ?>
                        </span>
                    </div>

                    <div class="p-6 space-y-6">
                        <?php foreach ($grp['fields'] as $f): 
                            $fName = $f['name'];
                            $fLabel = $locale === 'en' ? $f['label_en'] : $f['label_ar'];
                            $fInst = $locale === 'en' ? $f['instructions_en'] : $f['instructions_ar'];
                            $fVal = $savedValues[$fName] ?? $f['default_value'];
                        ?>
                            <div class="space-y-1.5 pb-5 border-b border-slate-100 last:border-0 last:pb-0" data-field-name="<?= htmlspecialchars($fName) ?>" data-field-type="<?= htmlspecialchars($f['type']) ?>">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-bold text-slate-900">
                                        <?= htmlspecialchars($fLabel) ?>
                                        <?php if (!empty($f['required'])): ?>
                                            <span class="text-red-500">*</span>
                                        <?php endif; ?>
                                        <code class="ms-2 text-[11px] font-mono font-normal text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">
                                            get_field('<?= htmlspecialchars($fName) ?>')
                                        </code>
                                    </label>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">
                                        <?= htmlspecialchars($f['type']) ?>
                                    </span>
                                </div>

                                <?php if (!empty($fInst)): ?>
                                    <p class="text-xs text-slate-500"><?= htmlspecialchars($fInst) ?></p>
                                <?php endif; ?>

                                <?php if ($f['type'] === 'text'): ?>
                                    <input type="text" name="acf[<?= htmlspecialchars($fName) ?>]" value="<?= htmlspecialchars((string)($fVal ?? '')) ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-semibold transition" <?= !empty($f['required']) ? 'required' : '' ?>>

                                <?php elseif ($f['type'] === 'textarea'): ?>
                                    <textarea name="acf[<?= htmlspecialchars($fName) ?>]" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-semibold transition"><?= htmlspecialchars((string)($fVal ?? '')) ?></textarea>

                                <?php elseif ($f['type'] === 'wysiwyg'): ?>
                                    <textarea name="acf[<?= htmlspecialchars($fName) ?>]" rows="5" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono transition"><?= htmlspecialchars((string)($fVal ?? '')) ?></textarea>

                                <?php elseif ($f['type'] === 'number'): ?>
                                    <input type="number" step="any" name="acf[<?= htmlspecialchars($fName) ?>]" value="<?= htmlspecialchars((string)($fVal ?? '')) ?>" class="w-full sm:w-64 px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-semibold transition">

                                <?php elseif ($f['type'] === 'color'): ?>
                                    <div class="flex items-center gap-3">
                                        <input type="color" name="acf[<?= htmlspecialchars($fName) ?>]" value="<?= htmlspecialchars((string)($fVal ?: '#4f46e5')) ?>" class="w-12 h-11 p-1 rounded-xl border border-slate-200 cursor-pointer">
                                        <input type="text" value="<?= htmlspecialchars((string)($fVal ?: '#4f46e5')) ?>" class="w-32 px-3 py-2 rounded-xl border border-slate-200 font-mono text-xs text-slate-700 bg-slate-50" readonly>
                                    </div>

                                <?php elseif ($f['type'] === 'boolean'): ?>
                                    <label class="relative inline-flex items-center cursor-pointer mt-1">
                                        <input type="hidden" name="acf[<?= htmlspecialchars($fName) ?>]" value="0">
                                        <input type="checkbox" name="acf[<?= htmlspecialchars($fName) ?>]" value="1" <?= (!empty($fVal) && $fVal !== '0') ? 'checked' : '' ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                        <span class="ms-3 text-xs font-semibold text-slate-600"><?= __('enabled_active') ?></span>
                                    </label>

                                <?php elseif ($f['type'] === 'image'): ?>
                                    <div class="space-y-3">
                                        <?php if (!empty($fVal)): ?>
                                            <div class="flex items-center gap-4 p-3 bg-slate-50 border border-slate-200 rounded-xl max-w-md">
                                                <img src="<?= asset((string)$fVal) ?>" alt="Preview" class="w-16 h-16 object-cover rounded-lg border border-slate-200 shadow-sm bg-white">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-bold text-slate-800 truncate"><?= htmlspecialchars((string)$fVal) ?></p>
                                                    <span class="text-[11px] text-emerald-600 font-bold"><?= __('current_image') ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <input type="hidden" name="acf[<?= htmlspecialchars($fName) ?>]" value="<?= htmlspecialchars((string)($fVal ?? '')) ?>">
                                        <input type="file" name="acf[<?= htmlspecialchars($fName) ?>]" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                    </div>

                                <?php elseif ($f['type'] === 'icon'):
                                    $iconVal = trim((string)($fVal ?? ''));
                                ?>
                                    <div class="icon-picker-field relative" data-field-name="<?= htmlspecialchars($fName) ?>">
                                        <input type="hidden" name="acf[<?= htmlspecialchars($fName) ?>]" value="<?= htmlspecialchars($iconVal) ?>" class="icon-picker-value">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center text-indigo-600 text-xl icon-picker-preview">
                                                <i class="<?= htmlspecialchars($iconVal ?: 'fa-solid fa-star') ?>"></i>
                                            </div>
                                            <code class="flex-1 px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-mono text-slate-600 icon-picker-label truncate" dir="ltr"><?= htmlspecialchars($iconVal ?: __('no_icon_selected')) ?></code>
                                            <button type="button" onclick="toggleIconPicker(this)" class="px-3.5 py-2.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-xs border border-indigo-200 transition whitespace-nowrap flex items-center gap-1.5">
                                                <i data-lucide="grid-3x3" class="w-3.5 h-3.5"></i>
                                                <span><?= __('choose_icon') ?></span>
                                            </button>
                                            <button type="button" onclick="clearIconPicker(this)" class="px-3 py-2.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold text-xs border border-rose-200 transition">
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>

                                        <div class="icon-picker-panel hidden absolute z-20 mt-2 w-full sm:w-[420px] p-4 bg-white border border-slate-200 rounded-2xl shadow-2xl space-y-3">
                                            <input type="text" placeholder="<?= __('search_icons_placeholder') ?>" oninput="filterIconPicker(this)" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500">
                                            <div class="icon-picker-grid max-h-64 overflow-y-auto space-y-4 pe-1"></div>
                                        </div>
                                    </div>

                                <?php elseif ($f['type'] === 'select'):
                                    $optionsArr = !empty($f['options']) ? json_decode($f['options'], true) : [];
                                    if (!is_array($optionsArr)) $optionsArr = [];
                                ?>
                                    <select name="acf[<?= htmlspecialchars($fName) ?>]" class="w-full sm:w-80 px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-semibold transition">
                                        <option value=""><?= __('select_option') ?></option>
                                        <?php foreach ($optionsArr as $optKey => $optVal): ?>
                                            <option value="<?= htmlspecialchars((string)$optKey) ?>" <?= ((string)$fVal === (string)$optKey) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars((string)$optVal) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                <?php elseif ($f['type'] === 'repeater'): 
                                    $subFields = !empty($f['options']) ? json_decode($f['options'], true) : [];
                                    if (!is_array($subFields)) $subFields = [];
                                    $repeaterRows = is_array($fVal) ? $fVal : [];
                                ?>
                                    <div class="repeater-container border border-slate-200 rounded-2xl p-4 bg-slate-50/70 space-y-3" data-repeater="<?= htmlspecialchars($fName) ?>">
                                        <div class="repeater-rows-wrapper space-y-3">
                                            <?php if (empty($repeaterRows)): ?>
                                                <!-- Row template placeholder if empty -->
                                                <div class="repeater-row bg-white border border-slate-200 rounded-xl p-4 shadow-sm relative group" data-row-index="0">
                                                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100">
                                                        <span class="text-xs font-bold text-indigo-600 row-number">#1</span>
                                                        <button type="button" onclick="removeRepeaterRow(this)" class="text-xs text-red-500 hover:text-red-700 font-bold flex items-center gap-1">
                                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> <?= __('delete_row') ?>
                                                        </button>
                                                    </div>
                                                    <div class="grid grid-cols-1 sm:grid-cols-<?= min(count($subFields), 3) ?: 1 ?> gap-3">
                                                        <?php foreach ($subFields as $sf): 
                                                            $sfName = $sf['name'];
                                                            $sfLabel = ($locale === 'en' && !empty($sf['label_en'])) ? $sf['label_en'] : ($sf['label_ar'] ?? ($sf['label'] ?? $sfName));
                                                            $sfType = $sf['type'] ?? 'text';
                                                        ?>
                                                            <div>
                                                                <label class="block text-xs font-bold text-slate-700 mb-1"><?= htmlspecialchars($sfLabel) ?></label>
                                                                <?php if ($sfType === 'textarea'): ?>
                                                                    <textarea name="acf[<?= htmlspecialchars($fName) ?>][0][<?= htmlspecialchars($sfName) ?>]" rows="2" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500"></textarea>
                                                                <?php elseif ($sfType === 'image'): ?>
                                                                    <input type="text" name="acf[<?= htmlspecialchars($fName) ?>][0][<?= htmlspecialchars($sfName) ?>]" placeholder="/uploads/... أو رابط الصورة" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                                                                <?php elseif ($sfType === 'number'): ?>
                                                                    <input type="number" step="any" name="acf[<?= htmlspecialchars($fName) ?>][0][<?= htmlspecialchars($sfName) ?>]" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                                                                <?php elseif ($sfType === 'color'): ?>
                                                                    <input type="color" name="acf[<?= htmlspecialchars($fName) ?>][0][<?= htmlspecialchars($sfName) ?>]" value="#4f46e5" class="w-10 h-8 p-0.5 rounded-lg border border-slate-200 cursor-pointer">
                                                                <?php elseif ($sfType === 'boolean'): ?>
                                                                    <select name="acf[<?= htmlspecialchars($fName) ?>][0][<?= htmlspecialchars($sfName) ?>]" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700">
                                                                        <option value="1">نعم (Yes)</option>
                                                                        <option value="0">لا (No)</option>
                                                                    </select>
                                                                <?php else: ?>
                                                                    <input type="text" name="acf[<?= htmlspecialchars($fName) ?>][0][<?= htmlspecialchars($sfName) ?>]" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($repeaterRows as $rIdx => $rData): ?>
                                                    <div class="repeater-row bg-white border border-slate-200 rounded-xl p-4 shadow-sm relative group" data-row-index="<?= $rIdx ?>">
                                                        <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100">
                                                            <span class="text-xs font-bold text-indigo-600 row-number">#<?= $rIdx + 1 ?></span>
                                                            <button type="button" onclick="removeRepeaterRow(this)" class="text-xs text-red-500 hover:text-red-700 font-bold flex items-center gap-1">
                                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> <?= __('delete_row') ?>
                                                            </button>
                                                        </div>
                                                        <div class="grid grid-cols-1 sm:grid-cols-<?= min(count($subFields), 3) ?: 1 ?> gap-3">
                                                            <?php foreach ($subFields as $sf): 
                                                                $sfName = $sf['name'];
                                                                $sfLabel = ($locale === 'en' && !empty($sf['label_en'])) ? $sf['label_en'] : ($sf['label_ar'] ?? ($sf['label'] ?? $sfName));
                                                                $sfType = $sf['type'] ?? 'text';
                                                                $sfVal = $rData[$sfName] ?? '';
                                                            ?>
                                                                <div>
                                                                    <label class="block text-xs font-bold text-slate-700 mb-1"><?= htmlspecialchars($sfLabel) ?></label>
                                                                    <?php if ($sfType === 'textarea'): ?>
                                                                        <textarea name="acf[<?= htmlspecialchars($fName) ?>][<?= $rIdx ?>][<?= htmlspecialchars($sfName) ?>]" rows="2" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars((string)$sfVal) ?></textarea>
                                                                    <?php elseif ($sfType === 'image'): ?>
                                                                        <input type="text" name="acf[<?= htmlspecialchars($fName) ?>][<?= $rIdx ?>][<?= htmlspecialchars($sfName) ?>]" value="<?= htmlspecialchars((string)$sfVal) ?>" placeholder="/uploads/... أو رابط الصورة" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                                                                    <?php elseif ($sfType === 'number'): ?>
                                                                        <input type="number" step="any" name="acf[<?= htmlspecialchars($fName) ?>][<?= $rIdx ?>][<?= htmlspecialchars($sfName) ?>]" value="<?= htmlspecialchars((string)$sfVal) ?>" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                                                                    <?php elseif ($sfType === 'color'): ?>
                                                                        <input type="color" name="acf[<?= htmlspecialchars($fName) ?>][<?= $rIdx ?>][<?= htmlspecialchars($sfName) ?>]" value="<?= htmlspecialchars((string)($sfVal ?: '#4f46e5')) ?>" class="w-10 h-8 p-0.5 rounded-lg border border-slate-200 cursor-pointer">
                                                                    <?php elseif ($sfType === 'boolean'): ?>
                                                                        <select name="acf[<?= htmlspecialchars($fName) ?>][<?= $rIdx ?>][<?= htmlspecialchars($sfName) ?>]" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700">
                                                                            <option value="1" <?= ((string)$sfVal === '1') ? 'selected' : '' ?>>نعم (Yes)</option>
                                                                            <option value="0" <?= ((string)$sfVal === '0') ? 'selected' : '' ?>>لا (No)</option>
                                                                        </select>
                                                                    <?php else: ?>
                                                                        <input type="text" name="acf[<?= htmlspecialchars($fName) ?>][<?= $rIdx ?>][<?= htmlspecialchars($sfName) ?>]" value="<?= htmlspecialchars((string)$sfVal) ?>" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>

                                        <button type="button" onclick="addRepeaterRow(this, '<?= htmlspecialchars($fName) ?>', <?= htmlspecialchars(json_encode($subFields, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) ?>)" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-xs border border-indigo-200 transition">
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> <?= __('add_repeater_row') ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <script>
        function removeRepeaterRow(btn) {
            const row = btn.closest('.repeater-row');
            const wrapper = row.parentElement;
            if (wrapper.querySelectorAll('.repeater-row').length > 1) {
                row.remove();
                wrapper.querySelectorAll('.repeater-row').forEach((r, idx) => {
                    const num = r.querySelector('.row-number');
                    if (num) num.textContent = '#' + (idx + 1);
                });
            } else {
                row.querySelectorAll('input, textarea').forEach(i => i.value = '');
            }
        }

        function addRepeaterRow(btn, fieldName, subFields) {
            const container = btn.closest('.repeater-container');
            const wrapper = container.querySelector('.repeater-rows-wrapper');
            const rows = wrapper.querySelectorAll('.repeater-row');
            const nextIdx = rows.length;

            let fieldsHtml = '';
            subFields.forEach(sf => {
                const sfName = sf.name;
                const sfLabel = sf.label_ar || sf.label || sfName;
                const sfType = sf.type || 'text';

                let inputHtml = '';
                if (sfType === 'textarea') {
                    inputHtml = `<textarea name="acf[${fieldName}][${nextIdx}][${sfName}]" rows="2" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500"></textarea>`;
                } else if (sfType === 'image') {
                    inputHtml = `<input type="text" name="acf[${fieldName}][${nextIdx}][${sfName}]" placeholder="/uploads/... أو رابط الصورة" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">`;
                } else if (sfType === 'number') {
                    inputHtml = `<input type="number" step="any" name="acf[${fieldName}][${nextIdx}][${sfName}]" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">`;
                } else if (sfType === 'color') {
                    inputHtml = `<input type="color" name="acf[${fieldName}][${nextIdx}][${sfName}]" value="#4f46e5" class="w-10 h-8 p-0.5 rounded-lg border border-slate-200 cursor-pointer">`;
                } else if (sfType === 'boolean') {
                    inputHtml = `<select name="acf[${fieldName}][${nextIdx}][${sfName}]" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700"><option value="1">نعم</option><option value="0">لا</option></select>`;
                } else {
                    inputHtml = `<input type="text" name="acf[${fieldName}][${nextIdx}][${sfName}]" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">`;
                }

                fieldsHtml += `
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">${sfLabel}</label>
                        ${inputHtml}
                    </div>
                `;
            });

            const newRow = document.createElement('div');
            newRow.className = 'repeater-row bg-white border border-slate-200 rounded-xl p-4 shadow-sm relative group';
            newRow.dataset.rowIndex = nextIdx;
            newRow.innerHTML = `
                <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-indigo-600 row-number">#${nextIdx + 1}</span>
                    <button type="button" onclick="removeRepeaterRow(this)" class="text-xs text-red-500 hover:text-red-700 font-bold flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> حذف الصف
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-${Math.min(subFields.length, 3) || 1} gap-3">
                    ${fieldsHtml}
                </div>
            `;

            wrapper.appendChild(newRow);
            if (window.lucide && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        }

        // ==========================================
        // Icon Picker (Font Awesome) - ACF "icon" field type
        // ==========================================
        window.__ACF_FA_ICONS__ = <?= json_encode(\App\Core\FontAwesomeIcons::catalog(), JSON_UNESCAPED_UNICODE) ?>;

        function toggleIconPicker(btn) {
            const wrapper = btn.closest('.icon-picker-field');
            const panel = wrapper.querySelector('.icon-picker-panel');
            const isHidden = panel.classList.contains('hidden');
            document.querySelectorAll('.icon-picker-panel').forEach(p => p.classList.add('hidden'));
            if (isHidden) {
                panel.classList.remove('hidden');
                renderIconGrid(wrapper);
                const searchInput = panel.querySelector('input[type="text"]');
                if (searchInput) { searchInput.value = ''; searchInput.focus(); }
            }
        }

        function renderIconGrid(wrapper, query = '') {
            const grid = wrapper.querySelector('.icon-picker-grid');
            const q = query.trim().toLowerCase();
            let html = '';
            Object.entries(window.__ACF_FA_ICONS__).forEach(([category, icons]) => {
                const matched = icons.filter(slug => !q || slug.includes(q) || category.toLowerCase().includes(q));
                if (matched.length === 0) return;
                html += `<div><p class="text-[10px] font-black text-slate-400 uppercase tracking-wide mb-1.5">${category}</p><div class="grid grid-cols-6 sm:grid-cols-8 gap-1.5">`;
                matched.forEach(slug => {
                    html += `<button type="button" title="${slug}" onclick="selectIcon(this, '${slug}')" class="icon-swatch w-9 h-9 rounded-lg border border-slate-200 bg-white hover:bg-indigo-50 hover:border-indigo-300 flex items-center justify-center text-slate-700 hover:text-indigo-600 transition"><i class="fa-solid fa-${slug}"></i></button>`;
                });
                html += `</div></div>`;
            });
            grid.innerHTML = html || `<p class="text-xs text-slate-400 text-center py-6"><?= __('no_icons_found') ?></p>`;
        }

        function filterIconPicker(input) {
            const wrapper = input.closest('.icon-picker-field');
            renderIconGrid(wrapper, input.value);
        }

        function selectIcon(btn, slug) {
            const wrapper = btn.closest('.icon-picker-field');
            const fullClass = 'fa-solid fa-' + slug;
            wrapper.querySelector('.icon-picker-value').value = fullClass;
            wrapper.querySelector('.icon-picker-preview').innerHTML = `<i class="${fullClass}"></i>`;
            wrapper.querySelector('.icon-picker-label').textContent = fullClass;
            wrapper.querySelector('.icon-picker-panel').classList.add('hidden');
        }

        function clearIconPicker(btn) {
            const wrapper = btn.closest('.icon-picker-field');
            wrapper.querySelector('.icon-picker-value').value = '';
            wrapper.querySelector('.icon-picker-preview').innerHTML = '<i class="fa-solid fa-star"></i>';
            wrapper.querySelector('.icon-picker-label').textContent = <?= json_encode(__('no_icon_selected')) ?>;
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.icon-picker-field')) {
                document.querySelectorAll('.icon-picker-panel').forEach(p => p.classList.add('hidden'));
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
