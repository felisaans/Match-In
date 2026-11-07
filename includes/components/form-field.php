<?php
/**
 * @var string $label
 * @var string $name
 * @var string $type
 * @var string|null $value
 * @var bool|null $required
 * @var string|null $placeholder
 * @var array|null $options [value => label] for select
 * @var array|null $optgroups [group label => [value => label, ...]] for grouped select
 */
$type = $type ?? 'text';
$required = $required ?? false;
?>
<div class="mb-4">
    <label class="block text-sm font-semibold text-slate-600 mb-2" for="<?= htmlspecialchars($name) ?>">
        <?= htmlspecialchars($label) ?>
    </label>
    <?php if ($type === 'select' && !empty($optgroups)): ?>
        <select
            id="<?= htmlspecialchars($name) ?>"
            name="<?= htmlspecialchars($name) ?>"
            class="input-field"
            <?= $required ? 'required' : '' ?>
        >
            <?php if (!$required): ?>
                <option value="">— Pilih —</option>
            <?php endif; ?>
            <?php foreach ($optgroups as $groupLabel => $groupOptions): ?>
                <optgroup label="<?= htmlspecialchars($groupLabel) ?>">
                    <?php foreach ($groupOptions as $optValue => $optLabel): ?>
                        <option value="<?= htmlspecialchars($optValue) ?>"
                            <?= ($value ?? '') == $optValue ? 'selected' : '' ?>>
                            <?= htmlspecialchars($optLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
    <?php elseif ($type === 'select' && !empty($options)): ?>
        <select
            id="<?= htmlspecialchars($name) ?>"
            name="<?= htmlspecialchars($name) ?>"
            class="input-field"
            <?= $required ? 'required' : '' ?>
        >
            <?php foreach ($options as $optValue => $optLabel): ?>
                <option value="<?= htmlspecialchars($optValue) ?>"
                    <?= ($value ?? '') == $optValue ? 'selected' : '' ?>>
                    <?= htmlspecialchars($optLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php elseif ($type === 'textarea'): ?>
        <textarea
            id="<?= htmlspecialchars($name) ?>"
            name="<?= htmlspecialchars($name) ?>"
            class="input-field min-h-[100px]"
            placeholder="<?= htmlspecialchars($placeholder ?? '') ?>"
            <?= $required ? 'required' : '' ?>
        ><?= htmlspecialchars($value ?? '') ?></textarea>
    <?php else: ?>
        <input
            id="<?= htmlspecialchars($name) ?>"
            name="<?= htmlspecialchars($name) ?>"
            type="<?= htmlspecialchars($type) ?>"
            value="<?= htmlspecialchars($value ?? '') ?>"
            placeholder="<?= htmlspecialchars($placeholder ?? '') ?>"
            class="input-field"
            <?= $required ? 'required' : '' ?>
        >
    <?php endif; ?>
</div>
