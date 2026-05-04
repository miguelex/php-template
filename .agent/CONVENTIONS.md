# CONVENTIONS.md

Convenciones de código para este proyecto. **Todos los colaboradores y agentes deben seguirlas.**

---

## PHP

### General
- PHP mínimo: **8.2**
- `declare(strict_types=1);` **obligatorio** en todos los ficheros PHP
- Estándar de estilo: **PSR-12**
- Namespace raíz: `App\`
- Autoload: PSR-4 vía Composer

### Nomenclatura
| Elemento           | Convención         | Ejemplo                     |
|--------------------|--------------------|-----------------------------|
| Clases             | PascalCase         | `UserService`               |
| Interfaces         | PascalCase + I*    | `UserRepositoryInterface`   |
| Traits             | PascalCase         | `HasTimestamps`             |
| Métodos            | camelCase          | `getUserById()`             |
| Variables          | camelCase          | `$userId`                   |
| Constantes         | UPPER_SNAKE_CASE   | `MAX_RETRIES`               |
| Propiedades        | camelCase          | `$firstName`                |

### Estructura de clases
```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Exceptions\UserNotFoundException;

final class UserService
{
    public function __construct(
        private readonly UserRepository $repository,
    ) {}

    public function findById(int $id): User
    {
        $user = $this->repository->find($id);

        if ($user === null) {
            throw new UserNotFoundException("User {$id} not found");
        }

        return $user;
    }
}
```

### Reglas adicionales
- Preferir `final class` salvo que se necesite herencia explícita
- Usar **constructor promotion** (PHP 8.x)
- Usar **readonly** en propiedades que no cambien
- Usar **named arguments** cuando mejoren la legibilidad
- Tipar **siempre**: parámetros, retornos, propiedades
- No usar `mixed` salvo causa justificada
- No usar `@suppress` en PHPStan sin comentario explicativo

### Tests
- Un fichero de test por clase: `UserService` → `UserServiceTest.php`
- Métodos de test: snake_case descriptivo → `test_throws_when_user_not_found()`
- Pest: `it('throws when user not found', ...)`
- No usar `@covers` (se configura en phpunit.xml)
- Usar `Faker` para datos de prueba, no hardcodear strings arbitrarios

---

## JavaScript

- ES Modules (`import`/`export`)
- **No `var`** — solo `const` y `let`
- Single quotes `'cadena'`
- Punto y coma obligatorio
- Arrow functions para callbacks
- Nombres de funciones: camelCase
- Nombres de clases JS: PascalCase
- Constantes de módulo: UPPER_SNAKE_CASE

```js
// ✅ Correcto
const fetchUser = async (id) => {
    const response = await fetch(`/api/users/${id}`);
    return response.json();
};

// ❌ Incorrecto
var getUser = function(id) {
    return fetch("/api/users/" + id).then(r => r.json())
}
```

---

## SCSS

- Metodología **BEM**: `.bloque__elemento--modificador`
- Máximo **3 niveles** de anidado
- Variables en `_variables.scss`
- Mixins en `_mixins.scss`
- Componentes: un fichero por componente en `resources/scss/components/`
- No usar `!important` salvo utilities

```scss
// ✅ Correcto
.card {
    padding: 1rem;

    &__title {
        font-size: 1.25rem;
    }

    &--featured {
        border: 2px solid $color-primary;
    }
}
```

---

## Git

### Ramas
- `main` → producción (protegida)
- `develop` → integración
- `feature/nombre-corto` → nuevas funcionalidades
- `fix/nombre-corto` → correcciones
- `chore/nombre-corto` → mantenimiento

### Commits (Conventional Commits)
```
feat: añadir autenticación OAuth2
fix: corregir error en cálculo de IVA
chore: actualizar dependencias Composer
test: añadir tests para UserService
docs: actualizar README con instrucciones de instalación
refactor: extraer lógica de validación a Validator
```

### Reglas
- No commitear `.env` (solo `.env.example`)
- No commitear `vendor/` ni `node_modules/`
- No commitear `public/assets/` (generado por Gulp)
- `composer qa` debe pasar antes de cualquier commit

---

## Estructura de directorios

```
src/
├── Controllers/     HTTP handlers — reciben request, devuelven response
├── Models/          Entidades del dominio y acceso a datos
├── Services/        Lógica de negocio — orquesta Models
├── Helpers/         Funciones puras sin estado ni dependencias
└── Exceptions/      Excepciones tipadas del dominio
```

**Regla de dependencias**: `Controllers → Services → Models`. Nunca al revés.
