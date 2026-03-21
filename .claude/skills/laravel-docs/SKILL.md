---
name: laravel-docs
description: Mantiene actualizada la documentación del boilerplate: README.md, CHANGELOG.md, CONTEXT.md, documentación técnica por feature en docs/, y SKILL.md de cada skill. Úsala al terminar de implementar un módulo o feature, cuando el README esté desactualizado, al hacer un release, al crear un skill nuevo, o cuando digas "actualiza la documentación", "documenta esto", "update docs", "genera el changelog", "doc this feature", "documenta el módulo de X", "actualiza el contexto".
---

# Documentación del Boilerplate

Este skill mantiene sincronizada toda la documentación con el estado real del código.

## When to Apply

- Acabas de implementar un módulo, feature o sistema nuevo
- Se modificó la arquitectura, una API pública o un contrato de servicio
- Se creó o modificó un skill en `.claude/skills/`
- Se va a hacer un release (versión nueva en CHANGELOG)
- El usuario pide explícitamente actualizar o crear documentación
- El README describe features que ya no existen o no menciona las nuevas
- Se añade, modifica o elimina cualquier feature, modelo, ruta, convención o comando relevante (actualizar `CONTEXT.md`)

## Responsibilities

Cada tipo de cambio tiene su archivo asignado:

| Tipo de cambio | Archivo a actualizar |
|----------------|----------------------|
| Feature nueva o eliminada | `README.md` + `docs/features/{feature}.md` |
| Cambio arquitectónico | `docs/architecture/overview.md` o el archivo específico |
| Release / versión | `CHANGELOG.md` |
| Nuevo skill o skill modificado | `.claude/skills/{skill}/SKILL.md` |
| Convenciones de desarrollo | `docs/development/conventions.md` |
| Nuevo registry o contrato | `docs/architecture/registries.md` |
| Setup o comandos dev | `docs/development/getting-started.md` |
| Cualquier cambio significativo en stack, features, arquitectura, convenciones o comandos | `CONTEXT.md` |

**Regla:** Nunca documentes detalles de implementación interna que pueden derivarse leyendo el código. Documenta el *qué* y el *por qué*, no el *cómo* línea por línea.

---

## README Maintenance

El README vive en la raíz y es el entry point del proyecto. Mantén estas secciones sincronizadas:

### Secciones del README

1. **Stack** — Versiones exactas de packages principales (leer `composer.json`)
2. **Features** — Lista de capacidades del boilerplate (una línea por feature)
3. **Quick Start** — Comandos para levantar el proyecto desde cero
4. **Architecture** — Descripción de alto nivel de la estructura
5. **Development** — Comandos frecuentes (dev, test, lint, build)

### Cuándo actualizar

- **Añadir** al README: feature nueva o capacidad relevante para quien evalúa el boilerplate
- **Quitar** del README: feature eliminada o deprecada
- **Actualizar**: cambio en versiones de packages, comandos, o estructura de carpetas

### Lo que NO va en el README

- Documentación detallada de features (eso va en `docs/features/`)
- Ejemplos de código extensos
- Decisiones de implementación internas

---

## CHANGELOG Updates

Sigue el formato [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) con Semantic Versioning.

### Formato de entrada

```markdown
## [X.Y.Z] - YYYY-MM-DD

### Added
- Descripción concisa del feature añadido

### Changed
- Descripción del comportamiento modificado

### Fixed
- Descripción del bug corregido

### Removed
- Feature o funcionalidad eliminada
```

### Tipos de cambio

| Tipo | Cuándo usarlo |
|------|---------------|
| `Added` | Feature nueva, endpoint nuevo, comando nuevo |
| `Changed` | Comportamiento modificado, interfaz actualizada |
| `Deprecated` | Feature que se eliminará en versión futura |
| `Removed` | Feature o API eliminada |
| `Fixed` | Bug corregido |
| `Security` | Vulnerabilidad corregida |

### Semver

- **PATCH** (x.y.Z): bug fixes, cambios internos sin impacto público
- **MINOR** (x.Y.z): features nuevas backwards-compatible
- **MAJOR** (X.y.z): breaking changes

---

## Feature Docs (`docs/features/`)

Cada feature principal del boilerplate tiene su propio archivo en `docs/features/`.

### Template de documentación de feature

```markdown
# [Feature Name]

Descripción de una línea de para qué sirve esta feature.

## Overview

Explicación de alto nivel: qué problema resuelve, cuándo usarla.

## Configuration

Variables de entorno relevantes, archivos de config.

## Usage

Cómo usar la feature desde el punto de vista del desarrollador que
consume el boilerplate.

## Key Concepts

Conceptos importantes para entender el sistema (máximo 5).

## Integration Points

Cómo se integra con otras features del boilerplate.

## Extending

Cómo extender o personalizar esta feature en un proyecto cliente.
```

### Features a documentar en este boilerplate

- `authentication.md` — Fortify, 2FA, rate limiting
- `api-keys.md` — ULID keys, scopes, rate limiting, expiración
- `ai-gateway.md` — Drivers, PromptRegistry, UsageTracker, cost tracking
- `webhooks.md` — HMAC signing, retry, circuit breaker
- `users-roles.md` — Spatie Permissions, 3 roles base, grupos de permisos
- `module-system.md` — Registries, `make:module`, paquetes path composer

