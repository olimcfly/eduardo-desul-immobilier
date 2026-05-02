#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="${1:-.}"
cd "$ROOT_DIR"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

errors=0
warnings=0

ok() {
  printf "${GREEN}OK${NC}  %s\n" "$1"
}

warn() {
  printf "${YELLOW}WARN${NC} %s\n" "$1"
  warnings=$((warnings + 1))
}

fail() {
  printf "${RED}FAIL${NC} %s\n" "$1"
  errors=$((errors + 1))
}

check_dir() {
  local path="$1"
  if [ -d "$path" ]; then
    ok "Dossier present: $path"
  else
    fail "Dossier manquant: $path"
  fi
}

echo "Verification structure duplication (modele site-immo)"
echo "Racine: $(pwd)"
echo

required_dirs=(
  "admin"
  "admin/features"
  "modules"
  "core"
  "config"
  "database"
  "public"
  "storage"
  "scripts"
)

echo "1) Verification des dossiers obligatoires"
for dir in "${required_dirs[@]}"; do
  check_dir "$dir"
done
echo

echo "2) Verification absence ancien chemin admin/modules"
if [ -d "admin/modules" ]; then
  fail "Ancien dossier detecte: admin/modules (doit etre renomme en admin/features)"
else
  ok "Pas de dossier admin/modules"
fi

if command -v rg >/dev/null 2>&1; then
  if rg -n "admin/modules" . --glob '!vendor/**' --glob '!.git/**' >/dev/null; then
    fail "Des references texte a admin/modules existent encore"
    echo "Occurrences detectees:"
    rg -n "admin/modules" . --glob '!vendor/**' --glob '!.git/**' || true
  else
    ok "Aucune reference texte a admin/modules"
  fi
elif command -v python3 >/dev/null 2>&1; then
  tmp_hits="$(mktemp)"
  python3 - <<'PY' > "$tmp_hits"
from pathlib import Path
root = Path(".")
skip_parts = {"vendor", ".git"}
for p in root.rglob("*"):
    if not p.is_file():
        continue
    if any(part in skip_parts for part in p.parts):
        continue
    if p.as_posix() == "scripts/verify-duplication-structure.sh":
        continue
    try:
        text = p.read_text(encoding="utf-8", errors="ignore")
    except Exception:
        continue
    if "admin/modules" in text:
        print(p.as_posix())
PY
  mapfile -t hits < "$tmp_hits"
  rm -f "$tmp_hits"
  if [ "${#hits[@]}" -gt 0 ]; then
    fail "Des references texte a admin/modules existent encore"
    echo "Occurrences detectees:"
    printf '%s\n' "${hits[@]}"
  else
    ok "Aucune reference texte a admin/modules (fallback python3)"
  fi
else
  warn "Ni rg ni python3 indisponibles, verification texte admin/modules non executee"
fi
echo

echo "3) Verification de base admin/features"
expected_features=("blog" "cms" "crm" "gmb")
for feature in "${expected_features[@]}"; do
  if [ -d "admin/features/$feature" ]; then
    ok "Feature admin detectee: admin/features/$feature"
  else
    warn "Feature admin absente: admin/features/$feature (optionnelle selon le projet duplique)"
  fi
done
echo

echo "4) Verification fichiers clefs"
required_files=(
  "admin/index.php"
  "index.php"
  "README.md"
)

for file in "${required_files[@]}"; do
  if [ -f "$file" ]; then
    ok "Fichier present: $file"
  else
    fail "Fichier manquant: $file"
  fi
done
echo

echo "Bilan"
echo "- Erreurs   : $errors"
echo "- Avertis.  : $warnings"

if [ "$errors" -gt 0 ]; then
  echo
  fail "Verification KO"
  exit 1
fi

echo
ok "Verification OK"
exit 0
