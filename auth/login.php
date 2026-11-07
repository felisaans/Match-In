<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../services/AuthService.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . url('pages/dashboard.php'));
    exit();
}

$error = '';
$auth = new AuthService($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->login($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($result['success']) {
        header('Location: ' . url('pages/dashboard.php'));
        exit();
    }
    $error = $result['message'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - <?= APP_NAME ?></title>
    <link href="<?= asset('css/fonts.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/output.css') ?>?v=<?= filemtime(__DIR__ . '/../assets/css/output.css') ?>" rel="stylesheet">
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
<body class="min-h-screen bg-gradient-to-br from-pastel-bg via-white to-pastel-pink/30 dark:from-dark-bg dark:via-dark-surface dark:to-pastel-pink/10 transition-colors duration-300 flex items-stretch overflow-x-hidden">
    <!-- background decorative blur -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute -top-32 -right-32 w-72 h-72 bg-pastel-blue/40 rounded-full blur-3xl animate-float-slow"></div>
                <div class="absolute -top-32 -left-32 w-72 h-72 bg-pastel-blue/40 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute -bottom-32 -left-32 w-72 h-72 bg-pastel-pink/40 rounded-full blur-3xl animate-float-slower"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-pastel-purple/20 rounded-full blur-3xl animate-float-slow" style="animation-delay: -2s;"></div>
    </div>

    <div class="w-full min-h-screen flex flex-col md:grid md:grid-cols-2 relative z-10">
        <!-- LEFT COLUMN: Branding & Explanation -->
        <div class="flex flex-col justify-between p-8 md:p-12 lg:p-16 xl:p-20 bg-white/40 dark:bg-dark-surface/40 backdrop-blur-md border-b md:border-b-0 md:border-r border-[#80ECF2]/15 dark:border-dark-border/40">
            <!-- Top brand header -->
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-3xl bg-gradient-to-br from-pastel-pink via-pastel-purple to-pastel-blue flex items-center justify-center shadow-soft">
                    <svg class="w-9 h-9 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                </div>
                <span class="text-3xl font-extrabold bg-gradient-to-r from-pastel-deep to-pastel-purple bg-clip-text text-transparent dark:from-pastel-blue dark:to-pastel-pink">Match-In</span>
            </div>

            <!-- Main branding & introduction content -->
            <div class="my-auto py-2">
                <h2 class="text-2xl lg:text-2xl font-bold bg-gradient-to-r from-pastel-deep to-pastel-purple bg-clip-text text-transparent dark:from-pastel-blue dark:to-pastel-pink mb-2">
                    Temukan partner tugas terbaikmu
                </h2>
                <p class="text-slate-600 dark:text-dark-muted mb-4 leading-relaxed">
                    Kombinasi kecerdasan sistem pendukung keputusan dengan kenyamanan kolaborasi. Membantu kamu rekan satu tim dengan keahlian yang saling melengkapi dan gaya kerja yang serasi untuk hasil belajar yang maksimal.
                </p>

                <!-- Key features bullet points using matching pastel styles -->
                <div class="space-y-4">
                    <!-- Feature 1: Criteria-based partner matching -->
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-pastel-blue/15 dark:bg-pastel-blue/10 border border-pastel-blue/30 dark:border-pastel-blue/20 hover:bg-pastel-blue/25 dark:hover:bg-pastel-blue/15 transition-all duration-300">
                        <div class="w-8 h-8 rounded-xl bg-pastel-blue flex items-center justify-center text-slate-800 shadow-sm shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 dark:text-dark-text">Pencarian Berbasis Kriteria</h4>
                            <p class="text-sm text-slate-600 dark:text-dark-muted mt-0.5">Mencocokkan partner berdasarkan keahlian teknis (skill) dan kriteria perkuliahan secara komprehensif.</p>
                        </div>
                    </div>

                    <!-- Feature 2: Objective calculations using SAW -->
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-pastel-purple/15 dark:bg-pastel-purple/10 border border-pastel-purple/30 dark:border-pastel-purple/20 hover:bg-pastel-purple/25 dark:hover:bg-pastel-purple/15 transition-all duration-300">
                        <div class="w-8 h-8 rounded-xl bg-pastel-purple flex items-center justify-center text-slate-800 shadow-sm shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 dark:text-dark-text">Perhitungan Objektif SAW</h4>
                            <p class="text-sm text-slate-600 dark:text-dark-muted mt-0.5">Rekomendasi dihitung secara objektif menggunakan metode <i>Simple Additive Weighting</i> (SAW) untuk kecocokan maksimal.</p>
                        </div>
                    </div>

                    <!-- Feature 3: Work Style & Preference Compatibility -->
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-pastel-pink/15 dark:bg-pastel-pink/10 border border-pastel-pink/30 dark:border-pastel-pink/20 hover:bg-pastel-pink/25 dark:hover:bg-pastel-pink/15 transition-all duration-300">
                        <div class="w-8 h-8 rounded-xl bg-pastel-pink flex items-center justify-center text-slate-800 shadow-sm shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 dark:text-dark-text">Kesesuaian Gaya Kerja</h4>
                            <p class="text-sm text-slate-600 dark:text-dark-muted mt-0.5">Menilai preferensi cara berkomunikasi, waktu belajar, dan kesiapan berkontribusi.</p>
                        </div>
                    </div>

                    <!-- Feature 4: Real-time Recommendations -->
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-pastel-mint/15 dark:bg-pastel-mint/10 border border-pastel-mint/30 dark:border-pastel-mint/20 hover:bg-pastel-mint/25 dark:hover:bg-pastel-mint/15 transition-all duration-300">
                        <div class="w-8 h-8 rounded-xl bg-pastel-mint flex items-center justify-center text-slate-800 shadow-sm shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 dark:text-dark-text">Rekomendasi Real-Time</h4>
                            <p class="text-sm text-slate-600 dark:text-dark-muted mt-0.5">Dapatkan hasil ranking kecocokan instan secara real-time berdasarkan data kelas terupdate.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer copyright or info -->
            <div class="text-xs text-slate-400 dark:text-dark-muted mt-4 md:mt-0">
                &copy; <?= date('Y') ?> <?= APP_NAME ?>. Hak Cipta Dilindungi.
            </div>
        </div>

        <!-- RIGHT COLUMN: Login Form -->
        <div class="flex flex-col justify-center items-center p-6 sm:p-10 md:p-12 lg:p-16 relative">
            <!-- Theme Toggle in Login Page -->
            <div class="absolute top-4 right-4 z-20">
                <button id="theme-toggle" class="theme-toggle border border-[#80ECF2]/30 dark:border-dark-border/60 bg-white/80 dark:bg-dark-surface/85 backdrop-blur-md" aria-label="Ganti tema gelap/terang" title="Ganti tema">
                    <svg id="theme-icon-sun" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg id="theme-icon-moon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
            </div>

            <div class="pastel-card w-full max-w-md p-8 relative z-10 bg-white/90 dark:bg-dark-surface/90">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 rounded-3xl bg-gradient-to-br from-pastel-pink via-pastel-purple to-pastel-blue mx-auto flex items-center justify-center mb-4 shadow-soft">
                        <svg class="w-9 h-9 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-dark-text">Masuk ke MatchIn</h1>
                    <p class="text-muted text-sm mt-2">Temukan partner tugas terbaikmu dengan metode SAW</p>
                </div>

                <?php if ($error): ?>
                    <?php $type = 'error'; $message = $error; include __DIR__ . '/../includes/components/alert.php'; ?>
                <?php endif; ?>

                <form method="POST" action="">
                    <?php
                    $label = 'Email';
                    $name = 'email';
                    $type = 'email';
                    $placeholder = 'nama@email.com';
                    $required = true;
                    include __DIR__ . '/../includes/components/form-field.php';

                    $label = 'Kata Sandi';
                    $name = 'password';
                    $type = 'password';
                    $placeholder = 'Masukkan kata sandi';
                    $required = true;
                    include __DIR__ . '/../includes/components/form-field.php';
                    ?>

                    <button type="submit" class="btn-primary w-full mt-2">Masuk</button>
                </form>

                <p class="text-center text-sm text-muted mt-6">
                    Belum punya akun?
                    <a href="<?= url('auth/register.php') ?>" class="text-primary hover:text-accent transition-colors font-semibold">Daftar di sini</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Theme switcher script for login page -->
    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeIconSun = document.getElementById('theme-icon-sun');
        const themeIconMoon = document.getElementById('theme-icon-moon');

        function updateIcons() {
            if (document.documentElement.classList.contains('dark')) {
                themeIconSun.classList.remove('hidden');
                themeIconMoon.classList.add('hidden');
            } else {
                themeIconSun.classList.add('hidden');
                themeIconMoon.classList.remove('hidden');
            }
        }

        updateIcons();

        themeToggleBtn.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('matchin-theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('matchin-theme', 'dark');
            }
            updateIcons();
        });
    </script>
</body>
</html>
