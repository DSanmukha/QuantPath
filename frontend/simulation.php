<?php
// frontend/simulation.php
// Demo-ready simulation page (self-contained Monte Carlo demo).
session_start();
$user = htmlspecialchars($_SESSION['user_name'] ?? 'Demo user', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>QuantPath — Simulation (Demo)</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    :root{--bg:#071029;--panel:#0f1726;--muted:#9fb0c8;--accent:#7c5cff;--accent2:#00d4ff;--radius:12px;--font:'Inter',system-ui;}
    *{box-sizing:border-box} body{margin:0;font-family:var(--font);background:linear-gradient(180deg,var(--bg),#04101a);color:#e6f0fb;min-height:100vh}
    .topbar{display:flex;justify-content:space-between;align-items:center;padding:18px 28px}
    .brand{display:flex;align-items:center;gap:12px}
    .logo{width:46px;height:46px;border-radius:10px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-weight:700;color:white}
    .container{max-width:1100px;margin:18px auto;padding:18px;display:grid;grid-template-columns:360px 1fr;gap:18px}
    .panel{background:linear-gradient(180deg,var(--panel),rgba(255,255,255,0.01));border-radius:var(--radius);padding:16px;border:1px solid rgba(255,255,255,0.04)}
    .input{width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,0.04);background:transparent;color:inherit;outline:none}
    .row{display:flex;gap:8px;margin-top:12px}
    .btn{padding:10px 14px;border-radius:10px;border:none;background:linear-gradient(90deg,var(--accent),var(--accent2));color:white;font-weight:600;cursor:pointer}
    .btn.ghost{background:transparent;border:1px solid rgba(255,255,255,0.04);color:var(--muted)}
    .chart-wrap{height:420px;border-radius:10px;overflow:hidden;padding:8px;background:linear-gradient(180deg,rgba(255,255,255,0.01),transparent);border:1px solid rgba(255,255,255,0.02)}
    .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:12px}
    .stat{padding:10px;border-radius:10px;background:linear-gradient(180deg,rgba(255,255,255,0.01),transparent);border:1px solid rgba(255,255,255,0.02)}
    .stat-label{font-size:12px;color:var(--muted)}
    .stat-value{font-size:18px;font-weight:700}
    @media (max-width:1000px){.container{grid-template-columns:1fr}.chart-wrap{height:300px}}
  </style>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <div class="logo">Q</div>
      <div class="brand-text">
        <div class="app-name">QuantPath</div>
        <div class="app-sub">Simulation</div>
      </div>
    </div>
    <div class="top-actions">
      <div class="greet">Hello, <?php echo $user; ?></div>
      <a class="btn ghost" href="/quantpath/frontend/dashboard.php">Dashboard</a>
      <a class="btn ghost" href="/quantpath/backend/logout.php">Logout</a>
    </div>
  </header>

  <main class="container">
    <section class="panel">
      <h3>Monte Carlo Controls</h3>

      <label class="label">Ticker</label>
      <input id="symbol" class="input" placeholder="AAPL" />

      <label class="label">Initial price (S0)</label>
      <input id="s0" class="input" type="number" value="100" />

      <label class="label">Drift μ</label>
      <input id="mu" class="input" type="number" step="0.0001" value="0.05" />

      <label class="label">Volatility σ</label>
      <input id="sigma" class="input" type="number" step="0.0001" value="0.2" />

      <label class="label">Horizon (years)</label>
      <input id="horizon" class="input" type="number" step="0.1" value="1" />

      <label class="label">Paths</label>
      <input id="paths" class="input" type="number" value="200" />

      <div class="row">
        <button id="fetch" class="btn ghost">Fetch (demo)</button>
        <button id="run" class="btn">Run</button>
        <button id="save" class="btn ghost">Save</button>
      </div>

      <p class="hint">This page runs a browser demo until your API is wired.</p>
    </section>

    <section class="panel">
      <h3>Simulation Preview</h3>
      <div class="chart-wrap" style="margin-top:12px">
        <canvas id="simChart"></canvas>
      </div>

      <div class="stats">
        <div class="stat"><div class="stat-label">Expected</div><div id="sim-expected" class="stat-value">—</div></div>
        <div class="stat"><div class="stat-label">Median</div><div id="sim-median" class="stat-value">—</div></div>
        <div class="stat"><div class="stat-label">95% CI</div><div id="sim-ci" class="stat-value">—</div></div>
      </div>
    </section>
  </main>

  <script>
  // Self-contained simulation demo script
  (function () {
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

    function init() {
      const el = id => document.getElementById(id);
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
  })();
  </script>
</body>
</html>