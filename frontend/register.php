<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Create Account — QuantPath</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/quantpath/assets/css/tailwind.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100 flex items-center justify-center px-4">
  <!-- Animated Gradient Background -->
  <div class="fixed inset-0 pointer-events-none overflow-hidden">
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl animate-pulse"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-green-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
  </div>

  <div class="relative z-10 w-full max-w-md">
    <!-- Logo Header -->
    <div class="text-center mb-8">
      <a href="/quantpath/frontend/index.html" class="inline-flex items-center justify-center gap-3 group">
        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center text-white font-bold text-lg group-hover:shadow-2xl group-hover:shadow-emerald-500/50 transition-all duration-300">
          Q
        </div>
        <div class="text-2xl font-bold bg-gradient-to-r from-emerald-400 to-green-400 bg-clip-text text-transparent">QuantPath</div>
      </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl hover:shadow-emerald-500/20 transition-all duration-300">
      <h2 class="text-2xl font-bold mb-2 bg-gradient-to-r from-emerald-400 to-green-400 bg-clip-text text-transparent">Create Account</h2>
      <p class="text-slate-400 text-sm mb-6">Join QuantPath to start simulating stock prices</p>
      
      <form id="regForm" class="space-y-4">
        <div>
          <label class="block text-sm text-slate-300 font-semibold mb-2">Full Name</label>
          <input id="name" type="text" placeholder="John Doe" class="w-full bg-slate-800/50 border border-slate-700/50 px-4 py-3 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500/50 transition-all duration-200 hover:border-slate-600/50" required />
        </div>

        <div>
          <label class="block text-sm text-slate-300 font-semibold mb-2">Email Address</label>
          <input id="email" type="email" placeholder="you@example.com" class="w-full bg-slate-800/50 border border-slate-700/50 px-4 py-3 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500/50 transition-all duration-200 hover:border-slate-600/50" required />
        </div>

        <div>
          <label class="block text-sm text-slate-300 font-semibold mb-2">Password</label>
          <input id="password" type="password" placeholder="••••••••" class="w-full bg-slate-800/50 border border-slate-700/50 px-4 py-3 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500/50 transition-all duration-200 hover:border-slate-600/50" required />
        </div>

        <div>
          <label class="block text-sm text-slate-300 font-semibold mb-2">Confirm Password</label>
          <input id="confirm" type="password" placeholder="••••••••" class="w-full bg-slate-800/50 border border-slate-700/50 px-4 py-3 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500/50 transition-all duration-200 hover:border-slate-600/50" required />
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-semibold py-3 rounded-lg transition-all duration-200 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:scale-[1.02] active:scale-95 mt-6">
          Create Account
        </button>

        <div id="msg" class="text-sm text-red-400 mt-2 text-center font-medium hidden px-4 py-2 bg-red-500/10 rounded-lg border border-red-500/20"></div>
      </form>

      <!-- Divider -->
      <div class="my-6 relative">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-white/10"></div>
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="px-3 bg-slate-900/50 text-slate-400">Already registered?</span>
        </div>
      </div>

      <!-- Sign In Link -->
      <a href="/quantpath/frontend/login.php" class="block w-full text-center py-3 border border-slate-700/50 rounded-lg text-slate-300 hover:text-white hover:border-emerald-500/50 hover:bg-emerald-500/5 transition-all duration-200 font-medium">
        Sign In Instead
      </a>

      <!-- Terms -->
      <div class="mt-4 text-center text-xs text-slate-500">
        By signing up, you agree to our <a href="#" class="text-slate-400 hover:text-slate-300 underline">Terms of Service</a>
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
  document.getElementById('regForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm').value;
    const msg = document.getElementById('msg');
    msg.textContent = '';
    msg.classList.add('hidden');
    
    if (password !== confirm) {
      msg.textContent = 'Passwords do not match';
      msg.classList.remove('hidden');
      return;
    }

    if (password.length < 6) {
      msg.textContent = 'Password must be at least 6 characters';
      msg.classList.remove('hidden');
      return;
    }
    
    try {
      const form = new FormData();
      form.append('name', name);
      form.append('email', email);
      form.append('password', password);
      const res = await fetch('/quantpath/backend/register.php', { 
        method: 'POST', 
        body: form,
        credentials: 'same-origin'
      });
      const data = await res.json();
      if (!res.ok) { 
        msg.textContent = data.error || 'Registration failed'; 
        msg.classList.remove('hidden');
        return; 
      }
      alert('Account created! Redirecting to login...');
      window.location.href = '/quantpath/frontend/login.php';
    } catch (err) { 
      msg.textContent = 'Network error'; 
      msg.classList.remove('hidden');
    }
  });
  </script>
</body>
</html>
