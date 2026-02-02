// File: quantpath/assets/js/simulation.js
// Simulation page script: runs a browser Monte Carlo demo, plots chart, shows stats.

(function () {
  // --- Helpers ---
  function randNormal() {
    let u = 0, v = 0;
    while (u === 0) u = Math.random();
    while (v === 0) v = Math.random();
    return Math.sqrt(-2 * Math.log(u)) * Math.cos(2 * Math.PI * v);
  }

  function gbmPath(S0, mu, sigma, T, steps) {
    const dt = T / steps;
    const p = [S0];
    for (let i = 1; i <= steps; i++) {
      const z = randNormal();
      p.push(p[i - 1] * Math.exp((mu - 0.5 * sigma * sigma) * dt + sigma * Math.sqrt(dt) * z));
    }
    return p;
  }

  function monteCarlo(S0, mu, sigma, T, steps, n) {
    const all = [];
    for (let i = 0; i < n; i++) all.push(gbmPath(S0, mu, sigma, T, steps));
    return all;
  }

  function createChart(ctx) {
    return new Chart(ctx, {
      type: 'line',
      data: { labels: [], datasets: [] },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: true } },
        scales: { x: { display: true }, y: { display: true } }
      }
    });
  }

  function updateChart(chart, allPaths) {
    const labels = Array.from({ length: allPaths[0].length }, (_, i) => i);
    const sample = allPaths.slice(0, 40);
    const datasets = sample.map((p, idx) => ({
      label: `Path ${idx + 1}`,
      data: p,
      borderColor: `rgba(99,102,241,${0.12 + (idx % 6) * 0.02})`,
      pointRadius: 0,
      borderWidth: 1,
      fill: false
    }));
    const meanPath = labels.map((_, t) => allPaths.reduce((acc, p) => acc + p[t], 0) / allPaths.length);
    datasets.push({ label: 'Mean', data: meanPath, borderColor: '#ef4444', borderWidth: 2, pointRadius: 0 });

    chart.data.labels = labels;
    chart.data.datasets = datasets;
    chart.update();

    // summary
    const horizon = allPaths.map(p => p[p.length - 1]);
    const expected = horizon.reduce((a, b) => a + b, 0) / horizon.length;
    const sorted = horizon.slice().sort((a, b) => a - b);
    const median = sorted[Math.floor(sorted.length / 2)];
    const lower = sorted[Math.floor(sorted.length * 0.025)] || sorted[0];
    const upper = sorted[Math.floor(sorted.length * 0.975)] || sorted[sorted.length - 1];

    document.getElementById('sim-expected').textContent = expected.toFixed(2);
    document.getElementById('sim-median').textContent = median.toFixed(2);
    document.getElementById('sim-ci').textContent = `${lower.toFixed(2)} — ${upper.toFixed(2)}`;
  }

  // --- UI wiring ---
  function init() {
    const el = id => document.getElementById(id);
    const symbolEl = el('symbol');
    const s0El = el('s0');
    const muEl = el('mu');
    const sigmaEl = el('sigma');
    const horizonEl = el('horizon');
    const pathsEl = el('paths');
    const fetchBtn = el('fetch');
    const runBtn = el('run');
    const saveBtn = el('save');
    const canvas = el('simChart');

    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const chart = createChart(ctx);

    fetchBtn?.addEventListener('click', () => {
      // demo fetch: set S0 to a friendly value
      alert('Demo fetch: setting initial price to 100');
      s0El.value = 100;
    });

    runBtn?.addEventListener('click', () => {
      const S0 = parseFloat(s0El.value || 100);
      const mu = parseFloat(muEl.value || 0.05);
      const sigma = parseFloat(sigmaEl.value || 0.2);
      const T = parseFloat(horizonEl.value || 1);
      const steps = Math.max(1, Math.round(252 * T));
      const n = Math.max(1, parseInt(pathsEl.value || 200, 10));

      runBtn.disabled = true;
      runBtn.textContent = 'Running...';

      setTimeout(() => {
        const all = monteCarlo(S0, mu, sigma, T, steps, n);
        updateChart(chart, all);
        runBtn.disabled = false;
        runBtn.textContent = 'Run';
      }, 50);
    });

    saveBtn?.addEventListener('click', () => {
      alert('Save is disabled in demo mode. Use the API to persist simulations.');
    });
  }

  document.addEventListener('DOMContentLoaded', init);
  window.simulation = { monteCarlo, gbmPath };
})();