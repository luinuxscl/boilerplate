# Webhooks

Sistema de notificaciones outbound hacia URLs externas con firmado HMAC, reintentos automáticos y circuit breaker.

## Overview

Cuando ocurre un evento en el sistema (e.g., `user.created`), el boilerplate puede notificar automáticamente a URLs externas configuradas por el usuario. Cada endpoint externo puede suscribirse a los eventos que le interesen.

## Architecture

```
Evento ocurre → WebhookEvent emitido → DispatchWebhook job encolado → HTTP POST firmado → Retry si falla
```

## Webhook Endpoints

Los endpoints externos se gestionan desde `/webhooks` (requiere permiso `webhooks.view`).

Cada endpoint tiene:
- `url` — URL de destino
- `secret` — Secreto para verificar la firma en el receptor
- `events` — Lista de eventos suscritos (o `['*']` para todos)
- `is_active` — Estado activo/inactivo
- `failed_attempts` — Contador de fallos consecutivos

## Event Registry

Registrar eventos disponibles en un ServiceProvider:

```php
WebhookEventRegistry::register('order.created', OrderCreatedPayload::class);
WebhookEventRegistry::register('order.updated', OrderUpdatedPayload::class);
WebhookEventRegistry::register('order.deleted', OrderDeletedPayload::class);
```

## Dispatching Events

```php
use App\Jobs\DispatchWebhook;

DispatchWebhook::dispatch('order.created', [
    'id'     => $order->id,
    'status' => $order->status,
    'total'  => $order->total,
]);
```

El job es asíncrono (`ShouldQueue`) — se procesa en la cola.

## HMAC Signing

El receptor puede verificar la autenticidad del webhook:

**Header enviado:**
```
X-Webhook-Signature: sha256={hash}
```

**Verificación en el receptor (PHP):**
```php
$expectedSignature = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);
$isValid = hash_equals($expectedSignature, $request->header('X-Webhook-Signature'));
```

## Retry & Circuit Breaker

- **Reintentos**: Automáticos con backoff exponencial si el endpoint responde con error
- **Circuit breaker**: Tras `N` fallos consecutivos el endpoint se desactiva (`is_active = false`)
- Los `failed_attempts` se resetean cuando el endpoint responde con éxito

## Extending

Para emitir un webhook desde un módulo nuevo:

1. Registrar los eventos con `WebhookEventRegistry::register()`
2. Llamar `DispatchWebhook::dispatch('event.name', $payload)` cuando el evento ocurra
3. El boilerplate gestiona el resto (entrega, firma, retry)

Para implementación detallada, activar el skill `webhook-builder`.
