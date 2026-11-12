#!/usr/bin/env bash
#
# Production deploy script for tourneyforge.com.
# Invoked over SSH by .github/workflows/deploy.yml (runs as root, the deploy user).
#
# It force-syncs the checkout to origin/main, installs production dependencies,
# runs migrations, clears caches, and reloads the runtime. .env, writable/ and
# uploads/ live on the server (git-ignored) and are never touched here.
#
# Prerequisites on the server (one-time, see docs):
#   - this directory is a git checkout of the repo, tracking origin/main
#   - PHP 8.3 is the active handler for the site (backend template PHP-8_3)
#   - ws.php runs as the systemd unit `tourneyforge-ws`
#   - the deploy user is root (the checkout and its data are root-owned)

set -euo pipefail

APP_DIR="/home/tourneyforge/web/tourneyforge.com/public_html/Tournament_Bracket_Generator"
PHP_FPM_SERVICE="php8.3-fpm"
WS_SERVICE="tourneyforge-ws"
PHP_BIN="/usr/bin/php8.3"
COMPOSER_BIN="$(command -v composer)"

# Run composer as root (the checkout is root-owned) and let git trust this path.
export COMPOSER_ALLOW_SUPERUSER=1
git config --global --add safe.directory "$APP_DIR"

cd "$APP_DIR"

echo "==> Syncing code to origin/main"
git fetch --prune origin
PREV_REF="$(git rev-parse HEAD)"        # remembered for manual rollback
git reset --hard origin/main
echo "    was ${PREV_REF}, now $(git rev-parse HEAD)"

echo "==> Installing production dependencies"
"$PHP_BIN" "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction --no-progress

echo "==> Running migrations"
"$PHP_BIN" spark migrate --all

echo "==> Clearing caches"
"$PHP_BIN" spark cache:clear || true

echo "==> Reloading runtime"
systemctl reload "$PHP_FPM_SERVICE"
systemctl restart "$WS_SERVICE"

echo "==> Deploy complete: $(git rev-parse --short HEAD)"
