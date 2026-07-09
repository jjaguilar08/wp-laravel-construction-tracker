# WP + Laravel Construction Tracker

A headless WordPress + Laravel portfolio project. WordPress manages construction 
log entries (materials, payroll, permits, hauling, equipment) via a custom plugin 
and REST API. Laravel consumes that API to build an expense dashboard.

## Status: In Progress (Day 4 of 7 - WP plugin + full Laravel dashboard complete)

## Stack
- WordPress (Docker) - custom plugin, custom post type, custom REST endpoint
- Laravel - consumes the WP REST API via a dedicated service class (with 
  response caching and error handling), renders a filterable expense dashboard 
  with a Chart.js visualization

## Features
- **WordPress plugin** (`wordpress/plugins/construction-tracker/`): registers a 
  `construction_log` custom post type with fields (entry_date, category, amount, 
  notes) and exposes them via a custom REST endpoint at 
  `/wp-json/wp-tracker/v1/logs`.
- **Laravel dashboard** (`expense-dashboard/`): a `ConstructionLogService` fetches 
  entries from the WP REST API over HTTP, caching the response for 60 seconds 
  and handling connection failures gracefully (a friendly error message instead 
  of a crash if WordPress is unreachable). The dashboard displays total spend, 
  a category-by-category breakdown, a Chart.js bar chart of spend by category, 
  and a full entries table - all filterable by category and date range via 
  plain GET query parameters (no JS framework required).

## Engineering Notes
A couple of things worth calling out from building this:
- **Caching gotcha**: Laravel 13 defaults `serializable_classes` to `false` in 
  `config/cache.php` - a security hardening that blocks `unserialize()` from 
  reconstructing arbitrary PHP objects out of the cache. Caching a `Collection` 
  directly worked on the first write but silently degraded to a 
  `__PHP_Incomplete_Class` on the next read, only surfacing as a `TypeError`. 
  Fixed by caching the plain decoded array instead and wrapping it in 
  `collect()` after every read, rather than loosening the security default.
- **QA pass**: ran a written manual test plan across filtering, the error path, 
  caching, and mobile viewport rendering. Found and fixed a stale-Tailwind-build 
  issue (new utility classes added to a Blade template aren't picked up until 
  `npm run build` reruns) and a table overflow bug clipping content on narrow 
  screens instead of scrolling.

## Coming Next
- Automated test coverage (feature test for filtering, unit test for the 
  service's caching/error handling)
- README screenshots / demo GIF
- Final polish pass

## WordPress Plugin
Located in `wordpress/plugins/construction-tracker/`. Registers a `construction_log` 
custom post type with fields (entry_date, category, amount, notes) exposed via 
`/wp-json/wp-tracker/v1/logs`.
