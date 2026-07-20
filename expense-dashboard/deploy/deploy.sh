#!/usr/bin/env bash
#
# Redeploy script - run this on the server for every deploy *after* the
# first one (initial server setup, .env, and first migrate are done by
# hand per the deploy walkthrough in PROJECT_NOTES.md's Day 23 entry).
#
# Usage: ./deploy/deploy.sh
# Run from the repo root (the directory containing this repo's
# expense-dashboard/ subdirectory), as the user that owns the app files.

set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

echo "==> Pulling latest code"
git pull --ff-only

echo "==> Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Installing and building frontend assets"
npm ci
npm run build

echo "==> Putting the app in maintenance mode"
php artisan down || true

echo "==> Running migrations"
php artisan migrate --force

echo "==> Refreshing cached config/routes/views"
php artisan optimize:clear
php artisan optimize

echo "==> Bringing the app back up"
php artisan up

echo "==> Reloading PHP-FPM"
sudo systemctl reload php8.4-fpm

echo "==> Done"
