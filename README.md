# Ledger

A personal finance tracker built in Laravel — with a Claude-generated
spending overview, custom budget cycles, and multi-currency support.

**Live demo:** [ledger.jjaguilar.dev](https://ledger.jjaguilar.dev)

<!-- screenshot: dashboard with AI overview -->

## What it does

Ledger lets a user log expenses, set an expected income and a savings
goal for their current budget period, and see a dashboard summarizing
spend by category, income vs. spend, and progress toward their goal —
plus a short natural-language recap of their spending, written by Claude,
that they can generate on demand.

## Features

- **Custom budget cycles** — not locked to the calendar month. A user can
  set any day of the month as their cycle start (e.g. the 20th), and every
  total, chart, and trend on the site is computed against *their* period
  boundaries instead of `Jan 1 - Jan 31`.
- **Multi-currency support** — USD/PHP today, with amounts formatted
  throughout the app via each user's own currency preference.
- **AI spending overview** — a one-paragraph, Claude Haiku 4.5-generated
  summary of the current period's spending, built only from aggregate
  figures (category totals, total spent, income, goal progress) — never
  raw expense notes. Generation is cached per user per period and capped
  at a few regenerations a day via a server-side rate limit, so a user
  mashing the button can't run up an unbounded API bill.
- **Spending trends** — a 6-period rolling view of total spend, so a
  pattern across periods is visible at a glance, not just a single
  period's snapshot.
- **Fully responsive** — dual mobile/desktop layouts throughout
  (stacked cards vs. tables, a collapsible summary on the dashboard, a
  mobile quick-add FAB), not just a shrunk desktop view.
- Standard groundwork underneath: authenticated, per-user expense/income/
  savings-goal CRUD with ownership enforced via Eloquent Policies.

## Tech Stack

- **Backend:** PHP 8.4, Laravel 13
- **Frontend:** Blade, Tailwind CSS 4 (via `@tailwindcss/vite`), Alpine.js
  3, Chart.js 4
- **Auth:** Laravel Breeze (Blade stack)
- **Build tooling:** Vite 8
- **Database:** SQLite
- **AI:** Anthropic Claude Haiku 4.5 (Messages API)

## Project History

This started as a portfolio piece demonstrating headless CMS
architecture — a custom WordPress plugin exposing construction-expense
data via a REST API, consumed by a Laravel dashboard. It was pivoted into
a personal finance tracker with real user accounts, and the WordPress
integration was later retired from the shipping app rather than kept
alongside it. The full day-by-day build log — including that pivot, every
feature added since, and the bugs found and fixed along the way — lives
in [`PROJECT_NOTES.md`](PROJECT_NOTES.md) and the git history.

## Local Setup

```bash
git clone https://github.com/jjaguilar08/personal-finance-tracker.git
cd personal-finance-tracker/expense-dashboard

composer install
cp .env.example .env
php artisan key:generate

# Required for the AI overview feature to work locally - add your key:
#   ANTHROPIC_API_KEY=sk-ant-...
# to the .env file just created.

touch database/database.sqlite
php artisan migrate

npm install
npm run build

php artisan serve
```

The app will be running at `http://localhost:8000`. For active frontend
development, use `npm run dev` (Vite dev server with HMR) instead of
`npm run build`, or `composer run dev` to run the PHP server, queue
listener, log viewer, and Vite dev server together.

## Testing

```bash
php artisan test
```

131 tests, 370 assertions, all passing as of the latest commit.

## License

[MIT](LICENSE)
