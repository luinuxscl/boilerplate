# Getting Started

## Prerequisites

- PHP 8.4+
- Composer 2+
- Node.js 20+ y npm
- Git

## Setup Local

```bash
# 1. Clonar el repo
git clone {repo-url} my-project
cd my-project

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Base de datos (SQLite por defecto — sin configuración extra)
touch database/database.sqlite
php artisan migrate --seed

# 5. Build de assets
npm run build
```

## Dev Environment

Un solo comando levanta todo:

```bash
composer run dev
```

Esto inicia en paralelo:
- PHP server en `http://localhost:8000`
- Queue worker
- Pail (log viewer)
- Vite HMR para assets

## Comandos Frecuentes

```bash
# Tests
php artisan test --compact                          # Todos los tests
php artisan test --compact --filter=AuthTest        # Filtrar por nombre
php artisan test --compact tests/Feature/Auth/      # Un archivo o carpeta

# Base de datos
php artisan migrate                                 # Ejecutar migraciones
php artisan migrate:fresh --seed                    # Reset completo con datos

# Code style
vendor/bin/pint --dirty                             # Formatear solo archivos modificados
vendor/bin/pint                                     # Formatear todo el proyecto

# Assets
npm run build                                       # Build de producción
npm run dev                                         # Dev con watch (sin HMR completo)

# Artisan helpers
php artisan route:list                              # Ver todas las rutas
php artisan list                                    # Ver todos los comandos disponibles
php artisan tinker --execute "User::count()"        # Ejecutar PHP rápido
```

## Variables de Entorno Importantes

```dotenv
# AI Gateway (opcional)
AI_DRIVER=openrouter
OPENROUTER_API_KEY=sk-or-...
OPENROUTER_MODEL=openai/gpt-4o-mini

# Mail (para email verification y password reset)
MAIL_MAILER=log           # En dev: logs en storage/logs/laravel.log
MAIL_MAILER=smtp          # En prod: configurar servidor SMTP

# Queue
QUEUE_CONNECTION=sync     # Sync en dev (sin worker)
QUEUE_CONNECTION=database # Database en prod (requiere queue worker)
```

## Estructura de Carpetas Clave

```
app/
├── Actions/Fortify/    # Lógica de autenticación
├── Concerns/           # Traits de validación reutilizables
├── Livewire/           # Componentes reactivos
├── Models/             # Modelos Eloquent
├── Providers/          # Service providers
└── Services/           # Servicios de negocio

resources/views/
├── pages/             # Vistas full-page (auth, settings)
├── livewire/          # Vistas de componentes Livewire
└── layouts/           # Layouts base (app, auth)

routes/
├── web.php            # Rutas web
├── settings.php       # Settings pages
├── api.php            # API entry point
└── api_v1.php         # Endpoints v1

.claude/skills/        # Skills de Claude Code
docs/                  # Esta documentación
```
