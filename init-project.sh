#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────
# init-project.sh
# Inicializa un nuevo proyecto a partir de php-template
#
# Uso:
#   ./init-project.sh                   → modo interactivo
#   ./init-project.sh mi-proyecto full  → nombre + modo directo
# ─────────────────────────────────────────────────────────────────

set -euo pipefail

# ── Colores ──────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

info()    { echo -e "${CYAN}[INFO]${NC} $*"; }
success() { echo -e "${GREEN}[OK]${NC}   $*"; }
warn()    { echo -e "${YELLOW}[WARN]${NC} $*"; }
error()   { echo -e "${RED}[ERR]${NC}  $*"; exit 1; }

# ── Banner ───────────────────────────────────────────────────────
echo -e "${BOLD}"
echo "╔══════════════════════════════════════╗"
echo "║        PHP Template — Init           ║"
echo "╚══════════════════════════════════════╝"
echo -e "${NC}"

# ── Parámetros ───────────────────────────────────────────────────
PROJECT_NAME="${1:-}"
PROJECT_MODE="${2:-}"

if [[ -z "$PROJECT_NAME" ]]; then
    read -rp "$(echo -e "${BOLD}Nombre del proyecto:${NC} ")" PROJECT_NAME
fi

if [[ -z "$PROJECT_NAME" ]]; then
    error "El nombre del proyecto no puede estar vacío."
fi

# Sanitizar: minúsculas, solo letras/números/guiones
PROJECT_NAME=$(echo "$PROJECT_NAME" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9-]/-/g')
PROJECT_DIR="./$PROJECT_NAME"

if [[ -z "$PROJECT_MODE" ]]; then
    echo ""
    echo -e "${BOLD}Modo del proyecto:${NC}"
    echo "  1) backend-only  → PHP puro, sin assets ni Gulp"
    echo "  2) fullstack     → PHP + SCSS + JS + Gulp + Vitest + Playwright"
    echo ""
    read -rp "Elige [1/2]: " MODE_CHOICE
    case "$MODE_CHOICE" in
        1) PROJECT_MODE="backend" ;;
        2) PROJECT_MODE="full"    ;;
        *) error "Opción no válida. Elige 1 o 2." ;;
    esac
fi

case "$PROJECT_MODE" in
    backend|backend-only) PROJECT_MODE="backend" ;;
    full|fullstack)       PROJECT_MODE="full"    ;;
    *) error "Modo no válido: '$PROJECT_MODE'. Usa 'backend' o 'full'." ;;
esac

# ── Confirmar ────────────────────────────────────────────────────
echo ""
info "Proyecto  : ${BOLD}$PROJECT_NAME${NC}"
info "Modo      : ${BOLD}$PROJECT_MODE${NC}"
info "Directorio: ${BOLD}$PROJECT_DIR${NC}"
echo ""
read -rp "¿Continuar? [S/n] " CONFIRM
CONFIRM="${CONFIRM:-S}"
[[ "$CONFIRM" =~ ^[Ss]$ ]] || { echo "Cancelado."; exit 0; }

# ── Verificar que no exista el directorio ────────────────────────
[[ -d "$PROJECT_DIR" ]] && error "El directorio '$PROJECT_DIR' ya existe."

# ── Copiar plantilla ─────────────────────────────────────────────
info "Copiando plantilla..."
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cp -r "$SCRIPT_DIR" "$PROJECT_DIR"
cd "$PROJECT_DIR"

# Limpiar git de la plantilla
rm -rf .git
success "Plantilla copiada."

# ── Modo backend-only: eliminar ficheros front ───────────────────
if [[ "$PROJECT_MODE" == "backend" ]]; then
    info "Modo backend-only: eliminando ficheros front..."
    rm -f  gulpfile.js package.json eslint.config.js .stylelintrc.json
    rm -f  vitest.config.js playwright.config.js
    rm -rf resources/ tests/front/
    # Eliminar job front del CI
    sed -i '/# ── Front: Lint/,/retention-days: 7/d' .github/workflows/ci.yml
    # Eliminar sección front de AGENTS.md
    sed -i '/Vitest 2/d; /Playwright/d; /npm run/d' .agent/AGENTS.md
    success "Ficheros front eliminados."
fi

# ── Personalizar ficheros ────────────────────────────────────────
info "Personalizando ficheros..."

VENDOR_NAME=$(echo "$PROJECT_NAME" | sed 's/-[^-]*$//' | sed 's/-//g')
[[ -z "$VENDOR_NAME" ]] && VENDOR_NAME="vendor"

# composer.json
sed -i "s/your-vendor\/your-project/$VENDOR_NAME\/$PROJECT_NAME/g" composer.json
sed -i "s/Project description/$PROJECT_NAME/g" composer.json

# package.json (solo fullstack)
[[ -f "package.json" ]] && sed -i "s/\"name\": \"your-project\"/\"name\": \"$PROJECT_NAME\"/g" package.json

# .agent/AGENTS.md
sed -i "s/\[RELLENAR\]/$PROJECT_NAME/g" .agent/AGENTS.md
AGENT_MODE=$([[ "$PROJECT_MODE" == "backend" ]] && echo "backend-only" || echo "fullstack")
sed -i "s/\[backend-only | fullstack\]/$AGENT_MODE/g" .agent/AGENTS.md

# .env.example
sed -i "s/My App/$PROJECT_NAME/g" .env.example

success "Ficheros personalizados."

# ── Git init ─────────────────────────────────────────────────────
info "Inicializando repositorio Git..."
git init -q
git add .
git commit -q -m "chore: init project from php-template"
success "Repositorio inicializado."

# ── Instrucciones finales ────────────────────────────────────────
echo ""
echo -e "${GREEN}${BOLD}✓ Proyecto '$PROJECT_NAME' creado correctamente${NC}"
echo ""
echo -e "${BOLD}Próximos pasos:${NC}"
echo ""
echo -e "  ${CYAN}cd $PROJECT_NAME${NC}"
echo ""
echo -e "  ${CYAN}# Instalar dependencias PHP${NC}"
echo -e "  ${CYAN}composer install${NC}"
echo ""
if [[ "$PROJECT_MODE" == "full" ]]; then
    echo -e "  ${CYAN}# Instalar dependencias Node${NC}"
    echo -e "  ${CYAN}npm install${NC}"
    echo -e "  ${CYAN}npx playwright install chromium${NC}"
    echo ""
    echo -e "  ${CYAN}# Compilar assets (primera vez)${NC}"
    echo -e "  ${CYAN}npm run build${NC}"
    echo ""
    echo -e "  ${CYAN}# Desarrollo (Gulp + BrowserSync)${NC}"
    echo -e "  ${CYAN}npm run dev${NC}"
    echo ""
fi
echo -e "  ${CYAN}# QA completo PHP${NC}"
echo -e "  ${CYAN}composer qa${NC}"
echo ""
echo -e "  ${CYAN}# Rellenar .agent/PROJECT.md con contexto del proyecto${NC}"
echo ""
