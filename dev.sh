#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────
# dev.sh — Lanzador de entorno de desarrollo con tmux
#
# Abre una sesión tmux con 3 paneles:
#   [1] Servidor PHP
#   [2] Gulp watch / Vite dev  (según --bundler)
#   [3] Vitest watch           (tests JS en tiempo real)
#
# Uso:
#   ./dev.sh                   → Gulp (defecto)
#   ./dev.sh --bundler=vite    → Vite
#   ./dev.sh --php-only        → Solo servidor PHP (modo backend)
#   ./dev.sh --tests           → Añade panel extra con Pest --watch
#
# Requisitos: tmux instalado
#   Windows: instalar via WSL o usar Windows Terminal manualmente
#   macOS:   brew install tmux
#   Linux:   apt install tmux / pacman -S tmux
# ─────────────────────────────────────────────────────────────────

set -euo pipefail

SESSION="dev"
BUNDLER="gulp"
PHP_ONLY=false
WITH_TESTS=false

# ── Parsear argumentos ────────────────────────────────────────────
for arg in "$@"; do
    case $arg in
        --bundler=vite)    BUNDLER="vite"  ;;
        --bundler=gulp)    BUNDLER="gulp"  ;;
        --php-only)        PHP_ONLY=true   ;;
        --tests)           WITH_TESTS=true ;;
        -h|--help)
            echo "Uso: ./dev.sh [--bundler=gulp|vite] [--php-only] [--tests]"
            exit 0
            ;;
    esac
done

# ── Verificar tmux ────────────────────────────────────────────────
if ! command -v tmux &>/dev/null; then
    echo "⚠  tmux no encontrado."
    echo ""
    echo "Alternativa sin tmux — abrir manualmente:"
    echo ""
    echo "  Terminal 1:  php -S localhost:8000 -t public"
    if [[ "$PHP_ONLY" == false ]]; then
        if [[ "$BUNDLER" == "vite" ]]; then
            echo "  Terminal 2:  npx vite"
        else
            echo "  Terminal 2:  npx gulp watch"
        fi
        if [[ "$WITH_TESTS" == true ]]; then
            echo "  Terminal 3:  npx vitest"
        fi
    fi
    echo ""
    echo "O usa: npm run dev  (concurrently, sin tmux necesario)"
    exit 1
fi

# ── Matar sesión anterior si existe ──────────────────────────────
tmux kill-session -t "$SESSION" 2>/dev/null || true

# ── Crear sesión ──────────────────────────────────────────────────
tmux new-session -d -s "$SESSION" -n "dev"

# ── Panel 1: Servidor PHP ─────────────────────────────────────────
tmux send-keys -t "$SESSION" \
    "echo '🐘 Servidor PHP → http://localhost:8000' && php -S localhost:8000 -t public" \
    Enter

# ── Panel 2: Bundler (si no es php-only) ─────────────────────────
if [[ "$PHP_ONLY" == false ]]; then
    tmux split-window -h -t "$SESSION"

    if [[ "$BUNDLER" == "vite" ]]; then
        tmux send-keys -t "$SESSION" \
            "echo '⚡ Vite dev server → http://localhost:5173' && npx vite" \
            Enter
    else
        tmux send-keys -t "$SESSION" \
            "echo '🥤 Gulp watching assets...' && npx gulp watch" \
            Enter
    fi
fi

# ── Panel 3: Tests JS (opcional) ─────────────────────────────────
if [[ "$WITH_TESTS" == true ]] && [[ "$PHP_ONLY" == false ]]; then
    tmux split-window -v -t "$SESSION"
    tmux send-keys -t "$SESSION" \
        "echo '🧪 Vitest watching...' && npx vitest" \
        Enter
fi

# ── Layout y adjuntar ─────────────────────────────────────────────
tmux select-layout -t "$SESSION" tiled
tmux select-pane -t "$SESSION:0.0"

echo ""
echo "✓ Sesión tmux '$SESSION' iniciada"
echo "  Adjuntar:   tmux attach -t $SESSION"
echo "  Desconectar: Ctrl+B → D"
echo "  Cerrar todo: tmux kill-session -t $SESSION"
echo ""

tmux attach -t "$SESSION"
