<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Sign In — QuantPath</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/quantpath/assets/css/tailwind.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100 flex items-center justify-center px-4">
  <!-- Animated Gradient Background -->
  <div class="fixed inset-0 pointer-events-none overflow-hidden">
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl animate-pulse"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
  </div>

  <div class="relative z-10 w-full max-w-md">
    <!-- Logo Header -->
    <div class="text-center mb-8">
      <a href="/quantpath/frontend/index.html" class="inline-flex items-center justify-center gap-3 group">
        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-lg group-hover:shadow-2xl group-hover:shadow-indigo-500/50 transition-all duration-300">
          Q
        </div>
        <div class="text-2xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">QuantPath</div>
      </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl hover:shadow-indigo-500/20 transition-all duration-300">
      <h2 class="text-2xl font-bold mb-2 bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">Welcome Back</h2>
      <p class="text-slate-400 text-sm mb-6">Sign in to access your simulations</p>
      
      <form id="loginForm" class="space-y-4">
        <div>
          <label class="block text-sm text-slate-300 font-semibold mb-2">Email Address</label>
          <input id="email" type="email" placeholder="you@example.com" class="w-full bg-slate-800/50 border border-slate-700/50 px-4 py-3 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500/50 transition-all duration-200 hover:border-slate-600/50" required />
        </div>

        <div>
          <label class="block text-sm text-slate-300 font-semibold mb-2">Password</label>
          <input id="password" type="password" placeholder="••••••••" class="w-full bg-slate-800/50 border border-slate-700/50 px-4 py-3 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500/50 transition-all duration-200 hover:border-slate-600/50" required />
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 rounded-lg transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-95 mt-6">
          Sign In
        </button>

        <div id="msg" class="text-sm text-red-400 mt-2 text-center font-medium hidden px-4 py-2 bg-red-500/10 rounded-lg border border-red-500/20"></div>
      </form>

      <!-- Divider -->
      <div class="my-6 relative">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-white/10"></div>
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="px-3 bg-slate-900/50 text-slate-400">New to QuantPath?</span>
        </div>
      </div>

      <!-- Sign Up Link -->
      <a href="/quantpath/frontend/register.php" class="block w-full text-center py-3 border border-slate-700/50 rounded-lg text-slate-300 hover:text-white hover:border-indigo-500/50 hover:bg-indigo-500/5 transition-all duration-200 font-medium">
        Create an Account
      </a>

      <!-- Lost Password Link -->
      <div class="mt-4 text-center">
        <a href="#" class="text-xs text-slate-400 hover:text-slate-300 transition">Forgot your password?</a>
      </div>
    </div>

    <!-- Back to Home -->
    <div class="mt-8 text-center">
      <a href="/quantpath/frontend/index.html" class="text-slate-400 hover:text-slate-300 text-sm transition inline-flex items-center gap-2 group">
        <span class="group-hover:-translate-x-1 transition">←</span> Back to Home
      </a>
    </div>
  </div>

  <script>
  document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const msg = document.getElementById('msg');
    msg.textContent = '';
    msg.classList.add('hidden');
    
    try {
      const res = await fetch('/quantpath/backend/login.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        credentials: 'same-origin',
        body: JSON.stringify({email,password})
      });
      const data = await res.json();
      if (!res.ok) { 
        msg.textContent = data.error || 'Login failed'; 
        msg.classList.remove('hidden');
        return; 
      }
      window.location.href = '/quantpath/frontend/dashboard.php';
    } catch (err) { 
      msg.textContent = 'Network error'; 
      msg.classList.remove('hidden');
    }
  });
  </script>
</body>
</html>
