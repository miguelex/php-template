# php-template

Plantilla base para proyectos PHP 8.2 con arquitectura MVC, tooling moderno y soporte para desarrollo con agentes IA.

[![CI](https://github.com/miguelex/php-template/actions/workflows/ci.yml/badge.svg)](https://github.com/miguelex/php-template/actions)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
> 🇬🇧 [English README](README.en.md)

---

## ¿Qué incluye?

### 🏗 Arquitectura MVC
- **Router** — registro de rutas GET/POST/PUT/PATCH/DELETE, soporte `_method` override, middleware global, manejo de 404/500
- **ActiveRecord** — ORM ligero sobre PDO con prepared statements, `find`, `where`, `save`, `delete`, validación, alertas
- **EmailService** — envío SMTP vía PHPMailer con templates HTML base
- **Html helper** — escape seguro, CSRF, alertas en vista, dump de debug

### ✅ Calidad de código PHP
- **PHPStan** nivel 6 — análisis estático
- **PHP_CodeSniffer** — PSR-12
- **PHP-CS-Fixer** — autocorrección de estilo
- **PHPUnit 11** + **Pest 3** — tests unitarios, integración y feature

### ⚡ Frontend (modo `fullstack`)
Dos opciones de bundler, elige la que prefieras:

| | **Gulp** | **Vite** |
|---|---|---|
| Ideal para | JS vanilla, proyectos sin módulos | Proyectos con imports/exports, HMR |
| SCSS → CSS | ✅ | ✅ |
| Imágenes WebP + optimización | ✅ | ✅ (plugin) |
| BrowserSync / HMR | BrowserSync | HMR nativo |
| Configuración | `gulpfile.js` | `vite.config.js` |

Linting y tests front:
- **ESLint** + **Stylelint**
- **Vitest** — tests unitarios JS
- **Playwright** — tests E2E

### 🤖 Preparado para agentes IA
Ficheros `.agent/` leídos automáticamente por Claude Code, Cursor y similares:
- `AGENTS.md` — permisos, restricciones, comandos disponibles
- `CONVENTIONS.md` — PSR-12, BEM, Conventional Commits
- `PROJECT.md` — contexto de negocio (rellenar por proyecto)
- `TASKS.md` — backlog de tareas

### 🔄 CI/CD
GitHub Actions con dos jobs paralelos: PHP QA (PHPStan + PHPCS + PHPUnit + Pest) y Front QA (ESLint + Stylelint + Vitest + Playwright).

---

## Requisitos

| Herramienta | Versión mínima | Notas |
|---|---|---|
| PHP | 8.2 | `php --version` |
| Composer | 2.x | `composer --version` |
| Node.js | 20 LTS | Solo modo `fullstack` |
| npm | 10+ | Solo modo `fullstack` |
| Git | cualquiera | |

### Windows
- Instalar PHP desde [windows.php.net](https://windows.php.net/download/) o via **Laragon** / **XAMPP**
- Instalar Node.js desde [nodejs.org](https://nodejs.org)
- Añadir PHP y Composer al PATH
- El script `dev.sh` requiere WSL o Git Bash; alternativamente usar `npm run dev` directamente

### WSL (Windows Subsystem for Linux)
```bash
# PHP 8.2
sudo apt update && sudo apt install php8.2 php8.2-mbstring php8.2-xml php8.2-curl

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js via nvm
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
composer # desde getcomposer.org
nvm install 20
```

---

## Crear un nuevo proyecto

### Opción A — Clonar y usar el script `init-project.sh`

```bash
git clone https://github.com/miguelex/php-template.git
cd php-template
./init-project.sh
```

El script es interactivo y pregunta:
1. **Nombre del proyecto** (se usará como nombre de directorio y en composer/package.json)
2. **Modo**: `backend-only` o `fullstack`

También acepta argumentos directos:
```bash
./init-project.sh mi-api      backend   # API PHP sin front
./init-project.sh mi-web      full      # Web completa con assets
```

El script genera `./mi-proyecto/` con:
- Git inicializado (commit inicial limpio, sin historia de la plantilla)
- Ficheros personalizados con el nombre del proyecto
- Modo `backend` elimina automáticamente gulpfile, vite.config, tests/front, etc.

### Opción B — Fork en GitHub

1. Hacer fork del repositorio
2. Clonar tu fork: `git clone https://github.com/TU_USER/php-template.git mi-proyecto`
3. Eliminar la historia: `rm -rf .git && git init && git add . && git commit -m "chore: init"`
4. Ajustar manualmente `composer.json` y `package.json`

---

## Instalación (tras crear el proyecto)

```bash
cd mi-proyecto

# Dependencias PHP (copia .env.example a .env automáticamente)
composer install

# Dependencias Node (solo fullstack)
npm install

# Instalar navegadores para Playwright (solo fullstack)
npx playwright install chromium
```

Editar `.env` con los valores reales del proyecto:
```env
APP_NAME="Mi Proyecto"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=mi_base_de_datos
DB_USERNAME=root
DB_PASSWORD=
```

---

## Desarrollo

### Arrancar el entorno

**Opción 1 — `concurrently` (recomendado en Windows/WSL sin tmux)**

```bash
# Gulp + PHP server en una sola terminal
npm run dev

# Vite + PHP server en una sola terminal
npm run dev:vite

# Solo servidor PHP (backend-only)
npm run php
```

Cada proceso aparece con prefijo de color: `[PHP]` en azul, `[GULP]`/`[VITE]` en verde/amarillo.

**Opción 2 — `dev.sh` con tmux (Linux/macOS/WSL)**

```bash
./dev.sh                  # Gulp + PHP (paneles separados en tmux)
./dev.sh --bundler=vite   # Vite + PHP
./dev.sh --php-only       # Solo PHP (backend)
./dev.sh --tests          # Gulp + PHP + Vitest watch
```

Atajos tmux: `Ctrl+B → flechas` para moverse entre paneles · `Ctrl+B → D` para desconectar · `tmux attach -t dev` para reconectar.

**Opción 3 — Terminales manuales**

```bash
# Terminal 1
php -S localhost:8000 -t public

# Terminal 2 (fullstack Gulp)
npx gulp watch

# Terminal 2 (fullstack Vite)
npx vite
```

### Compilar assets

```bash
# Desarrollo (sourcemaps, sin minificar)
npm run build          # Gulp
npm run build:vite     # Vite

# Producción (minificado, optimizado)
npm run build:prod          # Gulp
npm run build:vite:prod     # Vite
```

---

## Comandos de calidad

### PHP
```bash
composer qa           # Todo: lint + stan + tests (pasar antes de cada commit)
composer lint         # PHPCS — detecta problemas de estilo
composer lint:fix     # PHP-CS-Fixer — autocorrige el estilo
composer stan         # PHPStan — análisis estático nivel 6
composer test         # PHPUnit
composer test:pest    # Pest
composer test:cover   # PHPUnit + coverage HTML en storage/coverage/
```

### Front (fullstack)
```bash
npm run test          # Vitest unit
npm run test:watch    # Vitest en modo watch
npm run test:e2e      # Playwright E2E (requiere servidor PHP activo)
npm run test:all      # Vitest + Playwright
npm run lint:js       # ESLint
npm run lint:css      # Stylelint
npm run lint:fix      # ESLint --fix
npm run format        # Prettier
```

---

## Estructura del proyecto

```
php-template/
├── .agent/                        ← Contexto para agentes IA
│   ├── AGENTS.md                  ← Instrucciones, permisos, comandos
│   ├── CONVENTIONS.md             ← Convenciones de código
│   ├── PROJECT.md                 ← Contexto de negocio (rellenar)
│   └── TASKS.md                   ← Backlog de tareas
│
├── .github/workflows/ci.yml       ← GitHub Actions (PHP QA + Front QA)
│
├── src/                           ← Código fuente PHP
│   ├── Core/
│   │   ├── App.php                ← Bootstrap (env, config, BD)
│   │   ├── Router.php             ← Router MVC
│   │   ├── ActiveRecord.php       ← ORM base con PDO
│   │   └── Database.php           ← Conexión PDO (singleton)
│   ├── Controllers/               ← HTTP handlers
│   ├── Models/                    ← Entidades (extienden ActiveRecord)
│   │   └── User.php               ← Modelo de ejemplo
│   ├── Services/
│   │   └── EmailService.php       ← SMTP con PHPMailer
│   ├── Helpers/
│   │   └── Html.php               ← Escape, CSRF, alertas, dump
│   └── Exceptions/
│       ├── RouteNotFoundException.php
│       └── DatabaseException.php
│
├── views/                         ← Vistas PHP
│   ├── layouts/default.php        ← Layout base
│   ├── templates/
│   │   ├── header.php
│   │   └── footer.php
│   ├── errors/
│   │   ├── 404.php
│   │   └── 500.php
│   └── home.php                   ← Vista de ejemplo
│
├── config/
│   └── routes.php                 ← Definición de rutas
│
├── public/                        ← Document root del servidor web
│   ├── index.php                  ← Entry point
│   └── assets/                    ← Generado por Gulp/Vite (no editar)
│
├── resources/                     ← Fuentes front (solo fullstack)
│   ├── scss/
│   │   ├── app.scss
│   │   ├── _variables.scss
│   │   ├── _mixins.scss
│   │   ├── _base.scss
│   │   └── components/
│   └── js/
│       ├── app.js
│       └── modules/
│
├── tests/
│   ├── TestCase.php               ← Base para PHPUnit/Pest
│   ├── Unit/                      ← Tests unitarios PHPUnit
│   ├── Integration/               ← Tests de integración
│   ├── Feature/                   ← Tests Pest
│   └── front/                     ← Solo fullstack
│       ├── unit/                  ← Vitest
│       └── e2e/                   ← Playwright
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── storage/
│   ├── logs/
│   └── cache/
│
├── composer.json
├── phpstan.neon                   ← Nivel 6
├── phpcs.xml                      ← PSR-12
├── .php-cs-fixer.php
├── phpunit.xml
├── pest.php
├── gulpfile.js                    ← Bundler opción A
├── vite.config.js                 ← Bundler opción B
├── package.json
├── vitest.config.js
├── playwright.config.js
├── eslint.config.js
├── .stylelintrc.json
├── .editorconfig
├── .gitignore
├── .env.example
├── dev.sh                         ← Lanzador tmux
└── init-project.sh                ← Script de inicialización
```

---

## Decisiones de diseño

**¿Por qué sin framework como Laravel/Symfony?**
Para proyectos donde el overhead de un framework completo no está justificado: módulos de negocio específicos, integraciones con sistemas legacy, o proyectos donde el control fino sobre cada capa es importante.

**¿Gulp o Vite?**
Ambos están disponibles. Gulp es ideal si el JS es simple (unas funciones, jQuery): pipeline directo, sin módulos, sin bundling. Vite es mejor si empiezas a usar `import/export`, necesitas HMR instantáneo, o prevés que el front crezca.

**¿Por qué PHPStan nivel 6 y no 9?**
El nivel 9 en un proyecto real con código legacy o integraciones externas genera decenas de falsos positivos. El 6 ofrece el 90% del valor sin el ruido. Se puede subir gradualmente.

**ActiveRecord vs Repository**
Para proyectos pequeños/medianos ActiveRecord es más directo. Si el dominio crece, el patrón Repository es el paso siguiente: extraer la lógica de acceso a datos de los modelos a clases `UserRepository`, etc.

---

## Contribuir

¡Las contribuciones son bienvenidas! Esto incluye mejoras al core MVC, nuevas features de tooling, correcciones, documentación o ejemplos adicionales.

### Antes de abrir un PR

1. Haz **fork** del repositorio y clona tu fork
2. Crea una rama descriptiva:
   ```bash
   git checkout -b feature/nombre-corto
   # o
   git checkout -b fix/descripcion-del-bug
   ```
3. Asegúrate de que **`composer qa` pasa en verde** antes de hacer push
4. Si añades código PHP, añade también sus tests
5. Sigue las convenciones de [Conventional Commits](https://www.conventionalcommits.org/):
   ```
   feat: añadir soporte para rutas con parámetros dinámicos
   fix: corregir resolución de URI con query string
   chore: actualizar dependencias Composer
   docs: mejorar sección de contribución en README
   test: añadir tests para ActiveRecord::whereMany
   ```

### Tipos de contribución bienvenidas

- 🐛 **Bug fixes** — con test que reproduzca el fallo
- ✨ **Features** — discutir primero en un issue si es algo grande
- 📖 **Documentación** — mejoras al README, comentarios en código
- 🧪 **Tests** — más cobertura siempre es bienvenida
- 🌐 **Compatibilidad** — mejoras para Windows, macOS, distintas versiones de PHP

### Tipos de contribución que no encajan

- Añadir dependencias de frameworks completos (Laravel, Symfony, etc.)
- Cambios de estilo sin funcionalidad asociada
- Romper compatibilidad con PHP 8.2

### Flujo de un PR

```
fork → rama → código + tests → composer qa verde → push → PR
```

En el PR describe:
- **Qué** hace el cambio
- **Por qué** es necesario
- **Cómo** probarlo

---

## Licencia

MIT — ver [LICENSE](LICENSE).

---

## Agradecimientos

Inspirado en el mini-framework de [codigoconjuan](https://codigoconjuan.com), modernizado con PHP 8.2, PDO, typed properties y tooling actual.

---

## CLI — bin/console

La plantilla incluye una herramienta de línea de comandos para las tareas más comunes:

```bash
# Migraciones
php bin/console migrate               # ejecutar pendientes
php bin/console migrate:status        # ver estado
php bin/console migrate:rollback      # deshacer última
php bin/console migrate:rollback 3    # deshacer últimas 3
php bin/console migrate:fresh         # rollback todo + migrate

# Generadores de código
php bin/console make:migration create_posts_table
php bin/console make:controller Post
php bin/console make:model Post
php bin/console make:repository Post

# Otros
php bin/console cache:clear
php bin/console help
```

O con Make:
```bash
make migrate
make migration NAME=create_posts_table
make controller NAME=Post
make model NAME=Post
```

---

## Patrones disponibles: ActiveRecord vs Repository

### ActiveRecord — para proyectos simples/medianos

```php
// src/Models/Post.php
final class Post extends ActiveRecord {
    protected static string $table = 'posts';
    protected static array $columns = ['id', 'title', 'body'];
    public string $title = '';
    public string $body  = '';
}

// Uso directo
$post = Post::find(1);
$posts = Post::all();
$post->title = 'Nuevo título';
$post->save();
```

### Repository — para proyectos con dominio complejo

```php
// src/Repositories/PostRepository.php
final class PostRepository extends BaseRepository {
    protected string $table = 'posts';

    public function findPublished(): array {
        return $this->findWhere(['published' => 1], 'created_at DESC');
    }

    protected function hydrate(array $row): Post { ... }
    protected function extract(object $entity): array { ... }
}

// Uso con inyección
$repo = new PostRepository(Database::connect());
$posts = $repo->paginate(page: 1, perPage: 10);
```

---

## Makefile

```bash
make help           # ver todos los comandos disponibles
make install        # composer install + npm install
make dev            # PHP + Gulp (concurrently)
make dev-vite       # PHP + Vite (concurrently)
make qa             # lint + stan + tests
make test-all       # PHP + JS + E2E
make migrate        # ejecutar migraciones
make cache-clear    # vaciar storage/cache/
```

En **Windows** sin WSL, instalar `make` con:
```powershell
winget install GnuWin32.Make
# o
choco install make
```

---

## Sistema de agentes IA — estructura completa

```
.agent/
├── AGENTS.md        ← Punto de entrada: qué es el proyecto, permisos, comandos
├── WORKFLOW.md      ← Cómo actuar: planificar, verificar, aprender, elegancia
├── CONVENTIONS.md   ← Cómo escribir: PHP, JS, SCSS, Git, nomenclatura
├── PROJECT.md       ← Contexto de negocio (rellenar por proyecto)
└── TASKS.md         ← Backlog estratégico del proyecto

tasks/
├── todo.md          ← Tarea activa: plan + checkboxes + review section
└── lessons.md       ← Errores pasados + reglas para no repetirlos
```

Los agentes que leen `AGENTS.md` de forma nativa (Claude Code, Cursor en modo agente)
cargan automáticamente todo el contexto. El fichero `WORKFLOW.md` garantiza que el agente
planifica antes de actuar, hace cambios quirúrgicos, y aprende de los errores del proyecto.
