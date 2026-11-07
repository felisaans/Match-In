<?php
/**
 * @var string $label
 * @var string|int $value
 * @var string|null $color pink|purple|mint|blue
 */
$colorClasses = match ($color ?? 'blue') {
    'pink' => 'from-pastel-pink/50 to-white border-pastel-pink/40',
    'purple' => 'from-pastel-purple/50 to-white border-pastel-purple/40',
    'mint' => 'from-pastel-mint/50 to-white border-pastel-mint/40',
    default => 'from-pastel-blue/50 to-white border-pastel-blue/40',
};
?>
<div class="pastel-card p-5 bg-gradient-to-br <?= $colorClasses ?>">
    <p class="text-l mb-1 font-medium bg-gradient-to-r from-pastel-deep to-pastel-purple bg-clip-text text-transparent dark:from-pastel-blue dark:to-pastel-pink"><?= htmlspecialchars($label) ?></p>
    <p class="text-3xl font-bold bg-gradient-to-r from-pastel-deep to-pastel-purple bg-clip-text text-transparent dark:from-pastel-blue dark:to-pastel-pinks"><?= htmlspecialchars((string) $value) ?></p>
</div>

