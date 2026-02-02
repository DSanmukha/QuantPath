// File: quantpath/assets/js/chart-quick.js
// Self-contained: loads Chart.js if needed, then draws a demo chart in <canvas id="mainChart">.
// Copy-paste this entire file into your project and include it on the page.
// No external setup required beyond having a <canvas id="mainChart"></canvas> in the HTML.

(function () {
  // --- Config ---
  const CANVAS_ID = 'mainChart';
  const CHART_JS_SRC = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';

  // --- Utilities ---
  function loadScript(src) {
    return new Promise((resolve, reject) => {
      if (document.querySelector(`script[src="${src}"]`)) return resolve();
      const s = document.createElement('script');
      s.src = src;
      s.onload = () => resolve();
      s.onerror = () => reject(new Error('Failed to load ' + src));
      document.head.appendChild(s);
    });
  }

  function randNormal() {
    let u = 0, v = 0;
    while (u === 0) u = Math.random();
    while (v === 0) v = Math.random();
    return Math.sqrt(-2 * Math.log(u)) * Math.cos(2 * Math.PI * v);
  }

  function genDemoPaths(nPaths = 60, steps = 252, S0 = 100, mu = 0.05, sigma = 0.2) {
    const T = 1;
    const dt = T / steps;
    const all = [];
    for (let p = 0; p < nPaths; p++) {
      const path = [S0];
      for (let i = 1; i <= steps; i++) {
        const z = randNormal();
        const prev = path[i - 1];
        path.push(prev * Math.exp((mu - 0.5 * sigma * sigma) * dt + sigma * Math.sqrt(dt) * z));
      }
      all.push(path);
    }
    return all;
  }

  function computeSummary(paths) {
    const horizon = paths.map(p => p[p.length - 1]);
    const expected = horizon.reduce((a, b) => a + b, 0) / horizon.length;
    const sorted = horizon.slice().sort((a, b) => a - b);
    const median = sorted[Math.floor(sorted.length / 2)];
    const lower = sorted[Math.floor(sorted.length * 0.025)] ?? sorted[0];
    const upper = sorted[Math.floor(sorted.length * 0.975)] ?? sorted[sorted.length - 1];
    return { expected, median, lower, upper };
  }

  // --- Chart rendering ---
  let chartInstance = null;

  function createChartInstance(ctx) {
    return new Chart(ctx, {
      type: 'line',
      data: { labels: [], datasets: [] },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: true, labels: { boxWidth: 10, padding: 12 } }
        },
        scales: {
          x: { display: true, title: { display: true, text: 'Step' } },
          y: { display: true, title: { display: true, text: 'Price' } }
        }
      }
    });
  }

  function renderPathsOnChart(allPaths, title = 'Demo Monte Carlo') {
    if (!allPaths || !allPaths.length) return;
    const labels = Array.from({ length: allPaths[0].length }, (_, i) => i);
    const sample = allPaths.slice(0, 40);
    const datasets = sample.map((p, idx) => ({
      label: `Path ${idx + 1}`,
      data: p,
      borderColor: `rgba(99,102,241,${0.12 + (idx % 6) * 0.02})`,
      pointRadius: 0,
      borderWidth: 1,
      fill: false,
      tension: 0.12
    }));

    const meanPath = labels.map((_, t) => {
      const vals = allPaths.map(p => p[t]);
      return vals.reduce((a, b) => a + b, 0) / vals.length;
    });
    datasets.push({
      label: 'Mean',
      data: meanPath,
      borderColor: '#ef4444',
      borderWidth: 2,
      pointRadius: 0,
      tension: 0.18
    });

    if (!chartInstance) {
      const canvas = document.getElementById(CANVAS_ID);
      if (!canvas) {
        console.warn('Canvas with id "' + CANVAS_ID + '" not found.');
        return;
      }
      const ctx = canvas.getContext('2d');
      chartInstance = createChartInstance(ctx);
    }

    chartInstance.data.labels = labels;
    chartInstance.data.datasets = datasets;
    chartInstance.options.plugins.title = { display: true, text: title, padding: { top: 6, bottom: 6 }, color: '#e6eef8', font: { size: 16, weight: '600' } };
    chartInstance.update();
  }

  // --- Public quick demo function ---
  async function drawDemo(options = {}) {
    const { paths = 80, steps = 252, S0 = 100, mu = 0.05, sigma = 0.2, title = 'Demo Monte Carlo' } = options;
    if (typeof Chart === 'undefined') {
      await loadScript(CHART_JS_SRC);
    }
    const demo = genDemoPaths(paths, steps, S0, mu, sigma);
    renderPathsOnChart(demo, title);
    const summary = computeSummary(demo);
    return { demo, summary };
  }

  // --- Auto-run demo on load ---
  (async function autoRun() {
    try {
      if (typeof Chart === 'undefined') await loadScript(CHART_JS_SRC);
      // small delay to ensure canvas sizing
      await new Promise(r => setTimeout(r, 60));
      await drawDemo(); // default demo
      // expose for console debugging
      window._chartQuick = { drawDemo, renderPathsOnChart, genDemoPaths, computeSummary };
      console.log('chart-quick: demo rendered. Use window._chartQuick.drawDemo({...}) to redraw.');
    } catch (err) {
      console.error('chart-quick error:', err);
    }
  })();
})();