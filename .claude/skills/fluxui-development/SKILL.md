---
name: fluxui-development
description: "Use this skill for Flux UI development in Livewire applications only. Trigger when working with <flux:*> components, building or customizing Livewire component UIs, creating forms, modals, tables, or other interactive elements. Covers: flux: components (buttons, inputs, modals, forms, tables, date-pickers, kanban, badges, tooltips, etc.), component composition, Tailwind CSS styling, Heroicons/Lucide icon integration, validation patterns, responsive design, and theming. Do not use for non-Livewire frameworks or non-component styling."
license: MIT
metadata:
  author: laravel
---

# Flux UI Development

## When to Apply

Activate this skill when:

- Creating UI components or pages
- Working with forms, modals, or interactive elements
- Checking available Flux components

## Documentation

Use `search-docs` for detailed Flux UI patterns and documentation.

## Basic Usage

This project uses the free edition of Flux UI, which includes all free components and variants but **not Pro components**.

Flux UI is a component library for Livewire built with Tailwind CSS. It provides components that are easy to use and customize.

## Component Decision Flow (CRITICAL)

Always follow this sequence when choosing a component:

1. **Flux Free** — Use a `<flux:*>` component if it exists and is free.
2. **Custom component** — If the Flux component doesn't exist or is Pro-only, check `resources/views/components/` for a custom equivalent before building anything new.
3. **Create custom** — If no custom component exists, create one styled to match Flux UI conventions (see below).

**Never use Pro-only Flux components.** If a component is Pro, treat it as if it doesn't exist and follow steps 2–3.

## Available Components (Free Edition)

Available: avatar, badge, brand, breadcrumbs, button, callout, checkbox, dropdown, field, heading, icon, input, modal, navbar, otp-input, profile, radio, select, separator, skeleton, switch, text, textarea, tooltip

## Icons

**Always use [Lucide](https://lucide.dev/) icons** — this is the only icon set used in this project. Import icons with the Artisan command before using them:

```bash
php artisan flux:icon lucide-icon-name another-icon
```

Then reference them normally:

```blade
<flux:button icon="arrow-down-tray">Export</flux:button>
<flux:icon name="circle-user" />
```

Search [lucide.dev](https://lucide.dev/) for exact icon names — do not guess or invent names.

## Creating Custom Components (Flux-style)

When a required component is Pro-only or missing from Flux Free, build a custom Blade component in `resources/views/components/` that matches Flux UI's visual style:

- Use the same Tailwind utility classes as nearby Flux components (spacing, border radius, colors, shadows, typography).
- Support the same props/slots pattern Flux components use (`$variant`, `$size`, etc.) when applicable.
- Name the file clearly after its purpose, e.g. `components/stat-card.blade.php` → `<x-stat-card />`.
- Keep markup minimal and composable — don't bundle unrelated responsibilities.

```blade
{{-- resources/views/components/stat-card.blade.php --}}
@props(['label', 'value', 'icon' => null])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900']) }}>
    @if ($icon)
        <flux:icon :name="$icon" class="mb-2 size-5 text-zinc-400" />
    @endif
    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $label }}</p>
    <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $value }}</p>
</div>
```

## Common Patterns

### Form Fields

<!-- Form Field -->
```blade
<flux:field>
    <flux:label>Email</flux:label>
    <flux:input type="email" wire:model="email" />
    <flux:error name="email" />
</flux:field>
```

### Modals

<!-- Modal -->
```blade
<flux:modal wire:model="showModal">
    <flux:heading>Title</flux:heading>
    <p>Content</p>
</flux:modal>
```

## Verification

1. Check component renders correctly
2. Test interactive states
3. Verify mobile responsiveness

## Common Pitfalls

- Using Pro-only Flux components — always check if a component is free before using it
- Using Heroicons — **only Lucide icons are allowed** in this project
- Creating a new custom component without first checking `resources/views/components/` for an existing one
- Custom components that don't visually match Flux UI's style (spacing, radius, dark mode, etc.)
- Forgetting to run `php artisan flux:icon` before referencing a new Lucide icon
- Forgetting to use the `search-docs` tool for component-specific documentation