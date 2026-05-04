# AGENTS.md

> Punto de entrada para agentes IA (Claude Code, Cursor, GitHub Copilot, etc.)
> Leer también: `WORKFLOW.md` (cómo actuar), `CONVENTIONS.md` (cómo escribir código),
> `PROJECT.md` (contexto de negocio), `TASKS.md` (backlog), `tasks/todo.md` (tarea activa).

---

## Identidad del proyecto

- **Nombre**: [RELLENAR]
- **Tipo**: PHP 8.2 / [backend-only | fullstack]
- **Descripción**: [RELLENAR]
- **URL local**: http://localhost:8000
- **Rama principal**: `main`

---

## Stack técnico

| Capa           | Tecnología                                        |
|----------------|---------------------------------------------------|
| Backend        | PHP 8.2, Composer                                 |
| Core MVC       | Router, ActiveRecord, BaseRepository, Migration   |
| CLI            | bin/console (migrate, make:*, cache:clear)        |
| Testing PHP    | PHPUnit 11, Pest 3                                |
| QA PHP         | PHPStan level 6, PHP-CS-Fixer, PHPCS              |
| Front          | SCSS, JS vanilla (ES2024)                         |
| Build          | Gulp 5 o Vite 6 (elegir por proyecto)             |
| Testing JS     | Vitest 2, Playwright                              |
| CI             | GitHub Actions                                    |

---

## Estructura de capas

```
Controllers  →  reciben request, delegan en Services/Repositories
Services     →  lógica de negocio, orquestan Repositories/Models
Repositories →  acceso a datos desacoplado (proyectos complejos)
Models       →  entidades + ActiveRecord (proyectos simples/medianos)
Middleware   →  auth, CSRF, rate limiting
Helpers      →  Html, Paginator, JsonResponse (sin estado)
Core         →  Router, ActiveRecord, BaseRepository, Migration, App
```

**Regla de dependencias**: Controllers → Services → Repositories/Models. Nunca al revés.

### ActiveRecord vs Repository

| | ActiveRecord | Repository |
|---|---|---|
| Cuándo | CRUD directo, proyectos pequeños/medianos | Dominio complejo, alta testabilidad |
| Extiende | `App\Core\ActiveRecord` | `App\Core\BaseRepository` |
| Ejemplo | `src/Models/User.php` | `src/Repositories/UserRepository.php` |

Pueden coexistir en el mismo proyecto. No mezclar para la misma entidad.

---

## Behavioral Guidelines (resumen)

> Ver `WORKFLOW.md` para el detalle completo.

**Antes de implementar**
- Escribir plan en `tasks/todo.md` para tareas de 3+ pasos
- Hacer explícitas las asunciones — si hay duda, preguntar
- Si hay varias interpretaciones, presentarlas. No elegir en silencio

**Durante la implementación**
- Código mínimo que resuelve el problema. Sin features no pedidas
- Tocar solo lo relacionado con la tarea. No "mejorar" código adyacente
- Límite orientativo: ~500 líneas por fichero
- Health check antes de añadir cualquier dependencia nueva

**Antes de declarar hecho**
- `composer qa` debe pasar en verde (sin excepciones)
- Revisar el diff: cada línea debe trazarse a la petición original
- Si hubo correcciones del usuario → actualizar `tasks/lessons.md`

---

## Lo que el agente PUEDE hacer

- Crear/modificar ficheros en `src/`, `tests/`, `resources/`, `config/`, `database/`, `tasks/`
- Generar código con `php bin/console make:*`
- Añadir dependencias en `composer.json` o `package.json` (con health check)
- Modificar configuraciones de QA (`phpstan.neon`, `phpcs.xml`, `.php-cs-fixer.php`)
- Actualizar `.github/workflows/ci.yml`
- Actualizar `tasks/todo.md` y `tasks/lessons.md`

## Lo que el agente NO DEBE hacer

- ❌ Modificar `public/assets/` directamente (generado por Gulp/Vite)
- ❌ Ejecutar `composer install` / `npm install` sin indicarlo explícitamente
- ❌ Bajar el nivel de PHPStan por debajo de 6
- ❌ Omitir `declare(strict_types=1)` en cualquier fichero PHP
- ❌ Usar `var_dump` / `print_r` fuera de debug temporal (usar `Html::dump()`)
- ❌ Commitear `.env` o `.env.testing`
- ❌ Escribir lógica de negocio en `public/index.php` o `config/routes.php`
- ❌ Interpolar variables directamente en SQL — siempre prepared statements
- ❌ Modificar código no relacionado con la tarea activa
- ❌ Añadir dependencias sin health check (releases recientes, adopción, mantenimiento)

---

## Comandos disponibles

```bash
# PHP — calidad (ejecutar antes de cualquier commit)
composer qa           # lint + stan + tests (todo)
composer lint         # PHPCS
composer lint:fix     # PHP-CS-Fixer
composer stan         # PHPStan
composer test         # PHPUnit
composer test:pest    # Pest
composer test:cover   # PHPUnit + coverage HTML

# CLI de la aplicación
php bin/console help
php bin/console migrate
php bin/console migrate:status
php bin/console migrate:rollback [n]
php bin/console migrate:fresh
php bin/console make:migration nombre
php bin/console make:controller Nombre
php bin/console make:model Nombre
php bin/console make:repository Nombre
php bin/console cache:clear

# Makefile (atajos para todo lo anterior)
make help             # ver todos los comandos
make qa
make migrate
make test-all
make dev

# Front (solo fullstack)
npm run dev           # Gulp + PHP (concurrently)
npm run dev:vite      # Vite + PHP (concurrently)
npm run build:prod    # Producción
npm run test          # Vitest
npm run test:e2e      # Playwright
```

---

## Flujo de trabajo esperado

```
1. Leer tasks/lessons.md  → evitar errores pasados
2. Escribir plan          → tasks/todo.md con criterios verificables
3. Implementar            → cambios quirúrgicos, sin desvíos
4. Verificar              → composer qa verde, diff limpio
5. Actualizar             → tasks/todo.md (review) + tasks/lessons.md (si hubo correcciones)
6. Commit                 → Conventional Commits, CI verde
```

---

## Contexto adicional

- `WORKFLOW.md` → cómo planificar, verificar y aprender de errores
- `CONVENTIONS.md` → PSR-12, BEM, Conventional Commits, nomenclatura
- `PROJECT.md` → descripción del negocio, entidades, decisiones técnicas
- `TASKS.md` → backlog del proyecto
- `tasks/todo.md` → tarea activa con plan y review
- `tasks/lessons.md` → errores pasados y reglas derivadas
