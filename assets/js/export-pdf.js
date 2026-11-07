document.addEventListener('DOMContentLoaded', () => {
  const btnExportPdf = document.getElementById('btn-export-pdf');
  if (!btnExportPdf) return;

  // Palet warna tema MatchIn (selaras dengan tailwind.config.js)
  const THEME = {
    sky: [89, 181, 246], // #59B5F6 - primary
    deep: [52, 103, 181], // #3467B5 - pastel-deep
    blue: [128, 236, 242], // #80ECF2 - pastel-blue
    purple: [219, 137, 221], // #DB89DD - pastel-purple
    bg: [242, 246, 247], // #F2F6F7 - pastel-bg
    text: [30, 41, 59], // slate-800
    muted: [97, 112, 140], // muted
    mintBg: [229, 247, 244], // top-rank highlight
    mintText: [6, 95, 70], // emerald-800
  };

  btnExportPdf.addEventListener('click', () => {
    const data = window.MatchInMatchingResult;
    if (!data || !data.ranking || !data.ranking.length) {
      alert('Belum ada hasil rekomendasi untuk diekspor. Tekan "Tentukan Rekomendasi" dulu ya!');
      return;
    }

    try {
      generatePdf(data);
    } catch (err) {
      console.error('Gagal membuat PDF:', err);
      alert('Terjadi kesalahan saat membuat PDF. Coba lagi ya!');
    }
  });

  function generatePdf(data) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
    const pageWidth = doc.internal.pageSize.getWidth();
    const margin = 40;

    let cursorY = drawHeader(doc, data, pageWidth, margin);
    cursorY = drawClassInfo(doc, data, pageWidth, margin, cursorY);
    cursorY = drawCriteria(doc, data, pageWidth, margin, cursorY);
    cursorY = drawRankingTable(doc, data, pageWidth, margin, cursorY);
    drawSkillsTable(doc, data, pageWidth, margin, cursorY);

    addFooterToAllPages(doc, pageWidth);

    doc.save(buildFileName(data));
  }

  // ---------- Header ----------
  function drawHeader(doc, data, pageWidth, margin) {
    const headerHeight = 64;

    doc.setFillColor(...THEME.sky);
    doc.roundedRect(margin, 30, pageWidth - margin * 2, headerHeight, 14, 14, 'F');

    // Aksen gradasi-imitasi: garis tipis di bawah header dengan warna purple
    doc.setFillColor(...THEME.purple);
    doc.roundedRect(margin, 30 + headerHeight - 6, pageWidth - margin * 2, 6, 3, 3, 'F');

    doc.setTextColor(255, 255, 255);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(18);
    doc.text('MatchIn', margin + 20, 30 + 26);

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10.5);
    doc.text('Hasil Rekomendasi Partner Tugas — Metode SAW', margin + 20, 30 + 44);

    const generatedAt = new Date().toLocaleString('id-ID', {
      dateStyle: 'long',
      timeStyle: 'short',
    });
    doc.setFontSize(9);
    doc.text(`Dibuat: ${generatedAt}`, pageWidth - margin - 20, 30 + 44, { align: 'right' });

    return 30 + headerHeight + 20;
  }

  // ---------- Info kelas ----------
  function drawClassInfo(doc, data, pageWidth, margin, startY) {
    if (!data.class) return startY;

    const boxHeight = 46;
    doc.setFillColor(...THEME.bg);
    doc.setDrawColor(...THEME.blue);
    doc.roundedRect(margin, startY, pageWidth - margin * 2, boxHeight, 10, 10, 'FD');

    doc.setTextColor(...THEME.muted);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8.5);
    doc.text('MATA KULIAH', margin + 16, startY + 16);

    doc.setTextColor(...THEME.text);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.text(String(data.class.course_name || '-'), margin + 16, startY + 31);

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(9.5);
    doc.setTextColor(...THEME.deep);
    const classLine = `Kelas ${data.class.class_name || '-'} · Semester ${data.class.semester || '-'} · ${data.class.lecturer || '-'}`;
    doc.text(classLine, margin + 16, startY + 41);

    return startY + boxHeight + 24;
  }

  // ---------- Bobot kriteria ----------
  function drawCriteria(doc, data, pageWidth, margin, startY) {
    if (!data.criteria || !data.criteria.length) return startY;

    doc.setTextColor(...THEME.text);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(11.5);
    doc.text('Bobot Kriteria SAW', margin, startY);

    const chipY = startY + 10;
    const chipHeight = 28;
    const gap = 10;
    const chipWidth = (pageWidth - margin * 2 - gap * (data.criteria.length - 1)) / data.criteria.length;

    data.criteria.forEach((c, i) => {
      const x = margin + i * (chipWidth + gap);

      doc.setFillColor(...THEME.bg);
      doc.setDrawColor(...THEME.blue);
      doc.roundedRect(x, chipY, chipWidth, chipHeight, 8, 8, 'FD');

      doc.setTextColor(...THEME.deep);
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(8);
      doc.text(String(c.code || ''), x + 10, chipY + 11);

      doc.setTextColor(...THEME.text);
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(11);
      doc.text(`${Math.round((c.weight || 0) * 100)}%`, x + 10, chipY + 22);

      doc.setFont('helvetica', 'normal');
      doc.setFontSize(7.3);
      doc.setTextColor(...THEME.muted);
      const label = truncateText(doc, String(c.name || ''), chipWidth - 20);
      doc.text(label, x + chipWidth - 10, chipY + 17, { align: 'right' });
    });

    return chipY + chipHeight + 26;
  }

  // ---------- Tabel Peringkat Akhir ----------
  function drawRankingTable(doc, data, pageWidth, margin, startY) {
    doc.setTextColor(...THEME.text);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12.5);
    doc.text('Tabel Peringkat Akhir', margin, startY);

    const rows = data.ranking.map((item) => [
      String(item.rank),
      item.name || '-',
      item.npm || '-',
      formatNumber(item.totalScore),
      `${formatNumber(item.percentage)}%`,
      statusLabel(item.rank),
    ]);

    doc.autoTable({
      startY: startY + 10,
      margin: { left: margin, right: margin },
      head: [['Peringkat', 'Nama', 'NPM', 'Skor Akhir', 'Persentase', 'Status']],
      body: rows,
      theme: 'plain',
      styles: {
        font: 'helvetica',
        fontSize: 9,
        cellPadding: 7,
        textColor: THEME.text,
        lineColor: [226, 232, 240],
        lineWidth: 0.5,
      },
      headStyles: {
        fillColor: THEME.sky,
        textColor: [255, 255, 255],
        fontStyle: 'bold',
        fontSize: 9.5,
      },
      columnStyles: {
        0: { halign: 'center', cellWidth: 50 },
        3: { halign: 'right', cellWidth: 65 },
        4: { halign: 'right', cellWidth: 65 },
        5: { cellWidth: 110 },
      },
      alternateRowStyles: { fillColor: [232, 247, 255] },
      didParseCell: (hookData) => {
        if (hookData.section !== 'body') return;
        const rank = Number(rows[hookData.row.index][0]);
        if (rank <= 3) {
          hookData.cell.styles.fillColor = THEME.mintBg;
        }
        if (hookData.column.index === 0) {
          hookData.cell.styles.fontStyle = 'bold';
          hookData.cell.styles.textColor = rank <= 3 ? THEME.mintText : THEME.text;
        }
        if (hookData.column.index === 4) {
          hookData.cell.styles.fontStyle = 'bold';
          hookData.cell.styles.textColor = THEME.deep;
        }
        if (hookData.column.index === 5 && rank <= 3) {
          hookData.cell.styles.textColor = THEME.mintText;
          hookData.cell.styles.fontStyle = 'bold';
        }
      },
    });

    return doc.lastAutoTable.finalY + 28;
  }

  // ---------- Tabel Ringkasan Skill ----------
  function drawSkillsTable(doc, data, pageWidth, margin, startY) {
    const comparisonByNpm = {};
    (data.comparison || []).forEach((c) => {
      comparisonByNpm[c.npm] = c;
    });

    const rows = data.ranking.map((item) => {
      const detail = comparisonByNpm[item.npm];
      return [
        String(item.rank),
        item.name || '-',
        item.npm || '-',
        detail ? (detail.skills_summary || '-') : '-',
        detail ? (detail.work_style || '-') : '-',
      ];
    });

    let y = startY;
    if (y > doc.internal.pageSize.getHeight() - 140) {
      doc.addPage();
      y = 50;
    }

    doc.setTextColor(...THEME.text);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12.5);
    doc.text('Tabel Ringkasan Skill Kandidat', margin, y);

    doc.autoTable({
      startY: y + 10,
      margin: { left: margin, right: margin },
      head: [['Peringkat', 'Nama', 'NPM', 'Ringkasan Skill', 'Gaya Kerja']],
      body: rows,
      theme: 'plain',
      styles: {
        font: 'helvetica',
        fontSize: 9,
        cellPadding: 7,
        textColor: THEME.text,
        lineColor: [226, 232, 240],
        lineWidth: 0.5,
        valign: 'top',
      },
      headStyles: {
        fillColor: THEME.purple,
        textColor: [255, 255, 255],
        fontStyle: 'bold',
        fontSize: 9.5,
      },
      columnStyles: {
        0: { halign: 'center', cellWidth: 50 },
        2: { cellWidth: 75 },
        3: { cellWidth: 180 },
        4: { cellWidth: 90 },
      },
      alternateRowStyles: { fillColor: [250, 240, 251] },
    });

    return doc.lastAutoTable.finalY;
  }

  // ---------- Footer ----------
  function addFooterToAllPages(doc, pageWidth) {
    const pageCount = doc.internal.getNumberOfPages();
    const pageHeight = doc.internal.pageSize.getHeight();

    for (let i = 1; i <= pageCount; i++) {
      doc.setPage(i);
      doc.setDrawColor(...THEME.blue);
      doc.setLineWidth(0.7);
      doc.line(40, pageHeight - 36, pageWidth - 40, pageHeight - 36);

      doc.setFont('helvetica', 'normal');
      doc.setFontSize(8);
      doc.setTextColor(...THEME.muted);
      doc.text('MatchIn — Sistem Pendukung Keputusan Pencocokan Partner Tugas', 40, pageHeight - 22);
      doc.text(`Halaman ${i} dari ${pageCount}`, pageWidth - 40, pageHeight - 22, { align: 'right' });
    }
  }

  // ---------- Helpers ----------
  function statusLabel(rank) {
    if (rank <= 3) return `Rekomendasi Top ${rank}`;
    return 'Kandidat';
  }

  function formatNumber(value) {
    const num = Number(value);
    if (Number.isNaN(num)) return '-';
    return num.toLocaleString('id-ID', { maximumFractionDigits: 2 });
  }

  function truncateText(doc, text, maxWidth) {
    if (doc.getTextWidth(text) <= maxWidth) return text;
    let truncated = text;
    while (doc.getTextWidth(truncated + '…') > maxWidth && truncated.length > 1) {
      truncated = truncated.slice(0, -1);
    }
    return truncated + '…';
  }

  function buildFileName(data) {
    const className = data.class && data.class.class_name ? data.class.class_name : 'Kelas';
    const safeClassName = String(className)
      .normalize('NFKD')
      .replace(/[^\w\s-]/g, '')
      .trim()
      .replace(/\s+/g, '-');
    return `MatchIn_Hasil-SAW_${safeClassName}.pdf`;
  }
});
