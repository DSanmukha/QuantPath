# QuantPath — Quantitative Stock Simulation for Indian Markets

<p align="center">
  <strong>An advanced web-based stock price simulation platform with 4 stochastic models (GBM, Ornstein-Uhlenbeck, Merton Jump Diffusion, GARCH), built specifically for Indian stock markets (BSE/NSE).</strong>
</p>

<p align="center">
  <em>Final Year Software Development Engineering Project</em>
</p>

---

## 📌 What is QuantPath?

QuantPath is a **quantitative finance web application** that allows users to simulate future stock prices using **four stochastic models**: Monte Carlo (GBM), Mean Reversion (Ornstein-Uhlenbeck), Jump Diffusion (Merton), and GARCH(1,1). It fetches real historical data from the **Alpha Vantage API** for Indian BSE/NSE stocks, automatically calculates statistical parameters, and generates hundreds of possible future price trajectories.

The platform is designed for **Indian retail investors, finance students, and educators** who want to understand and apply stochastic modelling concepts to real-world stock market data — all priced in **₹ (Indian Rupees)**.

---

## 🤔 Why is This Needed?

### The Problem

1. **Financial Literacy Gap**: India has over 10 crore demat accounts, but most retail investors make decisions based on tips, news, or gut feelings — not quantitative analysis.

2. **Expensive Tools**: Professional quantitative finance tools like Bloomberg Terminal (₹15+ lakhs/year), MATLAB, or QuantLib require expensive licenses and technical expertise.

3. **Education Gap**: Students studying finance learn stochastic calculus theory but rarely get hands-on experience with real stock data. Most university labs lack practical tools for experiments.

4. **Risk Ignorance**: Retail investors in India often don't understand volatility, Value at Risk (VaR), or standard deviation — leading to uninformed investment decisions and avoidable losses.

### The Solution

QuantPath bridges this gap by providing a **free, web-based, easy-to-use platform** that:

- Makes quantitative stock analysis accessible to **everyone**
- Works with **real Indian stock market data** (BSE/NSE)
- Auto-calculates complex parameters (drift, volatility) from historical data
- Provides visual, intuitive results that **anyone can understand**
- Requires **no software installation** — runs in any web browser

---

## 💡 Use Cases

### 1. 🎓 Academic Research & Education

- **Final year projects**: Students can use QuantPath to demonstrate Monte Carlo simulation, GBM, and risk modelling
- **Lab assignments**: Faculty can assign exercises like "Simulate RELIANCE.BSE for 1 year and report the VaR"
- **Dissertation support**: Generate data and visualizations for academic papers
- **Concept learning**: Understand how drift (μ), volatility (σ), and sample paths work in practice

### 2. 💰 Retail Investment Decisions

- **Before investing**: Simulate a stock's future price range before putting money in
- **Risk assessment**: Check the VaR (Value at Risk) to understand potential downside
- **Stock comparison**: Compare RELIANCE vs TCS vs INFY to find the best risk-reward ratio
- **Portfolio planning**: Understand which stocks are high-volatility vs stable

### 3. 👨‍🏫 Teaching Tool for Educators

- **Live demonstrations**: Show students how changing drift or volatility affects price paths
- **Interactive labs**: Students can experiment with parameters and see immediate results
- **Export data**: Download CSV files for further analysis in Excel, Python, or R

### 4. 🔬 Quantitative Finance Research

- **Market behaviour analysis**: Study how Indian market stocks behave under stochastic models
- **Model validation**: Compare GBM predictions against actual historical performance
- **Parameter sensitivity**: Analyse how small changes in μ or σ dramatically change outcomes

---

## 🔧 How It Works

### The Mathematics Behind QuantPath

QuantPath uses **Geometric Brownian Motion (GBM)**, the standard model for stock price evolution:

```
dS = μ·S·dt + σ·S·dW
```

Where:

- **S** = Stock price at time t
- **μ (drift)** = Expected annualized return (auto-calculated from historical data)
- **σ (volatility)** = Annualized standard deviation of returns
- **dW** = Wiener process (random walk component)
- **dt** = Time step

