#!/usr/bin/env bash
# Pulls the latest commit on $DEPLOY_BRANCH and rebuilds/restarts the VitaDB
# containers. Safe to run manually at any time - it's also exactly what
# deploy/webhook_listener.py runs after verifying a GitHub push. This is also
# the command for your very first deploy (run it once config.php and
# vitadb.env exist - see the deploy guide).
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="$REPO_DIR/../vitadb.env"

if [ ! -f "$ENV_FILE" ]; then
  echo "[update.sh] Missing $ENV_FILE - copy deploy/.env.example there and fill it in first." >&2
  exit 1
fi

if [ ! -f "$REPO_DIR/../config.php" ]; then
  echo "[update.sh] Missing $REPO_DIR/../config.php - see the deploy guide's 'secrets' step." >&2
  exit 1
fi

# shellcheck disable=SC1090
set -a; source "$ENV_FILE"; set +a
BRANCH="${DEPLOY_BRANCH:-master}"

cd "$REPO_DIR"

echo "[update.sh] Fetching origin/$BRANCH..."
git fetch origin "$BRANCH"

echo "[update.sh] Resetting working tree to origin/$BRANCH..."
git reset --hard "origin/$BRANCH"

echo "[update.sh] Building and starting containers..."
# docker-compose.yml's db healthcheck + the app's "condition: service_healthy"
# dependency mean this blocks until MariaDB is actually accepting
# connections (not just "container exists") - safe to run a mysql import
# immediately after this script finishes, even on a fresh/wiped volume.
docker compose -f deploy/docker-compose.yml --env-file "$ENV_FILE" up -d --build

echo "[update.sh] Removing dangling images..."
docker image prune -f >/dev/null

echo "[update.sh] Done: $(git rev-parse --short HEAD)"
