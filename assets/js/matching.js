document.addEventListener('DOMContentLoaded', () => {
  const btnCalculate = document.getElementById('btn-calculate');
  const btnExportPdf = document.getElementById('btn-export-pdf');
  const classSelect = document.getElementById('class-select');

  if (!btnCalculate || !classSelect) return;

  const apiUrl = btnCalculate.dataset.apiUrl;

  // ---------- Modal lihat foto kandidat ----------
  const photoModal = document.getElementById('photo-modal');
  const photoModalImg = document.getElementById('photo-modal-img');
  const photoModalClose = document.getElementById('photo-modal-close');

  function openPhotoModal(url, name) {
    if (!photoModal || !photoModalImg) return;
    photoModalImg.src = url;
    photoModalImg.alt = `Foto ${name || 'kandidat'}`;
    photoModal.classList.remove('hidden');
  }

  function closePhotoModal() {
    if (!photoModal || !photoModalImg) return;
    photoModal.classList.add('hidden');
    photoModalImg.src = '';
  }

  if (photoModalClose) photoModalClose.addEventListener('click', closePhotoModal);
  if (photoModal) {
    photoModal.addEventListener('click', (e) => {
      if (e.target === photoModal) closePhotoModal();
    });
  }
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closePhotoModal();
  });

  const resultSections = [
    'class-info',
    'criteria-info',
    'top3-section',
    'table-comparison',
    'table-matrix',
    'table-normalization',
    'table-weighted',
    'table-ranking',
  ];

  classSelect.addEventListener('change', () => {
    const params = new URLSearchParams(window.location.search);
    params.set('class', classSelect.value);
    window.location.search = params.toString();
  });

  btnCalculate.addEventListener('click', async () => {
    const classId = classSelect.value;
    hideAll();
    showLoading(true);
    btnCalculate.disabled = true;
    setExportEnabled(false);

    try {
      const response = await fetch(`${apiUrl}?id_class=${classId}`);
      const data = await response.json();

      if (!data.success) {
        showError(data.message || 'Terjadi kesalahan saat mengambil data.');
        return;
      }

      renderResults(data);

      // Simpan hasil terakhir agar bisa dipakai oleh export-pdf.js
      window.MatchInMatchingResult = data;
      setExportEnabled(true);
    } catch (err) {
      showError('Gagal terhubung ke server. Cek koneksi internetmu ya!');
      console.error(err);
    } finally {
      showLoading(false);
      btnCalculate.disabled = false;
    }
  });

  function hideAll() {
    resultSections.forEach((id) => {
      const el = document.getElementById(id);
      if (el) el.classList.add('hidden');
    });
    const errorBox = document.getElementById('error-box');
    if (errorBox) errorBox.classList.add('hidden');
  }

  function showLoading(show) {
    const el = document.getElementById('loading');
    if (!el) return;
    el.classList.toggle('hidden', !show);
  }

  function setExportEnabled(enabled) {
    if (!btnExportPdf) return;
    btnExportPdf.classList.toggle('hidden', !enabled);
    btnExportPdf.disabled = !enabled;
  }

  function showError(message) {
    const box = document.getElementById('error-box');
    if (!box) return;
    box.classList.remove('hidden');
    box.innerHTML = `
      <div class="pastel-card px-4 py-4 alert-error text-sm flex items-start gap-3 animate-fade-in">
        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        ${escapeHtml(message)}
      </div>`;
  }

  function renderResults(data) {
    if (data.class) {
      const info = document.getElementById('class-info');
      document.getElementById('info-course').textContent = data.class.course_name;
      document.getElementById('info-class').textContent =
        `Kelas ${data.class.class_name} · Semester ${data.class.semester} · ${data.class.lecturer}`;
      info.classList.remove('hidden');
    }

    renderCriteria(data.criteria);
    renderTop3(data.top3);
    renderComparisonTable(data.comparison);
    renderMatrixTable(data.matrix);
    renderNormalizationTable(data.normalization);
    renderWeightedTable(data.weighted);
    renderRankingTable(data.ranking);
  }

  function renderCriteria(criteria) {
    const section = document.getElementById('criteria-info');
    const list = document.getElementById('criteria-list');
    if (!section || !list || !criteria) return;

    list.innerHTML = criteria
      .map(
        (c) => `
      <div class="flex justify-between items-center p-4 rounded-2xl bg-pastel-bg border border-slate-100">
        <div>
          <span class="text-xs font-bold uppercase tracking-wider text-primary">${c.code}</span>
          <p class="mt-1 text-sm text-slate-700 font-medium">${escapeHtml(c.name)}</p>
        </div>
        <span class="text-lg font-bold text-slate-800">${(c.weight * 100).toFixed(0)}%</span>
      </div>`
      )
      .join('');

    section.classList.remove('hidden');
  }

  function renderTop3(top3) {
    const section = document.getElementById('top3-section');
    const container = document.getElementById('top3-cards');
    if (!section || !container || !top3) return;

    const styles = [
      'from-pastel-purple/60 to-white border-pastel-purple',
      'from-pastel-blue/60 to-white border-pastel-blue',
      'from-pastel-blue/60 to-white border-pastel-blue',
    ];

    container.innerHTML = top3
      .map((item, i) => {
        const style = styles[i] || 'from-pastel-blue/40 to-white border-pastel-blue';
        return `
        <div class="pastel-card p-5 bg-gradient-to-br ${style} border-2">
          <div class="flex items-center justify-between mb-4 gap-3">
            <div>
              <p class="text-xs uppercase tracking-wider text-muted font-semibold">Top ${item.rank}</p>
              <h3 class="text-xl font-bold text-slate-800">${escapeHtml(item.name)}</h3>
            </div>
            <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white text-lg font-bold shadow-soft">
              ${item.rank}
            </span>
          </div>
          <p class="text-sm text-muted">NPM ${escapeHtml(item.npm)}</p>
          <div class="mt-4 rounded-2xl bg-white/80 p-4 shadow-soft border border-white">
            <p class="text-xs text-muted font-medium">Skor Kecocokan</p>
            <p class="mt-1 text-3xl font-bold text-slate-800">${item.percentage}%</p>
          </div>
        </div>`;
      })
      .join('');

    section.classList.remove('hidden');
  }

  function renderComparisonTable(comparison) {
    const section = document.getElementById('table-comparison');
    const tbody = document.getElementById('comparison-body');
    if (!section || !tbody || !comparison) return;

    tbody.innerHTML = comparison
      .map((item, i) => {
        const initial = (item.name || '?').trim().charAt(0).toUpperCase() || '?';
        const photoCell = item.has_photo && item.photo_url
          ? `<img src="${escapeHtml(item.photo_url)}" alt="Foto ${escapeHtml(item.name)}"
                 class="candidate-photo-thumb" data-photo-view="${escapeHtml(item.photo_url)}"
                 data-photo-name="${escapeHtml(item.name)}" title="Klik untuk lihat foto">`
          : `<div class="candidate-photo-placeholder">${escapeHtml(initial)}</div>`;

        return `
      <tr>
        <td>${i + 1}</td>
        <td>${photoCell}</td>
        <td class="font-semibold">${escapeHtml(item.name)}</td>
        <td>${escapeHtml(item.npm)}</td>
        <td>${escapeHtml(item.major)}</td>
        <td class="max-w-xs">${escapeHtml(item.skills_summary)}</td>
        <td>${escapeHtml(item.work_style)}</td>
      </tr>`;
      })
      .join('');

    tbody.querySelectorAll('[data-photo-view]').forEach((img) => {
      img.addEventListener('click', () => {
        openPhotoModal(img.dataset.photoView, img.dataset.photoName);
      });
    });

    section.classList.remove('hidden');
  }

  function renderMatrixTable(matrix) {
    const section = document.getElementById('table-matrix');
    const tbody = document.getElementById('matrix-body');
    if (!section || !tbody || !matrix) return;

    tbody.innerHTML = matrix
      .map(
        (item) => `
      <tr>
        <td class="font-semibold">${escapeHtml(item.name)}</td>
        <td>${escapeHtml(item.npm)}</td>
        <td>${item.scores.C1}</td>
        <td>${item.scores.C2}</td>
        <td>${item.scores.C3}</td>
        <td>${item.scores.C4}</td>
      </tr>`
      )
      .join('');

    section.classList.remove('hidden');
  }

  function renderNormalizationTable(normalization) {
    const section = document.getElementById('table-normalization');
    const tbody = document.getElementById('normalization-body');
    const maxInfo = document.getElementById('max-values-info');
    if (!section || !tbody || !normalization) return;

    if (maxInfo && normalization.maxValues) {
      const maxParts = Object.entries(normalization.maxValues)
        .map(([code, val]) => `<strong>${code}</strong>: ${val}`)
        .join(' · ');
      maxInfo.innerHTML = `Nilai maksimum per kriteria: ${maxParts}`;
    }

    tbody.innerHTML = (normalization.rows || [])
      .map(
        (item) => `
      <tr>
        <td class="font-semibold">${escapeHtml(item.name)}</td>
        <td>${escapeHtml(item.npm)}</td>
        <td>${item.normalized.C1}</td>
        <td>${item.normalized.C2}</td>
        <td>${item.normalized.C3}</td>
        <td>${item.normalized.C4}</td>
      </tr>`
      )
      .join('');

    section.classList.remove('hidden');
  }

  function renderWeightedTable(weighted) {
    const section = document.getElementById('table-weighted');
    const tbody = document.getElementById('weighted-body');
    if (!section || !tbody || !weighted) return;

    tbody.innerHTML = weighted
      .map(
        (item) => `
      <tr>
        <td class="font-semibold">${escapeHtml(item.name)}</td>
        <td>${escapeHtml(item.npm)}</td>
        <td>${item.weighted.C1}</td>
        <td>${item.weighted.C2}</td>
        <td>${item.weighted.C3}</td>
        <td>${item.weighted.C4}</td>
        <td class="font-bold text-primary">${item.totalScore}</td>
      </tr>`
      )
      .join('');

    section.classList.remove('hidden');
  }

  function renderRankingTable(ranking) {
    const section = document.getElementById('table-ranking');
    const tbody = document.getElementById('ranking-body');
    if (!section || !tbody || !ranking) return;

    tbody.innerHTML = ranking
      .map((item) => {
        const isTop3 = item.rank <= 3;
        const badge = isTop3
          ? `<span class="badge badge-success">Rekomendasi Top ${item.rank}</span>`
          : '<span class="text-muted text-xs">—</span>';
        return `
      <tr class="${isTop3 ? 'top-rank' : ''}">
        <td>
          <span class="inline-flex items-center justify-center w-9 h-9 rounded-full ${isTop3 ? 'bg-pastel-pink/60 text-slate-800' : 'bg-pastel-blue/30 text-slate-700'} text-sm font-bold">
            ${item.rank}
          </span>
        </td>
        <td class="font-semibold">${escapeHtml(item.name)}</td>
        <td>${escapeHtml(item.npm)}</td>
        <td class="font-bold">${item.totalScore}</td>
        <td class="font-bold text-primary">${item.percentage}%</td>
        <td>${badge}</td>
      </tr>`;
      })
      .join('');

    section.classList.remove('hidden');
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
});
