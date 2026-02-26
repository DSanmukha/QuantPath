<?php
// frontend/watchlist.php
session_start();
require_once __DIR__ . '/../private_config/config.php';

$user_id = $_SESSION['user_id'] ?? '';
$user_name = $_SESSION['user_name'] ?? 'Guest';

$watchlist_items = [];
if ($user_id) {
    $stmt = $conn->prepare("SELECT id, stock_symbol, added_at FROM watchlist WHERE user_id=? ORDER BY added_at DESC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $watchlist_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$conn->close();

// Pre-defined trending stocks data with prices and signals
$trending_stocks = [
    ['symbol' => 'RELIANCE.BSE', 'name' => 'Reliance Industries', 'sector' => 'Energy', 'price' => 2945.60, 'change' => +1.82],
    ['symbol' => 'TCS.BSE', 'name' => 'Tata Consultancy', 'sector' => 'IT', 'price' => 3872.15, 'change' => +0.65],
    ['symbol' => 'INFY.BSE', 'name' => 'Infosys Ltd', 'sector' => 'IT', 'price' => 1856.30, 'change' => -0.42],
    ['symbol' => 'HDFCBANK.BSE', 'name' => 'HDFC Bank', 'sector' => 'Banking', 'price' => 1724.80, 'change' => +1.15],
    ['symbol' => 'ICICIBANK.BSE', 'name' => 'ICICI Bank', 'sector' => 'Banking', 'price' => 1089.55, 'change' => +0.93],
    ['symbol' => 'WIPRO.BSE', 'name' => 'Wipro Ltd', 'sector' => 'IT', 'price' => 562.40, 'change' => -1.27],
    ['symbol' => 'SBIN.BSE', 'name' => 'State Bank of India', 'sector' => 'Banking', 'price' => 745.90, 'change' => +2.14],
    ['symbol' => 'BHARTIARTL.BSE', 'name' => 'Bharti Airtel', 'sector' => 'Telecom', 'price' => 1632.75, 'change' => +0.38],
    ['symbol' => 'KOTAKBANK.BSE', 'name' => 'Kotak Mahindra Bank', 'sector' => 'Banking', 'price' => 1798.20, 'change' => -0.56],
    ['symbol' => 'LT.BSE', 'name' => 'Larsen & Toubro', 'sector' => 'Infrastructure', 'price' => 3428.90, 'change' => +1.47],
];

$top_performers = [
    ['symbol' => 'TATAMOTORS.BSE', 'name' => 'Tata Motors', 'sector' => 'Auto', 'badge' => 'Top Gainer', 'price' => 987.45, 'change' => +4.82],
    ['symbol' => 'ADANIENT.BSE', 'name' => 'Adani Enterprises', 'sector' => 'Conglomerate', 'badge' => 'High Volume', 'price' => 3124.60, 'change' => +3.56],
    ['symbol' => 'BAJFINANCE.BSE', 'name' => 'Bajaj Finance', 'sector' => 'NBFC', 'badge' => 'Momentum', 'price' => 7235.80, 'change' => +2.91],
    ['symbol' => 'HCLTECH.BSE', 'name' => 'HCL Technologies', 'sector' => 'IT', 'badge' => 'Breakout', 'price' => 1789.30, 'change' => +2.14],
    ['symbol' => 'MARUTI.BSE', 'name' => 'Maruti Suzuki', 'sector' => 'Auto', 'badge' => 'Trending', 'price' => 12450.50, 'change' => +1.73],
];

$low_performers = [
    ['symbol' => 'VEDL.BSE', 'name' => 'Vedanta Ltd', 'sector' => 'Mining', 'badge' => 'Top Loser', 'price' => 412.30, 'change' => -4.15],
    ['symbol' => 'INDUSINDBK.BSE', 'name' => 'IndusInd Bank', 'sector' => 'Banking', 'badge' => 'Under Pressure', 'price' => 1045.60, 'change' => -3.28],
    ['symbol' => 'ZOMATO.BSE', 'name' => 'Zomato Ltd', 'sector' => 'Tech', 'badge' => 'Sell Signal', 'price' => 178.90, 'change' => -2.76],
    ['symbol' => 'PAYTM.BSE', 'name' => 'One97 Communications', 'sector' => 'Fintech', 'badge' => 'Weak', 'price' => 398.25, 'change' => -2.45],
    ['symbol' => 'YESBANK.BSE', 'name' => 'Yes Bank', 'sector' => 'Banking', 'badge' => 'Declining', 'price' => 22.65, 'change' => -1.92],
];

$watchlist_symbols = array_column($watchlist_items, 'stock_symbol');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Watchlist — QuantPath</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
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
    .stock-card { transition: all 0.3s; border: 1px solid rgba(255,255,255,0.04); }
    .stock-card:hover { border-color: rgba(234,179,8,0.3); transform: translateY(-2px); box-shadow: 0 15px 40px rgba(0,0,0,0.3); }
    .trending-card { transition: all 0.3s; cursor: pointer; }
    .trending-card:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); border-color: rgba(99,102,241,0.3); }
    @keyframes fadeIn { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .fade-in { animation: fadeIn 0.5s ease-out; }
    .loader { width:16px;height:16px;border:2px solid rgba(255,255,255,0.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;display:inline-block; }
    @keyframes spin { to { transform:rotate(360deg); } }
    .badge { padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .added-badge { background: rgba(16,185,129,0.15); color: #34d399; }
  </style>
</head>
<body class="min-h-screen text-slate-100 flex">
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
      <a href="/quantpath/frontend/watchlist.php" class="sidebar-link active">
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
        <h1 class="text-xl font-bold text-white">Watchlist</h1>
        <p class="text-xs text-slate-500">Track your favourite Indian stocks</p>
      </div>
      <?php if ($user_id): ?>
      <a href="/quantpath/frontend/profile.php" class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-bold transition shadow-lg shadow-indigo-500/30">
        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
      </a>
      <?php endif; ?>
    </header>

    <main class="px-8 py-6">
      <?php if (!$user_id): ?>
        <div class="flex items-center justify-center min-h-[60vh]">
          <div class="glass rounded-2xl p-12 text-center max-w-md">
            <div class="mb-4 text-slate-400">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" class="mx-auto" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Login Required</h2>
            <p class="text-slate-400 mb-6">Sign in to manage your watchlist.</p>
            <a href="/quantpath/frontend/login.php" class="inline-block px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold">Sign In</a>
          </div>
        </div>
      <?php else: ?>

      <!-- Add Stock -->
      <div class="glass rounded-2xl p-6 mb-6 fade-in">
        <h2 class="text-lg font-bold text-white mb-4">Add Stock to Watchlist</h2>
        <div class="flex gap-3 max-w-lg">
          <input id="addSymbol" type="text" placeholder="e.g. RELIANCE.BSE, TCS.BSE, INFY.BSE" class="flex-1 input-field px-4 py-3 rounded-xl text-white text-sm placeholder-slate-600" />
          <button id="addBtn" onclick="addStock()" class="px-6 py-3 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-600 hover:to-amber-600 text-black font-semibold rounded-xl text-sm transition shadow-lg shadow-yellow-500/20 flex items-center gap-1">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Add
          </button>
        </div>
      </div>

      <!-- Top 10 Trending Stocks -->
      <div class="glass rounded-2xl p-6 mb-6 fade-in">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
              Top 10 Trending Stocks
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Most actively traded Indian BSE stocks</p>
          </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
          <?php foreach ($trending_stocks as $ts): ?>
          <?php $isInWatchlist = in_array($ts['symbol'], $watchlist_symbols); $isUp = $ts['change'] >= 0; ?>
          <div class="trending-card glass rounded-xl p-4 text-center relative" id="trending-<?php echo htmlspecialchars($ts['symbol']); ?>">
            <div class="w-10 h-10 mx-auto mb-2 bg-indigo-500/15 rounded-lg flex items-center justify-center text-indigo-300 font-bold text-sm">
              <?php echo strtoupper(substr($ts['symbol'], 0, 2)); ?>
            </div>
            <div class="text-sm font-semibold text-white truncate"><?php echo htmlspecialchars($ts['symbol']); ?></div>
            <div class="text-xs text-slate-500 truncate"><?php echo $ts['name']; ?></div>
            <div class="mt-2 text-lg font-bold <?php echo $isUp ? 'text-green-400' : 'text-red-400'; ?>">₹<?php echo number_format($ts['price'], 2); ?></div>
            <div class="flex items-center justify-center gap-1 mt-1">
              <?php if ($isUp): ?>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="3"><path d="M18 15l-6-6-6 6"/></svg>
                <span class="text-xs font-semibold text-green-400">+<?php echo $ts['change']; ?>%</span>
              <?php else: ?>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="3"><path d="M6 9l6 6 6-6"/></svg>
                <span class="text-xs font-semibold text-red-400"><?php echo $ts['change']; ?>%</span>
              <?php endif; ?>
            </div>
            <div class="text-[10px] text-slate-600 mt-1"><?php echo $ts['sector']; ?></div>
            <?php if ($isInWatchlist): ?>
              <div class="mt-2 badge added-badge">Added</div>
            <?php else: ?>
              <button onclick="quickAdd('<?php echo htmlspecialchars($ts['symbol']); ?>', this)" class="mt-2 px-3 py-1 bg-indigo-500/15 text-indigo-300 rounded-lg text-xs font-medium hover:bg-indigo-500/25 transition w-full">
                + Add
              </button>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Top Performers -->
      <div class="glass rounded-2xl p-6 mb-6 fade-in">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
              Top Performers
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Stocks showing strong momentum and breakout patterns</p>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
          <?php foreach ($top_performers as $tp): ?>
          <?php $isInWatchlist = in_array($tp['symbol'], $watchlist_symbols); ?>
          <div class="trending-card glass rounded-xl p-4 relative" id="performer-<?php echo htmlspecialchars($tp['symbol']); ?>">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-10 h-10 bg-green-500/15 rounded-lg flex items-center justify-center text-green-300 font-bold text-sm flex-shrink-0">
                <?php echo strtoupper(substr($tp['symbol'], 0, 2)); ?>
              </div>
              <div class="min-w-0">
                <div class="text-sm font-semibold text-white truncate"><?php echo htmlspecialchars($tp['symbol']); ?></div>
                <div class="text-xs text-slate-500 truncate"><?php echo $tp['name']; ?></div>
              </div>
            </div>
            <div class="flex items-center justify-between">
              <div class="text-base font-bold text-green-400">₹<?php echo number_format($tp['price'], 2); ?></div>
              <div class="flex items-center gap-1">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="3"><path d="M18 15l-6-6-6 6"/></svg>
                <span class="text-xs font-semibold text-green-400">+<?php echo $tp['change']; ?>%</span>
              </div>
            </div>
            <div class="flex items-center justify-between mt-2">
              <span class="badge bg-green-500/15 text-green-300"><?php echo $tp['badge']; ?></span>
              <?php if ($isInWatchlist): ?>
                <span class="badge added-badge">Added</span>
              <?php else: ?>
                <button onclick="quickAdd('<?php echo htmlspecialchars($tp['symbol']); ?>', this)" class="px-3 py-1 bg-green-500/15 text-green-300 rounded-lg text-xs font-medium hover:bg-green-500/25 transition">
                  + Add
                </button>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Low Performers -->
      <div class="glass rounded-2xl p-6 mb-6 fade-in">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
              Low Performers
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Stocks under selling pressure — high risk, watch closely</p>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
          <?php foreach ($low_performers as $lp): ?>
          <?php $isInWatchlist = in_array($lp['symbol'], $watchlist_symbols); ?>
          <div class="trending-card glass rounded-xl p-4 relative" style="border-color:rgba(248,113,113,0.1)" id="low-<?php echo htmlspecialchars($lp['symbol']); ?>">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-10 h-10 bg-red-500/15 rounded-lg flex items-center justify-center text-red-300 font-bold text-sm flex-shrink-0">
                <?php echo strtoupper(substr($lp['symbol'], 0, 2)); ?>
              </div>
              <div class="min-w-0">
                <div class="text-sm font-semibold text-white truncate"><?php echo htmlspecialchars($lp['symbol']); ?></div>
                <div class="text-xs text-slate-500 truncate"><?php echo $lp['name']; ?></div>
              </div>
            </div>
            <div class="flex items-center justify-between">
              <div class="text-base font-bold text-red-400">₹<?php echo number_format($lp['price'], 2); ?></div>
              <div class="flex items-center gap-1">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="3"><path d="M6 9l6 6 6-6"/></svg>
                <span class="text-xs font-semibold text-red-400"><?php echo $lp['change']; ?>%</span>
              </div>
            </div>
            <div class="flex items-center justify-between mt-2">
              <span class="badge bg-red-500/15 text-red-300"><?php echo $lp['badge']; ?></span>
              <?php if ($isInWatchlist): ?>
                <span class="badge added-badge">Added</span>
              <?php else: ?>
                <button onclick="quickAdd('<?php echo htmlspecialchars($lp['symbol']); ?>', this)" class="px-3 py-1 bg-red-500/15 text-red-300 rounded-lg text-xs font-medium hover:bg-red-500/25 transition">
                  + Add
                </button>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- My Watchlist -->
      <div class="glass rounded-2xl p-6 fade-in">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#eab308" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              My Watchlist
            </h2>
            <span id="watchlist-count" class="px-2.5 py-0.5 bg-yellow-500/20 text-yellow-300 rounded-full text-xs font-bold border border-yellow-500/30 transition-all"><?php echo count($watchlist_items); ?></span>
          </div>
          <p class="text-xs text-slate-500" id="watchlist-status"><?php echo count($watchlist_items); ?> stocks tracked</p>
        </div>

        <div id="watchlist-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php foreach ($watchlist_items as $item): ?>
          <div class="stock-card glass rounded-xl p-5 fade-in" data-symbol="<?php echo htmlspecialchars($item['stock_symbol']); ?>">
            <div class="flex justify-between items-start mb-3">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-yellow-500/15 rounded-xl flex items-center justify-center text-yellow-300 font-bold text-lg">
                  <?php echo strtoupper(substr($item['stock_symbol'], 0, 2)); ?>
                </div>
                <div>
                  <h3 class="font-bold text-white text-lg"><?php echo htmlspecialchars($item['stock_symbol']); ?></h3>
                  <p class="text-xs text-slate-500">Added <?php echo date('M d, Y', strtotime($item['added_at'])); ?></p>
                </div>
              </div>
              <button onclick="removeStock('<?php echo htmlspecialchars($item['stock_symbol']); ?>', this)" class="px-2 py-1 bg-red-500/10 text-red-400 rounded-lg text-xs hover:bg-red-500/20 transition">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>
            <div class="flex gap-2 mt-3">
              <a href="/quantpath/frontend/simulation.php?ticker=<?php echo urlencode($item['stock_symbol']); ?>" class="flex-1 flex justify-center items-center gap-1 px-3 py-2 bg-indigo-500/15 text-indigo-300 rounded-lg text-xs font-medium hover:bg-indigo-500/25 transition">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg> Simulate
              </a>
              <button onclick="fetchPrice('<?php echo htmlspecialchars($item['stock_symbol']); ?>', this)" class="flex-1 flex justify-center items-center gap-1 px-3 py-2 bg-green-500/15 text-green-300 rounded-lg text-xs font-medium hover:bg-green-500/25 transition">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg> Fetch
              </button>
            </div>
            <div class="price-info mt-3 hidden text-center p-3 bg-slate-900/50 rounded-lg"></div>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if (empty($watchlist_items)): ?>
        <div id="empty-state" class="flex flex-col items-center justify-center py-12 fade-in">
          <div class="mb-4 text-slate-600">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" class="mx-auto" stroke="currentColor" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          </div>
          <h3 class="text-xl font-bold text-white mb-2">Your watchlist is empty</h3>
          <p class="text-slate-500 text-sm">Add stocks from the trending section above or type a ticker manually</p>
        </div>
        <?php endif; ?>
      </div>

      <?php endif; ?>
    </main>
  </div>

  <script src="/quantpath/assets/js/api.js?v=2"></script>
  <script>
    // Toast fallback
    if (typeof Toast === 'undefined') {
      var Toast = { container: null,
        init() { if (!this.container) { this.container = document.createElement('div'); this.container.className = 'fixed bottom-6 right-6 z-50 flex flex-col gap-3'; document.body.appendChild(this.container); } },
        show(msg, type='info') { this.init(); const t = document.createElement('div'); const bg = type==='error'?'#ef4444':type==='success'?'#10b981':type==='warning'?'#f59e0b':'#6366f1'; t.style.cssText = `background:${bg};color:white;padding:12px 20px;border-radius:12px;font-size:14px;font-weight:500;box-shadow:0 10px 40px rgba(0,0,0,0.3);display:flex;align-items:center;gap:8px;transform:translateX(120%);transition:transform 0.3s;min-width:250px;`; t.innerHTML = msg; this.container.appendChild(t); requestAnimationFrame(()=>t.style.transform='translateX(0)'); setTimeout(()=>{t.style.transform='translateX(120%)';setTimeout(()=>t.remove(),300);},3000); }
      };
    }

    // Quick add from trending / top performers
    async function quickAdd(symbol, btn) {
      btn.innerHTML = '<span class="loader"></span>';
      btn.disabled = true;
      try {
        await API.addToWatchlist(symbol);
        btn.outerHTML = '<div class="mt-2 badge added-badge">Added</div>';
        Toast.show(`${symbol} added to watchlist`, 'success');
        // Also add to main watchlist grid
        addCardToGrid(symbol);
      } catch (e) {
        btn.innerHTML = '+ Add';
        btn.disabled = false;
        Toast.show(e.message, 'error');
      }
    }

    // Update the live counter
    function updateWatchlistCount(delta) {
      const countEl = document.getElementById('watchlist-count');
      const statusEl = document.getElementById('watchlist-status');
      if (countEl) {
        const current = parseInt(countEl.textContent) || 0;
        const newCount = Math.max(0, current + delta);
        countEl.textContent = newCount;
        // Pulse animation
        countEl.style.transform = 'scale(1.3)';
        countEl.style.boxShadow = '0 0 15px rgba(234,179,8,0.4)';
        setTimeout(() => { countEl.style.transform = 'scale(1)'; countEl.style.boxShadow = 'none'; }, 300);
      }
      if (statusEl) {
        const current = parseInt(statusEl.textContent) || 0;
        const newCount = Math.max(0, current + delta);
        statusEl.textContent = newCount + ' stocks tracked';
      }
    }

    function addCardToGrid(symbol) {
      const emptyState = document.getElementById('empty-state');
      if (emptyState) emptyState.remove();
      updateWatchlistCount(1);
      const grid = document.getElementById('watchlist-grid');
      const card = document.createElement('div');
      card.className = 'stock-card glass rounded-xl p-5 fade-in';
      card.dataset.symbol = symbol;
      card.innerHTML = `
        <div class="flex justify-between items-start mb-3">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-yellow-500/15 rounded-xl flex items-center justify-center text-yellow-300 font-bold text-lg">${symbol.substring(0,2)}</div>
            <div>
              <h3 class="font-bold text-white text-lg">${symbol}</h3>
              <p class="text-xs text-slate-500">Added just now</p>
            </div>
          </div>
          <button onclick="removeStock('${symbol}', this)" class="px-2 py-1 bg-red-500/10 text-red-400 rounded-lg text-xs hover:bg-red-500/20 transition"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <div class="flex gap-2 mt-3">
          <a href="/quantpath/frontend/simulation.php?ticker=${encodeURIComponent(symbol)}" class="flex-1 flex justify-center items-center gap-1 px-3 py-2 bg-indigo-500/15 text-indigo-300 rounded-lg text-xs font-medium hover:bg-indigo-500/25 transition"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg> Simulate</a>
          <button onclick="fetchPrice('${symbol}', this)" class="flex-1 flex justify-center items-center gap-1 px-3 py-2 bg-green-500/15 text-green-300 rounded-lg text-xs font-medium hover:bg-green-500/25 transition"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg> Fetch</button>
        </div>
        <div class="price-info mt-3 hidden text-center p-3 bg-slate-900/50 rounded-lg"></div>
      `;
      grid.insertBefore(card, grid.firstChild);
    }

    async function addStock() {
      const input = document.getElementById('addSymbol');
      const symbol = input.value.trim().toUpperCase();
      if (!symbol) { Toast.show('Enter a stock symbol', 'warning'); return; }
      try {
        await API.addToWatchlist(symbol);
        Toast.show(`${symbol} added to watchlist`, 'success');
        input.value = '';
        addCardToGrid(symbol);
      } catch (e) { Toast.show(e.message, 'error'); }
    }

    document.getElementById('addSymbol')?.addEventListener('keydown', e => { if (e.key === 'Enter') addStock(); });

    async function removeStock(symbol, btn) {
      try {
        await API.removeFromWatchlist(symbol);
        const card = btn.closest('.stock-card');
        card.style.opacity = '0'; card.style.transform = 'scale(0.95)';
        setTimeout(() => card.remove(), 300);
        updateWatchlistCount(-1);
        Toast.show(`${symbol} removed`, 'success');
      } catch (e) { Toast.show(e.message, 'error'); }
    }

    async function fetchPrice(symbol, btn) {
      const card = btn.closest('.stock-card');
      const priceInfo = card.querySelector('.price-info');
      btn.innerHTML = '<span class="loader"></span>';
      btn.disabled = true;
      try {
        const data = await API.fetchStock(symbol);
        const tsKey = Object.keys(data).find(k => k.includes('Time Series'));
        if (!tsKey) throw new Error('No data');
        const ts = data[tsKey];
        const dates = Object.keys(ts).sort();
        const latest = ts[dates[dates.length - 1]];
        const prev = dates.length > 1 ? ts[dates[dates.length - 2]] : latest;
        const price = parseFloat(latest['4. close']);
        const prevPrice = parseFloat(prev['4. close']);
        const change = ((price - prevPrice) / prevPrice * 100).toFixed(2);
        const isUp = change >= 0;
        priceInfo.innerHTML = `
          <div class="text-lg font-bold ${isUp ? 'text-green-300' : 'text-red-300'}">₹${price.toLocaleString('en-IN', {minimumFractionDigits:2})}</div>
          <div class="text-xs ${isUp ? 'text-green-400' : 'text-red-400'}">${isUp ? '▲' : '▼'} ${change}%</div>
          <div class="text-xs text-slate-500 mt-1">Last: ${dates[dates.length-1]}</div>
        `;
        priceInfo.classList.remove('hidden');
      } catch (e) {
        priceInfo.innerHTML = `<div class="text-xs text-red-400">${e.message}</div>`;
        priceInfo.classList.remove('hidden');
      }
      btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg> Fetch';
      btn.disabled = false;
    }
  </script>
</body>
</html>