The discretized version used in simulation:

```
S(t+1) = S(t) × exp[(μ - σ²/2)·Δt + σ·√Δt·Z]
```

Where **Z ~ N(0,1)** is a standard normal random variable generated using the **Box-Muller Transform**.

### Monte Carlo Method

The simulation runs this process **hundreds of times** (e.g., 500 paths) to generate a probability distribution of future prices. From this distribution, we calculate:

| Metric                      | Description                                   |
| --------------------------- | --------------------------------------------- |
| **Expected Price**          | Mean of all terminal prices                   |
| **Median Price**            | 50th percentile — more robust than mean       |
| **Standard Deviation**      | How spread out the results are                |
| **95% Confidence Interval** | Range where 95% of simulated prices fall      |
| **Value at Risk (VaR 5%)**  | The worst 5% scenario — maximum expected loss |

---

## 🌟 Key Features

| Feature                        | Description                                                    |
| ------------------------------ | -------------------------------------------------------------- |
| **4 Simulation Models**        | GBM, Mean Reversion (O-U), Jump Diffusion (Merton), GARCH(1,1) |
| **Live BSE/NSE Data**          | Fetch real stock prices via Alpha Vantage API                  |
| **Auto-Parameter Calculation** | Drift and volatility calculated from historical returns        |
| **Interactive Charts**         | Chart.js visualizations with confidence bands and mean path    |
| **Stock Watchlist**            | Track favourite stocks with trending stocks & top performers   |
| **Compare Simulations**        | Side-by-side parameter & risk comparison                       |
| **Risk Metrics**               | VaR, CI, std dev — all in Indian Rupees                        |
| **User Profiles**              | Editable profile with institution and bio                      |
| **Save & Export**              | Save simulations, export CSV reports                           |
| **Authentication**             | Secure registration and login system                           |
| **Premium Dark UI**            | Glassmorphism design with sidebar navigation                   |

---

## 📸 Impact & Changes This Project Makes

### For Students

- **Hands-on learning**: Instead of just studying formulas, students can see GBM in action
- **Project-ready**: Provides a complete, deployable web application for final year submissions
- **Real data**: No dummy data — everything uses live Indian market prices

### For Investors

- **Data-driven decisions**: Replace guesswork with quantitative analysis
- **Risk awareness**: Understand potential losses before investing
- **Free alternative**: No need for expensive Bloomberg or MATLAB licenses

### For the Indian Market

- **Financial literacy**: Making quantitative analysis accessible to 10cr+ Indian investors
- **Educational infrastructure**: Providing teachers with practical tools for finance courses
- **Open-source contribution**: A reference implementation for GBM and Monte Carlo in PHP/JavaScript

---

## 🛠️ Tech Stack

| Technology            | Purpose                                    |
| --------------------- | ------------------------------------------ |
| **PHP 8.x**           | Backend API and server-side rendering      |
| **MySQL / MariaDB**   | Database (users, simulations, watchlist)   |
| **JavaScript (ES6+)** | Client-side simulation engine and UI logic |
| **Chart.js 4.x**      | Interactive charts and visualizations      |
| **Tailwind CSS**      | Responsive UI with glassmorphism design    |
| **Alpha Vantage API** | Real-time and historical stock data        |
| **XAMPP**             | Local development server (Apache + MySQL)  |

---

## 📁 Project Structure

