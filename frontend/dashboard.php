<?php
// frontend/dashboard.php
session_start();
require_once __DIR__ . '/../private_config/config.php';

// Fetch simulations for logged-in user
$simulations = [];
$user_name = '';
$user_id = '';

if (!empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? 'User';
    
    $stmt = $conn->prepare("SELECT id, stock_symbol, model_used, parameters, results_json, created_at FROM simulations WHERE user_id=? ORDER BY created_at DESC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $simulations = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
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
          <a href="/quantpath/frontend/register.html" class="px-3 py-2 bg-white/10 rounded">Sign up</a>
        <?php endif; ?>
        <a href="/quantpath/frontend/simulation.php" class="px-3 py-2 bg-indigo-600 text-white rounded">Simulation</a>
      </nav>
    </div>
  </header>

  <main class="max-w-6xl mx-auto p-6">
    <section class="bg-white/5 backdrop-blur-sm p-6 rounded-lg shadow-lg border border-white/10">
      <h2 class="text-2xl font-semibold mb-4">Saved Simulations</h2>

      <?php if (!$user_id): ?>
        <div class="p-4 bg-blue-500/10 border border-blue-500/30 rounded text-center">
          <p class="text-slate-300">Please <a href="/quantpath/frontend/login.html" class="text-blue-400 hover:underline">log in</a> to view your simulations.</p>
        </div>
      <?php elseif (empty($simulations)): ?>
        <div class="p-4 bg-slate-500/10 border border-slate-500/30 rounded text-center">
          <p class="text-slate-300">No saved simulations yet. <a href="/quantpath/frontend/simulation.php" class="text-indigo-400 hover:underline">Create one</a></p>
        </div>
      <?php else: ?>
        <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
          <?php foreach ($simulations as $sim): ?>
            <div class="bg-white/5 border border-white/10 rounded p-4 hover:bg-white/10 transition">
              <h3 class="font-medium text-lg text-white mb-2"><?php echo htmlspecialchars($sim['stock_symbol']); ?> — <?php echo htmlspecialchars($sim['model_used']); ?></h3>
              <p class="text-xs text-white/50 mb-2"><?php echo date('Y-m-d H:i', strtotime($sim['created_at'])); ?></p>
              <details class="text-sm text-white/70 cursor-pointer">
                <summary>Parameters</summary>
                <pre class="mt-2 text-xs bg-black/20 p-2 rounded overflow-auto"><?php echo htmlspecialchars($sim['parameters']); ?></pre>
              </details>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
