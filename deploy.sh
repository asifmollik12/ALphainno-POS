#!/usr/bin/env bash
# Deploy Alphainno POS to live server
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
COMMIT_MSG="${1:-Deploy: update POS app}"

if [[ -f "$ROOT/.env.deploy" ]]; then
  # shellcheck disable=SC1091
  source "$ROOT/.env.deploy"
fi

exec "$ROOT/scripts/deploy-live.sh" pos "$COMMIT_MSG"
