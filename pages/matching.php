<?php
require_once __DIR__ . '/../includes/auth-guard.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/EnrollmentService.php';
require_once __DIR__ . '/../services/PreferenceService.php';

$userId = (int) $_SESSION['user_id'];
$enrollmentService = new EnrollmentService($db);
$prefService = new PreferenceService($db);

$classes = $enrollmentService->getUserClasses($userId);
$selectedClassId = (int) ($_GET['class'] ?? ($classes[0]['id_class'] ?? 0));

$hasPreference = $selectedClassId
    ? (bool) $prefService->getByUserAndClass($userId, $selectedClassId)
    : false;

$pageTitle = 'Cari Partner';
$extraScripts = [
    'vendor/jspdf.umd.min.js',
    'vendor/jspdf.plugin.autotable.min.js',
    'saw.js',
    'matching.js',
    'export-pdf.js',
];
include __DIR__ . '/../includes/layout/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl md:text-3xl font-extrabold bg-gradient-to-r from-pastel-deep to-pastel-purple bg-clip-text text-transparent dark:from-pastel-blue dark:to-pastel-pink">Cari Partner Tugas</h1>
        <p class="text-muted mt-1">Rekomendasi partner berdasarkan perhitungan metode SAW (Simple Additive Weighting)</p>
    </div>
    <button id="btn-export-pdf"
        class="btn-calculate whitespace-nowrap bg-pastel-purple border-2 border-pastel-blue text-white hover:text-pastel-deep hover:bg-pastel-bg hidden shrink-0 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
        disabled>
        <span class="inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M4 7V5a2 2 0 012-2h12a2 2 0 012 2v2M5 19h14a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"/>
            </svg>
            Ekspor PDF
        </span>
    </button>
</div>

<?php if (empty($classes)): ?>
    <?php $type = 'warning'; $message = 'Kamu belum terdaftar di kelas manapun. Daftar ke kelas dulu ya!'; include __DIR__ . '/../includes/components/alert.php'; ?>
    <a href="<?= url('pages/classes.php') ?>" class="btn-primary inline-block">Pilih Kelas</a>
