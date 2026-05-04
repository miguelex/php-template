# Lessons Learned

> Registro acumulativo de errores, correcciones y reglas derivadas.
> El agente actualiza este fichero después de cualquier corrección del usuario.
> **Revisar al inicio de cada sesión** para no repetir errores pasados.

---

## Formato de entrada

```
## [YYYY-MM-DD] — Descripción corta del error

**Error cometido**: qué se hizo mal
**Causa raíz**: por qué ocurrió (no el síntoma, la causa)
**Corrección aplicada**: qué se cambió
**Regla futura**: [regla concreta, aplicable, en imperativo]
```

---

## Registro

<!-- El agente añade entradas aquí después de cada corrección -->

<!-- Ejemplo:

## [2025-01-20] — Modificación de código no relacionado

**Error cometido**: Al arreglar un bug en UserService, se refactorizó también
el formato de EmailService "ya que estaba ahí".

**Causa raíz**: No se aplicó el principio de cambios quirúrgicos. Se confundió
"mejorar" con "arreglar lo pedido".

**Corrección aplicada**: Revertir cambios en EmailService, mantener solo el fix
en UserService.

**Regla futura**: Antes de cada commit, revisar el diff completo. Cada línea
modificada debe trazarse a la petición original. Si no se puede → revertir.
-->
