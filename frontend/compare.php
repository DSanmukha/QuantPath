<?php
// frontend/compare.php
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
  <title>Compare Stocks — QuantPath</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    * { font-family: 'Inter', sans-serif; }
    body { background: #0a0e1a; }
    .glass { background: rgba(255,255,255,0.03); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.06); }
    .sidebar { background: rgba(10,14,30,0.95); border-right: 1px solid rgba(255,255,255,0.06); }
    .sidebar-link { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-radius: 10px; transition: all 0.2s; color: #94a3b8; font-size: 14px; }
    .sidebar-link:hover { background: rgba(99,102,241,0.1); color: #c7d2fe; }
    .sidebar-link.active { background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.15)); color: #a5b4fc; font-weight: 600; }
    .input-field { background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.08); transition: all 0.2s; }
    .input-field:focus { border-color: rgba(99,102,241,0.5); box-shadow: 0 0 20px rgba(99,102,241,0.1); outline: none; }
    .loader { width:16px;height:16px;border:2px solid rgba(255,255,255,0.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;display:inline-block; }
    @keyframes spin { to { transform:rotate(360deg); } }
    @keyframes fadeIn { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .fade-in { animation: fadeIn 0.5s ease-out; }
  </style>
</head>
<body class="min-h-screen text-slate-100 flex">
  <!-- Toast Notifier -->
  <div id="toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col gap-3"></div>
  
  <!-- Sidebar -->
  <aside class="sidebar w-[260px] min-h-screen fixed left-0 top-0 p-5 flex flex-col z-40">
    <a href="/quantpath/frontend/index.html" class="flex items-center gap-3 mb-8 group">
      <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-indigo-500/30">Q</div>
      <div><div class="text-lg font-bold text-white">QuantPath</div><div class="text-xs text-slate-500">Stock Analytics</div></div>
    </a>
    <div class="text-xs text-slate-600 uppercase tracking-wider font-semibold mb-3 px-2">Main Menu</div>
    <nav class="space-y-1 mb-8">
      <a href="/quantpath/frontend/dashboard.php" class="sidebar-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Dashboard
      </a>
      <a href="/quantpath/frontend/simulation.php" class="sidebar-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        New Simulation
      </a>
      <a href="/quantpath/frontend/watchlist.php" class="sidebar-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        Watchlist
      </a>
      <a href="/quantpath/frontend/compare.php" class="sidebar-link active">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
        Compare
      </a>
    </nav>
    <div class="text-xs text-slate-600 uppercase tracking-wider font-semibold mb-3 px-2">Account</div>
    <nav class="space-y-1">
      <a href="/quantpath/frontend/profile.php" class="sidebar-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profile
      </a>
      <?php if ($user_id): ?>
      <a href="/quantpath/backend/logout.php" class="sidebar-link text-red-400 hover:bg-red-500/10 hover:text-red-300">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        Logout
      </a>
      <?php else: ?>
      <a href="/quantpath/frontend/login.php" class="sidebar-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
        Log In
      </a>
      <?php endif; ?>
    </nav>
  </aside>

  <!-- Main Content -->
  <div class="ml-[260px] flex-1 min-h-screen">
    <header class="sticky top-0 z-30 glass border-b border-white/5 px-8 py-4 flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-white">Compare Stocks</h1>
        <p class="text-xs text-slate-500">Run quick Monte Carlo analysis across 2–3 Indian stocks</p>
      </div>
      <?php if ($user_id): ?>
      <div class="flex items-center gap-3">
        <a href="/quantpath/frontend/profile.php" class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 rounded-full flex items-center justify-center text-white text-sm font-bold transition shadow-lg shadow-indigo-500/30">
          <?php echo strtoupper(substr($user_name, 0, 1)); ?>
        </a>
      </div>
      <?php endif; ?>
    </header>

    <main class="px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Input Form -->
        <div class="lg:col-span-1">
          <div class="glass rounded-2xl p-6 fade-in sticky top-24">
            <h3 class="text-lg font-bold text-white mb-1">Stock Selection</h3>
            <p class="text-xs text-slate-500 mb-5">Enter BSE/NSE tickers to compare</p>
            
            <form id="compare-form" class="space-y-4">
              <div>
                <label class="block text-xs text-slate-400 font-medium mb-1.5">Stock 1 (Required)</label>
                <input id="stock1" placeholder="RELIANCE.BSE" class="w-full input-field px-3 py-2.5 rounded-lg text-white text-sm placeholder-slate-600" required />
              </div>
              <div>
                <label class="block text-xs text-slate-400 font-medium mb-1.5">Stock 2 (Required)</label>
                <input id="stock2" placeholder="TCS.BSE" class="w-full input-field px-3 py-2.5 rounded-lg text-white text-sm placeholder-slate-600" required />
              </div>
              <div>
                <label class="block text-xs text-slate-400 font-medium mb-1.5">Stock 3 (Optional)</label>
                <input id="stock3" placeholder="INFY.BSE" class="w-full input-field px-3 py-2.5 rounded-lg text-white text-sm placeholder-slate-600" />
              </div>
              
              <div class="pt-2 border-t border-white/5 mt-4">
                <div class="grid grid-cols-2 gap-3 mb-4">
                  <div>
                    <label class="block text-xs text-slate-400 font-medium mb-1.5">Paths</label>
                    <input id="paths" value="250" type="number" class="w-full input-field px-3 py-2.5 rounded-lg text-white text-sm placeholder-slate-600" />
                  </div>
                  <div>
                    <label class="block text-xs text-slate-400 font-medium mb-1.5">Horizon (years)</label>
                    <input id="horizon" value="1" type="number" step="0.5" class="w-full input-field px-3 py-2.5 rounded-lg text-white text-sm placeholder-slate-600" />
                  </div>
                </div>
                
                <button type="button" id="compare-btn" class="w-full px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-semibold text-sm transition shadow-lg shadow-indigo-500/20 flex items-center justify-center gap-2">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg> Compare Stocks
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Comparison Results -->
        <div class="lg:col-span-3">
          <div id="compare-results" class="space-y-6">
            <div class="flex flex-col items-center justify-center py-20 glass rounded-2xl">
              <div class="mb-4 text-slate-600">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" class="mx-auto" stroke="currentColor" stroke-width="1.5"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
              </div>
              <h3 class="text-xl font-bold text-slate-400 mb-2">Ready to Compare</h3>
              <p class="text-slate-500 max-w-sm text-center text-sm">Enter 2 or 3 Indian stock tickers on the left to instantly run Monte Carlo simulations and compare their risk vs return profiles.</p>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="/quantpath/assets/js/api.js?v=2"></script>
  <script>
    // Define a basic Toast if not globally available from api.js
    const Toast = window.Toast || {
      container: null,
      init() {
        if(!this.container){
            this.container = document.getElementById('toast-container');
            if(!this.container){
                this.container = document.createElement('div');
                this.container.id = 'toast-container';
                this.container.className = 'fixed bottom-6 right-6 z-50 flex flex-col gap-3';
                document.body.appendChild(this.container);
            }
        }
      },
      show(msg, type='info') {
        this.init();
        const t = document.createElement('div');
        const bg = type==='error'?'#ef4444':type==='success'?'#10b981':'#6366f1';
        t.style.cssText = `background:${bg};color:white;padding:12px 20px;border-radius:12px;font-size:14px;box-shadow:0 10px 40px rgba(0,0,0,0.3);transform:translateX(120%);transition:transform 0.3s;`;
        t.innerHTML = msg;
        this.container.appendChild(t);
        requestAnimationFrame(() => t.style.transform = "translateX(0)");
        setTimeout(() => { t.style.transform = "translateX(120%)"; setTimeout(() => t.remove(), 300); }, 3000);
      }
    };

    // Proper Box-Muller random normal distribution
    function randNormal() {
      let u = 0, v = 0;
      while (u === 0) u = Math.random();
      while (v === 0) v = Math.random();
      return Math.sqrt(-2 * Math.log(u)) * Math.cos(2 * Math.PI * v);
    }
    
    document.getElementById('compare-btn').addEventListener('click', async () => {
      const s1 = document.getElementById('stock1').value.trim().toUpperCase();
      const s2 = document.getElementById('stock2').value.trim().toUpperCase();
      const s3 = document.getElementById('stock3').value.trim().toUpperCase();
      
      if (!s1 || !s2) { Toast.show('Stock 1 and Stock 2 are required', 'warning'); return; }
      const tickers = [s1, s2];
      if (s3) tickers.push(s3);
      
      const numPaths = parseInt(document.getElementById('paths').value) || 250;
      const T = parseFloat(document.getElementById('horizon').value) || 1;
      const steps = Math.round(252 * T);
      const dt = T / steps;

      const btn = document.getElementById('compare-btn');
      btn.innerHTML = '<span class="loader"></span> Comparing...';
      btn.disabled = true;
      
      const container = document.getElementById('compare-results');
      container.innerHTML = `
        <div class="flex flex-col items-center justify-center py-20 glass rounded-2xl">
          <span class="loader mb-4" style="width:30px;height:30px;border-width:3px;"></span>
          <p class="text-indigo-300 font-medium">Fetching historical data and running simulations...</p>
        </div>
      `;

      try {
        const sims = [];
        
        // Fetch data and configure models
        for (const ticker of tickers) {
          try {
            const data = await API.fetchStock(ticker);
            const tsKey = Object.keys(data).find(k => k.includes('Time Series'));
            if (!tsKey) throw new Error(`No data for ${ticker}`);
            const ts = data[tsKey];
            const dates = Object.keys(ts).sort();
            const prices = dates.map(d => parseFloat(ts[d]['4. close']));
            
            if (prices.length < 2) throw new Error(`Not enough data points for ${ticker}`);
            
            const S0 = prices[prices.length - 1];
            const returns = [];
            for (let i = 1; i < prices.length; i++) {
              returns.push(Math.log(prices[i] / prices[i - 1]));
            }
            const avgReturn = returns.reduce((a, b) => a + b, 0) / returns.length;
            const variance = returns.reduce((a, b) => a + Math.pow(b - avgReturn, 2), 0) / (returns.length - 1);
            const dailyVol = Math.sqrt(variance);
            const mu = avgReturn * 252;
            const sigma = dailyVol * Math.sqrt(252);
            
            sims.push({ symbol: ticker, S0, mu, sigma });
          } catch(e) {
            throw new Error(`Error with ${ticker}: ${e.message}`);
          }
        }
        
        // Run Simulations
        for (const sim of sims) {
          const results = [];
          for (let i = 0; i < numPaths; i++) {
            let S = sim.S0;
            for (let t = 0; t < steps; t++) {
              S = S * Math.exp((sim.mu - 0.5 * sim.sigma * sim.sigma) * dt + sim.sigma * Math.sqrt(dt) * randNormal());
            }
            results.push(S);
          }
          results.sort((a, b) => a - b);
          
          sim.mean = results.reduce((a, b) => a + b, 0) / results.length;
          const varc = results.reduce((a, b) => a + Math.pow(b - sim.mean, 2), 0) / results.length;
          sim.stddev = Math.sqrt(varc);
          sim.var5 = results[Math.floor(results.length * 0.05)];
          
          // Calculate expected return %
          sim.expectedReturnPct = ((sim.mean - sim.S0) / sim.S0) * 100;
        }

        // Render Results
        const colors = ['#818cf8', '#34d399', '#f59e0b'];
        const colorsBg = ['rgba(129,140,248,0.1)', 'rgba(52,211,153,0.1)', 'rgba(245,158,11,0.1)'];

        let tableRows = '';
        sims.forEach((s, i) => {
          const retColor = s.expectedReturnPct >= 0 ? 'text-green-300' : 'text-red-300';
          const retSign = s.expectedReturnPct >= 0 ? '+' : '';
          tableRows += `
            <tr class="border-b border-white/5">
              <td class="py-3 px-4"><span class="inline-block w-3 h-3 rounded-full mr-2" style="background:${colors[i]}"></span><strong class="text-white">${s.symbol}</strong></td>
              <td class="py-3 px-4 text-slate-300">₹${s.S0.toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
              <td class="py-3 px-4 text-blue-300">${s.mu.toFixed(4)}</td>
              <td class="py-3 px-4 text-purple-300">${s.sigma.toFixed(4)}</td>
              <td class="py-3 px-4 text-indigo-300">₹${s.mean.toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
              <td class="py-3 px-4 ${retColor} font-medium">${retSign}${s.expectedReturnPct.toFixed(2)}%</td>
              <td class="py-3 px-4 text-red-300">₹${s.var5.toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
            </tr>`;
        });

        container.innerHTML = `
          <!-- Comparison Table -->
          <div class="glass rounded-2xl p-6 fade-in">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              Parameter & Results Comparison
            </h3>
            <div class="overflow-x-auto">
              <table class="w-full text-sm text-slate-300 whitespace-nowrap">
                <thead><tr class="text-xs text-slate-500 uppercase border-b border-white/10">
                  <th class="py-2 px-4 text-left font-semibold">Stock</th>
                  <th class="py-2 px-4 text-left font-semibold">Initial (S₀)</th>
                  <th class="py-2 px-4 text-left font-semibold">Drift (μ)</th>
                  <th class="py-2 px-4 text-left font-semibold">Volatility (σ)</th>
                  <th class="py-2 px-4 text-left font-semibold">Expected Price</th>
                  <th class="py-2 px-4 text-left font-semibold">Expected Return</th>
                  <th class="py-2 px-4 text-left font-semibold">VaR 5%</th>
                </tr></thead>
                <tbody>${tableRows}</tbody>
              </table>
            </div>
          </div>

          <!-- Bar Chart Comparison -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div class="glass rounded-2xl p-6 fade-in">
              <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
                Expected Returns (%)
              </h3>
              <div style="height:220px;"><canvas id="returnChart"></canvas></div>
            </div>
            
            <div class="glass rounded-2xl p-6 fade-in">
              <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/></svg>
                Volatility / Risk (σ)
              </h3>
              <div style="height:220px;"><canvas id="volChart"></canvas></div>
            </div>
          </div>

          <!-- Risk Comparison -->
          <div class="glass rounded-2xl p-6 fade-in mt-6">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              Risk vs Reward Profile
            </h3>
            <div class="grid grid-cols-${sims.length} gap-4">
              ${sims.map((s, i) => {
                const riskLevel = s.sigma > 0.4 ? 'High Risk' : s.sigma > 0.25 ? 'Medium Risk' : 'Low Risk';
                const riskColor = s.sigma > 0.4 ? 'red' : s.sigma > 0.25 ? 'yellow' : 'green';
                
                // Calculate max drawdown proxy from VaR
                const maxLossPct = ((s.S0 - s.var5) / s.S0 * 100).toFixed(1);
                
                return `
                  <div class="text-center p-4 rounded-xl border border-white/5" style="background:${colorsBg[i]}">
                    <div class="text-sm font-bold text-white mb-2">${s.symbol}</div>
                    <div class="text-xl font-bold text-${riskColor}-400 mb-1">${riskLevel}</div>
                    <div class="flex justify-between text-xs text-slate-400 mt-3 pt-3 border-t border-white/10 px-2 lg:px-4">
                      <div class="text-left">
                        <span class="block mb-1">Max Loss (95%)</span>
                        <strong class="text-red-300">-${maxLossPct}%</strong>
                      </div>
                      <div class="text-right">
                        <span class="block mb-1">Exp. Return</span>
                        <strong class="text-${s.expectedReturnPct >= 0 ? 'green' : 'red'}-300">${s.expectedReturnPct > 0 ? '+' : ''}${s.expectedReturnPct.toFixed(1)}%</strong>
                      </div>
                    </div>
                  </div>`;
              }).join('')}
            </div>
          </div>
        `;

        // Render charts
        new Chart(document.getElementById('returnChart').getContext('2d'), {
          type: 'bar',
          data: {
            labels: sims.map(s => s.symbol),
            datasets: [{
              label: 'Expected Return (%)',
              data: sims.map(s => s.expectedReturnPct),
              backgroundColor: sims.map((s, i) => colors[i] + 'aa'),
              borderRadius: 4
            }]
          },
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.04)' } },
              x: { ticks: { color: '#cbd5e1' }, grid: { display: false } }
            }
          }
        });

        new Chart(document.getElementById('volChart').getContext('2d'), {
          type: 'bar',
          data: {
            labels: sims.map(s => s.symbol),
            datasets: [{
              label: 'Volatility (σ)',
              data: sims.map(s => s.sigma),
              backgroundColor: sims.map((s, i) => colors[i] + 'aa'),
              borderRadius: 4
            }]
          },
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.04)' } },
              x: { ticks: { color: '#cbd5e1' }, grid: { display: false } }
            }
          }
        });

        Toast.show('Comparison generated successfully', 'success');

      } catch (e) {
        container.innerHTML = `
          <div class="flex flex-col items-center justify-center py-16 glass rounded-2xl">
            <div class="mb-4 text-red-400">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" class="mx-auto" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <h3 class="text-xl font-bold text-red-400 mb-2">Error</h3>
            <p class="text-sm text-slate-500 max-w-sm text-center">${e.message}</p>
          </div>
        `;
        Toast.show('Comparison failed: ' + e.message, 'error');
      }

      btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg> Compare Stocks';
      btn.disabled = false;
    });
  </script>
</body>
</html>
