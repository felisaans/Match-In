<?php
/**
 * @var string $type success|error|warning|info
 * @var string $message
 */
$alertClasses = match ($type ?? 'info') {
    'success' => 'alert-success',
    'error' => 'alert-error',
    'warning' => 'alert-warning',
    default => 'alert-info',
};
?>
<div class="pastel-card px-4 py-3 mb-4 <?= $alertClasses ?> flex items-start gap-3 animate-fade-in">
    <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <?php if (($type ?? '') === 'success'): ?>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        <?php elseif (($type ?? '') === 'error'): ?>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        <?php else: ?>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        <?php endif; ?>
    </svg>
    <p class="text-sm font-medium"><?= htmlspecialchars($message) ?></p>
</div>
