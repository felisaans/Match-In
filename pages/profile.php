<?php
require_once __DIR__ . '/../includes/auth-guard.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/ProfileService.php';
$userId = (int) $_SESSION['user_id'];
$profileService = new ProfileService($db);
$user = $profileService->getById($userId);
$message = '';
$messageType = 'success';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_photo') {
        $result = $profileService->updatePhoto($userId, $_FILES['photo'] ?? []);
    } else {
        $result = $profileService->update($userId, $_POST);
    }

    if ($result['success']) {
        $message = $result['message'];
        $user = $profileService->getById($userId);
    } else {
        $errors = $result['errors'];
        $messageType = 'error';
    }
}
$pageTitle = 'Profil';
include __DIR__ . '/../includes/layout/header.php';
?>
<div class="mb-8">
    <h1 class="text-2xl md:text-3xl font-extrabold bg-gradient-to-r from-pastel-deep to-pastel-purple bg-clip-text text-transparent dark:from-pastel-blue dark:to-pastel-pink">Profil Saya</h1>
    <p class="text-muted mt-1">Kelola informasi pribadimu di sini</p>
</div>
<?php if ($message && empty($errors)): ?>
    <?php $type = $messageType; include __DIR__ . '/../includes/components/alert.php'; ?>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="pastel-card px-4 py-3 mb-4 alert-error">
        <ul class="text-sm space-y-1">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<div class="pastel-card p-6 max-w-xl mb-6">
    <h2 class="text-lg font-bold text-slate-800 mb-4">Foto Profil</h2>
    <div class="flex items-center gap-5">
        <?php if (!empty($user['has_photo'])): ?>
            <img src="<?= url('api/photo.php?id=' . (int) $user['id_user']) ?>&t=<?= time() ?>"
                 alt="Foto profil" class="profile-photo-preview">
        <?php else: ?>
            <div class="profile-photo-placeholder">
                <?= strtoupper(substr($user['name'] ?? 'M', 0, 1)) ?>
            </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="flex-1">
            <input type="hidden" name="action" value="update_photo">
            <label class="block text-sm font-semibold text-slate-600 mb-2" for="photo">Ganti foto (JPG/PNG/WEBP, maks 2MB)</label>
            <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" class="input-field" required>
            <button type="submit" class="btn-secondary mt-3">Unggah Foto</button>
        </form>
    </div>
</div>

<div class="pastel-card p-6 max-w-xl">
    <form method="POST">
        <?php
        $majorOptions = array_combine(ProfileService::VALID_MAJORS, ProfileService::VALID_MAJORS);
        $semesterOptions = array_combine(
            ProfileService::VALID_SEMESTERS,
            array_map(fn($s) => "Semester $s", ProfileService::VALID_SEMESTERS)
        );

        $fields = [
            ['label' => 'Nama Lengkap', 'name' => 'name', 'type' => 'text', 'value' => $user['name']],
            ['label' => 'NPM', 'name' => 'student_id_display', 'type' => 'text', 'value' => $user['student_id']],
            ['label' => 'Email', 'name' => 'email_display', 'type' => 'email', 'value' => $user['email']],
            ['label' => 'Jurusan', 'name' => 'major', 'type' => 'select', 'value' => $user['major'], 'options' => $majorOptions],
            ['label' => 'Semester', 'name' => 'semester', 'type' => 'select', 'value' => $user['semester'], 'options' => $semesterOptions],
        ];
        foreach ($fields as $field):
            $label = $field['label'];
            $name = $field['name'];
            $type = $field['type'];
            $value = $field['value'];
            $options = $field['options'] ?? null;
            $required = in_array($name, ['name', 'major', 'semester'], true);
            if (in_array($name, ['student_id_display', 'email_display'], true)):
        ?>
            <div class="mb-4 border-pastel-deep">
                <label class="block text-sm font-semibold text-slate-600 mb-2"><?= htmlspecialchars($label) ?></label>
                <input type="text" value="<?= htmlspecialchars($value) ?>" class="input-field opacity-60 cursor-not-allowed" disabled>
                <p class="text-xs text-muted mt-1">NPM dan email tidak bisa diubah</p>
            </div>
        <?php else:
            include __DIR__ . '/../includes/components/form-field.php';
        endif;
        endforeach;
        ?>
        <button type="submit" class="btn-primary mt-2">Simpan Perubahan</button>
    </form>
</div>
<?php include __DIR__ . '/../includes/layout/footer.php'; ?>
