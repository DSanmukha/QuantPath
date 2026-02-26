<?php
// frontend/dashboard.php
session_start();
require_once __DIR__ . '/../private_config/config.php';

$simulations = [];
$user_name = '';
$user_id = '';
$stats = ['total' => 0, 'avgExpected' => 0, 'totalDrift' => 0];
$tracked_stocks = [];
$stock_count = 0;
$watchlist_items = [];
$recent_activity = [];

if (!empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? 'User';

    // Fetch simulations
    $stmt = $conn->prepare("SELECT id, stock_symbol, model_used, parameters, results_json, created_at FROM simulations WHERE user_id=? ORDER BY created_at DESC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $simulations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stats['total'] = count($simulations);
    $stock_symbols = [];

    if ($stats['total'] > 0) {
        $avgExp = 0; $avgDrift = 0;
        foreach ($simulations as $s) {
            $params = json_decode($s['parameters'], true);
            $avgExp += $params['S0'] ?? 0;
            $avgDrift += $params['mu'] ?? 0;
            $stock_symbols[] = $s['stock_symbol'];
        }
        $stats['avgExpected'] = round($avgExp / $stats['total'], 2);
        $stats['totalDrift'] = round($avgDrift / $stats['total'], 4);
        $tracked_stocks = array_unique($stock_symbols);
        $stock_count = count($tracked_stocks);
    }

    // Fetch watchlist
    $stmt2 = $conn->prepare("SELECT id, stock_symbol, added_at FROM watchlist WHERE user_id=? ORDER BY added_at DESC LIMIT 10");
    $stmt2->bind_param('i', $user_id);
    $stmt2->execute();
    $watchlist_items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt2->close();
}

$conn->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard — QuantPath</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    * { font-family: 'Inter', sans-serif; }
    body { background: #0a0e1a; }
    .glass { background: rgba(255,255,255,0.03); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.06); }
    .glass:hover { border-color: rgba(255,255,255,0.1); }
    .sidebar { background: rgba(10,14,30,0.95); border-right: 1px solid rgba(255,255,255,0.06); }
    .sidebar-link { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-radius: 10px; transition: all 0.2s; color: #94a3b8; font-size: 14px; }
    .sidebar-link:hover { background: rgba(99,102,241,0.1); color: #c7d2fe; }
    .sidebar-link.active { background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.15)); color: #a5b4fc; font-weight: 600; }
    .stat-card { transition: all 0.3s cubic-bezier(0.4,0,0.2,1); }
    .stat-card:hover { transform: translateY(-4px); }
    @keyframes fadeIn { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .fade-in { animation: fadeIn 0.5s ease-out forwards; }
    .fade-in-d1 { animation-delay: 0.1s; opacity: 0; }
    .fade-in-d2 { animation-delay: 0.2s; opacity: 0; }
    .fade-in-d3 { animation-delay: 0.3s; opacity: 0; }
    .fade-in-d4 { animation-delay: 0.4s; opacity: 0; }
    .sim-card { transition: all 0.3s; border: 1px solid rgba(255,255,255,0.04); }
    .sim-card:hover { border-color: rgba(99,102,241,0.3); transform: translateY(-2px); box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
    .badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 500; }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
</head>
<body class="min-h-screen text-slate-100 flex">
  <!-- Sidebar -->
  <aside class="sidebar w-[260px] min-h-screen fixed left-0 top-0 p-5 flex flex-col z-40">
    <a href="/quantpath/frontend/index.html" class="flex items-center gap-3 mb-8 group">
      <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-indigo-500/30 group-hover:shadow-indigo-500/50 transition">Q</div>
      <div>
        <div class="text-lg font-bold text-white">QuantPath</div>
        <div class="text-xs text-slate-500">Stock Analytics</div>
      </div>
    </a>

    <div class="text-xs text-slate-600 uppercase tracking-wider font-semibold mb-3 px-2">Main Menu</div>
    <nav class="space-y-1 mb-8">
      <a href="/quantpath/frontend/dashboard.php" class="sidebar-link active">
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
      <a href="/quantpath/backend/logout.php" class="sidebar-link text-red-400 hover:bg-red-500/10 hover:text-red-300">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        Logout
      </a>
    </nav>

    <div class="mt-auto pt-6">
      <div class="glass rounded-xl p-4">
        <div class="text-sm font-semibold text-white mb-1 flex items-center gap-1.5"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2"><path d="M9 18h6M10 22h4M12 2a7 7 0 0 1 4 12.7V17H8v-2.3A7 7 0 0 1 12 2z"/></svg> Quick Tip</div>
        <p class="text-xs text-slate-400 leading-relaxed">Use Indian BSE stock tickers like RELIANCE.BSE, TCS.BSE for accurate Indian market data.</p>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="ml-[260px] flex-1 min-h-screen">
    <!-- Top Bar -->
    <header class="sticky top-0 z-30 glass border-b border-white/5 px-8 py-4 flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-white">Dashboard</h1>
        <p class="text-xs text-slate-500">Welcome back! Here's your simulation overview.</p>
      </div>
      <div class="flex items-center gap-4">
        <?php if ($user_id): ?>
          <div class="flex items-center gap-3">
            <a href="/quantpath/frontend/profile.php" class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 rounded-full flex items-center justify-center text-white text-sm font-bold transition shadow-lg shadow-indigo-500/30">
              <?php echo strtoupper(substr($user_name, 0, 1)); ?>
            </a>
          </div>
        <?php endif; ?>
      </div>
    </header>

    <main class="px-8 py-6">
      <?php if (!$user_id): ?>
        <div class="flex items-center justify-center min-h-[60vh]">
          <div class="glass rounded-2xl p-12 text-center max-w-md">
            <div class="mb-4 text-slate-400"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" class="mx-auto" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
            <h2 class="text-2xl font-bold text-white mb-2">Login Required</h2>
            <p class="text-slate-400 mb-6">Sign in to view your dashboard, simulations, and analytics.</p>
            <a href="/quantpath/frontend/login.php" class="inline-block px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold">Sign In</a>
          </div>
        </div>
      <?php else: ?>

      <!-- Stats Row -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card glass rounded-xl p-5 fade-in fade-in-d1">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-indigo-500/15 rounded-lg flex items-center justify-center">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <span class="badge bg-indigo-500/15 text-indigo-300">Total</span>
          </div>
          <div class="text-3xl font-bold text-white"><?php echo $stats['total']; ?></div>
          <div class="text-xs text-slate-500 mt-1">Simulations Run</div>
        </div>

        <div class="stat-card glass rounded-xl p-5 fade-in fade-in-d2">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-green-500/15 rounded-lg flex items-center justify-center">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
            </div>
            <span class="badge bg-green-500/15 text-green-300">Tracking</span>
          </div>
          <div class="text-3xl font-bold text-white"><?php echo $stock_count; ?></div>
          <div class="text-xs text-slate-500 mt-1">Unique Stocks</div>
        </div>

        <div class="stat-card glass rounded-xl p-5 fade-in fade-in-d3">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-blue-500/15 rounded-lg flex items-center justify-center">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </div>
            <span class="badge bg-blue-500/15 text-blue-300">Avg</span>
          </div>
          <div class="text-3xl font-bold text-white">₹<?php echo number_format($stats['avgExpected'], 0); ?></div>
          <div class="text-xs text-slate-500 mt-1">Avg Initial Price</div>
        </div>

        <div class="stat-card glass rounded-xl p-5 fade-in fade-in-d4">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-purple-500/15 rounded-lg flex items-center justify-center">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            </div>
            <span class="badge bg-purple-500/15 text-purple-300">Drift</span>
          </div>
          <div class="text-3xl font-bold text-white"><?php echo $stats['totalDrift']; ?></div>
          <div class="text-xs text-slate-500 mt-1">Avg Drift (μ)</div>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Simulations (2 cols) -->
        <div class="lg:col-span-2">
          <div class="glass rounded-2xl p-6 fade-in">
            <div class="flex justify-between items-center mb-5">
              <div>
                <h2 class="text-lg font-bold text-white">Recent Simulations</h2>
                <p class="text-xs text-slate-500"><?php echo count($simulations); ?> total simulations</p>
              </div>
              <?php if (!empty($simulations)): ?>
              <button onclick="exportAllSimulations()" class="px-3 py-1.5 bg-green-500/15 text-green-300 hover:bg-green-500/25 rounded-lg text-xs font-medium transition flex items-center gap-1"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg> Export All</button>
              <?php endif; ?>
            </div>

            <?php if (empty($simulations)): ?>
              <div class="flex flex-col items-center justify-center py-12">
                <div class="mb-3 text-slate-500"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" class="mx-auto" stroke="currentColor" stroke-width="1.5"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
                <p class="text-slate-400 mb-4">No simulations yet</p>
                <a href="/quantpath/frontend/simulation.php" class="px-5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg text-sm font-semibold">Create First Simulation</a>
              </div>
            <?php else: ?>
              <div class="space-y-3 max-h-[500px] overflow-y-auto scrollbar-hide">
                <?php foreach ($simulations as $idx => $sim): ?>
                  <?php $params = json_decode($sim['parameters'], true); $results = json_decode($sim['results_json'], true); ?>
                  <div class="sim-card bg-slate-900/50 rounded-xl p-4 group">
                    <div class="flex justify-between items-start">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-500/15 rounded-lg flex items-center justify-center text-indigo-300 font-bold text-sm">
                          <?php echo strtoupper(substr($sim['stock_symbol'], 0, 2)); ?>
                        </div>
                        <div>
                          <h3 class="font-semibold text-white group-hover:text-indigo-300 transition"><?php echo htmlspecialchars($sim['stock_symbol']); ?></h3>
                          <p class="text-xs text-slate-500"><?php echo htmlspecialchars($sim['model_used']); ?> • <?php echo date('M d, Y H:i', strtotime($sim['created_at'])); ?></p>
                        </div>
                      </div>
                      <div class="flex items-center gap-2">
                        <button onclick="downloadSim(<?php echo htmlspecialchars(json_encode($sim)); ?>)" class="px-2 py-1 bg-indigo-500/15 text-indigo-300 rounded text-xs hover:bg-indigo-500/25 transition flex items-center justify-center" title="Download CSV"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg></button>
                        <button onclick="deleteSim(<?php echo $sim['id']; ?>, this)" class="px-2 py-1 bg-red-500/10 text-red-400 rounded text-xs hover:bg-red-500/20 transition flex items-center justify-center" title="Delete"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                      </div>
                    </div>
                    <div class="grid grid-cols-4 gap-3 mt-3">
                      <div class="text-center">
                        <div class="text-xs text-slate-500">S₀</div>
                        <div class="text-sm font-semibold text-green-300">₹<?php echo number_format($params['S0'] ?? 0, 0); ?></div>
                      </div>
                      <div class="text-center">
                        <div class="text-xs text-slate-500">μ</div>
                        <div class="text-sm font-semibold text-blue-300"><?php echo $params['mu'] ?? '—'; ?></div>
                      </div>
                      <div class="text-center">
                        <div class="text-xs text-slate-500">σ</div>
                        <div class="text-sm font-semibold text-purple-300"><?php echo $params['sigma'] ?? '—'; ?></div>
                      </div>
                      <div class="text-center">
                        <div class="text-xs text-slate-500">Paths</div>
                        <div class="text-sm font-semibold text-orange-300"><?php echo $params['paths'] ?? '—'; ?></div>
                      </div>
                    </div>
                    <?php if ($results): ?>
                    <div class="mt-3 pt-3 border-t border-white/5 grid grid-cols-3 gap-3">
                      <div class="text-center">
                        <div class="text-xs text-slate-500">Expected</div>
                        <div class="text-sm font-semibold text-indigo-300">₹<?php echo number_format($results['mean'] ?? 0, 2); ?></div>
                      </div>
                      <div class="text-center">
                        <div class="text-xs text-slate-500">Median</div>
                        <div class="text-sm font-semibold text-green-300">₹<?php echo number_format($results['median'] ?? 0, 2); ?></div>
                      </div>
                      <div class="text-center">
                        <div class="text-xs text-slate-500">Std Dev</div>
                        <div class="text-sm font-semibold text-orange-300">₹<?php echo number_format($results['stddev'] ?? 0, 2); ?></div>
                      </div>
                    </div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
          <!-- Watchlist Widget -->
          <div class="glass rounded-2xl p-6 fade-in fade-in-d2">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-bold text-white">Watchlist</h3>
              <a href="/quantpath/frontend/watchlist.php" class="text-xs text-indigo-400 hover:text-indigo-300 transition">View All →</a>
            </div>
            <?php if (empty($watchlist_items)): ?>
              <div class="text-center py-6">
                <div class="mb-2 text-slate-500"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" class="mx-auto" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                <p class="text-sm text-slate-500 mb-3">No stocks in watchlist</p>
                <a href="/quantpath/frontend/watchlist.php" class="text-xs text-indigo-400 hover:text-indigo-300">Add stocks →</a>
              </div>
            <?php else: ?>
              <div class="space-y-2">
                <?php foreach ($watchlist_items as $wl): ?>
                <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl hover:bg-slate-900/70 transition">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-yellow-500/15 rounded-lg flex items-center justify-center text-yellow-300 text-xs font-bold">
                      <?php echo strtoupper(substr($wl['stock_symbol'], 0, 2)); ?>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-white"><?php echo htmlspecialchars($wl['stock_symbol']); ?></div>
                      <div class="text-xs text-slate-500"><?php echo date('M d', strtotime($wl['added_at'])); ?></div>
                    </div>
                  </div>
                  <a href="/quantpath/frontend/simulation.php?ticker=<?php echo urlencode($wl['stock_symbol']); ?>" class="text-xs text-indigo-400 hover:text-indigo-300">Simulate →</a>
                </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Simulation Distribution Chart -->
          <div class="glass rounded-2xl p-6 fade-in fade-in-d3">
            <h3 class="text-lg font-bold text-white mb-1">Stock Distribution</h3>
            <p class="text-xs text-slate-500 mb-4">Simulations by stock</p>
            <div style="height: 200px;">
              <canvas id="stockPieChart"></canvas>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="glass rounded-2xl p-6 fade-in fade-in-d4">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wide mb-4">Quick Actions</h3>
            <div class="space-y-2">
              <a href="/quantpath/frontend/simulation.php" class="flex items-center gap-3 p-3 bg-indigo-500/10 hover:bg-indigo-500/20 rounded-xl transition text-sm text-indigo-300">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg> Run New Simulation
              </a>
              <a href="/quantpath/frontend/compare.php" class="flex items-center gap-3 p-3 bg-purple-500/10 hover:bg-purple-500/20 rounded-xl transition text-sm text-purple-300">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg> Compare Simulations
              </a>
              <a href="/quantpath/frontend/watchlist.php" class="flex items-center gap-3 p-3 bg-yellow-500/10 hover:bg-yellow-500/20 rounded-xl transition text-sm text-yellow-300">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> Manage Watchlist
              </a>
            </div>
          </div>
        </div>
      </div>

      <?php endif; ?>
    </main>
  </div>

  <script src="/quantpath/assets/js/api.js?v=2"></script>
  <script>
    // Stock distribution pie chart
    <?php if (!empty($tracked_stocks)): ?>
    (function() {
      const stockCounts = {};
      const sims = <?php echo json_encode($simulations); ?>;
      sims.forEach(s => { stockCounts[s.stock_symbol] = (stockCounts[s.stock_symbol] || 0) + 1; });
      const labels = Object.keys(stockCounts);
      const data = Object.values(stockCounts);
      const colors = labels.map((_, i) => `hsl(${220 + i * 40}, 70%, 60%)`);

      new Chart(document.getElementById('stockPieChart'), {
        type: 'doughnut',
        data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 0, hoverOffset: 8 }] },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { color: '#94a3b8', font: { size: 11 }, padding: 12, usePointStyle: true, pointStyleWidth: 8 } }
          },
          cutout: '65%'
        }
      });
    })();
    <?php endif; ?>

    function downloadSim(sim) {
      const params = typeof sim.parameters === 'string' ? JSON.parse(sim.parameters) : sim.parameters;
      const csv = `Stock Symbol,Model,Date,Initial Price (₹),Drift (μ),Volatility (σ),Paths,Time Steps\n"${sim.stock_symbol}","${sim.model_used}","${sim.created_at}",${params.S0},${params.mu},${params.sigma},${params.paths},${params.steps}`;
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = `${sim.stock_symbol}_${new Date().toISOString().split('T')[0]}.csv`;
      link.click();
      Toast.show('CSV downloaded', 'success');
    }

    async function deleteSim(id, btn) {
      if (!confirm('Delete this simulation?')) return;
      const card = btn.closest('.sim-card');
      try {
        await API.deleteSimulation(id);
        card.style.opacity = '0';
        card.style.transform = 'translateX(-20px)';
        setTimeout(() => { card.remove(); Toast.show('Simulation deleted', 'success'); }, 300);
      } catch (e) {
        Toast.show('Error: ' + e.message, 'error');
      }
    }

    function exportAllSimulations() {
      const sims = <?php echo json_encode($simulations); ?>;
      let csv = 'Stock,Model,Date,S₀ (₹),μ,σ,Paths,Steps\n';
      sims.forEach(s => {
        const p = typeof s.parameters === 'string' ? JSON.parse(s.parameters) : s.parameters;
        csv += `"${s.stock_symbol}","${s.model_used}","${s.created_at}",${p.S0},${p.mu},${p.sigma},${p.paths},${p.steps}\n`;
      });
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = `quantpath_all_${new Date().toISOString().split('T')[0]}.csv`;
      link.click();
      Toast.show('All simulations exported', 'success');
    }
  </script>
</body>
</html>
