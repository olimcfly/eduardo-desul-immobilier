#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
HOST="127.0.0.1"
PORT="18081"
BASE_URL="http://${HOST}:${PORT}/admin/api/seo/seo-api.php"

cleanup() {
  if [[ -n "${PHP_PID:-}" ]] && kill -0 "$PHP_PID" 2>/dev/null; then
    kill "$PHP_PID" >/dev/null 2>&1 || true
    wait "$PHP_PID" >/dev/null 2>&1 || true
  fi
}
trap cleanup EXIT

php -S "${HOST}:${PORT}" -t "$ROOT_DIR" >/tmp/seo-api-smoke.log 2>&1 &
PHP_PID=$!
sleep 1

assert_unauth() {
  local method="$1"
  local url="$2"
  local data="${3:-}"

  local http_code
  local body

  if [[ "$method" == "POST" ]]; then
    body="$(curl -sS -X POST -H 'Content-Type: application/json' -d "$data" -w '\n%{http_code}' "$url")"
  else
    body="$(curl -sS -w '\n%{http_code}' "$url")"
  fi

  http_code="$(echo "$body" | tail -n1)"
  payload="$(echo "$body" | sed '$d')"

  if [[ "$http_code" != "401" ]]; then
    echo "[FAIL] ${method} ${url} -> HTTP ${http_code} (attendu 401)"
    echo "$payload"
    exit 1
  fi

  if [[ "$payload" != *'"success":false'* ]] || [[ "$payload" != *'Non authentifi'* ]]; then
    echo "[FAIL] ${method} ${url} -> payload inattendu"
    echo "$payload"
    exit 1
  fi

  echo "[OK] ${method} ${url} -> 401"
}

# GET routes
assert_unauth GET "${BASE_URL}?action=stats"
assert_unauth GET "${BASE_URL}?action=list&type=page"
assert_unauth GET "${BASE_URL}?action=get&type=page&id=1"
assert_unauth GET "${BASE_URL}?action=missing"
assert_unauth GET "${BASE_URL}?action=duplicates"
assert_unauth GET "${BASE_URL}?action=sitemap"
assert_unauth GET "${BASE_URL}?action=check-slug&type=page&slug=test"

# POST routes
assert_unauth POST "${BASE_URL}?action=save" '{"id":1,"type":"page","meta_title":"x"}'
assert_unauth POST "${BASE_URL}?action=bulk-save" '{"items":[{"id":1,"type":"page","meta_title":"x"}]}'
assert_unauth POST "${BASE_URL}?action=analyze" '{"id":1,"type":"page"}'

echo "SEO API smoke routes: OK"
