# Makefile — php-template
# ─────────────────────────────────────────────────────────────────
# Atajos para los comandos más habituales.
#
# Uso:
#   make dev          → arrancar entorno (PHP + Gulp)
#   make qa           → lint + stan + tests
#   make migrate      → ejecutar migraciones
#   make help         → ver todos los comandos
#
# Windows: instalar make via winget, chocolatey o usar Git Bash
#   winget install GnuWin32.Make
#   choco install make
# ─────────────────────────────────────────────────────────────────

.PHONY: help install dev dev-vite build build-prod qa \
        lint lint-fix stan test test-pest test-cover test-js test-e2e \
        migrate migrate-rollback migrate-status migrate-fresh \
        cache-clear console

# Colores
GREEN  := \033[0;32m
YELLOW := \033[1;33m
CYAN   := \033[0;36m
NC     := \033[0m

# ── Ayuda ─────────────────────────────────────────────────────────────────────
help: ## Mostrar esta ayuda
	@echo ""
	@echo "  $(CYAN)php-template$(NC) — Comandos disponibles"
	@echo ""
	@awk 'BEGIN {FS = ":.*##"} /^[a-zA-Z_-]+:.*?##/ { printf "  $(CYAN)%-20s$(NC) %s\n", $$1, $$2 }' $(MAKEFILE_LIST)
	@echo ""

# ── Instalación ───────────────────────────────────────────────────────────────
install: ## Instalar todas las dependencias (PHP + Node)
	@echo "$(CYAN)Installing PHP dependencies...$(NC)"
	composer install
	@echo "$(CYAN)Installing Node dependencies...$(NC)"
	npm install
	@echo "$(GREEN)✓ Done$(NC)"

install-php: ## Instalar solo dependencias PHP
	composer install

install-node: ## Instalar solo dependencias Node
	npm install

# ── Desarrollo ────────────────────────────────────────────────────────────────
dev: ## Arrancar PHP + Gulp (concurrently)
	npm run dev

dev-vite: ## Arrancar PHP + Vite (concurrently)
	npm run dev:vite

dev-tmux: ## Arrancar con tmux (paneles separados)
	./dev.sh

dev-tmux-vite: ## Arrancar con tmux usando Vite
	./dev.sh --bundler=vite

php: ## Solo servidor PHP en localhost:8000
	php -S localhost:8000 -t public

# ── Build ─────────────────────────────────────────────────────────────────────
build: ## Compilar assets (desarrollo)
	npm run build

build-prod: ## Compilar assets (producción, minificado)
	npm run build:prod

build-vite: ## Compilar con Vite (desarrollo)
	npm run build:vite

build-vite-prod: ## Compilar con Vite (producción)
	npm run build:vite:prod

# ── Calidad de código PHP ─────────────────────────────────────────────────────
qa: ## Ejecutar lint + stan + tests (todo)
	composer qa

lint: ## PHPCS — detectar problemas de estilo
	composer lint

lint-fix: ## PHP-CS-Fixer — autocorregir estilo
	composer lint:fix

stan: ## PHPStan nivel 6 — análisis estático
	composer stan

test: ## PHPUnit — tests unitarios e integración
	composer test

test-pest: ## Pest — feature tests
	composer test:pest

test-cover: ## PHPUnit con coverage HTML
	composer test:cover
	@echo "$(GREEN)Coverage report: storage/coverage/html/index.html$(NC)"

# ── Tests front ───────────────────────────────────────────────────────────────
test-js: ## Vitest — tests unitarios JavaScript
	npm run test

test-js-watch: ## Vitest en modo watch
	npm run test:watch

test-e2e: ## Playwright — tests E2E
	npm run test:e2e

test-all: ## Todos los tests (PHP + JS + E2E)
	composer qa
	npm run test:all

# ── Migraciones ───────────────────────────────────────────────────────────────
migrate: ## Ejecutar migraciones pendientes
	php bin/console migrate

migrate-rollback: ## Deshacer la última migración
	php bin/console migrate:rollback

migrate-status: ## Ver estado de migraciones
	php bin/console migrate:status

migrate-fresh: ## Rollback completo + migrate
	php bin/console migrate:fresh

# ── Generadores ───────────────────────────────────────────────────────────────
# Uso: make migration NAME=create_posts_table
migration: ## Crear migración (NAME=nombre_migracion)
	php bin/console make:migration $(NAME)

# Uso: make controller NAME=Post
controller: ## Crear controller (NAME=NombreController)
	php bin/console make:controller $(NAME)

# Uso: make model NAME=Post
model: ## Crear modelo (NAME=NombreModelo)
	php bin/console make:model $(NAME)

# Uso: make repository NAME=Post
repository: ## Crear repositorio (NAME=NombreRepository)
	php bin/console make:repository $(NAME)

# ── Otros ─────────────────────────────────────────────────────────────────────
cache-clear: ## Vaciar storage/cache/
	php bin/console cache:clear

console: ## Abrir ayuda del CLI
	php bin/console help

env: ## Copiar .env.example a .env
	@test -f .env && echo ".env ya existe" || (cp .env.example .env && echo "$(GREEN)✓ .env creado$(NC)")
