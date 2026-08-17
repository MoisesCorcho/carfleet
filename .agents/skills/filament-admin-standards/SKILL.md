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
  version: "1.0"
  stack: "filament/filament v4 · laravel v13 · livewire v3 · php 8.4"
---

# Filament Admin Standards — CarFleet

Actionable rules for **premium admin panels** in this repo. Domain architecture is already defined in [`AGENTS.md`](../../../AGENTS.md) / project-conventions — **do not reinvent it here**.

| Concern | Source of truth |
|---|---|
| Actions, DTOs, Services, Enums, Gateways | `AGENTS.md` / `.ai/guidelines/project-conventions.md` |
| Feature acceptance / EARS | `specs/features/**` |
| Filament v4 API syntax | Laravel Boost `search-docs` **before** non-trivial UI code |
| **Panel UI/UX quality** | **This skill** |

## When to Use

Load **before writing Filament code** when:

- Creating or editing Resources / Pages / Widgets / Relation Managers.
- Designing forms, tables, filters, bulk actions, empty states.
- Polishing admin navigation, labels, feedback, or branding.
- Reviewing "working but ugly/half-baked" admin CRUD.
- User mentions: panel Filament, resource, admin UX, `/filament-admin-standards`.

## Core Principles (non-negotiable)

1. **Domain outside Filament** — writes and invariants go through **Actions** (and Services only when shared). Resources orchestrate UI and call Actions with DTOs. See AGENTS.md.
2. **Thin Resources, rich UI** — Resources may be UI-heavy (layout, copy, affordances); they must not own business rules.
3. **Premium default** — every List/Form ships as product-grade operator UX, not scaffold leftovers.
4. **One language** — Spanish for operator-facing strings (labels, helpers, empty states, notifications, validation).
5. **No dead chrome** — no `FilamentInfoWidget` in production, no empty stubs.
6. **Verify API** — verify Filament v4 namespaces and components with `search-docs` + sibling Resources under `app/Filament/`.

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
    Widgets/              # fleet KPIs only (total vehicles, active trips, fuel costs)
  Providers/Filament/
    AdminPanelProvider.php
```

| Rule | Detail |
|---|---|
| Single admin panel | `id('admin')`, `path('admin')` |
| Area folders | Group Resources by domain area (`Vehicles`, `Drivers`, `Trips`, `Fuel`, `Invoices`) |
| Extraction | Form/Table schemas → `Schemas/*` when form is multi-section or > ~80 lines |

---

## 2. Resource Responsibilities

### Allowed in Resource / Pages

- Navigation: group (`Gestión de Flota`, `Operación`, `Administración`), icon (`Heroicon`), sort, badge.
- Form/table schema composition (layout, copy, field UX).
- Field-level validation for immediate feedback (`required`, `numeric`, `unique(ignoreRecord: true)`).
- Mapping create/edit to **Actions + DTOs**.
- Catching domain exceptions → danger `Notification` + `halt()` (no silent fail).

### Forbidden in Resource

- Multi-model business workflows without an Action.
- Mileage or trip status transition logic only inside `afterStateUpdated`.
- Copy-pasted field blocks across Resources.

---

## 3. Premium UI/UX — Forms

### Layout Hierarchy

| Level | Use |
|---|---|
| `Tabs` | ≥ 2 conceptual areas on a long form (e.g. Trip: Información · Evidencias · Firma) |
| `Section` | Group of related fields; always titled with optional description |
| `Grid` | 2–3 columns for short fields; full width for notes, upload, signature |

### Labels & Copy (Spanish Operators)

- Labels in human Spanish (e.g., `Placa del Vehículo`, `Kilometraje Inicial`, `Conductor Asignado`).
- Helper text for non-obvious format or constraints (e.g., "Lectura del odómetro en kilómetros").

---

## 4. Premium UI/UX — Tables

Every List page is an **operator workspace**:

- Columns operators scan first (plate number, driver name, status badge, mileage).
- Statuses as badges with distinct colors (`disponible` = success, `en_viaje` = warning, `mantenimiento` = danger).
- Filters matching real questions (Filter by status, fuel type, driver availability).
- Empty states with heading, description, and primary create action.
- Pagination options (`10, 25, 50`).

---

## 5. Pre-Merge Checklist

- [ ] Single admin panel configured in `AdminPanelProvider.php`.
- [ ] Resources grouped in area folders under `app/Filament/Resources/{Area}/`.
- [ ] Spanish copy on all labels, headings, notifications, and empty states.
- [ ] Multi-model writes mapped to Actions with `DB::transaction`.
- [ ] `./vendor/bin/sail pint` executed clean before committing.
