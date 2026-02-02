// assets/js/api.js
// Small wrapper around backend endpoints used by the frontend.
// All functions return a Promise that resolves to parsed JSON or throws an Error.

const API = (function () {
  const base = '/quantpath/backend';

  async function postJson(url, payload) {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json().catch(() => { throw new Error('Invalid JSON response'); });
    if (!res.ok) throw new Error(data.error || 'Request failed');
    return data;
  }

  async function formPost(url, formData) {
    const res = await fetch(url, { method: 'POST', body: formData });
    const data = await res.json().catch(() => { throw new Error('Invalid JSON response'); });
    if (!res.ok) throw new Error(data.error || 'Request failed');
    return data;
  }

  return {
    // Auth
    register: (name, email, password) => {
      const form = new FormData();
      form.append('name', name);
      form.append('email', email);
      form.append('password', password);
      return formPost(`${base}/register.php`, form);
    },
    login: (email, password) => postJson(`${base}/login.php`, { email, password }),
    logout: () => fetch(`${base}/logout.php`, { method: 'GET' }),

    // Stock data
    fetchStock: async (symbol) => {
      const res = await fetch(`${base}/fetch_stock.php?symbol=${encodeURIComponent(symbol)}`);
      if (!res.ok) {
        const err = await res.json().catch(()=>({ error: 'API fetch failed' }));
        throw new Error(err.error || 'Failed to fetch stock');
      }
      return res.json();
    },

    // Simulations
    saveSimulation: (payload) => postJson(`${base}/save_simulation.php`, payload),
    getSimulations: () => fetch(`${base}/get_simulations.php`).then(r => r.json())
  };
})();