// assets/js/api.js
// API wrapper for QuantPath backend endpoints

const API = (function () {
  const base = "/quantpath/backend";

  async function postJson(url, payload) {
    const res = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "same-origin",
      body: JSON.stringify(payload),
    });
    const data = await res.json().catch(() => {
      throw new Error("Invalid JSON response");
    });
    if (!res.ok) throw new Error(data.error || "Request failed");
    return data;
  }

  async function deleteJson(url, payload) {
    const res = await fetch(url, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      credentials: "same-origin",
      body: JSON.stringify(payload),
    });
    const data = await res.json().catch(() => {
      throw new Error("Invalid JSON response");
    });
    if (!res.ok) throw new Error(data.error || "Request failed");
    return data;
  }

  async function formPost(url, formData) {
    const res = await fetch(url, {
      method: "POST",
      credentials: "same-origin",
      body: formData,
    });
    const data = await res.json().catch(() => {
      throw new Error("Invalid JSON response");
    });
    if (!res.ok) throw new Error(data.error || "Request failed");
    return data;
  }

  async function getJson(url) {
    const res = await fetch(url, { credentials: "same-origin" });
    const data = await res.json().catch(() => {
      throw new Error("Invalid JSON response");
    });
    if (!res.ok) throw new Error(data.error || "Request failed");
    return data;
  }

  return {
    // Auth
    register: (name, email, password) => {
      const form = new FormData();
      form.append("name", name);
      form.append("email", email);
      form.append("password", password);
      return formPost(`${base}/register.php`, form);
    },
    login: (email, password) =>
      postJson(`${base}/login.php`, { email, password }),
    logout: () =>
      fetch(`${base}/logout.php`, {
        method: "GET",
        credentials: "same-origin",
      }),

    // Stock data
    fetchStock: (symbol, outputsize = "compact") => {
      return getJson(
        `${base}/fetch_stock.php?symbol=${encodeURIComponent(symbol)}&outputsize=${outputsize}`,
      );
    },

    // Simulations
    saveSimulation: (payload) =>
      postJson(`${base}/save_simulation.php`, payload),
    getSimulations: () => getJson(`${base}/get_simulations.php`),
    getSimulation: (id) => getJson(`${base}/get_simulation.php?id=${id}`),
    deleteSimulation: (id) => postJson(`${base}/delete_simulation.php`, { id }),

    // Watchlist
    getWatchlist: () => getJson(`${base}/watchlist.php`),
    addToWatchlist: (symbol) => postJson(`${base}/watchlist.php`, { symbol }),
    removeFromWatchlist: (symbol) =>
      deleteJson(`${base}/watchlist.php`, { symbol }),

    // Profile
    getProfile: () => getJson(`${base}/profile.php`),
    updateProfile: (data) => postJson(`${base}/profile.php`, data),
  };
})();

// Toast notification system
const Toast = {
  container: null,
  init() {
    if (this.container) return;
    this.container = document.createElement("div");
    this.container.id = "toast-container";
    this.container.style.cssText =
      "position:fixed;top:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;";
    document.body.appendChild(this.container);
  },
  show(message, type = "info", duration = 3000) {
    this.init();
    const toast = document.createElement("div");
    const colors = {
      success: "linear-gradient(135deg, #059669, #10b981)",
      error: "linear-gradient(135deg, #dc2626, #ef4444)",
      info: "linear-gradient(135deg, #4f46e5, #6366f1)",
      warning: "linear-gradient(135deg, #d97706, #f59e0b)",
    };
    const icons = { success: "✓", error: "✕", info: "i", warning: "!" };
    toast.style.cssText = `background:${colors[type]};color:white;padding:12px 20px;border-radius:12px;font-size:14px;font-weight:500;box-shadow:0 10px 40px rgba(0,0,0,0.3);display:flex;align-items:center;gap:8px;transform:translateX(120%);transition:transform 0.3s cubic-bezier(0.4,0,0.2,1);min-width:250px;backdrop-filter:blur(10px);`;
    toast.innerHTML = `<span>${icons[type]}</span><span>${message}</span>`;
    this.container.appendChild(toast);
    requestAnimationFrame(() => {
      toast.style.transform = "translateX(0)";
    });
    setTimeout(() => {
      toast.style.transform = "translateX(120%)";
      setTimeout(() => toast.remove(), 300);
    }, duration);
  },
};
