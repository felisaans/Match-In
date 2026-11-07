<?php
require_once __DIR__ . '/../includes/auth-guard.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/ProfileService.php';
require_once __DIR__ . '/../services/EnrollmentService.php';
require_once __DIR__ . '/../services/MatchingService.php';
require_once __DIR__ . '/../services/SystemLogService.php';

$userId = (int) $_SESSION['user_id'];
$profileService = new ProfileService($db);
$enrollmentService = new EnrollmentService($db);
$matchingService = new MatchingService($db);
$logService = new SystemLogService($db);

$stats = $profileService->getDashboardStats($userId);
$classes = $enrollmentService->getUserClasses($userId);
$user = $profileService->getById($userId);

// --- Widget 2: Kelengkapan profil (foto 20% + kelas 30% + skill>=3 30% + preferensi 20%) ---
$completion = 0;
$completion += !empty($user['has_photo']) ? 20 : 0;
$completion += $stats['classes'] > 0 ? 30 : 0;
$completion += $stats['skills'] >= 3 ? 30 : 0;
$completion += $stats['preferences'] > 0 ? 20 : 0;

if (!function_exists('completionIcon')) {
    function completionIcon(bool $done): string
    {
        return $done
            ? '<svg class="w-4 h-4 text-pastel-mint shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
            : '<svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>';
    }
}

// --- Widget 3: Rekomendasi terakhir ---
$lastRecommendation = $matchingService->getLastRecommendation($userId);

// --- Widget 4: Aktivitas terbaru ---
$recentActivity = $logService->getRecent($userId, 5);

// --- Welcome banner: 4 langkah cepat ---
$guideSteps = [
    ['n' => 1, 'label' => 'Pilih Kelas'],
    ['n' => 2, 'label' => 'Isi Skill'],
    ['n' => 3, 'label' => 'Atur Preferensi'],
    ['n' => 4, 'label' => 'Cari Partner'],
];

$extraScripts = ['dashboard.js'];
$pageTitle = 'Beranda';
include __DIR__ . '/../includes/layout/header.php';
?>

<div id="welcome-banner" class="relative overflow-hidden rounded-2xl shadow-soft mb-8 bg-gradient-to-r from-pastel-blue to-pastel-purple p-6 md:p-8 text-white animate-fade-in">
    <button type="button" id="welcome-banner-close" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-pastel-purple/20 hover:bg-white/30 flex items-center justify-center transition-colors" aria-label="Tutup banner">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <h2 class="text-xl md:text-2xl font-bold text-black/90 mb-1">Selamat datang, <?= htmlspecialchars($user['name']) ?>!</h2>
    <p class="text-black/90 mb-5 text-sm md:text-base">Belum punya partner tugas? Ikuti 4 langkah cepat ini:</p>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <?php foreach ($guideSteps as $step): ?>
        <div class="bg-black/15 rounded-2xl p-3 flex items-center gap-2">
            <div class="w-7 h-7 text-black/90 rounded-full bg-black/25 flex items-center justify-center shrink-0 text-sm font-bold"><?= $step['n'] ?></div>
            <span class="text-sm text-black/90 font-medium leading-tight"><?= $step['label'] ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="mb-8">
    <h1 class="text-2xl md:text-3xl font-extrabold bg-gradient-to-r from-pastel-deep to-pastel-purple bg-clip-text text-transparent dark:from-pastel-blue dark:to-pastel-pink">Dashboard</h1>
    <p class="text-muted mt-1">Hai <?= htmlspecialchars($user['name']) ?>, siap cari partner tugas hari ini? </p>
</div>

<?php if (!$stats['profile_complete']): ?>
    <?php $type = 'warning'; $message = 'Lengkapi skill dan preferensimu dulu ya, biar rekomendasi partner lebih akurat!'; include __DIR__ . '/../includes/components/alert.php'; ?>
<?php endif; ?>

