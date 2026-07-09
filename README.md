# WP + Laravel Construction Tracker

A headless WordPress + Laravel portfolio project. WordPress manages construction 
log entries (materials, payroll, permits, hauling, equipment) via a custom plugin 
and REST API. Laravel consumes that API to build an expense dashboard.

## Status: In Progress (Day 3 of 7 - WP plugin + Laravel dashboard complete)

## Stack
- WordPress (Docker) - custom plugin, custom post type, custom REST endpoint
- Laravel - consumes the WP REST API via a dedicated service class, renders an 
  expense dashboard

## Features
- **WordPress plugin** (`wordpress/plugins/construction-tracker/`): registers a 
  `construction_log` custom post type with fields (entry_date, category, amount, 
  notes) and exposes them via a custom REST endpoint at 
  `/wp-json/wp-tracker/v1/logs`.
- **Laravel dashboard** (`expense-dashboard/`): a `ConstructionLogService` fetches 
  entries from the WP REST API over HTTP (with timeout and error handling for 
  when WordPress is unreachable), and a dashboard view displays total spend, a 
  category-by-category breakdown, and a full entries table.

## Coming Next
- Filtering entries by category and date range
- A chart visualization of spend by category

## WordPress Plugin
Located in `wordpress/plugins/construction-tracker/`. Registers a `construction_log` 
custom post type with fields (entry_date, category, amount, notes) exposed via 
`/wp-json/wp-tracker/v1/logs`.