---

## Architecture Docs (`docs/architecture/`)

Documenta decisiones de diseño y estructura del sistema.

### `overview.md`

- Stack completo con versiones
- Diagrama de capas (auth → web → API → queue)
- Decisiones de diseño no obvias (por qué SQLite default, por qué ULID, etc.)

### `database.md`

- Todos los modelos con sus relaciones
- Por qué ULID como PKs
- Estrategia de testing con `:memory:`

### `registries.md`

- Los 4 registries: `NavigationRegistry`, `ScopeRegistry`, `WebhookEventRegistry`, `PermissionRegistrar`
- Cómo registrar en cada uno
- Cuándo crear un ServiceProvider dedicado vs usar AppServiceProvider

---

## Development Docs (`docs/development/`)

Guías para desarrolladores que trabajan en el boilerplate.

### `getting-started.md`

```markdown
# Getting Started

## Prerequisites
- PHP 8.4+, Node.js, Composer

## Setup
git clone ...
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build

## Dev environment
composer run dev   # PHP server + queue + Pail + Vite HMR

## Running tests
php artisan test --compact
```

### `conventions.md`

- Naming: variables descriptivas, enums TitleCase, snake_case para métodos
- Form Requests obligatorios para validación
- Traits en `app/Concerns/` para validación reutilizable
- Constructor property promotion en PHP 8
- Return types explícitos siempre

### `skills-guide.md`

Tabla con todos los skills disponibles y cuándo activar cada uno.

---

## Skill Docs (`.claude/skills/`)

Actualiza el SKILL.md de un skill cuando:
- Se añade o modifica un patrón que el skill debe seguir
- Cambia la API del package que el skill cubre
- Se descubren pitfalls nuevos en el proyecto
- El skill genera código incorrecto de forma recurrente

### Estructura de SKILL.md

```yaml
---
name: skill-name
description: "Trigger phrases y cuándo activar. Incluye ejemplos literales de lo que diría el usuario."
---

# Título del Skill

## When to Apply
## [Secciones de contenido]
## Common Pitfalls
## Verification
```

**Regla:** La `description` en el frontmatter es crítica — es lo que determina si el skill se activa. Incluye siempre frases literales que diría el usuario.

---

## CONTEXT.md — AI Context Summary

`CONTEXT.md` en la raíz es un snapshot del proyecto diseñado para compartir con otras IAs. Es el equivalente a un "briefing" que otra IA puede leer para entender el proyecto sin acceso al código.

### Cuándo actualizar

Actualiza `CONTEXT.md` siempre que se produzca cualquiera de estos cambios:

| Cambio | Sección afectada en CONTEXT.md |
|--------|-------------------------------|
| Se añade/elimina un feature | `## Features` |
| Cambia la versión de un package principal | `## Stack` |
| Nuevo modelo Eloquent o cambio en PKs | `## Architecture → Models` |
| Nueva convención de naming o coding | `## Code Conventions` |
| Cambio en comandos de desarrollo | `## Development Commands` |
| Nueva estructura de directorios relevante | `## Architecture → Directory Structure` |
| Cambio en decisiones de diseño | `## Architecture → Key Design Decisions` |

### Principios para CONTEXT.md

- **Conciso sobre exhaustivo**: Una IA externa necesita orientarse, no reemplazar la lectura del código. Máximo una o dos líneas por feature.
- **Contratos públicos, no implementación**: Documenta rutas, interfaces, convenciones — no lógica interna.
- **Siempre sincronizado**: Si el README cambió, `CONTEXT.md` probablemente también debe cambiar.
- **No duplicar README**: `CONTEXT.md` es más denso y orientado a IA; el README es más legible para humanos.

### Lo que NO va en CONTEXT.md

- Ejemplos de código extensos
- Instrucciones de instalación paso a paso (eso es del README)
- Detalles de implementación interna
- Historial de cambios (eso es del CHANGELOG)

---

## Common Pitfalls

- **Documentar implementación interna**: La docs debe describir el contrato público, no el código interno. Si alguien tiene que leer el código de todas formas, la doc no sirve.
- **Desincronización README ↔ código**: Verificar siempre que lo documentado en README existe en el código antes de escribir.
- **CHANGELOG sin fecha o versión**: Cada entrada debe tener versión semver y fecha ISO (YYYY-MM-DD).
- **Docs demasiado largas**: Una feature doc de más de 200 líneas probablemente mezcla implementación con uso. Separa o recorta.
- **Duplicar info entre README y docs/**: El README es el resumen. `docs/` tiene el detalle. No copies texto entre ambos.

---

## Verification

Antes de declarar la documentación terminada:

- [ ] Los comandos en `getting-started.md` funcionan en una máquina limpia
- [ ] Todas las features listadas en el README existen en el código
- [ ] El CHANGELOG tiene la versión correcta y la fecha de hoy
- [ ] Los archivos de `docs/features/` describen comportamiento real (verificar con `php artisan tinker` si es necesario)
- [ ] No hay referencias a rutas, clases o métodos que ya no existen
- [ ] `CONTEXT.md` refleja cualquier cambio en stack, features, arquitectura o convenciones
