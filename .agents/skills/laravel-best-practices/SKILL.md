---
name: laravel-best-practices
description: "Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code in CarFleet. Covers Actions, DTOs, Enums, Models, migrations, form requests, policies, query performance, and architectural consistency."
metadata:
  author: laravel
---

# Laravel Best Practices — CarFleet

Best practices for Laravel 13 development in CarFleet. For exact API syntax, verify with `search-docs`.

## Consistency First

Before applying any rule, check what CarFleet already does in `AGENTS.md` and `.ai/guidelines/project-conventions.md`. Laravel offers multiple valid approaches, and the best choice is the one the codebase already uses.

## How to Apply

1. Check changed files and project conventions (`AGENTS.md`).
2. Make the smallest coherent change adhering to type-first area structure (`app/Actions/{Area}`, `app/Enums/{Area}`).
3. Verify version-sensitive Laravel APIs for installed Laravel 13 with `search-docs`.
4. Run `./vendor/bin/sail pint` and `./vendor/bin/sail test` before finishing.

## Core Rules

- **Actions over Controllers/Resources**: Single use-case domain logic belongs in invokable Actions (`app/Actions/{Area}/*Action.php`).
- **DTOs at Edge Boundaries**: Pass validated DTOs (`app/DTOs/{Area}/*DTO.php`) to Actions instead of raw Requests.
- **Backed String Enums**: Store vocabularies as backed string enums (`app/Enums/{Area}/*Enum.php`) and cast on Eloquent models.
- **Eager Loading**: Prevent N+1 queries by eager-loading relations in Filament tables and API queries.
- **Transactions**: Wrap multi-model writes in `DB::transaction` inside Actions.