```
quantpath/
├── frontend/                 # All user-facing pages
│   ├── index.html           # Landing page
│   ├── login.php            # Sign in
│   ├── register.php         # Create account
│   ├── dashboard.php        # Main dashboard with sidebar
│   ├── simulation.php       # Multi-model simulation engine
│   ├── watchlist.php        # Watchlist + trending stocks
│   ├── compare.php          # Side-by-side simulation comparison
│   └── profile.php          # User profile management
├── backend/                  # REST API endpoints
│   ├── login.php            # Authentication
│   ├── register.php         # User registration
│   ├── logout.php           # Session logout
│   ├── fetch_stock.php      # Alpha Vantage API proxy
│   ├── save_simulation.php  # Save simulation results
│   ├── get_simulations.php  # Fetch user simulations
│   ├── get_simulation.php   # Fetch single simulation
│   ├── delete_simulation.php # Delete a simulation
│   ├── watchlist.php        # Watchlist CRUD (GET/POST/DELETE)
│   └── profile.php          # Profile CRUD (GET/POST)
├── assets/
│   ├── css/                 # Stylesheets
│   └── js/
│       └── api.js           # Frontend API wrapper + Toast system
├── database/
│   └── schema.sql           # Database schema
├── private_config/
│   └── config.php           # DB credentials & API key
├── tools/
│   ├── setup_db.php         # Initial database setup
│   └── migrate.php          # Schema migration script
├── docs/
│   ├── project_report.md    # Full project report/documentation
│   └── diagrams.puml        # PlantUML diagrams (DFD, Class, ER, etc.)
└── README.md                # This file
```

---

## 🚀 Installation & Setup

### Prerequisites

- **XAMPP** (Apache + MySQL + PHP) — Download from [apachefriends.org](https://www.apachefriends.org/)
- **Alpha Vantage API Key** — Free at [alphavantage.co](https://www.alphavantage.co/support/#api-key)

### Steps

1. **Clone the repository** into your XAMPP `htdocs` directory:

   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/YOUR_USERNAME/quantpath.git
   ```

2. **Start XAMPP**: Open XAMPP Control Panel and start **Apache** and **MySQL**

3. **Create the database**: Open [phpMyAdmin](http://localhost/phpmyadmin) and import `database/schema.sql`, or visit:

   ```
   http://localhost/quantpath/tools/setup_db.php
   ```

4. **Run migrations** (for new tables):

   ```
   http://localhost/quantpath/tools/migrate.php
   ```

5. **Configure API key** — Already set in `private_config/config.php`. To change:

   ```php
   $ALPHA_VANTAGE_API_KEY = 'YOUR_KEY_HERE';
   ```

6. **Open QuantPath**:
   ```
   http://localhost/quantpath/frontend/index.html
   ```

---

## 📊 Indian Stocks Supported

Use **.BSE** suffix for Bombay Stock Exchange stocks:

| Ticker           | Company                   |
| ---------------- | ------------------------- |
| `RELIANCE.BSE`   | Reliance Industries       |
| `TCS.BSE`        | Tata Consultancy Services |
| `INFY.BSE`       | Infosys                   |
| `HDFCBANK.BSE`   | HDFC Bank                 |
| `ICICIBANK.BSE`  | ICICI Bank                |
| `WIPRO.BSE`      | Wipro                     |
| `SBIN.BSE`       | State Bank of India       |
| `BHARTIARTL.BSE` | Bharti Airtel             |
| `ITC.BSE`        | ITC Ltd                   |
| `KOTAKBANK.BSE`  | Kotak Mahindra Bank       |

---

## Future Scope

- **Portfolio Simulation** — Multi-stock correlated simulations
- **Options Pricing** — Black-Scholes calculator
- **Machine Learning Integration** — LSTM-based price prediction comparison
- **Mobile App** — React Native frontend
- **Real-time Data** — WebSocket-based live price streaming

---

## 📝 References

1. Black, F., & Scholes, M. (1973). _The Pricing of Options and Corporate Liabilities_. Journal of Political Economy.
2. Hull, J. C. (2018). _Options, Futures, and Other Derivatives_ (10th ed.). Pearson.
3. Glasserman, P. (2003). _Monte Carlo Methods in Financial Engineering_. Springer.
4. Alpha Vantage API Documentation — [alphavantage.co/documentation](https://www.alphavantage.co/documentation/)

---

## 📄 License

This project is developed as a **Final Year SDE Project** for academic purposes.

---

<p align="center">
  <strong>Made with ❤️ for the Indian Finance Community</strong><br/>
  <em>QuantPath — Quantitative Finance, Simplified</em>
</p>