<?php else: ?>

    <div class="pastel-card p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm font-semibold text-slate-600 mb-2" for="class-select">Pilih Kelas</label>
                <select id="class-select" class="input-field">
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= (int) $class['id_class'] ?>"
                            <?= $class['id_class'] == $selectedClassId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($class['course_name']) ?> — Kelas <?= htmlspecialchars($class['class_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button id="btn-calculate" class="btn-calculate whitespace-nowrap"
                data-api-url="<?= url('api/matching-data.php') ?>"
                <?= !$hasPreference ? 'disabled title="Lengkapi preferensi terlebih dahulu"' : '' ?>>
                Tentukan Rekomendasi
            </button>
        </div>
        <?php if (!$hasPreference): ?>
            <p class="text-sm alert-warning mt-3 rounded-xl px-4 py-2">
                Lengkapi preferensi untuk kelas ini di
                <a href="<?= url('pages/preferences.php') ?>" class="underline font-semibold">halaman Preferensi</a>.
            </p>
        <?php endif; ?>
    </div>

    <div id="loading" class="hidden pastel-card p-10 text-center">
        <div class="inline-block w-12 h-12 border-4 border-pastel-purple border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-slate-700 font-medium mb-2">Sedang menghitung kecocokan...</p>
        <div class="loading-dots">
            <span></span><span></span><span></span>
        </div>
    </div>

    <div id="error-box" class="hidden mb-6"></div>

    <div id="class-info" class="hidden pastel-card p-4 mb-6 border-pastel-blue/40 result-section">
        <p class="text-sm text-muted">Mata Kuliah</p>
        <p id="info-course" class="font-bold text-slate-800 text-lg"></p>
        <p id="info-class" class="text-sm text-primary mt-1"></p>
    </div>

    <div id="criteria-info" class="hidden mb-8 pastel-card p-6 result-section">
        <h3 class="text-lg font-bold text-slate-800 mb-3">Bobot Kriteria SAW</h3>
        <div id="criteria-list" class="grid grid-cols-1 sm:grid-cols-2 gap-3"></div>
    </div>

    <div id="top3-section" class="hidden mb-8 result-section">
        <h2 class="text-xl font-bold text-slate-800 mb-4">Rekomendasi Terbaik (Top 3) 🏆</h2>
        <div id="top3-cards" class="grid grid-cols-1 md:grid-cols-3 gap-4"></div>
    </div>

    <details id="table-comparison" class="hidden mb-4 result-section pastel-card overflow-hidden accordion-item">
        <summary class="accordion-header">
            <h2>Perbandingan Kandidat</h2>
            <svg class="accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </summary>
        <div class="accordion-body">
            <div class="overflow-x-auto">
                <table class="table-pastel">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>NPM</th>
                            <th>Jurusan</th>
                            <th>Ringkasan Skill</th>
                            <th>Gaya Kerja</th>
                        </tr>
                    </thead>
                    <tbody id="comparison-body"></tbody>
                </table>
            </div>
        </div>
    </details>

    <details id="table-matrix" class="hidden mb-4 result-section pastel-card overflow-hidden accordion-item">
        <summary class="accordion-header">
            <h2>Matriks Kriteria</h2>
            <svg class="accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </summary>
        <div class="accordion-body">
            <div class="overflow-x-auto">
                <table class="table-pastel">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>NPM</th>
                            <th>C1 (Skill)</th>
                            <th>C2 (Preferensi)</th>
                            <th>C3 (Gaya Kerja)</th>
                            <th>C4 (Pengalaman)</th>
                        </tr>
                    </thead>
                    <tbody id="matrix-body"></tbody>
                </table>
            </div>
        </div>
    </details>

    <details id="table-normalization" class="hidden mb-4 result-section pastel-card overflow-hidden accordion-item">
        <summary class="accordion-header">
            <h2>Normalisasi SAW</h2>
            <svg class="accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </summary>
        <div class="accordion-body">
            <div id="max-values-info" class="mb-3 text-sm text-muted bg-pastel-bg rounded-xl px-4 py-2 border border-slate-100"></div>
            <div class="overflow-x-auto">
                <table class="table-pastel">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>NPM</th>
                            <th>C1</th>
                            <th>C2</th>
                            <th>C3</th>
                            <th>C4</th>
                        </tr>
                    </thead>
                    <tbody id="normalization-body"></tbody>
                </table>
            </div>
        </div>
    </details>

    <details id="table-weighted" class="hidden mb-8 result-section pastel-card overflow-hidden accordion-item">
        <summary class="accordion-header">
            <h2>Skor Terbobot</h2>
            <svg class="accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </summary>
        <div class="accordion-body">
            <div class="overflow-x-auto">
                <table class="table-pastel">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>NPM</th>
                            <th>C1 × 0.4</th>
                            <th>C2 × 0.3</th>
                            <th>C3 × 0.2</th>
                            <th>C4 × 0.1</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="weighted-body"></tbody>
                </table>
            </div>
        </div>
    </details>

    <!-- Tabel 5 sengaja TIDAK dijadikan dropdown, supaya hasil akhir langsung terlihat tanpa perlu klik. -->
    <div id="table-ranking" class="hidden mb-8 result-section">
        <h2 class="text-xl font-bold text-slate-800 mb-4">Peringkat Akhir</h2>
        <div class="pastel-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-pastel">
                    <thead>
                        <tr>
                            <th>Peringkat</th>
                            <th>Nama</th>
                            <th>NPM</th>
                            <th>Skor Akhir</th>
                            <th>Persentase</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="ranking-body"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal untuk melihat foto kandidat ukuran penuh -->
    <div id="photo-modal" class="hidden photo-modal-backdrop">
        <div class="photo-modal-panel">
            <button type="button" id="photo-modal-close" class="photo-modal-close" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img id="photo-modal-img" src="" alt="Foto kandidat">
        </div>
    </div>

<?php endif; ?>

<?php include __DIR__ . '/../includes/layout/footer.php'; ?>
