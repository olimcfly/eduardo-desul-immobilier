#!/usr/bin/env bash
# Serveur HTTP local pour tester l’admin dans le navigateur intégré de Cursor.
# Usage : ./scripts/serve-cursor-admin.sh
# Puis dans Cursor : Command Palette → "Simple Browser: Show" → l’URL affichée.

set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
HOST="${HOST:-127.0.0.1}"
PORT="${PORT:-8890}"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Admin local : http://${HOST}:${PORT}/admin/login.php"
echo "  (arrêt : Ctrl+C)"
echo ""
echo "  Dans .env : APP_ENV=development et DEV_ADMIN_PASSWORD=votre_mot_secret"
echo "  Email : celui de ADMIN_EMAIL dans includes/config.php"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
exec php -S "${HOST}:${PORT}"
