# Filament Anti-Patterns — CarFleet

Failures common in "working but semi-mature" admin panels. Agents must avoid introducing them.

## 1. Open Panel Access Without Guard
**Bad:** Returning unconditional `true` in `canAccessPanel()`.  
**Good:** Gate access with application policies and user roles (`User::canAccessPanel()`).

## 2. Scaffold Leftovers
**Bad:** Leaving `FilamentInfoWidget` on production dashboard or empty stub directories.  
**Good:** Remove default info widgets; keep production chrome domain-focused (fleet KPIs).

## 3. Flat CSV Form
**Bad:** 15 fields in a single column without sections or headers.  
**Good:** Group fields into `Section` / `Tabs` with grid columns and Spanish labels.

## 4. Business Logic inside Form Hooks (`afterStateUpdated`)
**Bad:** Updating odometer, closing trips, or calculating fuel performance inside form Livewire state hooks.  
**Good:** Encapsulate multi-model writes and status transitions in **Actions** + `DB::transaction`. Form hooks only adjust field visibility or UI defaults.

## 5. Silent Domain Failures
**Bad:** Catching domain exceptions without notifying the operator or swallowing errors into 500 pages.  
**Good:** Catch typed domain exceptions, show a danger `Notification` in Spanish, and call `halt()`.

## 6. Locale Salad
**Bad:** Section titles in English ("Vehicle Details") and helpers in Spanish ("Ingrese la placa").  
**Good:** Consistent Spanish copy across all operator-facing UI elements.

## 7. Unbounded File Uploads
**Bad:** `FileUpload` without file size or MIME type restrictions.  
**Good:** Constrain uploads (`image()`, `maxSize(5120)`, `directory('evidences/odometers')`).