<?php if ($stats['skills'] === 0 || $stats['classes'] === 0): ?>
    <!-- Smart empty state: metrik diganti CTA selama skill/kelas masih kosong -->
    <div class="pastel-card p-8 text-center mb-8">
        <svg class="w-16 h-16 mx-auto text-pastel-blue/50 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="font-semibold text-slate-800 mb-1">Profilmu masih kosong</p>
        <p class="text-muted text-sm mb-4">Lengkapi kelas dan skill dulu supaya sistem SAW bisa menghitung rekomendasi partner buat kamu.</p>
        <div class="flex flex-wrap justify-center gap-3">
            <?php if ($stats['classes'] === 0): ?><a href="<?= url('pages/classes.php') ?>" class="btn-primary text-sm">Pilih Kelas</a><?php endif; ?>
            <?php if ($stats['skills'] === 0): ?><a href="<?= url('pages/skills.php') ?>" class="btn-secondary text-sm">Isi Skill</a><?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <?php
        $label = 'Total Skill'; $value = $stats['skills']; $color = 'mint';
        include __DIR__ . '/../includes/components/stat-card.php';
        $label = 'Kelas Diikuti'; $value = $stats['classes']; $color = 'purple';
        include __DIR__ . '/../includes/components/stat-card.php';
        $label = 'Preferensi'; $value = $stats['preferences']; $color = 'blue';
        include __DIR__ . '/../includes/components/stat-card.php';
        ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Widget: Kelengkapan Profil -->
    <div class="pastel-card p-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-bold text-slate-800">Kelengkapan Profil</h2>
            <span class="text-sm font-bold text-pastel-deep"><?= $completion ?>%</span>
        </div>
        <div class="w-full h-3 rounded-full bg-pastel-mint/20 overflow-hidden mb-4">
            <div class="h-full rounded-full bg-pastel-mint transition-all duration-500" style="width: <?= $completion ?>%"></div>
        </div>
        <ul class="space-y-1.5 text-sm text-muted mb-4">
            <li class="flex items-center gap-2"><?= completionIcon(!empty($user['has_photo'])) ?> Foto profil</li>
            <li class="flex items-center gap-2"><?= completionIcon($stats['classes'] > 0) ?> Terdaftar di kelas</li>
            <li class="flex items-center gap-2"><?= completionIcon($stats['skills'] >= 3) ?> Minimal 1 skill</li>
            <li class="flex items-center gap-2"><?= completionIcon($stats['preferences'] > 0) ?> Preferensi partner</li>
        </ul>
        <?php if ($completion < 100): ?>
            <a href="<?= url('pages/profile.php') ?>" class="btn-secondary inline-block text-sm w-full text-center">Lengkapi Profil</a>
        <?php endif; ?>
    </div>

    <!-- Widget: Rekomendasi Terakhir -->
    <div class="pastel-card p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Rekomendasi Terakhir</h2>
        <?php if ($lastRecommendation): ?>
            <div class="flex items-center gap-4 p-4 rounded-2xl bg-gradient-to-br from-pastel-blue/15 to-pastel-purple/15 border border-pastel-blue/20">
                <?php if (!empty($lastRecommendation['has_photo'])): ?>
                    <img src="<?= url('api/photo.php?id=' . (int) $lastRecommendation['partner_id']) ?>"
                         alt="Foto <?= htmlspecialchars($lastRecommendation['name']) ?>" class="candidate-photo-thumb">
                <?php else: ?>
                    <div class="candidate-photo-placeholder"><?= strtoupper(substr($lastRecommendation['name'], 0, 1)) ?></div>
                <?php endif; ?>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-800 truncate"><?= htmlspecialchars($lastRecommendation['name']) ?></p>
                    <p class="text-sm text-muted"><?= htmlspecialchars($lastRecommendation['student_id']) ?></p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xl font-bold text-pastel-deep"><?= number_format((float) $lastRecommendation['final_score'] * 100, 1) ?>%</p>
                    <p class="text-xs text-muted">Skor kecocokan</p>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-6">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-muted text-sm mb-3">Belum menentukan rekomendasi.</p>
                <a href="<?= url('pages/matching.php') ?>" class="btn-primary inline-block text-sm">Cari Partner Sekarang</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Widget: Aktivitas Terbaru -->
