---
name: dev-workflow
description: Orquesta el ciclo de vida completo de desarrollo desde la creación de branch hasta el merge. Úsala al iniciar una nueva feature, fix o módulo, durante el desarrollo para proponer commits, al preparar un merge o PR, o al hacer cleanup post-merge. Activar cuando el usuario diga "quiero trabajar en X", "voy a implementar X", "inicia una feature", "start feature", "nueva feature", "nuevo fix", "propón un commit", "propose commit", "listo para mergear", "quiero mergear", "ready to merge", "ya se mergeó", "merge completado", "cleanup branch", "dev workflow", "/dev-workflow".
---

# Dev Workflow

Guía interactiva del ciclo completo de desarrollo. **Siempre propone antes de ejecutar** — nunca hace acciones git sin confirmación del usuario.

---

## Fase 1: Iniciar trabajo

Activar cuando el usuario dice que va a trabajar en algo nuevo.

### Proceso

1. Identifica el tipo de trabajo y el nombre descriptivo a partir de lo que dijo el usuario:

   | Tipo | Prefix de branch | Cuándo |
   |---|---|---|
   | Feature / módulo nuevo | `feature/` | Nueva funcionalidad |
   | Bug fix | `fix/` | Corrección de error |
   | Refactor | `refactor/` | Mejora sin nueva funcionalidad |
   | Hotfix urgente | `hotfix/` | Fix crítico en producción |
   | Tarea de mantenimiento | `chore/` | Deps, config, CI |
   | Paquete cliente | `feature/` | Nuevo paquete o personalización |

2. Verifica en qué branch estás actualmente con `git branch --show-current`.

3. Propón la branch antes de crearla:
   ```
   Voy a crear la branch `feature/payments-module` desde `main`.
   ¿Confirmas? (o dime si quieres un nombre diferente)
   ```

4. Con confirmación, ejecuta:
   ```bash
   git checkout main && git pull origin main
   git checkout -b feature/payments-module
   ```

5. Confirma al usuario: "Branch `feature/payments-module` creada. Puedes empezar a trabajar."

### Reglas de naming

- Solo minúsculas y guiones
- Máximo 40 caracteres después del prefix
- Descriptivo del trabajo, no del ticket

---

## Fase 2: Proponer commit

Activar cuando el usuario dice que quiere commitear o propone hacer un commit.

### Proceso

1. Ejecuta `git status` y `git diff --staged` para ver qué hay.

2. Si no hay nada staged, revisa `git diff` (unstaged) y sugiere qué archivos agregar:
   ```
   No hay cambios staged. Vi estos archivos modificados:
   - app/Models/Payment.php
   - app/Livewire/Payments/Index.php
   - tests/Feature/PaymentsTest.php

   ¿Quieres que los agregue todos o solo algunos?
   ```

3. Analiza los cambios y propón un mensaje siguiendo Conventional Commits:
   ```
   Propongo este commit:

   feat(payments): add payment listing with status filters

   ¿Lo confirmas, quieres modificarlo, o agregamos más cambios primero?
   ```

4. Con confirmación, ejecuta el commit con `git commit -m "..."`.

### Reglas

- Activa `git-conventions` para el formato exacto del mensaje
- **Nunca commitear:** `.env`, `*.sqlite`, `vendor/`, `node_modules/`, archivos de secretos
- Si ves esos archivos staged, advierte antes de proceder
- Un commit por unidad lógica de trabajo — no acumules todo en uno si no tiene sentido junto
- Si hay tests relevantes, sugiere correrlos antes: `php artisan test --compact --filter=X`

---

## Fase 3: Preparar merge / PR

Activar cuando el usuario dice que está listo para mergear o crear un PR.

### Proceso

1. Muestra un resumen del trabajo hecho:
   ```bash
   git log main..HEAD --oneline --no-merges
   ```

2. Verifica que los tests pasen antes de continuar:
   ```bash
   php artisan test --compact
   ```
   Si fallan, bloquea y pide que se resuelvan primero.

3. Activa el skill `code-review` sobre los cambios (`git diff main..HEAD`).

4. Propón el título y body del PR siguiendo `git-conventions`:
   ```markdown
   Título: feat(payments): add payment module with status filters

   ## Changes
   - Add Payment model with status enum and factory
   - Add Livewire Index component with real-time filters
   - Add feature tests covering all filter scenarios

   ## Testing
   - [ ] Unit tests passing
   - [ ] Feature tests passing
   - [ ] Tested manually in local environment

   ## Notes
   Usa ScopeRegistry para el filtro de status. Ver app/Registries/ScopeRegistry.php.
   ```

5. Pregunta la estrategia de merge:
   - **Squash** (recomendado para features): un commit limpio en main
   - **Merge commit**: preserva el historial de la branch
   - **Rebase**: historial lineal sin merge commit

6. Si el repo tiene remote, propón hacer push y crear el PR con `gh pr create`.

---

## Fase 4: Cleanup post-merge

Activar cuando el usuario confirma que el merge ya ocurrió.

### Proceso

1. Propón las acciones de cleanup juntas:
   ```
   El merge está completo. Propongo hacer cleanup:

   1. git checkout main
   2. git pull origin main
   3. git branch -d feature/payments-module        (eliminar local)
   4. git push origin --delete feature/payments-module  (eliminar remota)

   ¿Confirmas?
   ```

2. Ejecuta solo con confirmación explícita.

3. Confirma: "Cleanup completado. Estás en `main` actualizado."

### Regla

Si el usuario dice que no quiere eliminar la branch remota, respeta eso y solo elimina la local.

---

## Reglas generales

- Cada propuesta de acción git **espera confirmación** antes de ejecutar
- Usa los skills especializados en su momento: `git-conventions` para mensajes, `code-review` antes del PR
- Si el usuario menciona un issue o ticket, inclúyelo en el body del PR (no en el branch name, salvo que el equipo lo requiera)
- Si algo falla (test, push rechazado, conflicto), detente, muestra el error, y propón cómo resolverlo — no reintentes automáticamente
- Si hay conflictos al hacer merge, guía el proceso de resolución en lugar de abortarlo
