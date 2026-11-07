<?php
require_once __DIR__ . '/../../config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = $pageTitle ?? 'Beranda';

$navItems = [
    ['page' => 'dashboard', 'label' => 'Beranda', 'href' => url('pages/dashboard.php'), 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    ['page' => 'profile', 'label' => 'Profil', 'href' => url('pages/profile.php'), 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
    ['page' => 'skills', 'label' => 'Skill', 'href' => url('pages/skills.php'), 'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
    ['page' => 'preferences', 'label' => 'Preferensi', 'href' => url('pages/preferences.php'), 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
    ['page' => 'classes', 'label' => 'Kelas Saya', 'href' => url('pages/classes.php'), 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
    ['page' => 'matching', 'label' => 'Cari Partner', 'href' => url('pages/matching.php'), 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= APP_NAME ?></title>
    <link href="<?= asset('css/fonts.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/output.css') ?>?v=<?= filemtime(__DIR__ . '/../../assets/css/output.css') ?>" rel="stylesheet">
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
<body class="bg-gradient-to-br from-pastel-bg via-white to-pastel-blue/20 min-h-screen">
    <nav class="fixed top-0 left-0 right-0 z-50 h-16 bg-white/90 backdrop-blur-md border-b border-pastel-blue/30 shadow-soft">
        <div class="flex items-center justify-between h-full px-4 lg:px-6">
            <div class="flex items-center gap-3">
                <button id="sidebar-toggle" class="lg:hidden p-2 rounded-xl hover:bg-pastel-blue/30 transition-all duration-200 active:scale-95" aria-label="Buka menu">
                    <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <a href="<?= url('pages/dashboard.php') ?>" class="flex items-center gap-2 group">
                    <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-pastel-pink via-pastel-purple to-pastel-blue flex items-center justify-center shadow-soft group-hover:scale-105 transition-transform duration-200">
                        <svg class="w-5 h-5 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-slate-800 hidden sm:block text-lg"><?= APP_NAME ?></span>
                </a>
            </div>
            <div class="flex items-center gap-2 sm:gap-4">
                <span class="text-sm text-muted hidden sm:block">Good luck, <span class="text-slate-800 font-semibold"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Mahasiswa') ?></span>!</span>

                <button id="theme-toggle" class="theme-toggle" aria-label="Ganti tema gelap/terang" title="Ganti tema">
                    <svg id="theme-icon-sun" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg id="theme-icon-moon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                <a href="<?= url('auth/logout.php') ?>" class="link-danger">Keluar</a>
            </div>
        </div>
    </nav>

    <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/20 z-40 hidden lg:hidden backdrop-blur-sm"></div>

    <aside id="sidebar" class="fixed top-16 left-0 z-40 w-72 h-[calc(100vh-4rem)] bg-white/95 dark:bg-dark-surface/95 backdrop-blur-md border-r border-pastel-blue/30 dark:border-dark-border/40 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col justify-between shadow-soft">
        <nav class="p-5 space-y-2 flex-grow overflow-y-auto">
            <?php foreach ($navItems as $item): ?>
                <a href="<?= $item['href'] ?>"
                   class="<?= $currentPage === $item['page'] ? 'nav-link-active' : 'nav-link' ?> text-base flex items-center gap-4 px-5 py-3.5 rounded-[1.5rem] transition-all duration-300 hover:translate-x-2 hover:scale-[1.02] active:scale-95">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $item['icon'] ?>"/>
                    </svg>
                    <span><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Profile card at the bottom of the sidebar -->
        <div class="p-5 border-t border-pastel-blue/20 dark:border-dark-border/40 bg-pastel-bg/30 dark:bg-dark-surface-2/20">
            <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/70 dark:bg-dark-surface-2/60 border border-pastel-blue/10 dark:border-dark-border/20 shadow-sm hover:shadow-soft transition-all duration-300">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-pastel-pink via-pastel-purple to-pastel-blue flex items-center justify-center text-slate-800 font-bold text-lg shadow-sm shrink-0">
                    <?= strtoupper(substr($_SESSION['user_name'] ?? 'M', 0, 1)) ?>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-slate-800 dark:text-dark-text truncate leading-tight"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Mahasiswa') ?></p>
                    <p class="text-xs text-muted truncate mt-0.5"><?= htmlspecialchars($_SESSION['user_email'] ?? 'mahasiswa@univ.ac.id') ?></p>
                </div>
            </div>
        </div>
    </aside>

    <main class="lg:ml-72 pt-16 min-h-screen">
        <div class="p-4 md:p-6 lg:p-8 max-w-6xl mx-auto page-enter">
