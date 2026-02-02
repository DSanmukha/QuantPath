<?php
// frontend/dashboard.php
// Demo-ready dashboard page (self-contained demo chart + list).
session_start();
// If you have login, session user will be shown; otherwise shows "Demo user"
$user = htmlspecialchars($_SESSION['user_name'] ?? 'Demo user', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>QuantPath — Dashboard (Demo)</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg:#071029; --panel:#0f1726; --muted:#9fb0c8;
      --accent:#7c5cff; --accent2:#00d4ff; --radius:12px;
      --font: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue";
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:var(--font);background:linear-gradient(180deg,var(--bg),#04101a);color:#e6f0fb;min-height:100vh}
    .topbar{display:flex;justify-content:space-between;align-items:center;padding:18px 28px}
    .brand{display:flex;align-items:center;gap:12px}
    .logo{width:46px;height:46px;border-radius:10px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-weight:700;color:white}
    .brand-text .app-name{font-weight:700;font-size:18px}
    .brand-text .app-sub{font-size:12px;color:var(--muted)}
    .top-actions{display:flex;gap:10px;align-items:center}
    .container{display:grid;grid-template-columns:320px 1fr 320px;gap:18px;padding:18px;max-width:1200px;margin:0 auto;width:100%}
    .panel{background:linear-gradient(180deg,var(--panel),rgba(255,255,255,0.01));border-radius:var(--radius);padding:16px;border:1px solid rgba(255,255,255,0.04)}
    .panel.full{grid-column:1/-1}
    .panel-title{font-size:14px;color:var(--muted);margin:0 0 8px 0}
    .metric{display:flex;flex-direction:column;gap:6px}
    .metric-label{font-size:12px;color:var(--muted)}
    .metric-value{font-size:28px;font-weight:700}
    .input{width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,0.04);background:transparent;color:inherit;outline:none}
    .row{display:flex;gap:8px;margin-top:12px}
    .btn{padding:10px 14px;border-radius:10px;border:none;background:linear-gradient(90deg,var(--accent),var(--accent2));color:white;font-weight:600;cursor:pointer}
    .btn.ghost{background:transparent;border:1px solid rgba(255,255,255,0.04);color:var(--muted)}
    .hint{font-size:12px;color:var(--muted);margin-top:12px}
    .list{display:flex;flex-direction:column;gap:10px;max-height:56vh;overflow:auto;padding-right:6px}
    .sim-item{display:flex;justify-content:space-between;align-items:center;padding:10px;border-radius:10px;background:linear-gradient(180deg,rgba(255,255,255,0.01),transparent);border:1px solid rgba(255,255,255,0.02);cursor:pointer;transition:transform .12s}
    .sim-item:hover{transform:translateY(-4px)}
    .sim-meta{font-size:12px;color:var(--muted)}
    .chart-wrap{height:360px;border-radius:10px;overflow:hidden;padding:8px;background:linear-gradient(180deg,rgba(255,255,255,0.01),transparent);border:1px solid rgba(255,255,255,0.02)}
    .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:12px}
    .stat{padding:10px;border-radius:10px;background:linear-gradient(180deg,rgba(255,255,255,0.01),transparent);border:1px solid rgba(255,255,255,0.02)}
    .stat-label{font-size:12px;color:var(--muted)}
    .stat-value{font-size:18px;font-weight:700}
    .details{background:rgba(0,0,0,0.12);padding:12px;border-radius:8px;color:#cfe7ff;max-height:220px;overflow:auto}
    @media (max-width:1000px){.container{grid-template-columns:1fr}.chart-wrap{height:280px}}
  </style>

  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <div class="logo">Q</div>
      <div class="brand-text">
        <div class="app-name">QuantPath</div>
        <div class="app-sub">Monte Carlo & GBM</div>
      </div>
    </div>
    <div class="top-actions">
      <div class="greet">Hello, <?php echo $user; ?></div>
      <a class="btn ghost" href="/quantpath/frontend/simulation.php">Simulation</a>
      <a class="btn ghost" href="/quantpath/backend/logout.php">Logout</a>
    </div>
  </header>

  <main class="container">
    <aside class="panel">
      <h3 class="panel-title">Overview</h3>
      <div class="metric" style="margin-bottom:12px">
        <div class="metric-label">Saved simulations</div>
        <div id="sim-count" class="metric-value">—</div>
      </div>

      <input id="filter" class="input" placeholder="Search symbol or model" />

      <div class="row" style="margin-top:12px">
        <button id="refresh" class="btn">Refresh</button>
        <button id="demoToggle" class="btn ghost">Use dummy data</button>
      </div>

      <p class="hint">Click a saved simulation to view details and plot results.</p>
    </aside>

    <section class="panel">
      <div style="display:flex;justify-content:space-between;align-items:center">
        <div>
          <h3 id="chart-title">Simulation Chart</h3>
          <div id="chart-sub" class="hint">Select a saved simulation to plot</div>
        </div>
        <div class="hint">Preview</div>
      </div>

      <div class="chart-wrap" style="margin-top:12px">
        <canvas id="mainChart"></canvas>
      </div>

      <div class="stats">
        <div class="stat"><div class="stat-label">Expected</div><div id="expected" class="stat-value">—</div></div>
        <div class="stat"><div class="stat-label">Median</div><div id="median" class="stat-value">—</div></div>
        <div class="stat"><div class="stat-label">95% CI</div><div id="ci" class="stat-value">—</div></div>
      </div>
    </section>

    <aside class="panel">
      <h3 class="panel-title">Saved Simulations</h3>
      <div id="sim-list" class="list">Loading…</div>
    </aside>

    <section class="panel full">
      <h4>Details</h4>
      <pre id="details" class="details">Select a simulation to see JSON details here.</pre>
    </section>
  </main>

  <script>
  // Self-contained demo script for dashboard page
  (function () {
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

    function computeSummary(paths) {
      const horizon = paths.map(p => p[p.length - 1]);
      const expected = horizon.reduce((a, b) => a + b, 0) / horizon.length;
      const sorted = horizon.slice().sort((a, b) => a - b);
      const median = sorted[Math.floor(sorted.length / 2)];
      const lower = sorted[Math.floor(sorted.length * 0.025)] ?? sorted[0];
      const upper = sorted[Math.floor(sorted.length * 0.975)] ?? sorted[sorted.length - 1];
      return { expected, median, lower, upper };
    }

    const demo = [
      { id: 1, stock_symbol: 'AAPL', model_used: 'GBM', results_json: JSON.stringify({ paths: genPaths(80, 252, 150, 0.05, 0.2) }), created_at: '2026-02-01' },
      { id: 2, stock_symbol: 'MSFT', model_used: 'GBM', results_json: JSON.stringify({ paths: genPaths(60, 252, 320, 0.04, 0.18) }), created_at: '2026-01-28' },
      { id: 3, stock_symbol: 'TSLA', model_used: 'GBM', results_json: JSON.stringify({ paths: genPaths(50, 252, 220, 0.06, 0.35) }), created_at: '2026-01-20' }
    ];

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
    let usingDemo = true;

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

    function updateChart(paths) {
      const labels = Array.from({ length: paths[0].length }, (_, i) => i);
      const sample = paths.slice(0, 40);
      const datasets = sample.map((p, idx) => ({
        label: `Path ${idx + 1}`,
        data: p,
        borderColor: `rgba(99,102,241,${0.12 + (idx % 6) * 0.02})`,
        pointRadius: 0,
        borderWidth: 1,
        fill: false
      }));
      const meanPath = labels.map((_, t) => paths.reduce((acc, p) => acc + p[t], 0) / paths.length);
      datasets.push({ label: 'Mean', data: meanPath, borderColor: '#ef4444', borderWidth: 2, pointRadius: 0 });

      if (!chart) chart = createChart();
      chart.data.labels = labels;
      chart.data.datasets = datasets;
      chart.update();

      const summary = computeSummary(paths);
      expectedEl.textContent = summary.expected.toFixed(2);
      medianEl.textContent = summary.median.toFixed(2);
      ciEl.textContent = `${summary.lower.toFixed(2)} — ${summary.upper.toFixed(2)}`;
    }

    function renderList(list) {
      simListEl.innerHTML = '';
      if (!list.length) { simListEl.innerHTML = '<div class="hint">No saved simulations</div>'; simCountEl.textContent = '0'; return; }
      simCountEl.textContent = String(list.length);
      list.forEach(s => {
        const btn = document.createElement('button');
        btn.className = 'sim-item';
        btn.innerHTML = `<div><div style="font-weight:700">${s.stock_symbol}</div><div class="sim-meta">${s.model_used}</div></div><div class="sim-meta">${s.created_at}</div>`;
        btn.addEventListener('click', () => selectSim(s));
        simListEl.appendChild(btn);
      });
    }

    function selectSim(sim) {
      detailsEl.textContent = JSON.stringify(sim, null, 2);
      const results = JSON.parse(sim.results_json || '{}');
      if (Array.isArray(results.paths) && results.paths.length) {
        chartTitle.textContent = `${sim.stock_symbol} — ${sim.model_used}`;
        chartSub.textContent = `Saved: ${sim.created_at}`;
        updateChart(results.paths);
      } else {
        if (chart) { chart.destroy(); chart = null; }
        chartTitle.textContent = `${sim.stock_symbol} — ${sim.model_used}`;
        chartSub.textContent = `Saved: ${sim.created_at}`;
        expectedEl.textContent = medianEl.textContent = ciEl.textContent = '—';
      }
    }

    async function loadSims() {
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
          selectSim(sims[0]);
          return;
        }
        throw new Error('No sims');
      } catch (e) {
        usingDemo = true;
        sims = demo;
        renderList(sims);
        selectSim(sims[0]);
        const note = document.createElement('div'); note.className = 'hint'; note.style.color = '#fca5a5'; note.textContent = 'Showing demo data';
        if (!simListEl.querySelector('.demo-note')) { note.classList.add('demo-note'); simListEl.insertBefore(note, simListEl.firstChild); }
      }
    }

    refreshBtn.addEventListener('click', () => loadSims());
    demoToggle.addEventListener('click', () => {
      usingDemo = !usingDemo;
      if (usingDemo) { sims = demo; renderList(sims); selectSim(sims[0]); demoToggle.textContent = 'Using demo'; demoToggle.classList.add('active'); }
      else { demoToggle.textContent = 'Use dummy data'; demoToggle.classList.remove('active'); loadSims(); }
    });

    filterEl.addEventListener('input', () => {
      const q = filterEl.value.trim().toLowerCase();
      if (!q) renderList(sims);
      else renderList(sims.filter(s => (s.stock_symbol || '').toLowerCase().includes(q) || (s.model_used || '').toLowerCase().includes(q)));
    });

    chart = createChart();
    loadSims();
    window._dashboardDemo = { loadSims, sims };
  })();
  </script>
</body>
</html>