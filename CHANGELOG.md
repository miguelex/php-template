# Changelog

Todas las versiones relevantes de este proyecto se documentan aquí.
El formato sigue [Keep a Changelog](https://keepachangelog.com/) y el versionado
es [SemVer](https://semver.org/). A partir de v2.1 este fichero lo mantiene
automáticamente [release-please](https://github.com/googleapis/release-please).

## [2.1.2](https://github.com/miguelex/php-template/compare/v2.1.1...v2.1.2) (2026-10-25)


### Bug Fixes

* **deps:** bump major dependencies (vite 8, eslint 10, phpmailer 7, phpcs 4…) ([#32](https://github.com/miguelex/php-template/issues/32)) ([d3a4398](https://github.com/miguelex/php-template/commit/d3a4398b7d0305f3f2f01596fb8111f270a418bf))

## [2.1.1](https://github.com/miguelex/php-template/compare/v2.1.0...v2.1.1) (2026-10-25)


### Bug Fixes

* **deps:** bump vitest to 5 and stylelint to 17 ([#30](https://github.com/miguelex/php-template/issues/30)) ([c72abfb](https://github.com/miguelex/php-template/commit/c72abfb509e7130d58a551a9c176f6db2a1e78b0))

## [2.1.0](https://github.com/miguelex/php-template/compare/v2.0.0...v2.1.0) (2026-10-25)


### Features

* v2.1 quick-wins — multi-DB, logging, tooling and DX ([cebcb03](https://github.com/miguelex/php-template/commit/cebcb03e98e6870dd967b05e442e1035ee288700))
* v2.1 quick-wins — multi-DB, logging, tooling and DX ([66b0349](https://github.com/miguelex/php-template/commit/66b0349051226ffd54a5dcdb02e3c948bc5db360))

## [2.0.0] - 2026-10-24

### Added
- Router: parámetros de ruta (`/users/{id}`) y middleware por ruta encadenable.
- Verificación CSRF también vía cabecera `X-CSRF-Token` (APIs/fetch).
- Rector con set `php85` + sets de calidad (`composer rector` / `rector:fix`).
- `.gitattributes` para forzar finales de línea LF.
- Tests para el router y la validación de identificadores SQL.

### Changed
- Compatibilidad PHP 8.2 → 8.5; matriz de CI 8.2/8.3/8.4/8.5.
- PHPStan 1 → 2 (nivel 6).
- `composer.json` con nombre real `miguelex/php-template` (Packagist).

### Fixed
- `User`: `token`/`created_at` nullable para coincidir con el esquema (hidratación de `NULL`).
- `EmailService`: escape del nombre y `urlencode` del token (HTML injection).
- `Database`: guard de `APP_DEBUG` indefinido.

### Security
- Validación de identificadores y `ORDER BY` en `ActiveRecord` y `BaseRepository`.
- `php_codesniffer` 3.13.6 (CVE-2026-67434).

[2.0.0]: https://github.com/miguelex/php-template/releases/tag/v2.0.0
