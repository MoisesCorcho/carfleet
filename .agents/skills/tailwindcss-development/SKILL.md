---
name: tailwindcss-development
description: "Tailwind CSS v4 utility standards for CarFleet UI components, Filament custom views, Livewire components, responsive layouts, and dark mode."
metadata:
  author: laravel
---

# Tailwind CSS Development — CarFleet

Guidelines for styling custom HTML, Blade templates, and Livewire components using Tailwind CSS v4.

## Basic Usage

- Use Tailwind CSS v4 utility classes.
- Always use `@import "tailwindcss";` syntax in CSS files (no deprecated `@tailwind` directives).
- CSS-first configuration via `@theme` directive when extending theme variables.

## Spacing & Layout

- Use `gap` utilities (`gap-4`, `gap-6`) instead of margins for spacing between siblings.
- Build responsive flex and grid structures (`grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6`).

## Dark Mode

- Support dark mode using `dark:` variants (`bg-white dark:bg-gray-900 text-gray-900 dark:text-white`) matching Filament's theme.
