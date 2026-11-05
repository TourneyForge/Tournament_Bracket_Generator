#!/usr/bin/env bash
#
# Production deploy script for tourneyforge.com.
# Invoked over SSH by .github/workflows/deploy.yml (runs as the deploy user).
#
# It force-syncs the checkout to origin/main, installs production dependencies,
# runs migrations, clears caches, and reloads the runtime. .env, writable/ and
# uploads/ live on the server (git-ignored) and are never touched here.
#
# Prerequisites on the server (one-time, see docs):
#   - this directory is a clean git checkout of the repo, tracking origin/main
#   - PHP 8.3 FPM is the active handler for the site
#   - the deploy user has a narrow sudoers entry for the two systemctl lines below
#   - ws.php runs as the systemd unit `tourneyforge-ws`

set -euo pipefail

APP_DIR="/home/tourneyforge/web/tourneyforge.com/public_html/Tournament_Bracket_Generator"
PHP_FPM_SERVICE="php8.3-fpm"
WS_SERVICE="tourneyforge-ws"
PHP_BIN="$(command -v php)"
COMPOSER_BIN="$(command -v composer)"

cd "$APP_DIR"

echo "==> Syncing code to origin/main"
git fetch --prune origin
PREV_REF="$(git rev-parse HEAD)"        # remembered for manual rollback
git reset --hard origin/main
echo "    was ${PREV_REF}, now $(git rev-parse HEAD)"

echo "==> Installing production dependencies"
"$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction --no-progress

echo "==> Running migrations"
"$PHP_BIN" spark migrate --all

echo "==> Clearing caches"
"$PHP_BIN" spark cache:clear || true

echo "==> Reloading runtime"
sudo /usr/bin/systemctl reload "$PHP_FPM_SERVICE"
sudo /usr/bin/systemctl restart "$WS_SERVICE"

echo "==> Deploy complete: $(git rev-parse --short HEAD)"
