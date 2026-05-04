# WORKFLOW.md

> Guía de comportamiento para agentes IA trabajando en este proyecto.
> Combina principios de orquestación, toma de decisiones y estándares de calidad.
> Leer junto a `AGENTS.md` y `CONVENTIONS.md`.

---

## 1. Planificar antes de actuar

Para cualquier tarea no trivial (3+ pasos, decisiones arquitectónicas, cambios en Core):

1. Escribir el plan en `tasks/todo.md` con items verificables
2. Revisar el plan antes de empezar a implementar
3. Si algo se tuerce a mitad: **parar y replantear**. No seguir empujando hacia adelante
4. Usar criterios de éxito concretos, no vagos:

```
# MAL
- Arreglar el bug de autenticación

# BIEN
- Escribir test que reproduzca el fallo en AuthMiddleware::check()
- Hacer pasar el test
- Verificar que composer qa pasa en verde
- Comprobar que no hay regresiones en tests existentes
```

## 2. Pensar antes de codificar

**No asumir. No ocultar confusión. Exponer los tradeoffs.**

Antes de implementar cualquier cosa:

- Hacer explícitas las asunciones. Si hay incertidumbre → preguntar
- Si existen múltiples interpretaciones → presentarlas, no elegir en silencio
- Si existe una solución más simple → decirlo. Cuestionar cuando sea necesario
- Si algo no está claro → parar, nombrar qué es confuso, preguntar

**Nunca inventar requisitos.** Si el usuario pide X, implementar X. No X + Y + Z "por si acaso".

## 3. Simplicidad primero

El código mínimo que resuelve el problema. Nada especulativo.

- ❌ No añadir features que no se han pedido
- ❌ No crear abstracciones para código de un solo uso
- ❌ No añadir "flexibilidad" o "configurabilidad" que nadie pidió
- ❌ No manejar escenarios imposibles
- ❌ No escribir 200 líneas si bastan 50

**Test mental**: ¿Diría un senior engineer que esto está sobrecomplicado? Si la respuesta es sí → simplificar.

**Límite de fichero**: mantener ficheros por debajo de ~500 líneas. Si crece → split/refactor.

## 4. Cambios quirúrgicos

**Tocar solo lo necesario. Limpiar solo el propio desorden.**

Al editar código existente:
- ❌ No "mejorar" código adyacente, comentarios ni formato no relacionado
- ❌ No refactorizar lo que funciona
- ✅ Respetar el estilo existente aunque sea diferente al propio
- ✅ Si se detecta código muerto no relacionado → mencionarlo, no borrarlo

Al hacer cambios que crean huérfanos:
- ✅ Eliminar imports/variables/funciones que TUS cambios dejaron sin uso
- ❌ No eliminar código muerto preexistente salvo que se pida explícitamente

**Test**: cada línea modificada debe trazarse directamente a la petición del usuario.

## 5. Verificación antes de declarar hecho

Nunca marcar una tarea como completa sin demostrar que funciona:

```bash
composer qa          # lint + stan + tests — debe pasar en verde
npm run test         # si se tocó front
npm run test:e2e     # si se tocaron flujos de usuario
```

- Comparar comportamiento antes/después cuando sea relevante
- Revisar logs en busca de warnings nuevos
- Preguntarse: **"¿Aprobaría esto un senior engineer?"**

## 6. Dependencias nuevas

Antes de añadir cualquier dependencia (Composer o npm):

1. ¿Hay releases recientes? (últimos 6 meses)
2. ¿Tiene adopción razonable? (stars, descargas, issues activos)
3. ¿Está mantenido? (último commit, respuesta a issues)
4. ¿Es realmente necesario o se puede resolver sin ella?

Si pasa el check → añadir. Si no → buscar alternativa o resolver sin dependencia.

## 7. Elegancia (equilibrada)

Para cambios no triviales: pausar y preguntar **"¿hay una forma más elegante?"**

Si una solución se siente hacky:
> "Conociendo todo lo que sé ahora, ¿cuál sería la solución elegante?"

Implementar esa. Pero: **no aplicar esto a fixes simples y obvios**. No sobre-ingenierizar.

## 8. Bugs — resolución autónoma

Cuando se reporta un bug:

1. Reproducirlo (añadir test que falle si es posible)
2. Identificar la causa raíz — no parchear el síntoma
3. Resolverlo
4. Verificar que los tests existentes siguen pasando
5. No pedir ayuda para cada paso — resolver de forma autónoma

Sin cambio de contexto requerido del usuario para bugs estándar.

## 9. CI — mantener verde

Si el CI falla:

```bash
# Ver qué falló
gh run list
gh run view <id>

# Corregir → push → repetir hasta verde
```

No se entrega trabajo con CI en rojo.

## 10. Bucle de auto-mejora

Después de cualquier corrección del usuario:

1. Actualizar `tasks/lessons.md` con el patrón de error y su corrección
2. Escribir una regla que prevenga el mismo error en el futuro
3. Revisar `tasks/lessons.md` al inicio de cada sesión relevante

Esto convierte los errores en conocimiento acumulado del proyecto.

---

## Gestión de tareas

### tasks/todo.md — estructura esperada

```markdown
# TODO — [nombre de la tarea]

## Plan
- [ ] Paso 1 → verificar: [criterio concreto]
- [ ] Paso 2 → verificar: [criterio concreto]
- [ ] Paso 3 → verificar: [criterio concreto]

## Review
- Qué se hizo
- Qué decisiones se tomaron y por qué
- Qué quedó pendiente
```

### tasks/lessons.md — estructura esperada

```markdown
# Lessons Learned

## [fecha] — [descripción corta del error]
**Error cometido**: descripción
**Causa raíz**: por qué ocurrió
**Corrección**: qué se hizo
**Regla futura**: cómo evitarlo → [regla concreta y aplicable]
```

---

## Resumen: checklist antes de entregar

- [ ] `composer qa` pasa en verde
- [ ] No hay cambios en ficheros no relacionados con la tarea
- [ ] Cada línea modificada se justifica por la petición
- [ ] Si se añadieron dependencias → health check hecho
- [ ] Si hubo correcciones → `tasks/lessons.md` actualizado
- [ ] `tasks/todo.md` actualizado con review section
