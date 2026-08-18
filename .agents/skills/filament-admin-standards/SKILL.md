---
name: filament-admin-standards
description: >
  Premium UI/UX standards for Filament v4 admin panels in this CarFleet project.
  Use when creating or refactoring Filament Resources, Pages, Widgets, RelationManagers,
  forms, tables, filters, actions, PanelProvider branding, or admin UX; when improving
  panel aesthetics, validation feedback, empty states, or navigation; or when the user
  runs /filament-admin-standards. Domain logic stays in Actions/DTOs/Services per AGENTS.md —
  this skill owns panel presentation quality, not business rules.
metadata:
  short-description: "Filament v4 premium admin UI/UX for CarFleet"
  version: "1.1"
  stack: "filament/filament v4 · laravel v13 · livewire v3 · php 8.4"
---

# Filament Admin Standards — CarFleet

Actionable rules for **premium admin panels (Enterprise Grade)** in this repo. Domain architecture is defined in [`AGENTS.md`](../../../AGENTS.md) and [`specs/_global/03-domain-and-ux-standards.md`](../../../specs/_global/03-domain-and-ux-standards.md).

| Concern | Source of truth |
|---|---|
| Actions, DTOs, Services, Enums, Gateways | `AGENTS.md` / `.ai/guidelines/project-conventions.md` |
| Feature acceptance / EARS | `specs/features/**` |
| Domain Standards & Colombian Regulations | `specs/_global/03-domain-and-ux-standards.md` |
| Filament v4 API syntax | Laravel Boost `search-docs` **before** non-trivial UI code |
| **Panel UI/UX quality** | **This skill** |

## Core Principles (non-negotiable)

1. **Domain outside Filament** — writes and invariants go through **Actions** (and Services only when shared). Resources orchestrate UI and call Actions with DTOs.
2. **Thin Resources, rich UI** — Resources may be UI-heavy (layout, copy, affordances); they must not own business rules.
3. **Enterprise Polish Default** — every List/Form ships with semantic icons, clear placeholders, helper texts, and input normalization.
4. **Frictionless Relationships (`createOptionForm`)** — allow creating related entities (e.g. creating a User from Driver form) inline without leaving the view.
5. **Protection of Event-Driven Fields** — historical or calculation fields (like odometer/mileage in Edit forms) must be read-only (`disabled()`) and mutated solely by domain actions.
6. **One language** — Spanish for operator-facing strings (labels, helpers, empty states, notifications, validation).

---

## 1. Project Layout (Filament)

Follow **type-first** conventions:

```text
app/
  Filament/
    Resources/
      Vehicles/           # area folder
        VehicleResource.php
        Pages/
        Schemas/          # extract when form/table grows
      Drivers/
        DriverResource.php
      Trips/
        TripResource.php
    Pages/                # custom panel pages when needed
    Widgets/              # fleet KPIs only
  Providers/Filament/
    AdminPanelProvider.php
```

---

## 2. Premium UI/UX — Form Standards

### Visual Ergonomics & Semantic Icons
Use prefix icons on inputs to accelerate cognitive scanning:
* Identifiers / Plates / Documents: `prefixIcon('heroicon-m-identification')` or `prefixIcon('heroicon-m-credit-card')`
* Phone: `prefixIcon('heroicon-m-phone')`
* Email: `prefixIcon('heroicon-m-envelope')`
* Dates / Calendars: `prefixIcon('heroicon-m-calendar-days')`
* Users / Persons: `prefixIcon('heroicon-m-user')`
* Vehicles / Transport: `prefixIcon('heroicon-m-truck')`

### Input Guidance & Formatting
* Provide `placeholder('Ej: ...')` with realistic Colombian examples.
* Provide `helperText('...')` explaining formatting rules or purpose.
* Normalize case in client and server: `extraInputAttributes(['style' => 'text-transform: uppercase;'])` + `dehydrateStateUsing(fn ($state) => strtoupper(trim((string) $state)))`.
* For sensitive formats (phones, plates), use regex with clear Spanish error messages.

### Inline Entity Creation
Use `->createOptionForm([...])` on relationship Selects so operators never have to navigate away to create foreign keys.

---

## 3. Premium UI/UX — Table Standards

Every List page is an **operator workspace**:

- Columns operators scan first: Title/Identifier, Badges with status, Contact info, Validity.
- Statuses as badges with distinct colors:
  * `disponible` / `activo` = `success` (green)
  * `en_viaje` = `warning` (amber)
  * `mantenimiento` / `suspendido` = `danger` (red)
  * `inactivo` / `fuera_de_servicio` = `gray`
- Alerta visual explícita en registros vencidos o irregulares (ej. "Licencia Vencida").
- Filters matching real operational questions (Availability, Document Type, Eligibility for Trips).
- Copyable key columns (Plate, Document Number, Phone) with feedback notification.
- Empty states with heading, description, and primary create action.

---

## 4. Pre-Merge Checklist

- [ ] Resources grouped in area folders under `app/Filament/Resources/{Area}/`.
- [ ] Spanish copy on all labels, headings, notifications, and empty states.
- [ ] Semantic prefix icons on form inputs.
- [ ] Related entity creation inline via `createOptionForm` where applicable.
- [ ] Event-driven attributes (e.g. current mileage) protected from arbitrary edits.
- [ ] `./vendor/bin/sail pint` and `./vendor/bin/sail test` executed clean.
