/**
 * SAW (Simple Additive Weighting) Calculator — fallback client-side
 */

function calculateSAW(alternatives, criteria) {
  if (!alternatives || alternatives.length === 0) {
    return { ranking: [], top3: [], normalization: { maxValues: {}, rows: [] }, weighted: [] };
  }

  const maxValues = {};
  criteria.forEach((c) => {
    maxValues[c.code] = Math.max(
      ...alternatives.map((alt) => alt.scores[c.code] ?? 0),
      0.0001
    );
  });

  const normalizationRows = [];
  const weightedRows = [];
  const processed = [];

  alternatives.forEach((alt) => {
    const normalized = {};
    const weighted = {};
    let totalScore = 0;

    criteria.forEach((c) => {
      const raw = alt.scores[c.code] ?? 0;
      const normVal = raw / maxValues[c.code];
      normalized[c.code] = round(normVal, 4);
      weighted[c.code] = round(normVal * c.weight, 4);
      totalScore += normVal * c.weight;
    });

    normalizationRows.push({ id: alt.id, name: alt.name, npm: alt.npm, normalized });
    weightedRows.push({ id: alt.id, name: alt.name, npm: alt.npm, weighted, totalScore: round(totalScore, 4) });

    processed.push({
      ...alt,
      normalized,
      weighted,
      totalScore: round(totalScore, 4),
      percentage: round(totalScore * 100, 2),
    });
  });

  const ranking = [...processed].sort((a, b) => b.totalScore - a.totalScore);
  ranking.forEach((item, index) => {
    item.rank = index + 1;
  });

  return {
    ranking,
    top3: ranking.slice(0, 3),
    normalization: { maxValues, rows: normalizationRows },
    weighted: weightedRows,
  };
}

function round(value, decimals = 2) {
  const factor = Math.pow(10, decimals);
  return Math.round(value * factor) / factor;
}
