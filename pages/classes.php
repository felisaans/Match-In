<?php
require_once __DIR__ . '/../includes/auth-guard.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/EnrollmentService.php';
require_once __DIR__ . '/../services/ProfileService.php';
$userId = (int) $_SESSION['user_id'];
$enrollmentService = new EnrollmentService($db);
$profileService = new ProfileService($db);
$user = $profileService->getById($userId);
$message = '';
$messageType = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'enroll';
    $classId = (int) ($_POST['id_class'] ?? 0);
    if ($action === 'enroll') {
        $result = $enrollmentService->enroll($userId, $classId);
    } else {
        $result = $enrollmentService->unenroll($userId, $classId);
    }
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
}
$allClasses = $enrollmentService->getAllClassesWithEnrollment($userId);
$pageTitle = 'Kelas Saya';
include __DIR__ . '/../includes/layout/header.php';
?>
<div class="mb-8">
    <h1 class="text-2xl md:text-3xl font-extrabold bg-gradient-to-r from-pastel-deep to-pastel-purple bg-clip-text text-transparent dark:from-pastel-blue dark:to-pastel-pink">Kelas Saya</h1>
    <p class="text-muted mt-1">Pilih dari 6 mata kuliah wajib semester <?= (int) $user['semester'] ?></p>
</div>
<?php if ($message): ?>
    <?php $type = $messageType; include __DIR__ . '/../includes/components/alert.php'; ?>
<?php endif; ?>
<?php if (empty($allClasses)): ?>
    <?php $type = 'warning'; $message = 'Belum ada kelas tersedia untuk semestermu. Hubungi admin ya!'; include __DIR__ . '/../includes/components/alert.php'; ?>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($allClasses as $class): ?>
            <div class="pastel-card p-5 <?= $class['is_enrolled'] ? 'ring-2 ring-pastel-mint/60' : '' ?>">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-slate-800"><?= htmlspecialchars($class['course_name']) ?></h3>
                        <p class="text-sm text-muted">Kelas <?= htmlspecialchars($class['class_name']) ?> · Semester <?= (int) $class['semester'] ?></p>
                        <p class="text-xs text-muted mt-1">Dosen: <?= htmlspecialchars($class['lecturer']) ?></p>
                    </div>
                    <?php if ($class['is_enrolled']): ?>
                        <span class="badge badge-success">Terdaftar</span>
                    <?php endif; ?>
                </div>
                <form method="POST">
                    <input type="hidden" name="id_class" value="<?= (int) $class['id_class'] ?>">
                    <?php if ($class['is_enrolled']): ?>
                        <input type="hidden" name="action" value="unenroll">
                        <button type="submit" class="btn-secondary w-full text-sm" onclick="return confirm('Keluar dari kelas ini?')">Keluar dari Kelas</button>
                    <?php else: ?>
                        <input type="hidden" name="action" value="enroll">
                        <button type="submit" class="btn-primary w-full text-sm">Daftar ke Kelas</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/layout/footer.php'; ?>
