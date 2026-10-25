# TASKS.md — Backlog del proyecto

> **Este fichero es el backlog estratégico** — tareas planificadas, pendientes y completadas
> a nivel de producto/proyecto.
>
> Para la **tarea activa** que está ejecutando el agente ahora mismo,
> ver `tasks/todo.md`.
>
> Para **errores y lecciones aprendidas**, ver `tasks/lessons.md`.

---

## 🔴 Alta prioridad

- [ ] [CORE] Contenedor DI (PSR-11) — p. ej. PHP-DI o league/container
      Motivo: hoy los controllers se instancian con `new $class()` sin inyección → difícil de testear y de cablear servicios (EmailService, repos, PDO).
      Ficheros afectados: src/Core/Router.php (resolución del handler), src/Core/App.php (bootstrap del contenedor), public/index.php
      Criterio de aceptación: el Router resuelve controllers y dependencias vía contenedor; tests del contenedor; composer qa verde. Objetivo de versión: v2.2.

## 🟡 Media prioridad

- [ ] [CORE] HTTP PSR-7 + pipeline de middleware PSR-15 — nyholm/psr7 + relay/pipeline
      Motivo: el Router lee `$_SERVER`/`$_POST`/`$_GET` de globales; Request/Response inmutables y un pipeline PSR-15 desacoplan y hacen el core testeable de verdad.
      Ojo: cambia la firma de los handlers (Request → Response). Es el cambio más invasivo; posiblemente breaking → candidato a v3.0.
      Criterio de aceptación: handlers reciben ServerRequestInterface y devuelven ResponseInterface; middlewares PSR-15; tests; composer qa verde.

## 🟢 Backlog

- [ ] [FRONT] Modo TypeScript opcional (tsconfig + ESLint typed) sobre Vite.
- [ ] [CORE] Capa de validación reutilizable (hoy la validación vive ad-hoc en los modelos).
- [ ] [CI] Servicio PostgreSQL en CI para probar pgsql de verdad (hoy solo MySQL + SQLite).

## ✅ Completadas

- [x] v2.0 — Auditoría, PHP 8.2–8.5, router params/middleware, blindaje SQL, Rector, Packagist — 2026-10-24
- [x] v2.1 — Dependabot, release-please + CHANGELOG, Monolog (PSR-3), Whoops, tests SQLite en memoria, arch tests, multi-BD (MySQL/SQLite/PostgreSQL), Vite por defecto — 2026-10-25

---

## Formato de tarea para agentes

```
- [ ] [ÁREA] Descripción clara de qué hay que hacer
      Ficheros afectados: src/Services/X.php, tests/Unit/XTest.php
      Criterio de aceptación: tests pasan, PHPStan sin errores, composer qa verde
```
