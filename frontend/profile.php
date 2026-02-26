<?php
// frontend/profile.php
session_start();
require_once __DIR__ . '/../private_config/config.php';

$user_id = $_SESSION['user_id'] ?? '';
$user_name = $_SESSION['user_name'] ?? 'Guest';
$user = null;
$sim_count = 0;
$watch_count = 0;
$stock_count = 0;

if ($user_id) {
    $stmt = $conn->prepare("SELECT id, name, email, bio, phone, institution, created_at FROM users WHERE id=?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt2 = $conn->prepare("SELECT COUNT(*) as c FROM simulations WHERE user_id=?");
    $stmt2->bind_param('i', $user_id);
    $stmt2->execute();
    $sim_count = $stmt2->get_result()->fetch_assoc()['c'];
    $stmt2->close();

    $stmt3 = $conn->prepare("SELECT COUNT(*) as c FROM watchlist WHERE user_id=?");
    $stmt3->bind_param('i', $user_id);
    $stmt3->execute();
    $watch_count = $stmt3->get_result()->fetch_assoc()['c'];
    $stmt3->close();

    $stmt4 = $conn->prepare("SELECT COUNT(DISTINCT stock_symbol) as c FROM simulations WHERE user_id=?");
    $stmt4->bind_param('i', $user_id);
    $stmt4->execute();
    $stock_count = $stmt4->get_result()->fetch_assoc()['c'];
    $stmt4->close();
}
$conn->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Profile — QuantPath</title>
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
    @keyframes fadeIn { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .fade-in { animation: fadeIn 0.5s ease-out; }
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
      <a href="/quantpath/frontend/profile.php" class="sidebar-link active">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profile
      </a>
      <a href="/quantpath/backend/logout.php" class="sidebar-link text-red-400 hover:bg-red-500/10 hover:text-red-300">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        Logout
      </a>
    </nav>
  </aside>

  <!-- Main Content -->
  <div class="ml-[260px] flex-1 min-h-screen">
    <header class="sticky top-0 z-30 glass border-b border-white/5 px-8 py-4">
      <h1 class="text-xl font-bold text-white">Profile</h1>
      <p class="text-xs text-slate-500">Manage your account settings</p>
    </header>

    <main class="px-8 py-6">
      <?php if (!$user_id): ?>
        <div class="flex items-center justify-center min-h-[60vh]">
          <div class="glass rounded-2xl p-12 text-center max-w-md">
            <div class="text-5xl mb-4">🔐</div>
            <h2 class="text-2xl font-bold text-white mb-2">Login Required</h2>
            <a href="/quantpath/frontend/login.php" class="inline-block px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold mt-4">Sign In</a>
          </div>
        </div>
      <?php else: ?>

      <div class="max-w-3xl mx-auto">
        <!-- Profile Header -->
        <div class="glass rounded-2xl p-8 mb-6 fade-in">
          <div class="flex items-center gap-6">
            <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white text-3xl font-bold shadow-xl shadow-indigo-500/30">
              <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($user['name'] ?? ''); ?></h2>
              <p class="text-sm text-slate-400"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
              <p class="text-xs text-slate-500 mt-1">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-4 mb-6">
          <div class="glass rounded-xl p-5 text-center fade-in">
            <div class="text-3xl font-bold text-indigo-300"><?php echo $sim_count; ?></div>
            <div class="text-xs text-slate-500 mt-1">Simulations</div>
          </div>
          <div class="glass rounded-xl p-5 text-center fade-in">
            <div class="text-3xl font-bold text-yellow-300"><?php echo $watch_count; ?></div>
            <div class="text-xs text-slate-500 mt-1">Watchlist</div>
          </div>
          <div class="glass rounded-xl p-5 text-center fade-in">
            <div class="text-3xl font-bold text-green-300"><?php echo $stock_count; ?></div>
            <div class="text-xs text-slate-500 mt-1">Stocks Analyzed</div>
          </div>
        </div>

        <!-- Edit Profile -->
        <div class="glass rounded-2xl p-8 fade-in">
          <h3 class="text-lg font-bold text-white mb-6">Edit Profile</h3>
          <form id="profileForm" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs text-slate-400 font-medium mb-1.5">Full Name</label>
                <input id="prof-name" type="text" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" class="w-full input-field px-4 py-3 rounded-xl text-white text-sm" required />
              </div>
              <div>
                <label class="block text-xs text-slate-400 font-medium mb-1.5">Phone</label>
                <input id="prof-phone" type="text" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+91 XXXXX XXXXX" class="w-full input-field px-4 py-3 rounded-xl text-white text-sm placeholder-slate-600" />
              </div>
            </div>

            <div>
              <label class="block text-xs text-slate-400 font-medium mb-1.5">Institution / University</label>
              <input id="prof-institution" type="text" value="<?php echo htmlspecialchars($user['institution'] ?? ''); ?>" placeholder="Your college or university" class="w-full input-field px-4 py-3 rounded-xl text-white text-sm placeholder-slate-600" />
            </div>

            <div>
              <label class="block text-xs text-slate-400 font-medium mb-1.5">Bio</label>
              <textarea id="prof-bio" rows="3" placeholder="Tell us about yourself..." class="w-full input-field px-4 py-3 rounded-xl text-white text-sm placeholder-slate-600 resize-none"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>

            <div>
              <label class="block text-xs text-slate-400 font-medium mb-1.5">Email</label>
              <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled class="w-full input-field px-4 py-3 rounded-xl text-slate-500 text-sm cursor-not-allowed" />
              <p class="text-xs text-slate-600 mt-1">Email cannot be changed</p>
            </div>

            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition shadow-lg shadow-indigo-500/20 text-sm">
              💾 Save Changes
            </button>
          </form>
        </div>
      </div>

      <?php endif; ?>
    </main>
  </div>

  <script src="/quantpath/assets/js/api.js?v=2"></script>
  <script>
    document.getElementById('profileForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = e.target.querySelector('button[type="submit"]');
      btn.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,0.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;"></span> Saving...';
      btn.disabled = true;
      try {
        await API.updateProfile({
          name: document.getElementById('prof-name').value,
          phone: document.getElementById('prof-phone').value,
          institution: document.getElementById('prof-institution').value,
          bio: document.getElementById('prof-bio').value
        });
        Toast.show('Profile updated!', 'success');
      } catch (e) {
        Toast.show('Error: ' + e.message, 'error');
      }
      btn.innerHTML = '💾 Save Changes';
      btn.disabled = false;
    });
  </script>
</body>
</html>
