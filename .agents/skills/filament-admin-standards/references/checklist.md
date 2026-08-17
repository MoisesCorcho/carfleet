# Filament Panel — Pre-Merge Checklist (CarFleet)

Use before marking a Resource, Page, Widget, or Panel change as done.

## Panel & Access
- [ ] Single admin panel discovery limited to `App\Filament\*`
- [ ] No `FilamentInfoWidget` or default debug chrome in production
- [ ] Branding (name "CarFleet") and primary colors configured in `AdminPanelProvider.php`

## Resource / Page Structure
- [ ] Navigation group by domain (`Gestión de Flota`, `Operación`, `Administración`)
- [ ] Area folder under `app/Filament/Resources/{Area}/`
- [ ] Form/table extracted to `Schemas/` if long or multi-concern
- [ ] Create/Edit call Actions + DTOs; domain errors → Notification + halt

## Form UX (Premium)
- [ ] Sections (and Tabs if multi-concern / long form)
- [ ] Grid layout for short fields; full width for notes, uploads, signature
- [ ] Spanish labels on all fields, sections, and helpers
- [ ] Money/costs: stored as integers in minor/base units
- [ ] File uploads constrained (image types, max size, disk, directory)

## Table UX (Premium)
- [ ] Columns operators actually scan (plate number, driver, status, mileage)
- [ ] Statuses as badges with distinct colors (`disponible` = success, `en_viaje` = warning, etc.)
- [ ] Filters for real operator questions (status, fuel type)
- [ ] Empty state: heading + description + primary create action
- [ ] Striped + pagination options (`10, 25, 50`)

## Validation & i18n
- [ ] `unique(ignoreRecord: true)` on edit for plate numbers, documents, licenses
- [ ] Domain exceptions surfaced in Spanish notifications
- [ ] `./vendor/bin/sail pint` executed clean before committing
