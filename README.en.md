# php-template

A modern PHP 8.2–8.5 MVC starter template with full tooling, testing, and AI agent support.

[![CI](https://github.com/miguelex/php-template/actions/workflows/ci.yml/badge.svg)](https://github.com/miguelex/php-template/actions)
[![PHP](https://img.shields.io/badge/PHP-8.2%20–%208.5-777BB4?logo=php)](https://php.net)
[![Packagist](https://img.shields.io/packagist/v/miguelex/php-template?logo=packagist)](https://packagist.org/packages/miguelex/php-template)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
> 🇪🇸 [Versión en español / Spanish README](README.md)

---

## What's new in v2.0

- 🐘 **PHP 8.2 → 8.5 support** — CI tested on 8.2, 8.3, 8.4 and 8.5.
- 🔧 **Rector** with the `php85` set + quality sets (`composer rector` / `rector:fix`) to modernize code and catch deprecations.
- 🧭 **Route parameters** (`/users/{id}`) and **chainable per-route middleware**.
- 🛡 **SQL hardening** — identifier/`ORDER BY` validation in `ActiveRecord` and `BaseRepository` (values were already parameterized).
- 🔐 **CSRF** via `X-CSRF-Token` header too (APIs/fetch), escaped emails, and a fix for hydrating `NULL` columns.
- 📈 **PHPStan 2.x** and `.gitattributes` (forced LF — no more false lint failures on Windows).
- 📦 Published on **Packagist**: `composer create-project miguelex/php-template my-project`.

---

## What's included?

### 🏗 MVC Architecture
- **Router** — GET/POST/PUT/PATCH/DELETE, **route parameters `{id}`**, **global and per-route middleware**, `_method` override, 404/500 handling
- **ActiveRecord** — lightweight PDO-based ORM with prepared statements, `find`, `where`, `save`, `delete`, validation, alerts and SQL identifier validation
- **BaseRepository** — decoupled data access layer for complex domains, with `paginate`, `findWhere`, transactions
- **Database** — multi-driver PDO: **MySQL, SQLite and PostgreSQL** (DSN from `DB_CONNECTION`)
- **Migration system** — `up()`/`down()` migrations with state tracking and CLI runner
- **Log** — PSR-3 logging via **Monolog** (`Log::info()`, `Log::error()`) to `storage/logs/`
- **Whoops** — rich error pages in development
- **EmailService** — SMTP via PHPMailer with (escaped) HTML templates
- **Html helper** — safe escaping, CSRF (field or `X-CSRF-Token` header), alerts, debug dump
- **Paginator** — pagination with HTML renderer
- **JsonResponse** — structured JSON responses for API mode
- **AuthMiddleware** — session-based auth with `check()`, `require()`, `guest()`, session fixation protection

### ✅ PHP Code Quality
- **PHPStan 2.x** level 6 — static analysis
- **Rector** — automatic modernization and version readiness (`php85` set)
- **PHP_CodeSniffer** — PSR-12
- **PHP-CS-Fixer** — auto style correction
- **PHPUnit 11** + **Pest 3** — unit, integration and feature tests

### ⚡ Frontend (fullstack mode)
Two bundler options — pick what fits your project:

| | **Gulp** | **Vite** |
|---|---|---|
| Best for | Vanilla JS, simple projects | ES modules, HMR, growing frontend |
| SCSS → CSS | ✅ | ✅ |
| Images: WebP + AVIF + optimization | ✅ | ✅ (plugin) |
| Live reload | BrowserSync | Native HMR |
| Config file | `gulpfile.js` | `vite.config.js` |

Generated image formats: original (optimized) + WebP (quality 80) + AVIF (quality 50).

Front testing:
- **ESLint** + **Stylelint**
- **Vitest** — JS unit tests
- **Playwright** — E2E tests

### 🤖 AI Agent Ready
Files under `.agent/` are auto-read by Claude Code, Cursor, GitHub Copilot, and similar:

| File | Purpose |
|---|---|
| `AGENTS.md` | Entry point: project identity, permissions, available commands |
| `WORKFLOW.md` | How to act: plan first, surgical changes, verify before done, self-improvement loop |
| `CONVENTIONS.md` | How to write: PSR-12, BEM, Conventional Commits, naming |
| `PROJECT.md` | Business context (fill in per project) |
| `TASKS.md` | Strategic backlog |
| `tasks/todo.md` | Active task: plan + checkboxes + review section |
| `tasks/lessons.md` | Past mistakes + rules to avoid repeating them |

### 🛠 CLI — bin/console
```bash
php bin/console migrate
php bin/console migrate:status
php bin/console migrate:rollback [n]
php bin/console migrate:fresh
php bin/console make:migration create_posts_table
php bin/console make:controller Post
php bin/console make:model Post
php bin/console make:repository Post
php bin/console cache:clear
```

### 🔄 CI/CD
GitHub Actions with two parallel jobs: PHP QA (PHPStan + Rector + PHPCS + PHPUnit + Pest, PHP 8.2/8.3/8.4/8.5 matrix) and Front QA (ESLint + Stylelint + Vitest + Playwright).

Plus: **Dependabot** (Composer + npm + Actions), **release-please** (automatic versioning + CHANGELOG + releases from Conventional Commits) and **architecture tests** (pest-arch). The database layer is tested for real with **in-memory SQLite**.

---

## Requirements

| Tool | Min version | Notes |
|---|---|---|
| PHP | 8.2 (supports up to 8.5) | `php --version` |
| Composer | 2.x | `composer --version` |
| Node.js | 20 LTS | fullstack mode only |
| npm | 10+ | fullstack mode only |
| Git | any | |

### Windows
- Install PHP from [windows.php.net](https://windows.php.net/download/) or via **Laragon** / **XAMPP**
- Install Node.js from [nodejs.org](https://nodejs.org)
- Add PHP and Composer to PATH
- `dev.sh` requires WSL or Git Bash; alternatively use `npm run dev` directly
- Install `make` with: `winget install GnuWin32.Make` or `choco install make`

### WSL (Windows Subsystem for Linux)
```bash
sudo apt update && sudo apt install php8.2 php8.2-mbstring php8.2-xml php8.2-curl
curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
nvm install 20 && nvm use 20
```

### macOS
```bash
brew install php composer node@20
```

### Linux (Ubuntu/Debian)
```bash
sudo apt install php8.2 php8.2-mbstring php8.2-xml php8.2-curl
# Composer from getcomposer.org, Node via nvm
```

---

## Create a new project

### Option A — Composer create-project (fastest)

```bash
composer create-project miguelex/php-template my-project
cd my-project
```

Pulls the latest stable release from Packagist without the template's git history. Then adjust the `name` in `composer.json` and init your own repo.

### Option B — Clone and use `init-project.sh`

```bash
git clone https://github.com/miguelex/php-template.git
cd php-template
./init-project.sh
```

The script asks for:
1. **Project name** (used as directory name and in composer/package.json)
2. **Mode**: `backend-only` or `fullstack`

Or pass arguments directly:
```bash
./init-project.sh my-api      backend   # PHP API, no frontend
./init-project.sh my-website  full      # Full website with assets
```

### Option C — GitHub fork

Fork the repo, clone your fork, remove the history:
```bash
rm -rf .git && git init && git add . && git commit -m "chore: init"
```

---

## Installation

```bash
cd my-project

# PHP dependencies (.env is auto-created from .env.example)
composer install

# Node dependencies (fullstack only)
npm install
npx playwright install chromium
```

Edit `.env` with your project values:
```env
APP_NAME="My Project"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_DATABASE=my_database
DB_USERNAME=root
DB_PASSWORD=
```

> **Multi-DB:** `DB_CONNECTION` accepts `mysql`, `sqlite` or `pgsql`. For SQLite just set
> `DB_CONNECTION=sqlite` and `DB_DATABASE=storage/database.sqlite` (or `:memory:`), no host/credentials needed.
> The ORM uses portable SQL; the migrations you write are engine-specific.

---

## Development

### Start the environment

**Option 1 — `concurrently` (recommended on Windows/WSL)**
```bash
npm run dev        # Vite + PHP server in one terminal (default)
npm run dev:gulp   # Gulp + PHP server (alternative for vanilla JS)
npm run php        # PHP server only (backend-only)
```

Each process shows a colored prefix: `[PHP]` in blue, `[GULP]`/`[VITE]` in green/yellow.

**Option 2 — `dev.sh` with tmux (Linux/macOS/WSL)**
```bash
./dev.sh                  # Gulp + PHP (split tmux panes)
./dev.sh --bundler=vite   # Vite + PHP
./dev.sh --php-only       # PHP only
./dev.sh --tests          # Gulp + PHP + Vitest watch
```

**Option 3 — Manual terminals**
```bash
# Terminal 1
php -S localhost:8000 -t public

# Terminal 2 — Gulp
npx gulp watch

# Terminal 2 — Vite
npx vite
```

---

## Commands

### PHP
```bash
composer qa           # Everything: lint + stan + tests
composer lint         # PHPCS — detect style issues
composer lint:fix     # PHP-CS-Fixer — auto-fix style
composer stan         # PHPStan — static analysis level 6
composer rector       # Rector — show proposed modernizations (dry-run)
composer rector:fix   # Rector — apply modernizations
composer test         # PHPUnit
composer test:pest    # Pest
composer test:cover   # PHPUnit + HTML coverage at storage/coverage/
```

### Frontend (fullstack)
```bash
npm run build          # Compile assets with Vite (dev, default)
npm run build:prod     # Vite build (production, minified)
npm run build:gulp     # Gulp build (dev)
npm run build:gulp:prod # Gulp build (production)
npm run test           # Vitest unit
npm run test:watch     # Vitest watch mode
npm run test:e2e       # Playwright E2E
npm run test:all       # Vitest + Playwright
npm run lint:js        # ESLint
npm run lint:css       # Stylelint
```

### Makefile shortcuts
```bash
make help           # List all available commands
make install        # composer install + npm install
make dev            # PHP + Gulp
make dev-vite       # PHP + Vite
make qa             # lint + stan + tests
make test-all       # PHP + JS + E2E
make migrate        # Run pending migrations
make cache-clear    # Clear storage/cache/
```

---

## Project structure

```
php-template/
├── .agent/                        ← AI agent context
│   ├── AGENTS.md                  ← Entry point: permissions, commands
│   ├── WORKFLOW.md                ← Behavioral guidelines
│   ├── CONVENTIONS.md             ← Coding conventions
│   ├── PROJECT.md                 ← Business context (fill in)
│   └── TASKS.md                   ← Strategic backlog
│
├── tasks/                         ← Agent working files
│   ├── todo.md                    ← Active task: plan + review
│   └── lessons.md                 ← Past mistakes + rules
│
├── .github/
│   ├── workflows/ci.yml           ← GitHub Actions
│   ├── ISSUE_TEMPLATE/
│   └── PULL_REQUEST_TEMPLATE.md
│
├── src/
│   ├── Core/
│   │   ├── App.php                ← Bootstrap
│   │   ├── Router.php             ← MVC Router (params + middleware)
│   │   ├── ActiveRecord.php       ← PDO ORM base
│   │   ├── BaseRepository.php     ← Repository base
│   │   ├── RepositoryInterface.php
│   │   ├── Migration.php          ← Migration base
│   │   ├── Migrator.php           ← Migration runner
│   │   ├── Database.php           ← PDO (MySQL/SQLite/PostgreSQL)
│   │   └── Log.php                ← PSR-3 logging (Monolog)
│   ├── Controllers/
│   ├── Models/
│   │   └── User.php               ← Example model
│   ├── Repositories/
│   │   └── UserRepository.php     ← Example repository
│   ├── Services/
│   │   └── EmailService.php       ← SMTP via PHPMailer
│   ├── Middleware/
│   │   └── AuthMiddleware.php     ← Session auth
│   ├── Helpers/
│   │   ├── Html.php               ← Escaping, CSRF, alerts
│   │   ├── Paginator.php          ← Pagination
│   │   └── JsonResponse.php       ← API responses
│   └── Exceptions/
│
├── views/
│   ├── layouts/default.php
│   ├── templates/
│   ├── errors/
│   └── home.php
│
├── config/routes.php
├── rector.php                     ← Rector config (php85 + quality)
├── .gitattributes                 ← Forces LF line endings
├── public/index.php               ← Entry point
├── resources/                     ← Frontend sources (fullstack)
├── tests/                         ← PHP + JS + E2E
├── database/migrations/
├── bin/console                    ← CLI tool
├── gulpfile.js                    ← Bundler option A
├── vite.config.js                 ← Bundler option B
├── Makefile
├── dev.sh                         ← tmux launcher
└── init-project.sh                ← Project scaffolding script
```

---

## Routing and middleware

```php
// config/routes.php

// Simple route
$router->get('/', [HomeController::class, 'index']);

// Route parameters → passed as the handler's 2nd associative argument
$router->get('/users/{id}', function (Router $router, array $params): void {
    $id = (int) $params['id'];
    // ...
});

// Per-route middleware (chainable)
$router->get('/dashboard', [DashboardController::class, 'index'])
       ->middleware(App\Middleware\AuthMiddleware::check(...));

// Global middleware (all routes)
$router->use(App\Middleware\AuthMiddleware::check(...));
```

---

## ActiveRecord vs Repository

### ActiveRecord — simple/medium projects

```php
final class Post extends ActiveRecord {
    protected static string $table = 'posts';
    protected static array $columns = ['id', 'title', 'body'];
}

$post = Post::find(1);
$post->title = 'New title';
$post->save();
```

### Repository — complex domain

```php
final class PostRepository extends BaseRepository {
    protected string $table = 'posts';

    public function findPublished(): array {
        return $this->findWhere(['published' => 1], 'created_at DESC');
    }

    protected function hydrate(array $row): Post { ... }
    protected function extract(object $entity): array { ... }
}

$repo  = new PostRepository(Database::connect());
$posts = $repo->paginate(page: 1, perPage: 10);
```

Both patterns can coexist in the same project. Don't mix them for the same entity.

---

## Design decisions

**Why no framework (Laravel/Symfony)?**
For projects where a full framework's overhead isn't justified: specific business modules, legacy system integrations, or cases where fine-grained control over each layer matters.

**Gulp or Vite?**
Both are available. Gulp is ideal for simple JS (a few functions, jQuery): straightforward pipeline, no bundling needed. Vite is better when you start using `import/export`, need instant HMR, or expect the frontend to grow significantly.

**Why PHPStan level 6 and not 9?**
Level 9 in real-world projects with legacy code or external integrations generates dozens of false positives. Level 6 delivers 90% of the value without the noise. It can be raised gradually.

---

## Contributing

Contributions are welcome! This includes Core MVC improvements, new tooling features, bug fixes, documentation, or additional examples.

### Before opening a PR

1. **Fork** the repo and clone your fork
2. Create a descriptive branch:
   ```bash
   git checkout -b feature/short-name
   git checkout -b fix/bug-description
   ```
3. Make sure **`composer qa` passes** before pushing
4. If you add PHP code, add tests for it
5. Follow [Conventional Commits](https://www.conventionalcommits.org/):
   ```
   feat: add support for dynamic route parameters
   fix: resolve URI with query string
   chore: update Composer dependencies
   docs: improve README installation section
   test: add tests for ActiveRecord::whereMany
   ```

See [CONTRIBUTING.md](CONTRIBUTING.md) for the full guide.

---

## License

MIT — see [LICENSE](LICENSE).

---

## Acknowledgements

Inspired by the mini-framework from [codigoconjuan](https://codigoconjuan.com), modernized with PHP 8.2, PDO, typed properties, and current tooling.