<div class="pastel-card p-6 mb-6">
    <h2 class="text-lg font-bold text-slate-800 mb-4">Aktivitas Terbaru</h2>
    <?php if (empty($recentActivity)): ?>
        <p class="text-muted text-sm">Belum ada aktivitas tercatat.</p>
    <?php else: ?>
        <div class="timeline">
            <?php foreach ($recentActivity as $log): ?>
                <div class="timeline-item">
                    <span class="timeline-dot"></span>
                    <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($log['action']) ?></p>
                    <p class="text-sm text-muted"><?= htmlspecialchars($log['description']) ?></p>
                    <p class="text-xs text-slate-400 mt-0.5"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="pastel-card p-6">
        <h2 class="text-lg font-bold text-l mb-4">Langkah Cepat</h2>
        <div class="space-y-3">
            <a href="<?= url('pages/skills.php') ?>" class="flex items-center gap-3 p-4 rounded-2xl bg-pastel-blue/20 hover:bg-pastel-blue/40 border border-pastel-blue/30 transition-all duration-200 group hover:-translate-y-0.5">
                <div class="w-10 h-10 rounded-2xl bg-pastel-purple/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <span class="text-slate-800 font-bold">1</span>
                </div>
                <div>
                    <p class="font-semibold text-l">Isi Skill Kamu</p>
                    <p class="text-m text-muted">Tambahkan keahlian yang kamu punya</p>
                </div>
            </a>
            <a href="<?= url('pages/preferences.php') ?>" class="flex items-center gap-3 p-4 rounded-2xl bg-pastel-pink/20 hover:bg-pastel-pink/40 border border-pastel-pink/30 transition-all duration-200 group hover:-translate-y-0.5">
                <div class="w-10 h-10 rounded-2xl bg-pastel-purple/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <span class="text-slate-800 font-bold">2</span>
                </div>
                <div>
                    <p class="font-semibold text-l">Atur Preferensi</p>
                    <p class="text-m text-muted">Tentukan skill & gaya kerja yang kamu cari</p>
                </div>
            </a>
            <a href="<?= url('pages/matching.php') ?>" class="flex items-center gap-3 p-4 rounded-2xl bg-pastel-mint/20 hover:bg-pastel-mint/40 border border-pastel-mint/30 transition-all duration-200 group hover:-translate-y-0.5">
                <div class="w-10 h-10 rounded-2xl bg-pastel-purple/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <span class="text-slate-800 font-bold">3</span>
                </div>
                <div>
                    <p class="font-semibold text-l">Cari Partner</p>
                    <p class="text-m text-muted">Dapatkan rekomendasi dengan metode SAW</p>
                </div>
            </a>
        </div>
    </div>

    <div class="pastel-card p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Kelas Semester <?= (int) $user['semester'] ?></h2>
        <?php if (empty($classes)): ?>
            <p class="text-muted text-sm mb-4">Kamu belum terdaftar di kelas manapun.</p>
            <a href="<?= url('pages/classes.php') ?>" class="btn-primary inline-block text-sm">Pilih Kelas</a>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($classes as $class): ?>
                    <div class="p-4 rounded-2xl bg-white border border-slate-100 hover:border-pastel-blue/40 transition-colors">
                        <p class="font-semibold text-slate-800"><?= htmlspecialchars($class['course_name']) ?></p>
                        <p class="text-sm text-muted">Kelas <?= htmlspecialchars($class['class_name']) ?> · Semester <?= (int) $class['semester'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>


<!-- FAB: Cara Pakai MatchIn -->
<button type="button" id="fab-help"
        class="fixed bottom-8 right-8 w-14 h-14 rounded-full bg-gradient-to-br from-pastel-blue to-pastel-purple shadow-soft text-white text-2xl font-bold flex items-center justify-center hover:scale-110 transition-transform z-40"
        aria-label="Cara pakai MatchIn">?</button>

<div id="how-to-modal" class="hidden photo-modal-backdrop">
    <div class="photo-modal-panel info-modal-panel">
        <button type="button" id="how-to-modal-close" class="photo-modal-close" aria-label="Tutup">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <div class="p-2">
            <h3 class="text-xl font-bold text-slate-800 mb-2">Tentang MatchIn</h3>
            <p class="text-sm text-muted mb-4">MatchIn membantu mahasiswa Teknik Informatika, Ilmu Komputer, dan Teknik Komputer menemukan partner tugas paling cocok berdasarkan skill, preferensi, dan gaya kerja.</p>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Cara Kerja Metode SAW</h3>
            <ol class="list-decimal list-inside text-sm text-muted space-y-1.5">
                <li>Setiap kandidat dinilai dari 4 kriteria: skill, preferensi, gaya kerja, pengalaman.</li>
                <li>Nilai tiap kriteria dinormalisasi ke skala 0–1.</li>
                <li>Skor dikalikan bobot masing-masing kriteria (0.4 / 0.3 / 0.2 / 0.1).</li>
                <li>Total skor tertinggi = partner yang paling direkomendasikan.</li>
            </ol>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/layout/footer.php'; ?>
