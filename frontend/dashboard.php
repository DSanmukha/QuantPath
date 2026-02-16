<?php
// frontend/dashboard.php
session_start();
require_once __DIR__ . '/../private_config/config.php';

// Fetch simulations for logged-in user
$simulations = [];
$user_name = '';
$user_id = '';
$stats = ['total' => 0, 'avgExpected' => 0];

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
    if ($stats['total'] > 0) {
        $avgExp = 0;
        foreach ($simulations as $s) {
            $params = json_decode($s['parameters'], true);
            $avgExp += $params['S0'] ?? 0;
        }
        $stats['avgExpected'] = round($avgExp / $stats['total'], 2);
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
          <div class="text-sm text-slate-300">Hello, <span class="font-semibold text-indigo-300"><?php echo htmlspecialchars($user_name); ?></span></div>
          <a href="/quantpath/frontend/simulation.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">New Simulation</a>
          <a href="/quantpath/backend/logout.php" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg transition">Logout</a>
        <?php else: ?>
          <a href="/quantpath/frontend/login.html" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">Log in</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-6 py-8">
    <!-- Statistics Cards -->
    <?php if ($user_id): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6 hover:bg-white/10 transition">
        <div class="text-slate-400 text-sm font-medium mb-2">Total Simulations</div>
        <div class="text-3xl font-bold text-indigo-300"><?php echo $stats['total']; ?></div>
      </div>
      <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6 hover:bg-white/10 transition">
        <div class="text-slate-400 text-sm font-medium mb-2">Avg Initial Price</div>
        <div class="text-3xl font-bold text-green-300">$<?php echo $stats['avgExpected']; ?></div>
      </div>
      <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6 hover:bg-white/10 transition">
        <div class="text-slate-400 text-sm font-medium mb-2">Last Updated</div>
        <div class="text-lg font-semibold text-slate-300"><?php echo !empty($simulations) ? date('M d, Y', strtotime($simulations[0]['created_at'])) : '—'; ?></div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <section class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-8 shadow-xl">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Simulations</h2>
        <?php if ($user_id && !empty($simulations)): ?>
        <button onclick="exportAllSimulations()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition">↓ Export CSV</button>
        <?php endif; ?>
      </div>

      <?php if (!$user_id): ?>
        <div class="p-6 bg-blue-500/10 border border-blue-500/30 rounded-lg text-center">
          <p class="text-slate-300">Please <a href="/quantpath/frontend/login.html" class="text-blue-400 hover:underline font-semibold">log in</a> to view and manage your simulations.</p>
        </div>
      <?php elseif (empty($simulations)): ?>
        <div class="p-6 bg-slate-500/10 border border-slate-500/30 rounded-lg text-center">
          <p class="text-slate-300">No simulations yet. <a href="/quantpath/frontend/simulation.php" class="text-indigo-400 hover:underline font-semibold">Create your first simulation</a></p>
        </div>
      <?php else: ?>
        <div class="grid gap-6 grid-cols-1 lg:grid-cols-2">
          <?php foreach (array_slice($simulations, 0, 6) as $sim): ?>
            <div class="bg-slate-800/50 border border-white/5 rounded-lg p-5 hover:bg-slate-800/80 hover:border-white/10 transition">
              <div class="flex justify-between items-start mb-3">
                <div>
                  <h3 class="font-bold text-lg text-white"><?php echo htmlspecialchars($sim['stock_symbol']); ?></h3>
                  <p class="text-xs text-slate-400"><?php echo htmlspecialchars($sim['model_used']); ?> • <?php echo date('M d, Y H:i', strtotime($sim['created_at'])); ?></p>
                </div>
                <button onclick="downloadSim(<?php echo htmlspecialchars(json_encode($sim)); ?>)" class="px-2 py-1 bg-indigo-600/50 hover:bg-indigo-600 text-xs text-white rounded transition">Download</button>
              </div>
              <details class="text-sm text-slate-300 cursor-pointer">
                <summary class="font-semibold hover:text-white">View Parameters</summary>
                <pre class="mt-2 text-xs bg-black/30 p-3 rounded overflow-auto mt-2"><?php echo htmlspecialchars($sim['parameters']); ?></pre>
              </details>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if (count($simulations) > 6): ?>
        <div class="text-center mt-6 text-slate-400 text-sm">
          Showing 6 of <?php echo count($simulations); ?> simulations
        </div>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  </main>

  <script src="/quantpath/assets/js/api.js"></script>
  <script>
    function downloadSim(sim) {
      const csv = `Stock,Model,Date,Parameters\n"${sim.stock_symbol}","${sim.model_used}","${sim.created_at}","${sim.parameters.replace(/"/g, '""')}"`;
      const blob = new Blob([csv], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `${sim.stock_symbol}_${new Date().getTime()}.csv`;
      a.click();
      window.URL.revokeObjectURL(url);
    }

    function exportAllSimulations() {
      const sims = <?php echo json_encode($simulations); ?>;
      let csv = 'Stock,Model,Date,Initial Price,Drift,Volatility,Paths\n';
      sims.forEach(s => {
        const p = JSON.parse(s.parameters);
        csv += `"${s.stock_symbol}","${s.model_used}","${s.created_at}",${p.S0},${p.mu},${p.sigma},${p.paths}\n`;
      });
      const blob = new Blob([csv], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `quantpath_export_${new Date().getTime()}.csv`;
      a.click();
      window.URL.revokeObjectURL(url);
    }
  </script>
</body>
</html>
