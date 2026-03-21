# Skills Guide

Los skills son instrucciones especializadas para Claude Code. Se activan automáticamente según el contexto o manualmente con `/skill-name`.

## Skills Disponibles

| Skill | Cuándo Activar |
|-------|---------------|
| `laravel-docs` | Actualizar docs, CHANGELOG, README, o SKILL.md |
| `laravel-module` | Crear un módulo nuevo con scaffold completo |
| `laravel-package` | Crear un paquete Composer para cliente |
| `api-endpoint` | Crear endpoints REST versionados |
| `webhook-builder` | Implementar webhooks outbound |
| `ai-gateway` | Trabajar con el AI Gateway o añadir drivers |
| `livewire-development` | Crear/modificar componentes Livewire |
| `fluxui-development` | Trabajar con componentes `<flux:*>` |
| `pest-testing` | Escribir o corregir tests Pest |
| `fortify-development` | Features de autenticación (2FA, registro, etc.) |
| `blaze-optimize` | Optimizar rendering de componentes Blade |

## Referencia por Tarea

### "Quiero añadir una nueva feature de negocio"
→ `laravel-module` para el scaffold, luego `api-endpoint` si necesita API, `webhook-builder` si emite eventos.

### "Quiero crear un formulario o página"
→ `livewire-development` + `fluxui-development` para los componentes Flux UI.

### "Quiero exponer algo como API"
→ `api-endpoint` para endpoints REST versionados con scopes y API Resources.

### "Quiero que el sistema notifique externamente"
→ `webhook-builder` para entrega outbound con HMAC signing y retry.

### "Quiero añadir IA"
→ `ai-gateway` para integración con LLMs via driver pattern.

### "Quiero escribir tests"
→ `pest-testing` para patrones de testing con Pest 4 y Spatie Permissions.

### "Quiero personalizar para un cliente"
→ `laravel-package` para paquetes Composer locales aislados.

### "Quiero actualizar la documentación"
→ `laravel-docs` para README, CHANGELOG, docs/ o SKILL.md.

## Ubicación de los Skills

```
.claude/skills/
├── laravel-docs/SKILL.md
├── laravel-module/SKILL.md
├── laravel-package/SKILL.md
├── api-endpoint/SKILL.md
├── webhook-builder/SKILL.md
├── ai-gateway/SKILL.md
├── livewire-development/SKILL.md
├── fluxui-development/SKILL.md
├── pest-testing/SKILL.md
├── fortify-development/SKILL.md
└── blaze-optimize/SKILL.md
```

## Crear o Actualizar un Skill

Para crear un skill nuevo o mejorar uno existente, usar el meta-skill `skill-creator`.

Al terminar de implementar una feature nueva, actualizar `docs/development/skills-guide.md` si se añadió un skill nuevo.
