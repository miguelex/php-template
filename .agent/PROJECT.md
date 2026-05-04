# PROJECT.md

> Contexto de negocio y técnico del proyecto para agentes IA y nuevos colaboradores.
> **Mantener actualizado** conforme evoluciona el proyecto.

---

## Descripción

**[RELLENAR: Descripción breve del proyecto — qué hace, para quién, por qué existe]**

## Dominio

**[RELLENAR: Entidades principales del negocio, glosario de términos específicos]**

Ejemplo:
- **Cliente**: empresa que contrata el servicio
- **Expediente**: unidad de trabajo asociada a un cliente
- **Factura**: documento económico generado por un expediente

## Decisiones técnicas relevantes

| Decisión                        | Justificación                              |
|---------------------------------|--------------------------------------------|
| PHP sin framework               | [RELLENAR]                                 |
| PHPStan nivel 6                 | Equilibrio entre rigor y velocidad         |
| Gulp en lugar de Vite/Webpack   | Sin bundling de módulos JS complejos       |

## Integraciones externas

| Servicio          | Propósito            | Estado     |
|-------------------|----------------------|------------|
| [RELLENAR]        | [RELLENAR]           | Planificado|

## Entorno

```
PHP:        8.2
Node.js:    20 LTS
Servidor:   Apache / Nginx / php -S (dev)
Base datos: [MySQL / PostgreSQL / SQLite — RELLENAR]
```

## Variables de entorno importantes

Ver `.env.example` para la lista completa.

```env
APP_ENV=local|testing|production
APP_DEBUG=true|false
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
```

## Contacto / Responsable

- **Proyecto**: [RELLENAR]
- **Repositorio**: [RELLENAR]
