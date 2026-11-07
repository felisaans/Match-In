<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../services/AuthService.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . url('pages/dashboard.php'));
    exit();
}

$errors = [];
$success = '';
$auth = new AuthService($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->register($_POST);
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $errors = $result['errors'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - <?= APP_NAME ?></title>
    <link href="<?= asset('css/fonts.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/output.css') ?>" rel="stylesheet">
    <script>
        // Anti-FOUC: terapkan preferensi tema sebelum render, supaya tidak ada flash light->dark.
        (function () {
            try {
                var saved = localStorage.getItem('matchin-theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var isDark = saved ? saved === 'dark' : prefersDark;
                if (isDark) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
</head>
<body class="min-h-screen flex items-center justify-center p-4 py-10 bg-gradient-to-br from-pastel-bg via-white to-pastel-mint/30">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-32 -right-32 w-72 h-72 bg-pastel-purple/40 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-32 -left-32 w-72 h-72 bg-pastel-mint/40 rounded-full blur-3xl"></div>
    </div>

    <div class="pastel-card w-full max-w-md p-8 relative z-10 bg-white/90 dark:bg-dark-surface/90">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Buat Akun Baru</h1>
            <p class="text-muted text-sm mt-2">Yuk gabung dan cari partner tugas yang cocok!</p>
        </div>

        <?php if ($success): ?>
            <?php $type = 'success'; $message = $success; include __DIR__ . '/../includes/components/alert.php'; ?>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="pastel-card px-4 py-3 mb-4 border bg-pastel-pink/30 border-pastel-pink">
                <ul class="text-sm text-rose-800 space-y-1">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?php
            $majorOptions = array_combine(AuthService::VALID_MAJORS, AuthService::VALID_MAJORS);
            $semesterOptions = array_combine(
                AuthService::VALID_SEMESTERS,
                array_map(fn($s) => "Semester $s", AuthService::VALID_SEMESTERS)
            );

            $fields = [
                ['label' => 'Nama Lengkap', 'name' => 'name', 'type' => 'text', 'placeholder' => 'Contoh: Andi Pratama'],
                ['label' => 'NPM', 'name' => 'student_id', 'type' => 'text', 'placeholder' => 'Contoh: 2511001'],
                ['label' => 'Email', 'name' => 'email', 'type' => 'email', 'placeholder' => 'nama@email.com'],
                ['label' => 'Jurusan', 'name' => 'major', 'type' => 'select', 'options' => $majorOptions],
                ['label' => 'Semester', 'name' => 'semester', 'type' => 'select', 'options' => $semesterOptions, 'value' => '2'],
                ['label' => 'Kata Sandi', 'name' => 'password', 'type' => 'password', 'placeholder' => 'Minimal 6 karakter'],
                ['label' => 'Konfirmasi Kata Sandi', 'name' => 'password_confirm', 'type' => 'password', 'placeholder' => 'Ulangi kata sandi'],
            ];
            foreach ($fields as $field):
                $label = $field['label'];
                $name = $field['name'];
                $type = $field['type'];
                $placeholder = $field['placeholder'] ?? '';
                $options = $field['options'] ?? null;
                $value = $_POST[$name] ?? ($field['value'] ?? '');
                $required = true;
                include __DIR__ . '/../includes/components/form-field.php';
                unset($options);
            endforeach;
            ?>

            <button type="submit" class="btn-primary w-full mt-2">Daftar Sekarang</button>
        </form>

        <p class="text-center text-sm text-muted mt-6">
            Sudah punya akun?
            <a href="<?= url('auth/login.php') ?>" class="text-primary hover:text-accent transition-colors font-semibold">Masuk di sini</a>
        </p>
    </div>
</body>
</html>
