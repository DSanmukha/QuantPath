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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    * { font-family: 'Inter', sans-serif; }
    body { background: #0a0e1a; }
    .glass { background: rgba(255,255,255,0.03); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.06); }
    .glass-hover:hover { background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.12); }
    .glow-indigo { box-shadow: 0 0 30px rgba(99,102,241,0.15); }
    .glow-green { box-shadow: 0 0 30px rgba(16,185,129,0.15); }
    .input-field { background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.08); transition: all 0.2s; }
    .input-field:focus { border-color: rgba(99,102,241,0.5); box-shadow: 0 0 20px rgba(99,102,241,0.1); outline: none; }
    .btn-primary { background: linear-gradient(135deg, #4f46e5, #7c3aed); transition: all 0.3s; }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 10px 40px rgba(99,102,241,0.3); }
    .btn-fetch { background: linear-gradient(135deg, #4f46e5, #6366f1); }
    .btn-fetch:hover { box-shadow: 0 10px 40px rgba(99,102,241,0.3); transform: translateY(-1px); }
    .btn-save { background: linear-gradient(135deg, #059669, #10b981); }
    .btn-save:hover { box-shadow: 0 10px 40px rgba(16,185,129,0.3); }
    @keyframes fadeInUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
    .fade-in { animation: fadeInUp 0.5s ease-out; }
    .stat-card { transition: all 0.3s; }
    .stat-card:hover { transform: translateY(-2px); }
    .loader { width:16px;height:16px;border:2px solid rgba(255,255,255,0.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;display:inline-block; }
    @keyframes spin { to { transform:rotate(360deg); } }
    .sidebar { background: rgba(10,14,30,0.95); border-right: 1px solid rgba(255,255,255,0.06); }
    .sidebar-link { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-radius: 10px; transition: all 0.2s; color: #94a3b8; font-size: 14px; }
    .sidebar-link:hover { background: rgba(99,102,241,0.1); color: #c7d2fe; }
    .sidebar-link.active { background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.15)); color: #a5b4fc; font-weight: 600; }
    /* Model selector cards */
    .model-card { cursor: pointer; border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 10px; transition: all 0.25s; background: rgba(15,23,42,0.5); position: relative; }
    .model-card:hover { border-color: rgba(99,102,241,0.25); background: rgba(99,102,241,0.05); }
    .model-card.selected { border-color: rgba(99,102,241,0.5); background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(139,92,246,0.08)); box-shadow: 0 0 20px rgba(99,102,241,0.1); }
    .model-card.selected .model-dot { background: #6366f1; box-shadow: 0 0 8px rgba(99,102,241,0.5); }
    .model-dot { width: 10px; height: 10px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.15); transition: all 0.25s; flex-shrink: 0; }
  </style>
</head>
<body class="min-h-screen text-slate-100 flex">
  <!-- Sidebar -->
  <aside class="sidebar w-[260px] min-h-screen fixed left-0 top-0 p-5 flex flex-col z-40">
    <a href="/quantpath/frontend/index.html" class="flex items-center gap-3 mb-8 group">
      <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-indigo-500/30">Q</div>
      <div>
        <div class="text-lg font-bold text-white">QuantPath</div>
        <div class="text-xs text-slate-500">Stock Analytics</div>
      </div>
    </a>

    <div class="text-xs text-slate-600 uppercase tracking-wider font-semibold mb-3 px-2">Main Menu</div>
    <nav class="space-y-1 mb-8">
      <a href="/quantpath/frontend/dashboard.php" class="sidebar-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Dashboard
      </a>
      <a href="/quantpath/frontend/simulation.php" class="sidebar-link active">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        New Simulation
      </a>
      <a href="/quantpath/frontend/watchlist.php" class="sidebar-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        Watchlist
      </a>
      <a href="/quantpath/frontend/compare.php" class="sidebar-link">
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
        <h1 class="text-xl font-bold text-white">Stock Price Simulation</h1>
        <p class="text-xs text-slate-500">Generate future price paths with advanced stochastic models</p>
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
      <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
      <!-- Sidebar: Input Form -->
      <div class="xl:col-span-4">
        <section class="glass rounded-2xl p-6 sticky top-20 glow-indigo fade-in">
          <h2 class="text-lg font-bold mb-1 text-white">Simulation Parameters</h2>
          <p class="text-xs text-slate-500 mb-5">Configure your stock simulation</p>

          <form id="sim-form" class="space-y-4">
            <div>
              <label class="block text-xs text-slate-400 font-medium mb-2">Simulation Model</label>
              <input type="hidden" id="model-select" value="gbm">
              <div class="grid grid-cols-2 gap-2" id="model-cards">
                <div class="model-card selected" data-model="gbm">
                  <div class="flex items-center gap-2">
                    <div class="model-dot" style="background:#6366f1;box-shadow:0 0 8px rgba(99,102,241,0.5)"></div>
                    <span class="text-xs font-semibold text-white">Monte Carlo</span>
                  </div>
                  <div class="text-[10px] text-slate-500 mt-1 pl-[18px]">GBM (Geometric Brownian)</div>
                </div>
                <div class="model-card" data-model="mean-reversion">
                  <div class="flex items-center gap-2">
                    <div class="model-dot"></div>
                    <span class="text-xs font-semibold text-white">Mean Reversion</span>
                  </div>
                  <div class="text-[10px] text-slate-500 mt-1 pl-[18px]">Ornstein-Uhlenbeck</div>
                </div>
                <div class="model-card" data-model="jump-diffusion">
                  <div class="flex items-center gap-2">
                    <div class="model-dot"></div>
                    <span class="text-xs font-semibold text-white">Jump Diffusion</span>
                  </div>
                  <div class="text-[10px] text-slate-500 mt-1 pl-[18px]">Merton Model</div>
                </div>
                <div class="model-card" data-model="garch">
                  <div class="flex items-center gap-2">
                    <div class="model-dot"></div>
                    <span class="text-xs font-semibold text-white">GARCH</span>
                  </div>
                  <div class="text-[10px] text-slate-500 mt-1 pl-[18px]">Volatility Clustering</div>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs text-slate-400 font-medium mb-1.5">Stock Ticker (Indian BSE/NSE)</label>
              <input id="ticker" placeholder="RELIANCE.BSE" class="w-full input-field px-3 py-2.5 rounded-lg text-white text-sm placeholder-slate-600" />
              <p class="text-xs text-slate-600 mt-1">e.g. RELIANCE.BSE, TCS.BSE, INFY.BSE</p>
            </div>

            <button type="button" id="fetch-btn" class="w-full btn-fetch text-white px-4 py-2.5 rounded-xl font-semibold text-sm flex justify-center items-center gap-2 transition">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg> Fetch Stock Data
            </button>

            <div id="fetch-status" class="hidden text-xs p-3 rounded-lg"></div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-slate-400 font-medium mb-1.5">Initial Price</label>
                <input id="s0" placeholder="2500" type="number" step="0.01" class="w-full input-field px-3 py-2.5 rounded-lg text-white text-sm placeholder-slate-600" />
              </div>
              <div>
                <label class="block text-xs text-slate-400 font-medium mb-1.5">Drift (μ)</label>
                <input id="mu" placeholder="0.12" type="number" step="0.001" class="w-full input-field px-3 py-2.5 rounded-lg text-white text-sm placeholder-slate-600" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-slate-400 font-medium mb-1.5">Volatility (σ)</label>
                <input id="sigma" placeholder="0.25" type="number" step="0.001" class="w-full input-field px-3 py-2.5 rounded-lg text-white text-sm placeholder-slate-600" />
              </div>
              <div>
                <label class="block text-xs text-slate-400 font-medium mb-1.5">Paths</label>
                <input id="paths" placeholder="500" type="number" class="w-full input-field px-3 py-2.5 rounded-lg text-white text-sm placeholder-slate-600" />
              </div>
            </div>

            <div>
              <label class="block text-xs text-slate-400 font-medium mb-1.5">Horizon (years)</label>
              <input id="horizon" placeholder="1" type="number" step="0.1" class="w-full input-field px-3 py-2.5 rounded-lg text-white text-sm placeholder-slate-600" />
            </div>

            <!-- Mean Reversion Params -->
            <div id="mr-params" class="hidden space-y-3 p-3 bg-slate-900/30 rounded-xl border border-white/5">
              <div class="text-xs text-indigo-400 font-semibold uppercase tracking-wider">Ornstein-Uhlenbeck Parameters</div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs text-slate-400 font-medium mb-1.5">Mean Level (θ)</label>
                  <input id="mr-theta" placeholder="2500" type="number" step="0.01" class="w-full input-field px-3 py-2 rounded-lg text-white text-sm placeholder-slate-600" />
                </div>
                <div>
                  <label class="block text-xs text-slate-400 font-medium mb-1.5">Reversion Speed (κ)</label>
                  <input id="mr-kappa" placeholder="2.0" type="number" step="0.1" class="w-full input-field px-3 py-2 rounded-lg text-white text-sm placeholder-slate-600" />
                </div>
              </div>
            </div>

            <!-- Jump Diffusion Params -->
            <div id="jd-params" class="hidden space-y-3 p-3 bg-slate-900/30 rounded-xl border border-white/5">
              <div class="text-xs text-purple-400 font-semibold uppercase tracking-wider">Merton Jump Parameters</div>
              <div class="grid grid-cols-3 gap-3">
                <div>
                  <label class="block text-xs text-slate-400 font-medium mb-1.5">Jump Rate (λ)</label>
                  <input id="jd-lambda" placeholder="1.0" type="number" step="0.1" class="w-full input-field px-3 py-2 rounded-lg text-white text-sm placeholder-slate-600" />
                </div>
                <div>
                  <label class="block text-xs text-slate-400 font-medium mb-1.5">Jump Mean</label>
                  <input id="jd-jumpmu" placeholder="-0.05" type="number" step="0.01" class="w-full input-field px-3 py-2 rounded-lg text-white text-sm placeholder-slate-600" />
                </div>
                <div>
                  <label class="block text-xs text-slate-400 font-medium mb-1.5">Jump Vol</label>
                  <input id="jd-jumpsigma" placeholder="0.1" type="number" step="0.01" class="w-full input-field px-3 py-2 rounded-lg text-white text-sm placeholder-slate-600" />
                </div>
              </div>
            </div>

            <!-- GARCH Params -->
            <div id="garch-params" class="hidden space-y-3 p-3 bg-slate-900/30 rounded-xl border border-white/5">
              <div class="text-xs text-orange-400 font-semibold uppercase tracking-wider">GARCH(1,1) Parameters</div>
              <div class="grid grid-cols-3 gap-3">
                <div>
                  <label class="block text-xs text-slate-400 font-medium mb-1.5">Omega (ω)</label>
                  <input id="garch-omega" placeholder="0.00001" type="number" step="0.000001" class="w-full input-field px-3 py-2 rounded-lg text-white text-sm placeholder-slate-600" />
                </div>
                <div>
                  <label class="block text-xs text-slate-400 font-medium mb-1.5">Alpha (α)</label>
                  <input id="garch-alpha" placeholder="0.1" type="number" step="0.01" class="w-full input-field px-3 py-2 rounded-lg text-white text-sm placeholder-slate-600" />
                </div>
                <div>
                  <label class="block text-xs text-slate-400 font-medium mb-1.5">Beta (β)</label>
                  <input id="garch-beta" placeholder="0.85" type="number" step="0.01" class="w-full input-field px-3 py-2 rounded-lg text-white text-sm placeholder-slate-600" />
                </div>
              </div>
            </div>

            <div class="flex flex-col gap-2 pt-2">
              <button type="button" id="run-btn" class="w-full btn-primary text-white px-4 py-3 rounded-xl font-semibold text-sm flex justify-center items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg> Run Simulation
              </button>
              <?php if ($user_id): ?>
              <button type="button" id="save-btn" class="w-full btn-save text-white px-4 py-3 rounded-xl font-semibold text-sm flex justify-center items-center gap-2" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Save Results
              </button>
              <?php else: ?>
              <div class="p-2.5 bg-yellow-500/10 border border-yellow-500/20 rounded-xl text-xs text-yellow-300 text-center">
                <a href="/quantpath/frontend/login.php" class="underline hover:no-underline">Log in</a> to save simulations
              </div>
              <?php endif; ?>
            </div>
          </form>
        </section>
      </div>

      <!-- Main Content: Charts & Results -->
      <div class="xl:col-span-8">
        <!-- Stats Cards -->
        <div id="stats-row" class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6" style="display:none;">
          <div class="stat-card glass rounded-xl p-4 text-center">
            <div class="text-xs text-slate-500 mb-1">Expected Price</div>
            <div id="expected" class="text-lg font-bold text-indigo-300">—</div>
          </div>
          <div class="stat-card glass rounded-xl p-4 text-center">
            <div class="text-xs text-slate-500 mb-1">Median Price</div>
            <div id="median" class="text-lg font-bold text-green-300">—</div>
          </div>
          <div class="stat-card glass rounded-xl p-4 text-center">
            <div class="text-xs text-slate-500 mb-1">Std Deviation</div>
            <div id="stddev" class="text-lg font-bold text-orange-300">—</div>
          </div>
          <div class="stat-card glass rounded-xl p-4 text-center">
            <div class="text-xs text-slate-500 mb-1">95% CI</div>
            <div id="ci" class="text-sm font-bold text-purple-300">—</div>
          </div>
          <div class="stat-card glass rounded-xl p-4 text-center">
            <div class="text-xs text-slate-500 mb-1">VaR (5%)</div>
            <div id="var5" class="text-lg font-bold text-red-300">—</div>
          </div>
        </div>

        <!-- Empty State Placeholder -->
        <div id="empty-chart-state" class="glass rounded-2xl p-12 fade-in">
          <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="mb-6 text-slate-600">
              <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="mx-auto">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
              </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Ready to Simulate</h3>
            <p class="text-slate-500 text-sm max-w-md">Enter a stock ticker and click <strong class="text-indigo-400">Fetch Stock Data</strong> to auto-fill parameters, or enter them manually. Then hit <strong class="text-indigo-400">Run Simulation</strong> to generate Monte Carlo price paths.</p>
          </div>
        </div>

        <!-- Path Chart (hidden initially) -->
        <section id="path-chart-section" class="glass rounded-2xl p-6 mb-6 fade-in" style="display:none;">
          <div class="flex justify-between items-center mb-4">
            <div>
              <h3 class="text-lg font-bold text-white">Price Path Simulation</h3>
              <p class="text-xs text-slate-500">Geometric Brownian Motion trajectories</p>
            </div>
            <button id="export-btn" class="px-3 py-1.5 btn-save text-white text-xs rounded-lg font-medium flex items-center gap-1" style="display:none;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg> Export CSV
            </button>
          </div>
          <div class="bg-slate-900/50 rounded-xl p-3 border border-white/5" style="height: 320px;">
            <canvas id="pathChart"></canvas>
          </div>
        </section>

        <!-- Distribution Chart (hidden initially) -->
        <section id="dist-chart-section" class="glass rounded-2xl p-6 fade-in" style="display:none;">
          <h3 class="text-lg font-bold text-white mb-1">Terminal Price Distribution</h3>
          <p class="text-xs text-slate-500 mb-4">Histogram of final simulated prices</p>
          <div class="bg-slate-900/50 rounded-xl p-3 border border-white/5" style="height: 220px;">
            <canvas id="distChart"></canvas>
          </div>
        </section>
      </div>
    </main>
  </div>

  <script src="/quantpath/assets/js/api.js?v=2"></script>
  <script>
    // Toast fallback
    if (typeof Toast === 'undefined') {
      var Toast = {
        container: null,
        init() {
          if (!this.container) {
            this.container = document.getElementById('toast-container');
            if (!this.container) {
              this.container = document.createElement('div');
              this.container.id = 'toast-container';
              this.container.className = 'fixed bottom-6 right-6 z-50 flex flex-col gap-3';
              document.body.appendChild(this.container);
            }
          }
        },
        show(msg, type = 'info') {
          this.init();
          const t = document.createElement('div');
          const bg = type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : '#6366f1';
          t.style.cssText = `background:${bg};color:white;padding:12px 20px;border-radius:12px;font-size:14px;font-weight:500;box-shadow:0 10px 40px rgba(0,0,0,0.3);display:flex;align-items:center;gap:8px;transform:translateX(120%);transition:transform 0.3s;min-width:250px;`;
          t.innerHTML = msg;
          this.container.appendChild(t);
          requestAnimationFrame(() => t.style.transform = 'translateX(0)');
          setTimeout(() => { t.style.transform = 'translateX(120%)'; setTimeout(() => t.remove(), 300); }, 3000);
        }
      };
    }

    let lastResults = null;
    let pathChart = null;
    let distChart = null;

    // Toggle model-specific parameters — use event delegation
    document.getElementById('model-cards').addEventListener('click', function(e) {
      const card = e.target.closest('.model-card');
      if (!card) return;
      const model = card.dataset.model;
      document.getElementById('model-select').value = model;
      // Update card visual state
      document.querySelectorAll('.model-card').forEach(c => {
        c.classList.remove('selected');
        const dot = c.querySelector('.model-dot');
        dot.style.background = '';
        dot.style.boxShadow = '';
      });
      card.classList.add('selected');
      const dot = card.querySelector('.model-dot');
      dot.style.background = '#6366f1';
      dot.style.boxShadow = '0 0 8px rgba(99,102,241,0.5)';
      // Toggle param panels
      document.getElementById('mr-params').classList.toggle('hidden', model !== 'mean-reversion');
      document.getElementById('jd-params').classList.toggle('hidden', model !== 'jump-diffusion');
      document.getElementById('garch-params').classList.toggle('hidden', model !== 'garch');
    });
    function toggleModelParams() { /* handled by event delegation */ }

    // --- Box-Muller Transform for proper normal distribution ---
    function randNormal() {
      let u = 0, v = 0;
      while (u === 0) u = Math.random();
      while (v === 0) v = Math.random();
      return Math.sqrt(-2 * Math.log(u)) * Math.cos(2 * Math.PI * v);
    }

    // Initialize charts
    function initCharts() {
      const ctxPath = document.getElementById('pathChart').getContext('2d');
      const ctxDist = document.getElementById('distChart').getContext('2d');
      if (pathChart) pathChart.destroy();
      if (distChart) distChart.destroy();

      const chartColors = {
        grid: 'rgba(255,255,255,0.04)',
        tick: '#64748b'
      };

      pathChart = new Chart(ctxPath, {
        type: 'line',
        data: { labels: [], datasets: [] },
        options: {
          responsive: true, maintainAspectRatio: false, animation: { duration: 800 },
          plugins: { legend: { display: false } },
          scales: {
            y: { ticks: { color: chartColors.tick, callback: v => v.toLocaleString('en-IN') }, grid: { color: chartColors.grid } },
            x: { ticks: { color: chartColors.tick, maxTicksLimit: 10 }, grid: { display: false } }
          }
        }
      });

      distChart = new Chart(ctxDist, {
        type: 'bar',
        data: { labels: [], datasets: [{ label: 'Frequency', backgroundColor: 'rgba(139,92,246,0.5)', borderColor: '#8b5cf6', borderWidth: 1, borderRadius: 4, data: [] }] },
        options: {
          responsive: true, maintainAspectRatio: false, animation: { duration: 800 },
          plugins: { legend: { display: false } },
          scales: {
            y: { ticks: { color: chartColors.tick }, grid: { color: chartColors.grid } },
            x: { ticks: { color: chartColors.tick, callback: v => Number(v).toLocaleString('en-IN') }, grid: { display: false } }
          }
        }
      });
    }
    initCharts();

    // --- Fetch Data ---
    document.getElementById('fetch-btn').addEventListener('click', async () => {
      const ticker = document.getElementById('ticker').value.trim() || 'RELIANCE.BSE';
      const fetchBtn = document.getElementById('fetch-btn');
      const statusEl = document.getElementById('fetch-status');

      fetchBtn.innerHTML = '<span class="loader"></span> Fetching...';
      fetchBtn.disabled = true;
      statusEl.className = 'text-xs p-3 rounded-lg bg-blue-500/10 border border-blue-500/20 text-blue-300';
      statusEl.textContent = 'Fetching data for ' + ticker + '...';
      statusEl.style.display = 'block';

      try {
        const data = await API.fetchStock(ticker);
        const tsKey = Object.keys(data).find(k => k.includes('Time Series'));
        if (!tsKey) throw new Error('No time series data returned');

        const ts = data[tsKey];
        const dates = Object.keys(ts).sort();
        const prices = dates.map(d => parseFloat(ts[d]['4. close']));

        if (prices.length < 2) throw new Error('Insufficient data');

        // Calculate parameters from historical data
        const lastPrice = prices[prices.length - 1];
        const returns = [];
        for (let i = 1; i < prices.length; i++) {
          returns.push(Math.log(prices[i] / prices[i - 1]));
        }
        const avgReturn = returns.reduce((a, b) => a + b, 0) / returns.length;
        const variance = returns.reduce((a, b) => a + Math.pow(b - avgReturn, 2), 0) / (returns.length - 1);
        const dailyVol = Math.sqrt(variance);
        const annualizedMu = avgReturn * 252;
        const annualizedSigma = dailyVol * Math.sqrt(252);

        document.getElementById('s0').value = lastPrice.toFixed(2);
        document.getElementById('mu').value = annualizedMu.toFixed(4);
        document.getElementById('sigma').value = annualizedSigma.toFixed(4);

        statusEl.className = 'text-xs p-3 rounded-lg bg-green-500/10 border border-green-500/20 text-green-300';
        statusEl.innerHTML = `<strong>${ticker}</strong> — Last: ₹${lastPrice.toLocaleString('en-IN', {minimumFractionDigits:2})} | μ = ${annualizedMu.toFixed(4)} | σ = ${annualizedSigma.toFixed(4)} | ${prices.length} data points`;

        Toast.show(`Stock data loaded for ${ticker}`, 'success');
      } catch (e) {
        statusEl.className = 'text-xs p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-300';
        statusEl.textContent = e.message;
        Toast.show(e.message, 'error');
      }
      fetchBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg> Fetch Stock Data';
      fetchBtn.disabled = false;
    });

    // --- Run Simulation ---
    document.getElementById('run-btn').addEventListener('click', () => {
      const model = document.getElementById('model-select').value;
      const s0 = parseFloat(document.getElementById('s0').value) || 2500;
      const mu = parseFloat(document.getElementById('mu').value) || 0.12;
      const sigma = parseFloat(document.getElementById('sigma').value) || 0.25;
      const numPaths = parseInt(document.getElementById('paths').value) || 500;
      const T = parseFloat(document.getElementById('horizon').value) || 1;
      const steps = Math.round(252 * T);
      const dt = T / steps;

      const runBtn = document.getElementById('run-btn');
      runBtn.innerHTML = '<span class="loader"></span> Running...';
      runBtn.disabled = true;

      setTimeout(() => {
        const emptyState = document.getElementById('empty-chart-state');
        if (emptyState) emptyState.style.display = 'none';
        document.getElementById('path-chart-section').style.display = 'block';
        document.getElementById('dist-chart-section').style.display = 'block';

        const simResults = [];
        const allPaths = [];

        for (let i = 0; i < numPaths; i++) {
          let S = s0;
          const path = [s0];

          if (model === 'gbm') {
            // Geometric Brownian Motion
            for (let t = 0; t < steps; t++) {
              const Z = randNormal();
              S = S * Math.exp((mu - 0.5 * sigma * sigma) * dt + sigma * Math.sqrt(dt) * Z);
              path.push(S);
            }
          } else if (model === 'mean-reversion') {
            // Ornstein-Uhlenbeck Mean Reversion
            const theta = parseFloat(document.getElementById('mr-theta').value) || s0;
            const kappa = parseFloat(document.getElementById('mr-kappa').value) || 2.0;
            for (let t = 0; t < steps; t++) {
              const Z = randNormal();
              S = S + kappa * (theta - S) * dt + sigma * S * Math.sqrt(dt) * Z;
              if (S < 0) S = 0.01;
              path.push(S);
            }
          } else if (model === 'jump-diffusion') {
            // Merton Jump Diffusion
            const lambdaJ = parseFloat(document.getElementById('jd-lambda').value) || 1.0;
            const jumpMu = parseFloat(document.getElementById('jd-jumpmu').value) || -0.05;
            const jumpSigma = parseFloat(document.getElementById('jd-jumpsigma').value) || 0.1;
            for (let t = 0; t < steps; t++) {
              const Z = randNormal();
              const jump = Math.random() < lambdaJ * dt ? Math.exp(jumpMu + jumpSigma * randNormal()) - 1 : 0;
              S = S * Math.exp((mu - 0.5 * sigma * sigma) * dt + sigma * Math.sqrt(dt) * Z) * (1 + jump);
              if (S < 0) S = 0.01;
              path.push(S);
            }
          } else if (model === 'garch') {
            // GARCH(1,1) Volatility
            const omega = parseFloat(document.getElementById('garch-omega').value) || 0.00001;
            const alpha = parseFloat(document.getElementById('garch-alpha').value) || 0.1;
            const beta = parseFloat(document.getElementById('garch-beta').value) || 0.85;
            let h = sigma * sigma / 252; // daily variance
            let prevReturn = 0;
            for (let t = 0; t < steps; t++) {
              h = omega + alpha * prevReturn * prevReturn + beta * h;
              const dailyVol = Math.sqrt(h);
              const Z = randNormal();
              const ret = (mu / 252) + dailyVol * Z;
              prevReturn = ret;
              S = S * Math.exp(ret);
              if (S < 0) S = 0.01;
              path.push(S);
            }
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
        const var5 = simResults[Math.floor(simResults.length * 0.05)];

        // Compute mean path & percentile bands
        const meanPath = [];
        const p5Path = [];
        const p95Path = [];
        for (let t = 0; t <= steps; t++) {
          const vals = allPaths.map(p => p[t]).sort((a, b) => a - b);
          meanPath.push(vals.reduce((a, b) => a + b, 0) / vals.length);
          p5Path.push(vals[Math.floor(vals.length * 0.05)]);
          p95Path.push(vals[Math.floor(vals.length * 0.95)]);
        }

        // Plot
        const pathLabels = Array.from({length: steps + 1}, (_, i) => {
          const dayNum = Math.round(i * T * 252 / steps);
          return dayNum % 30 === 0 ? `Day ${dayNum}` : '';
        });
        pathChart.data.labels = pathLabels;

        const datasets = allPaths.map((path, idx) => ({
          label: `Path ${idx+1}`,
          data: path,
          borderColor: `hsla(${220 + idx * 3}, 70%, 65%, 0.12)`,
          borderWidth: 0.7,
          tension: 0,
          fill: false,
          pointRadius: 0
        }));

        // Mean path
        datasets.push({
          label: 'Mean', data: meanPath,
          borderColor: '#ef4444', borderWidth: 2.5, tension: 0.1,
          fill: false, pointRadius: 0, borderDash: []
        });

        // Confidence bands
        datasets.push({
          label: '95th Percentile', data: p95Path,
          borderColor: 'rgba(16,185,129,0.5)', borderWidth: 1, borderDash: [5,5],
          fill: false, pointRadius: 0
        });
        datasets.push({
          label: '5th Percentile', data: p5Path,
          borderColor: 'rgba(239,68,68,0.5)', borderWidth: 1, borderDash: [5,5],
          fill: false, pointRadius: 0
        });

        pathChart.data.datasets = datasets;
        pathChart.update();

        // Distribution histogram
        const bins = 25;
        const histogram = Array(bins).fill(0);
        const minVal = Math.min(...simResults);
        const maxVal = Math.max(...simResults);
        const binWidth = (maxVal - minVal) / bins;
        simResults.forEach(val => {
          const binIdx = Math.min(bins - 1, Math.floor((val - minVal) / binWidth));
          histogram[binIdx]++;
        });
        const binLabels = Array.from({length: bins}, (_, i) => (minVal + i * binWidth).toFixed(0));
        distChart.data.labels = binLabels;
        distChart.data.datasets[0].data = histogram;
        distChart.update();

        // Update stats
        const fmt = v => '₹' + v.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('expected').textContent = fmt(mean);
        document.getElementById('median').textContent = fmt(median);
        document.getElementById('stddev').textContent = fmt(stddev);
        document.getElementById('ci').textContent = `${fmt(ci95Low)} — ${fmt(ci95High)}`;
        document.getElementById('var5').textContent = fmt(var5);
        document.getElementById('stats-row').style.display = 'grid';
        document.getElementById('export-btn').style.display = 'block';
        const saveBtn = document.getElementById('save-btn');
        if (saveBtn) saveBtn.disabled = false;

        lastResults = { simResults, mean, median, stddev, ci95Low, ci95High, var5, params: {S0: s0, mu, sigma, paths: numPaths, steps, T}, model };

        runBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg> Run Simulation';
        runBtn.disabled = false;
        const modelNames = { gbm: 'GBM', 'mean-reversion': 'Ornstein-Uhlenbeck', 'jump-diffusion': 'Merton', garch: 'GARCH' };
        Toast.show(`${modelNames[model]} simulation complete — ${numPaths} paths`, 'success');
      }, 50);
    });

    // Export CSV
    document.getElementById('export-btn').addEventListener('click', () => {
      if (!lastResults) return;
      const { simResults, mean, median, stddev, ci95Low, ci95High, var5, params } = lastResults;
      const ticker = document.getElementById('ticker').value || 'STOCK';
      let csv = `QuantPath Simulation Export\nStock,${ticker}\nInitial Price (₹),${params.S0}\nDrift (μ),${params.mu}\nVolatility (σ),${params.sigma}\nPaths,${params.paths}\nHorizon (years),${params.T}\n\nResults\nMean (₹),${mean.toFixed(2)}\nMedian (₹),${median.toFixed(2)}\nStd Dev (₹),${stddev.toFixed(2)}\n95% CI Low (₹),${ci95Low.toFixed(2)}\n95% CI High (₹),${ci95High.toFixed(2)}\nVaR 5% (₹),${var5.toFixed(2)}\n\nAll Final Prices (₹)\n${simResults.map(r => r.toFixed(2)).join('\n')}`;
      const blob = new Blob([csv], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `quantpath_${ticker}_${new Date().toISOString().split('T')[0]}.csv`;
      a.click();
      window.URL.revokeObjectURL(url);
      Toast.show('CSV exported successfully', 'success');
    });

    // Save
    const saveBtn = document.getElementById('save-btn');
    if (saveBtn) {
      saveBtn.addEventListener('click', async () => {
        if (!lastResults) { Toast.show('Run a simulation first', 'warning'); return; }
        const ticker = document.getElementById('ticker').value || 'STOCK';
        saveBtn.innerHTML = '<span class="loader"></span> Saving...';
        saveBtn.disabled = true;
        try {
          const modelNames = { gbm: 'Monte Carlo (GBM)', 'mean-reversion': 'Mean Reversion (O-U)', 'jump-diffusion': 'Jump Diffusion (Merton)', garch: 'GARCH(1,1)' };
          await API.saveSimulation({
            stock_symbol: ticker,
            model: modelNames[lastResults.model] || 'Monte Carlo (GBM)',
            parameters: lastResults.params,
            results: { mean: lastResults.mean, median: lastResults.median, stddev: lastResults.stddev, ci95Low: lastResults.ci95Low, ci95High: lastResults.ci95High, var5: lastResults.var5 }
          });
          Toast.show('Simulation saved successfully!', 'success');
          setTimeout(() => { window.location.href = '/quantpath/frontend/dashboard.php'; }, 1000);
        } catch (e) {
          Toast.show('Error: ' + e.message, 'error');
          saveBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Save Results';
          saveBtn.disabled = false;
        }
      });
    }
  </script>
</body>
</html>
