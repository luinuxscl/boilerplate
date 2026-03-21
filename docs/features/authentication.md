# Authentication

Autenticación headless completa gestionada por **Laravel Fortify**. El boilerplate incluye login, registro, reset de contraseña, verificación de email y autenticación de dos factores (2FA/TOTP).

## Overview

Fortify actúa como backend de autenticación sin vistas propias. Las vistas están en `resources/views/pages/auth/` y son Blade puras (no Livewire). Las rutas las registra Fortify automáticamente — no se definen en `routes/web.php`.

## Configuration

`config/fortify.php` — features habilitadas:

```php
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
    Features::updateProfileInformation(),
    Features::updatePasswords(),
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]),
],
```

## Views

| Ruta | Vista |
|------|-------|
| `/login` | `pages/auth/login.blade.php` |
| `/register` | `pages/auth/register.blade.php` |
| `/forgot-password` | `pages/auth/forgot-password.blade.php` |
| `/reset-password` | `pages/auth/reset-password.blade.php` |
| `/verify-email` | `pages/auth/verify-email.blade.php` |
| `/two-factor-challenge` | `pages/auth/two-factor-challenge.blade.php` |

Las vistas se enlazan en `app/Providers/FortifyServiceProvider.php` via `Fortify::loginView()`, `Fortify::registerView()`, etc.

## Actions

Las acciones de Fortify están en `app/Actions/Fortify/`:

- `CreateNewUser` — crea usuario y le asigna el rol `user` por defecto
- `ResetUserPassword` — resetea contraseña con validación via `PasswordValidationRules` trait

## Two-Factor Authentication (2FA)

TOTP con app autenticadora (Google Authenticator, etc.):

- Se activa/desactiva desde `/settings/security`
- Genera un QR code y recovery codes
- Requiere confirmación de contraseña para activar
- `User` model usa el trait `TwoFactorAuthenticatable`

## Rate Limiting

Configurado en `FortifyServiceProvider::boot()`:

```php
RateLimiter::for('login', function (Request $request) {
    $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
    return Limit::perMinute(5)->by($throttleKey);
});
```

5 intentos por minuto por combinación email+IP.

## Extending

Para customizar la lógica de creación de usuario (e.g., campos extra):

1. Editar `app/Actions/Fortify/CreateNewUser.php`
2. El método `create(array $input)` recibe todos los campos del form de registro

Para añadir nuevas features de autenticación, activar el skill `fortify-development`.
