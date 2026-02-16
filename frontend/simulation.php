<?php
// frontend/simulation.php
session_start();
require_once __DIR__ . '/../private_config/config.php';

$user_id = $_SESSION['user_id'] ?? '';
$user_name = $_SESSION['user_name'] ?? 'Guest';
$conn->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Simulation — QuantPath</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <link rel="stylesheet" href="/quantpath/assets/css/tailwind.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-900 text-slate-100">
  <!-- Header -->
  <header class="sticky top-0 z-50 bg-slate-900/80 backdrop-blur-lg border-b border-white/5">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <a href="/quantpath/frontend/index.html" class="flex items-center gap-3 group">
        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-lg group-hover:shadow-lg group-hover:shadow-indigo-500/50 transition">Q</div>
        <div class="text-xl font-bold">QuantPath</div>
      </a>
      <nav class="flex items-center gap-4">
        <?php if ($user_id): ?>
          <div class="text-sm text-slate-300">Welcome, <span class="font-semibold text-indigo-300"><?php echo htmlspecialchars($user_name); ?></span></div>
          <a href="/quantpath/frontend/dashboard.php" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg transition">Dashboard</a>
          <a href="/quantpath/backend/logout.php" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg transition">Logout</a>
        <?php else: ?>
          <a href="/quantpath/frontend/login.html" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">Log in</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-6 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Sidebar: Input Form -->
      <div class="lg:col-span-1">
        <section class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6 sticky top-24">
          <h2 class="text-xl font-bold mb-4">Parameters</h2>
          
          <form id="sim-form" class="space-y-3">
            <div>
              <label class="block text-sm text-slate-400 font-medium mb-1">Stock Ticker</label>
              <input id="ticker" placeholder="AAPL" class="w-full bg-slate-800/50 border border-slate-700 px-3 py-2 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            <div>
              <label class="block text-sm text-slate-400 font-medium mb-1">Initial Price (S0)</label>
              <input id="s0" placeholder="150.00" type="number" step="0.01" class="w-full bg-slate-800/50 border border-slate-700 px-3 py-2 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            <div>
              <label class="block text-sm text-slate-400 font-medium mb-1">Drift (μ)</label>
              <input id="mu" placeholder="0.05" type="number" step="0.01" class="w-full bg-slate-800/50 border border-slate-700 px-3 py-2 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            <div>
              <label class="block text-sm text-slate-400 font-medium mb-1">Volatility (σ)</label>
              <input id="sigma" placeholder="0.2" type="number" step="0.01" class="w-full bg-slate-800/50 border border-slate-700 px-3 py-2 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            <div>
              <label class="block text-sm text-slate-400 font-medium mb-1">Paths</label>
              <input id="paths" placeholder="200" type="number" class="w-full bg-slate-800/50 border border-slate-700 px-3 py-2 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            <div>
              <label class="block text-sm text-slate-400 font-medium mb-1">Horizon (years)</label>
              <input id="horizon" placeholder="1" type="number" step="0.1" class="w-full bg-slate-800/50 border border-slate-700 px-3 py-2 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            <div class="flex flex-col gap-2 pt-2">
              <button type="button" id="fetch-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition">📊 Fetch Data</button>
              <button type="button" id="run-btn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold transition">▶ Run</button>
              <?php if ($user_id): ?>
              <button type="button" id="save-btn" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition">💾 Save</button>
              <?php else: ?>
              <div class="p-2 bg-yellow-500/10 border border-yellow-500/30 rounded-lg text-xs text-yellow-300 text-center">Log in to save</div>
              <?php endif; ?>
            </div>
          </form>
        </section>
      </div>

      <!-- Main Content: Charts & Results -->
      <div class="lg:col-span-2">
        <section class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6 shadow-xl">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Results</h3>
            <button id="export-btn" class="px-3 py-1 bg-green-600/50 hover:bg-green-600 text-sm text-white rounded-lg transition" style="display:none;">↓ Export</button>
          </div>

          <!-- Chart -->
          <div class="bg-slate-800/30 border border-slate-700/50 rounded-lg p-4 mb-4" style="height: 300px;">
            <canvas id="pathChart"></canvas>
          </div>

          <!-- Distribution Chart -->
          <div class="bg-slate-800/30 border border-slate-700/50 rounded-lg p-4 mb-4" style="height: 250px;">
            <canvas id="distChart"></canvas>
          </div>

          <!-- Statistics Cards -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-slate-800/50 border border-slate-700/50 rounded-lg p-3 text-center">
              <div class="text-xs text-slate-400 mb-1">Expected Price</div>
              <div id="expected" class="text-lg font-bold text-indigo-300">—</div>
            </div>
            <div class="bg-slate-800/50 border border-slate-700/50 rounded-lg p-3 text-center">
              <div class="text-xs text-slate-400 mb-1">Median Price</div>
              <div id="median" class="text-lg font-bold text-green-300">—</div>
            </div>
            <div class="bg-slate-800/50 border border-slate-700/50 rounded-lg p-3 text-center">
              <div class="text-xs text-slate-400 mb-1">Std Dev</div>
              <div id="stddev" class="text-lg font-bold text-orange-300">—</div>
            </div>
            <div class="bg-slate-800/50 border border-slate-700/50 rounded-lg p-3 text-center">
              <div class="text-xs text-slate-400 mb-1">95% CI</div>
              <div id="ci" class="text-xs font-bold text-purple-300">—</div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </main>

  <script src="/quantpath/assets/js/api.js"></script>
  <script>
    let lastResults = null;
    let pathChart = null;
    let distChart = null;

    // Initialize charts
    function initCharts() {
      const ctxPath = document.getElementById('pathChart').getContext('2d');
      const ctxDist = document.getElementById('distChart').getContext('2d');

      if (pathChart) pathChart.destroy();
      if (distChart) distChart.destroy();

      pathChart = new Chart(ctxPath, {
        type: 'line',
        data: { labels: [], datasets: [{ label: 'Price Paths', borderColor: '#60a5fa', borderWidth: 1, data: [], tension: 0, fill: false, pointRadius: 0 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { ticks: { color: '#94a3b8' }, grid: { display: false } } } }
      });

      distChart = new Chart(ctxDist, {
        type: 'bar',
        data: { labels: [], datasets: [{ label: 'Distribution', backgroundColor: '#8b5cf6', data: [] }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { ticks: { color: '#94a3b8' }, grid: { display: false } } } }
      });
    }

    initCharts();

    document.getElementById('fetch-btn').addEventListener('click', async () => {
      const ticker = document.getElementById('ticker').value || 'AAPL';
      try {
        const data = await API.fetchStock(ticker);
        alert('Stock data loaded: ' + ticker);
      } catch (e) {
        alert('Error: ' + e.message);
      }
    });

    document.getElementById('run-btn').addEventListener('click', () => {
      const s0 = parseFloat(document.getElementById('s0').value) || 150;
      const mu = parseFloat(document.getElementById('mu').value) || 0.05;
      const sigma = parseFloat(document.getElementById('sigma').value) || 0.2;
      const paths = parseInt(document.getElementById('paths').value) || 200;
      const T = parseFloat(document.getElementById('horizon').value) || 1;
      const dt = 0.01;

      const simResults = [];
      const allPaths = [];

      for (let i = 0; i < paths; i++) {
        let S = s0;
        const path = [s0];
        for (let t = 0; t < T; t += dt) {
          const Z = Math.random() * 2 - 1;
          S = S * Math.exp((mu - 0.5 * sigma * sigma) * dt + sigma * Math.sqrt(dt) * Z);
          path.push(S);
        }
        simResults.push(S);
        if (i < 50) allPaths.push(path);
      }

      simResults.sort((a, b) => a - b);
      const mean = simResults.reduce((a, b) => a + b, 0) / simResults.length;
      const median = simResults[Math.floor(simResults.length / 2)];
      const variance = simResults.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / simResults.length;
      const stddev = Math.sqrt(variance);
      const ci95Low = simResults[Math.floor(simResults.length * 0.025)];
      const ci95High = simResults[Math.floor(simResults.length * 0.975)];

      // Plot price paths
      const steps = Math.round(T / dt) + 1;
      const pathLabels = Array.from({length: steps}, (_, i) => (i * dt).toFixed(2));
      pathChart.data.labels = pathLabels;
      pathChart.data.datasets = allPaths.map((path, idx) => ({
        label: `Path ${idx+1}`,
        data: path,
        borderColor: `hsla(${idx * 360 / allPaths.length}, 70%, 60%, 0.4)`,
        borderWidth: 0.8,
        tension: 0,
        fill: false,
        pointRadius: 0
      }));
      pathChart.update();

      // Plot distribution
      const bins = 20;
      const histogram = Array(bins).fill(0);
      const binWidth = (Math.max(...simResults) - Math.min(...simResults)) / bins;
      simResults.forEach(val => {
        const binIdx = Math.min(bins - 1, Math.floor((val - Math.min(...simResults)) / binWidth));
        histogram[binIdx]++;
      });
      const binLabels = Array.from({length: bins}, (_, i) => (Math.min(...simResults) + i * binWidth).toFixed(0));
      distChart.data.labels = binLabels;
      distChart.data.datasets[0].data = histogram;
      distChart.update();

      document.getElementById('expected').textContent = '₹' + mean.toFixed(2);
      document.getElementById('median').textContent = '₹' + median.toFixed(2);
      document.getElementById('stddev').textContent = '₹' + stddev.toFixed(2);
      document.getElementById('ci').textContent = `₹${ci95Low.toFixed(2)}–₹${ci95High.toFixed(2)}`;
      document.getElementById('export-btn').style.display = 'block';

      lastResults = { simResults, mean, median, stddev, ci95Low, ci95High, params: {s0, mu, sigma, paths, T} };
    });

    document.getElementById('export-btn').addEventListener('click', () => {
      if (!lastResults) return;
      const { simResults, mean, median, stddev, ci95Low, ci95High, params } = lastResults;
      const csv = `Simulation Export\nS0 (₹),${params.s0}\nμ,${params.mu}\nσ,${params.sigma}\nPaths,${params.paths}\nHorizon,${params.T}\n\nResults\nMean (₹),${mean.toFixed(2)}\nMedian (₹),${median.toFixed(2)}\nStd Dev (₹),${stddev.toFixed(2)}\n95% CI Low (₹),${ci95Low.toFixed(2)}\n95% CI High (₹),${ci95High.toFixed(2)}\n\nFinal Prices (₹)\n${simResults.map(r => r.toFixed(2)).join('\n')}`;
      const blob = new Blob([csv], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `simulation_${new Date().getTime()}.csv`;
      a.click();
      window.URL.revokeObjectURL(url);
    });

    document.getElementById('save-btn').addEventListener('click', async () => {
      if (!lastResults) {
        alert('Run a simulation first');
        return;
      }
      const ticker = document.getElementById('ticker').value || 'AAPL';
      const model = 'Monte Carlo';
      try {
        const res = await API.saveSimulation({
          stock_symbol: ticker,
          model: model,
          parameters: lastResults.params,
          results: lastResults
        });
        alert('Simulation saved!');
        window.location.href = '/quantpath/frontend/dashboard.php';
      } catch (e) {
        alert('Error: ' + e.message);
      }
    });
  </script>
</body>
</html>
