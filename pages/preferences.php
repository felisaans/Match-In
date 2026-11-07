<?php
require_once __DIR__ . '/../includes/auth-guard.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/PreferenceService.php';
require_once __DIR__ . '/../services/EnrollmentService.php';
require_once __DIR__ . '/../services/SkillService.php';

$userId = (int) $_SESSION['user_id'];
$prefService = new PreferenceService($db);
$enrollmentService = new EnrollmentService($db);
$skillService = new SkillService($db);
$message = '';
$messageType = 'success';

$classes = $enrollmentService->getUserClasses($userId);
$selectedClassId = (int) ($_GET['class'] ?? ($classes[0]['id_class'] ?? 0));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $prefService->save(
        $userId,
        (int) ($_POST['id_class'] ?? 0),
        $_POST['preferred_skill'] ?? '',
        $_POST['work_style'] ?? 'fleksibel'
    );
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
    $selectedClassId = (int) ($_POST['id_class'] ?? 0);
}

$currentPref = $selectedClassId ? $prefService->getByUserAndClass($userId, $selectedClassId) : null;
$allPrefs = $prefService->getAllByUser($userId);

$workStyleOptions = [
    'tim' => 'Tim — suka kerja berkelompok',
    'individu' => 'Individu — lebih nyaman mandiri',
    'fleksibel' => 'Fleksibel — bisa menyesuaikan',
];

// preferred_skill disimpan sebagai teks (bukan FK) supaya tetap kompatibel
// dengan MatchingService, tapi pilihannya diambil dari katalog skill_options
// yang sama dengan halaman "Tambah Skill" — value & label sengaja sama-sama
// nama skill supaya tidak ada typo/mismatch saat dicocokkan.
$skillNameGroups = [];
foreach ($skillService->getOptionsGrouped() as $category => $options) {
    foreach ($options as $skillName) {
        $skillNameGroups[$category][$skillName] = $skillName;
    }
}

$pageTitle = 'Preferensi';
include __DIR__ . '/../includes/layout/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl md:text-3xl font-extrabold bg-gradient-to-r from-pastel-deep to-pastel-purple bg-clip-text text-transparent dark:from-pastel-blue dark:to-pastel-pink">Preferensi Partner</h1>
    <p class="text-muted mt-1">Tentukan skill dan gaya kerja yang kamu cari dari partner</p>
</div>

<?php if (empty($classes)): ?>
    <?php $type = 'warning'; $message = 'Daftar ke kelas dulu ya, baru bisa atur preferensi.'; include __DIR__ . '/../includes/components/alert.php'; ?>
    <a href="<?= url('pages/classes.php') ?>" class="btn-primary inline-block">Pilih Kelas</a>
<?php else: ?>

    <?php if ($message): ?>
        <?php $type = $messageType; include __DIR__ . '/../includes/components/alert.php'; ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="pastel-card p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Atur Preferensi</h2>
            <form method="POST">
                <?php
                $classOptions = [];
                foreach ($classes as $c) {
                    $classOptions[$c['id_class']] = $c['course_name'] . ' — Kelas ' . $c['class_name'];
                }
                $label = 'Pilih Kelas';
                $name = 'id_class';
                $type = 'select';
                $options = $classOptions;
                $value = $selectedClassId;
                $required = true;
                include __DIR__ . '/../includes/components/form-field.php';

                $label = 'Skill yang Dicari';
                $name = 'preferred_skill';
                $type = 'select';
                $optgroups = $skillNameGroups;
                $value = $currentPref['preferred_skill'] ?? '';
                $required = true;
                include __DIR__ . '/../includes/components/form-field.php';
                unset($optgroups);

                $label = 'Gaya Kerja';
                $name = 'work_style';
                $type = 'select';
                $options = $workStyleOptions;
                $value = $currentPref['work_style'] ?? 'fleksibel';
                $required = true;
                include __DIR__ . '/../includes/components/form-field.php';
                ?>
                <button type="submit" class="btn-primary">Simpan Preferensi</button>
            </form>
        </div>

        <div class="pastel-card p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Preferensi Tersimpan</h2>
            <?php if (empty($allPrefs)): ?>
                <p class="text-muted text-sm">Belum ada preferensi tersimpan.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($allPrefs as $pref): ?>
                        <div class="p-4 rounded-2xl bg-pastel-bg border border-slate-100 hover:border-pastel-pink/40 transition-colors">
                            <p class="font-semibold text-slate-800"><?= htmlspecialchars($pref['course_name']) ?></p>
                            <p class="text-sm text-muted">Kelas <?= htmlspecialchars($pref['class_name']) ?></p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="badge bg-pastel-purple/40 text-slate-700 border border-pastel-purple">
                                    <?= htmlspecialchars($pref['preferred_skill']) ?>
                                </span>
                                <span class="badge bg-pastel-blue/40 text-slate-700 border border-pastel-blue">
                                    <?= PreferenceService::workStyleLabel($pref['work_style']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/layout/footer.php'; ?>
