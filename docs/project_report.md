# QuantPath — Project Report

## 1. Modules Description

QuantPath is a web-based quantitative stock analysis platform for the Indian stock market (BSE/NSE). The system is organized into the following modules:

### 1.1 Authentication Module

Handles user registration, login, logout, and session management. Users can create accounts with email/password, and sessions are maintained using PHP's built-in session system. Passwords are securely hashed using `password_hash()` with bcrypt.

### 1.2 Simulation Engine Module

The core computational module providing four distinct stochastic simulation models:

- **Monte Carlo (GBM)** — Geometric Brownian Motion for standard log-normal price paths.
- **Mean Reversion (Ornstein-Uhlenbeck)** — Models prices reverting to a long-term mean, suitable for commodities and interest rates.
- **Jump Diffusion (Merton)** — Extends GBM with random price jumps to model crash/rally events and fat-tailed distributions.
- **GARCH(1,1) Volatility** — Generalized Autoregressive Conditional Heteroskedasticity model for time-varying, clustered volatility.

All simulations run client-side in JavaScript with Box-Muller normal distribution generation. Users can configure parameters (drift, volatility, paths, horizon) and model-specific parameters (mean level, reversion speed, jump rate, GARCH coefficients).

### 1.3 Stock Data Module

Fetches live and historical stock data from the Alpha Vantage API. Automatically computes annualized drift (μ) and volatility (σ) from historical daily returns using log-return analysis.

### 1.4 Watchlist Module

Enables users to track Indian BSE/NSE stocks. Features include:

- Adding/removing stocks manually or from curated trending/top performer lists.
- One-click price fetching with real-time change percentage display.
- Direct launch of simulations from any watchlisted stock.

### 1.5 Comparison Module

Allows side-by-side comparison of saved simulations across different stocks and models. Displays parameter tables, bar charts comparing expected prices and risk metrics, and a risk ranking system.

### 1.6 Dashboard Module

Central hub displaying simulation history, portfolio statistics (total simulations, tracked stocks, average initial price, average drift), watchlist widget, and quick action buttons for export and deletion.

### 1.7 Profile Module

User account management including name, email, bio, phone, institution, and avatar display. Supports account deletion with cascade removal of all associated data.

---

## 2. Study of Existing System

### 2.1 Current Landscape

Existing stock analysis tools in the Indian market fall into two categories:

1. **Commercial Platforms** (Zerodha Streak, Upstox, Sharekhan) — Provide charting and trading but lack quantitative simulation capabilities. Users cannot run Monte Carlo or stochastic models.
2. **Academic/Research Tools** (MATLAB, R, Python libraries) — Powerful but require programming knowledge, local installation, and are not accessible to retail investors or students.

### 2.2 How the Existing System Works

Traditional stock analysis relies on:

- **Manual spreadsheet modeling** — Users build GBM models in Excel, which is error-prone, slow, and lacks interactive visualization.
- **Desktop software** — Requires installation, licensing, and technical expertise.
- **Online charting tools** — Limited to historical data visualization without forward-looking simulation or risk quantification.

### 2.3 Issues and Problems with the Existing System

| Problem                                         | Impact                                                                       |
| ----------------------------------------------- | ---------------------------------------------------------------------------- |
| No web-based simulation tool for Indian markets | Students and retail investors cannot access quantitative analysis            |
| Single model limitation                         | Most tools only offer basic GBM; no mean reversion, jump diffusion, or GARCH |
| No parameter auto-estimation                    | Users must manually calculate μ and σ from historical data                   |
| No integrated watchlist                         | Users switch between tracking and simulation tools                           |
| No comparison features                          | Impossible to compare simulations across stocks or models in one view        |
| Steep learning curve                            | Existing tools require programming or financial engineering knowledge        |
| No persistence                                  | Simulation results are not saved for future reference                        |

---

## 3. System Requirements

### 3.1 Functional Requirements

- FR1: Users shall register and authenticate with email/password.
- FR2: Users shall run simulations using 4 different stochastic models.
- FR3: Users shall fetch live stock data and auto-populate parameters.
- FR4: Users shall save, view, export, and delete simulation results.
- FR5: Users shall manage a stock watchlist with trending stock recommendations.
- FR6: Users shall compare multiple simulations side-by-side.
- FR7: System shall compute VaR, confidence intervals, and risk statistics.

### 3.2 Non-Functional Requirements

- NFR1: Web-based, accessible via any modern browser without installation.
- NFR2: Responsive UI with sub-second simulation execution for 500 paths.
- NFR3: Secure password storage using bcrypt hashing.
- NFR4: RESTful API architecture for frontend-backend communication.

### 3.3 Technology Stack

| Layer        | Technology                                        |
| ------------ | ------------------------------------------------- |
| Frontend     | HTML5, Tailwind CSS, Vanilla JavaScript, Chart.js |
| Backend      | PHP 8.x with MySQLi                               |
| Database     | MySQL / MariaDB                                   |
| External API | Alpha Vantage (stock data)                        |
| Server       | Apache (XAMPP)                                    |

---

## 4. Improvements Using the Proposed System

### 4.1 Key Improvements Over Existing Solutions

| Feature              | Existing Systems             | QuantPath (Proposed)                       |
| -------------------- | ---------------------------- | ------------------------------------------ |
| Simulation Models    | Single GBM only              | 4 models (GBM, O-U, Merton, GARCH)         |
| Market Focus         | Global/US-centric            | Indian BSE/NSE focused                     |
| Parameter Estimation | Manual calculation           | Auto-computed from historical data         |
| Accessibility        | Desktop/programming required | Web-based, zero installation               |
| Watchlist            | Separate tools               | Integrated with one-click simulate         |
| Trending Stocks      | Not available                | Top 10 trending + Top performers           |
| Comparison           | Manual side-by-side          | Built-in multi-simulation comparison       |
| Data Persistence     | No saving                    | Full CRUD with export to CSV               |
| Visualization        | Static charts                | Interactive Chart.js with confidence bands |
| Cost                 | Paid licenses                | Free and open-source                       |

### 4.2 Impact and Benefits

1. **Democratization** — Makes quantitative analysis accessible to students, educators, and retail investors without requiring programming skills.
2. **Model Diversity** — Four simulation models allow users to understand different market assumptions (efficient markets vs. mean-reverting vs. jump-prone vs. volatility-clustering).
3. **Data-Driven** — Auto-parameter estimation eliminates guesswork and reduces human error.
4. **Indian Market Focus** — Tailored for BSE/NSE stocks with INR formatting, unlike US-centric alternatives.
5. **Educational Value** — Serves as a teaching tool for quantitative finance concepts in academic institutions.
