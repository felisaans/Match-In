<?php
require_once __DIR__ . '/../includes/auth-guard.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/SkillService.php';
require_once __DIR__ . '/../services/MatchingService.php';

$userId = (int) $_SESSION['user_id'];
$skillService = new SkillService($db);
$matchingService = new MatchingService($db);
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';

    if ($action === 'add') {
        $result = $skillService->add($userId, (int) ($_POST['id_skill_option'] ?? 0), $_POST['level'] ?? 'basic');
    } elseif ($action === 'update') {
        $result = $skillService->updateLevel(
            $userId,
            (int) ($_POST['skill_id'] ?? 0),
            $_POST['level'] ?? 'basic'
        );
    } elseif ($action === 'delete') {
        $result = $skillService->delete($userId, (int) ($_POST['skill_id'] ?? 0));
    } else {
        $result = ['success' => false, 'message' => 'Aksi tidak valid.'];
    }

    if ($result['success']) {
        $matchingService->updateSawValues($userId);
        $message = $result['message'];
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}

$skills = $skillService->getByUser($userId);
$skillOptionGroups = $skillService->getOptionsGrouped();
$levelOptions = [
    'basic' => 'Dasar',
    'intermediate' => 'Menengah',
    'expert' => 'Mahir',
];

$pageTitle = 'Skill';
include __DIR__ . '/../includes/layout/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl md:text-3xl font-extrabold bg-gradient-to-r from-pastel-deep to-pastel-purple bg-clip-text text-transparent dark:from-pastel-blue dark:to-pastel-pink">Skill Saya</h1>
    <p class="text-muted mt-1">Pilih keahlian yang kamu punya dari katalog skill Teknik Informatika / Ilmu Komputer</p>
</div>

<?php if ($message): ?>
    <?php $type = $messageType; include __DIR__ . '/../includes/components/alert.php'; ?>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="pastel-card p-6 lg:col-span-1">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Tambah Skill</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <?php
            $label = 'Pilih Skill';
            $name = 'id_skill_option';
            $type = 'select';
            $optgroups = $skillOptionGroups;
            $required = true;
            include __DIR__ . '/../includes/components/form-field.php';
            unset($optgroups);

            $label = 'Level';
            $name = 'level';
            $type = 'select';
            $options = $levelOptions;
            $required = true;
            include __DIR__ . '/../includes/components/form-field.php';
            unset($options);
            ?>
            <button type="submit" class="btn-primary w-full">Tambah Skill</button>
        </form>
    </div>

    <div class="pastel-card p-6 lg:col-span-2">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Daftar Skill (<?= count($skills) ?>)</h2>
        <?php if (empty($skills)): ?>
            <p class="text-muted text-sm">Belum ada skill. Yuk tambah skill pertamamu!</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($skills as $skill): ?>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-pastel-bg border border-slate-100 hover:border-pastel-purple/40 transition-all duration-200 hover:-translate-y-0.5">
                        <div>
                            <p class="font-semibold text-slate-800"><?= htmlspecialchars($skill['skill_name']) ?></p>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <span class="<?= SkillService::levelBadgeClass($skill['level']) ?>">
                                    <?= SkillService::levelLabel($skill['level']) ?>
                                </span>
                                <?php if (!empty($skill['category'])): ?>
                                    <span class="text-xs text-muted"><?= htmlspecialchars($skill['category']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <form method="POST" class="inline-flex gap-2 items-center">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="skill_id" value="<?= (int) $skill['id_skill'] ?>">
                                <select name="level" class="input-field py-1.5 text-sm w-auto" onchange="this.form.submit()">
                                    <?php foreach ($levelOptions as $val => $lbl): ?>
                                        <option value="<?= $val ?>" <?= $skill['level'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <form method="POST" onsubmit="return confirm('Hapus skill ini?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="skill_id" value="<?= (int) $skill['id_skill'] ?>">
                                <button type="submit" class="btn-danger">Hapus</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/layout/footer.php'; ?>
