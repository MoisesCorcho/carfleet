---
name: laravel-security
description: "Laravel security best practices for CarFleet — authentication, authorization policies, mass assignment protection, XSS prevention, file upload validation, and secure storage."
metadata:
  origin: ECC
---

# Laravel Security Best Practices — CarFleet

Comprehensive security guidelines for CarFleet to protect against common vulnerabilities.

## When to Activate

- Setting up authorization policies, gates, and Filament panel permissions.
- Writing Eloquent models, migrations, and Form Requests.
- Handling file uploads (odometer photo evidences, fuel vouchers, digital signatures).
- Validating inputs and sanitizing HTML/JS renderings.

## Core Rules

1. **Mass Assignment Protection**: Explicitly declare `$fillable` on all Eloquent models. Never use `$guarded = []`.
2. **File Upload Security**: Validate MIME types (`mimes:jpg,jpeg,png,webp`), max file size (`max:5120`), and extension matching for photo evidences and vouchers. Store files in private/controlled directories (`storage/app/public/evidences`).
3. **SQL Injection Prevention**: Rely on Eloquent parameterization. Never concatenate raw strings inside `whereRaw` or `orderByRaw`.
4. **Blade XSS Protection**: Use `{{ $var }}` Blade escaping. Never use `{!! $var !!}` with user-supplied input.
5. **Authorization at the Edge**: Enforce Policies (`App\Policies`) and Gate checks before executing domain Actions.
