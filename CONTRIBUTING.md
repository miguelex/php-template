# Guía de contribución

Gracias por tu interés en contribuir a **php-template**. Esta guía explica el proceso para que tu contribución llegue a main de la forma más fluida posible.

## Código de conducta

Sé respetuoso y constructivo. Las revisiones de código son sobre el código, no sobre las personas.

## Cómo reportar un bug

1. Comprueba primero que no existe ya un [issue abierto](../../issues) sobre el mismo problema
2. Abre un nuevo issue con:
   - Descripción clara del problema
   - Pasos para reproducirlo
   - Comportamiento esperado vs. comportamiento actual
   - Entorno: SO, versión de PHP, versión de Node

## Cómo proponer una mejora

1. Abre un issue describiendo la mejora antes de implementarla (para proyectos grandes)
2. Para cambios pequeños (typos, mejoras de docs, fixes evidentes) puedes ir directamente al PR

## Proceso de PR

```bash
# 1. Fork y clonar
git clone https://github.com/TU_USER/php-template.git
cd php-template

# 2. Crear rama
git checkout -b feature/mi-mejora

# 3. Instalar dependencias
composer install
npm install  # solo si tocas front

# 4. Desarrollar
# ... hacer cambios ...

# 5. Verificar que todo pasa
composer qa         # lint + stan + tests PHP
npm run test        # Vitest (si tocas front)

# 6. Commit con Conventional Commits
git commit -m "feat: descripción de la mejora"

# 7. Push y abrir PR
git push origin feature/mi-mejora
```

## Convenciones de commits

Usamos [Conventional Commits](https://www.conventionalcommits.org/):

| Prefijo | Cuándo usarlo |
|---------|---------------|
| `feat:` | Nueva funcionalidad |
| `fix:` | Corrección de bug |
| `chore:` | Mantenimiento, dependencias |
| `docs:` | Solo documentación |
| `test:` | Añadir o corregir tests |
| `refactor:` | Refactoring sin cambio de comportamiento |
| `style:` | Formato, espacios (sin cambio de lógica) |

## Checklist antes de abrir un PR

- [ ] `composer qa` pasa sin errores
- [ ] Si añado código PHP, añado tests
- [ ] Si añado una feature, actualizo el README si es relevante
- [ ] Los commits siguen Conventional Commits
- [ ] La rama está actualizada con `main`

## Revisión

Los PRs se revisan en un plazo razonable. Se pueden pedir cambios antes del merge. No te lo tomes como algo personal — es parte del proceso para mantener la calidad de la plantilla.
