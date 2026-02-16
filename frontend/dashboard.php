<?php
// frontend/dashboard.php
session_start();
require_once __DIR__ . '/../private_config/config.php';

// Fetch simulations for logged-in user
$simulations = [];
$user_name = '';
$user_id = '';
$stats = ['total' => 0, 'avgExpected' => 0, 'totalDrift' => 0];
$tracked_stocks = [];
$stock_count = 0;

if (!empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? 'User';
    
    $stmt = $conn->prepare("SELECT id, stock_symbol, model_used, parameters, results_json, created_at FROM simulations WHERE user_id=? ORDER BY created_at DESC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $simulations = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $stats['total'] = count($simulations);
    $stock_symbols = [];
    
    if ($stats['total'] > 0) {
        $avgExp = 0;
        $avgDrift = 0;
        
        foreach ($simulations as $s) {
            $params = json_decode($s['parameters'], true);
            $avgExp += $params['S0'] ?? 0;
            $avgDrift += $params['mu'] ?? 0;
            $stock_symbols[] = $s['stock_symbol'];
        }
        
        $stats['avgExpected'] = round($avgExp / $stats['total'], 2);
        $stats['totalDrift'] = round($avgDrift / $stats['total'], 4);
        
        // Get unique stocks tracked
        $tracked_stocks = array_unique($stock_symbols);
        $stock_count = count($tracked_stocks);
    }
}

$conn->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard — QuantPath</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpaca-js@latest/dist/alpaca.web.min.js"></script>
  <link rel="stylesheet" href="/quantpath/assets/css/tailwind.css">
  <style>
    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .animate-slide-in {
      animation: slideInUp 0.6s ease-out;
    }
    
    .stock-ticker {
      animation: slideInUp 0.3s ease-out;
    }
    
    @keyframes pulse-soft {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.8; }
    }
    
    .pulse-soft {
      animation: pulse-soft 3s ease-in-out infinite;
    }
  </style>
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
          <div class="text-sm text-slate-300">Hello, <span class="font-semibold text-indigo-300"><?php echo htmlspecialchars($user_name); ?></span></div>
          <a href="/quantpath/frontend/simulation.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">New Simulation</a>
          <a href="/quantpath/backend/logout.php" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg transition">Logout</a>
        <?php else: ?>
          <a href="/quantpath/frontend/login.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">Log in</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-6 py-8">
    <!-- Statistics Cards -->
    <?php if ($user_id): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 animate-slide-in">
      <div class="bg-gradient-to-br from-indigo-500/10 to-purple-500/10 backdrop-blur-sm border border-indigo-500/30 rounded-xl p-6 hover:from-indigo-500/20 hover:to-purple-500/20 transition">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-slate-400 text-xs font-medium uppercase tracking-wide mb-2">Total Simulations</div>
            <div class="text-4xl font-bold text-indigo-300"><?php echo $stats['total']; ?></div>
          </div>
          <div class="text-4xl opacity-30">📊</div>
        </div>
      </div>
      
      <div class="bg-gradient-to-br from-green-500/10 to-emerald-500/10 backdrop-blur-sm border border-green-500/30 rounded-xl p-6 hover:from-green-500/20 hover:to-emerald-500/20 transition">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-slate-400 text-xs font-medium uppercase tracking-wide mb-2">Tracked Stocks</div>
            <div class="text-4xl font-bold text-green-300"><?php echo $stock_count; ?></div>
          </div>
          <div class="text-4xl opacity-30">📈</div>
        </div>
      </div>
      
      <div class="bg-gradient-to-br from-blue-500/10 to-cyan-500/10 backdrop-blur-sm border border-blue-500/30 rounded-xl p-6 hover:from-blue-500/20 hover:to-cyan-500/20 transition">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-slate-400 text-xs font-medium uppercase tracking-wide mb-2">Avg Initial Price</div>
            <div class="text-3xl font-bold text-blue-300">₹<?php echo $stats['avgExpected']; ?></div>
          </div>
          <div class="text-4xl opacity-30">💰</div>
        </div>
      </div>
      
      <div class="bg-gradient-to-br from-purple-500/10 to-pink-500/10 backdrop-blur-sm border border-purple-500/30 rounded-xl p-6 hover:from-purple-500/20 hover:to-pink-500/20 transition">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-slate-400 text-xs font-medium uppercase tracking-wide mb-2">Avg Drift (μ)</div>
            <div class="text-3xl font-bold text-purple-300"><?php echo $stats['totalDrift']; ?></div>
          </div>
          <div class="text-4xl opacity-30">⚡</div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <section class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-8 shadow-xl">
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">Your Simulations</h2>
        <?php if ($user_id && !empty($simulations)): ?>
        <button onclick="exportAllSimulations()" class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-green-500/25">↓ Export All CSV</button>
        <?php endif; ?>
      </div>

      <?php if (!$user_id): ?>
        <div class="p-6 bg-blue-500/10 border border-blue-500/30 rounded-lg text-center">
          <p class="text-slate-300">Please <a href="/quantpath/frontend/login.php" class="text-blue-400 hover:underline font-semibold">log in</a> to view and manage your simulations.</p>
        </div>
      <?php elseif (empty($simulations)): ?>
        <div class="p-8 bg-slate-500/10 border border-slate-500/30 rounded-lg text-center">
          <p class="text-slate-300 mb-4">No simulations yet. Start by creating your first simulation!</p>
          <a href="/quantpath/frontend/simulation.php" class="inline-block px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition">Create First Simulation</a>
        </div>
      <?php else: ?>
        <div class="grid gap-6 grid-cols-1 lg:grid-cols-2">
          <?php foreach (array_slice($simulations, 0, 6) as $idx => $sim): ?>
            <div class="stock-ticker bg-gradient-to-br from-slate-800/70 to-slate-900/50 border border-white/5 rounded-lg p-6 hover:border-white/20 hover:from-slate-800 hover:to-slate-800/70 transition-all duration-300 group" style="animation-delay: <?php echo $idx * 0.1; ?>s">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <h3 class="font-bold text-2xl text-white group-hover:text-indigo-300 transition"><?php echo htmlspecialchars($sim['stock_symbol']); ?></h3>
                  <p class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars($sim['model_used']); ?> • <?php echo date('M d, Y H:i', strtotime($sim['created_at'])); ?></p>
                </div>
                <div class="flex gap-2">
                  <button onclick="downloadSim(<?php echo htmlspecialchars(json_encode($sim)); ?>)" class="px-3 py-1.5 bg-indigo-600/70 hover:bg-indigo-600 text-xs text-white rounded transition shadow-lg shadow-indigo-500/20" title="Download CSV">📥</button>
                  <button onclick="viewDetails(this)" class="px-3 py-1.5 bg-slate-700/70 hover:bg-slate-700 text-xs text-white rounded transition">📋</button>
                </div>
              </div>
              
              <?php $params = json_decode($sim['parameters'], true); ?>
              <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                <div class="bg-white/5 rounded p-3 border border-white/10">
                  <span class="text-slate-400 text-xs">Initial Price</span>
                  <div class="font-bold text-green-300">₹<?php echo $params['S0'] ?? 'N/A'; ?></div>
                </div>
                <div class="bg-white/5 rounded p-3 border border-white/10">
                  <span class="text-slate-400 text-xs">Drift (μ)</span>
                  <div class="font-bold text-blue-300"><?php echo $params['mu'] ?? 'N/A'; ?></div>
                </div>
                <div class="bg-white/5 rounded p-3 border border-white/10">
                  <span class="text-slate-400 text-xs">Volatility (σ)</span>
                  <div class="font-bold text-purple-300"><?php echo $params['sigma'] ?? 'N/A'; ?></div>
                </div>
                <div class="bg-white/5 rounded p-3 border border-white/10">
                  <span class="text-slate-400 text-xs">Paths</span>
                  <div class="font-bold text-orange-300"><?php echo $params['paths'] ?? 'N/A'; ?></div>
                </div>
              </div>
              
              <details class="text-xs text-slate-400 cursor-pointer group/details">
                <summary class="font-semibold hover:text-white transition py-2">Advanced Parameters →</summary>
                <div class="mt-2 text-xs bg-black/40 p-3 rounded border border-white/5 max-h-48 overflow-auto">
                  <pre class="text-slate-300"><?php echo json_encode(json_decode($sim['parameters'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?></pre>
                </div>
              </details>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if (count($simulations) > 6): ?>
        <div class="text-center mt-8 text-slate-400 text-sm py-4 border-t border-white/5">
          Showing 6 of <span class="font-bold text-white"><?php echo count($simulations); ?></span> simulations
        </div>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  </main>

  <script src="/quantpath/assets/js/api.js"></script>
  <script>
    // Enhanced stock ticker fetching for live market data
    async function fetchLiveMarketData(symbol) {
      try {
        const res = await fetch(`/quantpath/backend/fetch_stock.php?symbol=${symbol}`);
        if (!res.ok) return null;
        const data = await res.json();
        return data;
      } catch (err) {
        console.error(`Error fetching ${symbol}:`, err);
        return null;
      }
    }

    // View simulation details modal
    function viewDetails(btn) {
      const card = btn.closest('div.stock-ticker');
      const details = card.querySelector('details');
      if (details) {
        details.open = !details.open;
      }
    }

    function downloadSim(sim) {
      const params = JSON.parse(sim.parameters);
      const csv = `Stock Symbol,Model Used,Date Created,Initial Price (S0),Drift (μ),Volatility (σ),Simulation Paths,Time Steps\n"${sim.stock_symbol}","${sim.model_used}","${sim.created_at}",${params.S0},${params.mu},${params.sigma},${params.paths},${params.steps}`;
      
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      const url = URL.createObjectURL(blob);
      
      link.setAttribute('href', url);
      link.setAttribute('download', `${sim.stock_symbol}_${new Date().toISOString().split('T')[0]}.csv`);
      link.style.visibility = 'hidden';
      
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }

    function exportAllSimulations() {
      const sims = <?php echo json_encode($simulations); ?>;
      let csv = 'Stock Symbol,Model Used,Date Created,Initial Price (S0),Drift (μ),Volatility (σ),Simulation Paths,Time Steps\n';
      
      sims.forEach(s => {
        const params = JSON.parse(s.parameters);
        csv += `"${s.stock_symbol}","${s.model_used}","${s.created_at}",${params.S0},${params.mu},${params.sigma},${params.paths},${params.steps}\n`;
      });
      
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      const url = URL.createObjectURL(blob);
      
      link.setAttribute('href', url);
      link.setAttribute('download', `quantpath_all_simulations_${new Date().toISOString().split('T')[0]}.csv`);
      link.style.visibility = 'hidden';
      
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }

    // Load stock tickers on page load
    document.addEventListener('DOMContentLoaded', () => {
      const stockSymbols = <?php echo json_encode($tracked_stocks); ?>;
      
      // Animate cards on load
      const cards = document.querySelectorAll('.stock-ticker');
      cards.forEach((card, idx) => {
        card.style.animationDelay = `${idx * 0.1}s`;
      });
    });
  </script>
</body>
</html>
