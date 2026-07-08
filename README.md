# WP + Laravel Construction Tracker

A headless WordPress + Laravel portfolio project. WordPress manages construction 
log entries (materials, payroll, permits, hauling, equipment) via a custom plugin 
and REST API. Laravel consumes that API to build an expense dashboard.

## Status: In Progress (Day 2 of 7 - WP plugin complete)

## Stack
- WordPress (Docker) - custom plugin, custom post type, custom REST endpoint
- Laravel (coming Day 3+) - consumes WP REST API, expense dashboard

## WordPress Plugin
Located in `wordpress/plugins/construction-tracker/`. Registers a `construction_log` 
custom post type with fields (entry_date, category, amount, notes) exposed via 
`/wp-json/wp-tracker/v1/logs`.
