# Personal Finance Tracker

A Laravel portfolio project: a personal expense/finance tracker with user
accounts. Users log expenses, set monthly expected income and a savings
goal, and see a dashboard summarizing spend by category, income vs. spend,
and savings progress for the current month.

## Status: In Progress

## Stack
- Laravel (Blade + Breeze auth), Tailwind 4 via Vite, Chart.js for the
  category breakdown chart, SQLite (dev)

## Features
- **Auth** (Laravel Breeze, Blade stack): registration/login, per-user data
  scoping enforced via Eloquent Policies.
- **Expenses**: CRUD (`/expenses`), amount/category/date/notes, scoped to
  the authenticated user.
- **Income expectations & savings goals**: one row per user per month
  (`/income-expectations`, `/savings-goals`), enforced unique at the DB
  level.
- **Dashboard** (`/dashboard`, auth-gated): current-month total spend,
  category breakdown with a Chart.js bar chart, expected income vs. spend,
  and savings progress (expected income − spend), with friendly prompts
  when income/goal aren't set yet for the month. Quick-add expense form
  embedded on the page.

## Earlier Project Phase: WordPress + Laravel Headless CMS
This project started as a portfolio piece demonstrating headless CMS
architecture: a custom WordPress plugin ("Construction Tracker") exposing
construction expense entries via a REST API, consumed by a Laravel
dashboard. A `help_article` CPT and Laravel-side `/help` page were added
later in that phase for onboarding content.

That WordPress-backed code (`wordpress/plugins/construction-tracker/`, the
`ConstructionLogService`/`ConstructionDashboardController`/
`ConstructionLogController`, the `HelpArticleService`/`HelpController`, the
`/construction`, `/logs`, and `/help` routes, and their views/tests) has
since been removed from the shipping app. The project pivoted to a
personal finance tracker with user accounts instead, and the WordPress
integration was retired rather than repurposed.

It remains available in git history for anyone curious how the headless
CMS integration worked - see commits prior to the removal, and
`PROJECT_NOTES.md`'s Day 1-9 entries for the full build log (WP plugin
setup, the REST endpoints, the Laravel consumer service, caching/error
handling, and the `help_article` CPT addition).

## Tech Stack
PHP/Laravel (Jon's primary stack, 7+ years), WordPress (used during the
earlier headless-CMS phase, retired), Docker, MySQL (WP side, retired)
