// Holt Double Exponential Smoothing — SPPG Tanggetada
export function holt(series, alpha, beta) {
  const n = series.length;
  if (n < 2) throw new Error('Minimal 2 periode data');
  const L = [series[0]];
  const T = [series[1] - series[0]];
  const F = [null]; // F[0] tidak ada (butuh data sebelumnya)
  let sse = 0, sumPE = 0;
  for (let t = 1; t < n; t++) {
    F[t] = L[t - 1] + T[t - 1];
    L[t] = alpha * series[t] + (1 - alpha) * (L[t - 1] + T[t - 1]);
    T[t] = beta * (L[t] - L[t - 1]) + (1 - beta) * T[t - 1];
    const e = series[t] - F[t];
    sse += e * e;
    sumPE += (e / series[t]) * 100;
  }
  const mape = F.slice(1).reduce((s, f, i) => s + Math.abs((series[i + 1] - f) / series[i + 1]), 0) / (n - 1) * 100;
  const mpe = sumPE / (n - 1);
  const forecastNext = L[n - 1] + T[n - 1];
  return { L, T, F, mape, mpe, sse, forecastNext };
}

// grid search alpha 0.01..0.99 step 0.01 x beta sama -> MAPE terkecil
export function optimize(series) {
  let best = { alpha: 0.1, beta: 0.1, mape: Infinity };
  for (let a = 0.01; a <= 0.99; a += 0.01) {
    for (let b = 0.01; b <= 0.99; b += 0.01) {
      const r = holt(series, +a.toFixed(2), +b.toFixed(2));
      if (r.mape < best.mape) best = { alpha: +a.toFixed(2), beta: +b.toFixed(2), mape: r.mape };
    }
  }
  return best;
}
