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
  <link rel="stylesheet" href="/quantpath/assets/css/tailwind.css">
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-900 to-slate-800 text-slate-100">
  <header class="bg-transparent p-4 border-b border-white/10">
    <div class="max-w-6xl mx-auto flex items-center justify-between">
      <a href="/quantpath/frontend/index.html" class="flex items-center gap-3">
        <div class="w-8 h-8 bg-white/10 rounded flex items-center justify-center text-white font-bold">Q</div>
        <div class="text-lg font-semibold">QuantPath</div>
      </a>
      <nav class="flex items-center gap-3">
        <?php if ($user_id): ?>
          <span class="text-sm text-white/70">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
          <a href="/quantpath/backend/logout.php" class="px-3 py-2 bg-white/10 hover:bg-white/20 rounded">Logout</a>
        <?php else: ?>
          <a href="/quantpath/frontend/login.html" class="px-3 py-2 bg-indigo-600 text-white rounded">Log in</a>
        <?php endif; ?>
        <a href="/quantpath/frontend/dashboard.php" class="px-3 py-2 bg-white/10 hover:bg-white/20 rounded">Dashboard</a>
      </nav>
    </div>
  </header>

  <main class="max-w-6xl mx-auto p-6">
    <section class="bg-white/5 backdrop-blur-sm p-6 rounded-lg shadow-lg border border-white/10">
      <h2 class="text-2xl font-semibold mb-4">Monte Carlo Simulation</h2>

      <form id="sim-form" class="space-y-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <label class="block">
            <div class="text-sm text-white/80 mb-1">Ticker</div>
            <input id="ticker" name="ticker" placeholder="AAPL" class="w-full bg-white/5 border border-white/10 px-3 py-2 rounded text-white placeholder-white/50" />
          </label>
          <label class="block">
            <div class="text-sm text-white/80 mb-1">Initial Price (S0)</div>
            <input id="s0" name="s0" placeholder="150.00" class="w-full bg-white/5 border border-white/10 px-3 py-2 rounded text-white placeholder-white/50" />
          </label>
          <label class="block">
            <div class="text-sm text-white/80 mb-1">Paths</div>
            <input id="paths" name="paths" placeholder="200" class="w-full bg-white/5 border border-white/10 px-3 py-2 rounded text-white placeholder-white/50" />
          </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <label class="block">
            <div class="text-sm text-white/80 mb-1">Drift μ</div>
            <input id="mu" name="mu" placeholder="0.05" class="w-full bg-white/5 border border-white/10 px-3 py-2 rounded text-white placeholder-white/50" />
          </label>
          <label class="block">
            <div class="text-sm text-white/80 mb-1">Volatility σ</div>
            <input id="sigma" name="sigma" placeholder="0.2" class="w-full bg-white/5 border border-white/10 px-3 py-2 rounded text-white placeholder-white/50" />
          </label>
          <label class="block">
            <div class="text-sm text-white/80 mb-1">Horizon (years)</div>
            <input id="horizon" name="horizon" placeholder="1" class="w-full bg-white/5 border border-white/10 px-3 py-2 rounded text-white placeholder-white/50" />
          </label>
        </div>

        <div class="flex flex-wrap gap-3 mt-4">
          <button type="button" id="fetch-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Fetch Stock</button>
          <button type="button" id="run-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Run Simulation</button>
          <button type="button" id="save-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Save</button>
        </div>
      </form>

      <div class="mt-6 bg-white/6 border border-white/8 rounded p-4">
        <h4 class="text-lg font-medium text-white/90 mb-3">Results</h4>
        <div id="chart" class="w-full h-64 bg-white/5 rounded flex items-center justify-center text-white/60">Ready — click "Run Simulation" to see results</div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="p-4 bg-white/5 rounded text-center">
            <div class="text-sm text-white/70">Expected Price</div>
            <div id="expected" class="text-2xl font-semibold mt-2">—</div>
          </div>
          <div class="p-4 bg-white/5 rounded text-center">
            <div class="text-sm text-white/70">Median Price</div>
            <div id="median" class="text-2xl font-semibold mt-2">—</div>
          </div>
          <div class="p4 bg-white/5 rounded text-center">
            <div class="text-sm text-white/70">95% CI</div>
            <div id="ci" class="text-2xl font-semibold mt-2">—</div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script src="/quantpath/assets/js/api.js"></script>
  <script>
    let lastResults = null;

    document.getElementById('fetch-btn').addEventListener('click', async () => {
      const ticker = document.getElementById('ticker').value || 'AAPL';
      const chart = document.getElementById('chart');
      chart.textContent = 'Loading stock data...';
      try {
        const data = await API.fetchStock(ticker);
        if (data['Meta Data']) {
          const timeSeries = data['Time Series (Daily)'];
          chart.textContent = 'Stock data loaded: ' + Object.keys(timeSeries).length + ' trading days available';
        } else {
          chart.textContent = 'Error: Could not fetch stock data';
        }
      } catch (e) {
        chart.textContent = 'Failed to load data: ' + e.message;
      }
    });

    document.getElementById('run-btn').addEventListener('click', () => {
      const s0 = parseFloat(document.getElementById('s0').value) || 150;
      const mu = parseFloat(document.getElementById('mu').value) || 0.05;
      const sigma = parseFloat(document.getElementById('sigma').value) || 0.2;
      
      // Simple demo Monte Carlo
      const paths = parseInt(document.getElementById('paths').value) || 200;
      const T = parseFloat(document.getElementById('horizon').value) || 1;
      const dt = 0.01;
      const simResults = [];
      
      for (let i = 0; i < paths; i++) {
        let S = s0;
        for (let t = 0; t < T; t += dt) {
          const Z = Math.random() * 2 - 1;
          S = S * Math.exp((mu - 0.5 * sigma * sigma) * dt + sigma * Math.sqrt(dt) * Z);
        }
        simResults.push(S);
      }
      
      simResults.sort((a, b) => a - b);
      const mean = simResults.reduce((a, b) => a + b, 0) / simResults.length;
      const median = simResults[Math.floor(simResults.length / 2)];
      const ci95Low = simResults[Math.floor(simResults.length * 0.025)];
      const ci95High = simResults[Math.floor(simResults.length * 0.975)];
      
      document.getElementById('expected').textContent = '$' + mean.toFixed(2);
      document.getElementById('median').textContent = '$' + median.toFixed(2);
      document.getElementById('ci').textContent = '$' + ci95Low.toFixed(2) + ' — $' + ci95High.toFixed(2);
      
      lastResults = { paths: simResults, mean, median, ci95Low, ci95High };
      document.getElementById('chart').textContent = 'Simulation complete: ' + paths + ' paths';
    });

    document.getElementById('save-btn').addEventListener('click', async () => {
      <?php if (empty($user_id)): ?>
        alert('Please log in to save simulations');
        window.location.href = '/quantpath/frontend/login.html';
        return;
      <?php endif; ?>
      
      if (!lastResults) {
        alert('Run a simulation first');
        return;
      }
      
      const ticker = document.getElementById('ticker').value || 'AAPL';
      const model = 'Monte Carlo';
      const params = {
        S0: parseFloat(document.getElementById('s0').value) || 150,
        mu: parseFloat(document.getElementById('mu').value) || 0.05,
        sigma: parseFloat(document.getElementById('sigma').value) || 0.2,
        paths: parseInt(document.getElementById('paths').value) || 200
      };
      
      try {
        const res = await API.saveSimulation({
          stock_symbol: ticker,
          model: model,
          parameters: params,
          results: lastResults
        });
        alert('Simulation saved!');
        window.location.href = '/quantpath/frontend/dashboard.php';
      } catch (e) {
        alert('Failed to save: ' + e.message);
      }
    });
  </script>
</body>
</html>
