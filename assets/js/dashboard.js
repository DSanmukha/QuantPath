// File: quantpath/assets/js/dashboard.js
// Simple dashboard script: tries API, falls back to demo data, renders Chart.js chart and stats.

(function () {
  // --- Helpers ---
  function randNormal() {
    let u = 0, v = 0;
    while (u === 0) u = Math.random();
    while (v === 0) v = Math.random();
    return Math.sqrt(-2 * Math.log(u)) * Math.cos(2 * Math.PI * v);
  }

  function genPaths(nPaths, steps, S0, mu, sigma) {
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

  function safeParse(text) {
    try { return JSON.parse(text || '{}'); } catch { return {}; }
  }

  // --- Demo data (used if API not available) ---
  const demoSims = [
    {
      id: 1,
      stock_symbol: 'AAPL',
      model_used: 'GBM',
      parameters: JSON.stringify({ S0: 150, mu: 0.05, sigma: 0.2, paths: 100 }),
      results_json: JSON.stringify({ paths: genPaths(100, 252, 150, 0.05, 0.2) }),
      created_at: '2026-02-01 09:12:00'
    },
    {
      id: 2,
      stock_symbol: 'MSFT',
      model_used: 'GBM',
      parameters: JSON.stringify({ S0: 320, mu: 0.04, sigma: 0.18, paths: 80 }),
      results_json: JSON.stringify({ paths: genPaths(80, 252, 320, 0.04, 0.18) }),
      created_at: '2026-01-28 14:05:00'
    },
    {
      id: 3,
      stock_symbol: 'TSLA',
      model_used: 'GBM',
      parameters: JSON.stringify({ S0: 220, mu: 0.06, sigma: 0.35, paths: 60 }),
      results_json: JSON.stringify({ paths: genPaths(60, 252, 220, 0.06, 0.35) }),
      created_at: '2026-01-20 11:30:00'
    }
  ];

  // --- DOM refs (IDs used in the HTML) ---
  const simListEl = document.getElementById('sim-list');
  const simCountEl = document.getElementById('sim-count');
  const filterEl = document.getElementById('filter');
  const refreshBtn = document.getElementById('refresh');
  const demoToggle = document.getElementById('demoToggle');
  const detailsEl = document.getElementById('details');
  const chartTitle = document.getElementById('chart-title');
  const chartSub = document.getElementById('chart-sub');
  const expectedEl = document.getElementById('expected');
  const medianEl = document.getElementById('median');
  const ciEl = document.getElementById('ci');
  const canvas = document.getElementById('mainChart');

  let chart = null;
  let sims = [];
  let usingDemo = false;

  // --- Chart helpers ---
  function createChart() {
    const ctx = canvas.getContext('2d');
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

  function updateChartWithPaths(allPaths) {
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

    // mean path
    const meanPath = labels.map((_, t) => {
      const vals = allPaths.map(p => p[t]);
      return vals.reduce((a, b) => a + b, 0) / vals.length;
    });
    datasets.push({ label: 'Mean', data: meanPath, borderColor: '#ef4444', borderWidth: 2, pointRadius: 0 });

    if (!chart) chart = createChart();
    chart.data.labels = labels;
    chart.data.datasets = datasets;
    chart.update();

    // summary stats at horizon
    const horizonVals = allPaths.map(p => p[p.length - 1]);
    const expected = horizonVals.reduce((a, b) => a + b, 0) / horizonVals.length;
    const sorted = horizonVals.slice().sort((a, b) => a - b);
    const median = sorted[Math.floor(sorted.length / 2)];
    const lower = sorted[Math.floor(sorted.length * 0.025)] || sorted[0];
    const upper = sorted[Math.floor(sorted.length * 0.975)] || sorted[sorted.length - 1];

    expectedEl.textContent = expected.toFixed(2);
    medianEl.textContent = median.toFixed(2);
    ciEl.textContent = `${lower.toFixed(2)} — ${upper.toFixed(2)}`;
  }

  // --- UI rendering ---
  function renderList(list) {
    simListEl.innerHTML = '';
    if (!list.length) {
      simListEl.innerHTML = '<div class="hint">No saved simulations</div>';
      simCountEl.textContent = '0';
      return;
    }
    simCountEl.textContent = String(list.length);
    list.forEach(s => {
      const btn = document.createElement('button');
      btn.className = 'sim-item';
      btn.innerHTML = `<div>
          <div style="font-weight:700">${escapeHtml(s.stock_symbol || '—')}</div>
          <div class="sim-meta">${escapeHtml(s.model_used || '')}</div>
        </div>
        <div class="sim-meta">${escapeHtml(s.created_at || '')}</div>`;
      btn.addEventListener('click', () => selectSimulation(s));
      simListEl.appendChild(btn);
    });
  }

  function selectSimulation(sim) {
    detailsEl.textContent = JSON.stringify(sim, null, 2);
    const results = safeParse(sim.results_json);
    if (Array.isArray(results.paths) && results.paths.length) {
      chartTitle.textContent = `${sim.stock_symbol} — ${sim.model_used}`;
      chartSub.textContent = `Saved: ${sim.created_at}`;
      updateChartWithPaths(results.paths);
    } else if (Array.isArray(results.mean) && results.mean.length) {
      // plot mean only
      if (!chart) chart = createChart();
      chart.data.labels = results.mean.map((_, i) => i);
      chart.data.datasets = [{ label: 'Mean', data: results.mean, borderColor: '#ef4444', borderWidth: 2, pointRadius: 0 }];
      chart.update();
      expectedEl.textContent = results.expected ?? '—';
      medianEl.textContent = results.median ?? '—';
      ciEl.textContent = results.ci ?? '—';
    } else {
      if (chart) { chart.destroy(); chart = null; }
      chartTitle.textContent = `${sim.stock_symbol} — ${sim.model_used}`;
      chartSub.textContent = `Saved: ${sim.created_at}`;
      expectedEl.textContent = medianEl.textContent = ciEl.textContent = '—';
    }
  }

  // --- Load from API or fallback to demo ---
  async function loadSimulations() {
    simListEl.innerHTML = '<div class="hint">Loading…</div>';
    expectedEl.textContent = medianEl.textContent = ciEl.textContent = '—';
    try {
      const res = await fetch('/quantpath/backend/get_simulations.php', { cache: 'no-store' });
      if (!res.ok) throw new Error('API error');
      const data = await res.json();
      if (data && Array.isArray(data.simulations) && data.simulations.length) {
        sims = data.simulations;
        usingDemo = false;
        renderList(sims);
        selectSimulation(sims[0]);
        return;
      }
      throw new Error('No simulations from API');
    } catch (err) {
      // fallback to demo
      usingDemo = true;
      sims = demoSims;
      renderList(sims);
      selectSimulation(sims[0]);
      // show a small note
      const note = document.createElement('div');
      note.className = 'hint demo-note';
      note.style.color = '#fca5a5';
      note.style.marginTop = '8px';
      note.textContent = 'Showing demo data (API not available).';
      if (!simListEl.querySelector('.demo-note')) simListEl.insertBefore(note, simListEl.firstChild);
    }
  }

  // --- Events ---
  refreshBtn.addEventListener('click', () => loadSimulations());
  demoToggle.addEventListener('click', () => {
    usingDemo = !usingDemo;
    if (usingDemo) {
      sims = demoSims;
      renderList(sims);
      selectSimulation(sims[0]);
      demoToggle.textContent = 'Using demo';
      demoToggle.classList.add('active');
    } else {
      demoToggle.textContent = 'Use dummy data';
      demoToggle.classList.remove('active');
      loadSimulations();
    }
  });

  filterEl.addEventListener('input', () => {
    const q = filterEl.value.trim().toLowerCase();
    if (!q) renderList(sims);
    else renderList(sims.filter(s => (s.stock_symbol || '').toLowerCase().includes(q) || (s.model_used || '').toLowerCase().includes(q)));
  });

  // --- Utilities ---
  function escapeHtml(s) {
    return String(s || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
  }

  // --- Init ---
  chart = createChart();
  loadSimulations();

  // expose for debugging
  window._dashboard = { loadSimulations, sims, usingDemo: () => usingDemo };
})();