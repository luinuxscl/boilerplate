# AI Gateway

Sistema de integración con LLMs mediante un patrón driver pluggable. Incluye gestión de prompts reutilizables, tracking de uso y costos.

## Overview

El AI Gateway abstrae el proveedor LLM detrás de un contrato `AiProviderContract`, permitiendo cambiar de proveedor sin modificar el código de negocio. El driver por defecto es OpenRouter (compatible con la API de OpenAI).

## Configuration

`config/ai.php`:

```php
return [
    'driver' => env('AI_DRIVER', 'openrouter'),
    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'model'   => env('OPENROUTER_MODEL', 'openai/gpt-4o-mini'),
    ],
];
```

Variables de entorno:

```dotenv
AI_DRIVER=openrouter
OPENROUTER_API_KEY=sk-or-...
OPENROUTER_MODEL=openai/gpt-4o-mini
```

## Drivers Disponibles

| Driver | Descripción |
|--------|-------------|
| `openrouter` | OpenRouter API (OpenAI-compatible). Driver por defecto. |
| `null` | No hace nada. Útil para testing sin API key. |

## Uso Básico

```php
use App\Services\Ai\AiGateway;

$response = app(AiGateway::class)->complete('Summarize this: ' . $text);
```

## PromptRegistry

Gestiona templates de prompts reutilizables con variables `{{variable}}`:

```php
// Registrar un prompt
AiPrompt::create([
    'key'     => 'summarize',
    'content' => 'Summarize the following text in {{language}}: {{text}}',
]);

// Renderizar con datos
$prompt = AiPrompt::where('key', 'summarize')->first();
$rendered = $prompt->render(['language' => 'Spanish', 'text' => $content]);
```

La UI para gestionar prompts está en `/ai/prompts` (requiere permiso `ai.view`).

## Usage Tracking

Cada request al LLM se registra en `ai_usage_logs`:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `model` | string | Nombre del modelo usado |
| `prompt_tokens` | integer | Tokens del prompt |
| `completion_tokens` | integer | Tokens de la respuesta |
| `total_tokens` | integer | Total tokens |
| `cost_in_cents` | integer | Costo en centavos enteros (USD) |
| `duration_ms` | integer | Duración del request en ms |

**Importante:** Los costos se almacenan en centavos enteros (integer), no en float, para evitar problemas de precisión.

Dashboard de uso en `/ai/usage` (requiere permiso `ai.view`).

## Añadir un Driver Nuevo

1. Implementar `App\Contracts\AiDriver`
2. Registrar el driver en el service provider:

```php
app('ai')->extend('my-driver', fn () => new MyDriver(config('ai.my-driver')));
```

3. Configurar `AI_DRIVER=my-driver` en `.env`

Para implementación detallada, activar el skill `ai-gateway`.

## Integration Points

- **Permisos**: `ai.view`, `ai.manage` (gestionados por Spatie Permissions)
- **UI**: Componentes Livewire en `app/Livewire/Ai/`
- **Queue**: Los jobs de AI pueden encolarse con `ShouldQueue` si los requests son lentos
