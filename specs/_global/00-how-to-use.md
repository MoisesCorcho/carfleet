# Cómo usar estas specs (SDD) — CarFleet

## Qué es esto

Esta carpeta define el flujo de **Spec-Driven Development (SDD)** para el sistema **CarFleet** (Laravel 13, Filament v4, Sail, Boost) sin duplicar lo que ya es fuente canónica en el proyecto (rutas de código, esquemas de BD, paquetes y convenciones).

Cada feature se especifica en la ruta `specs/features/<NN-slug>/` mediante 3 archivos obligatorios:

| Archivo | Propósito |
|---|---|
| `requirements.md` | **QUÉ** — User stories + criterios EARS (R1..Rn) + decisiones de producto. Sin implementación. |
| `design.md` | **CÓMO** — Actions, Schemas/Form/Tables de Filament v4, DTOs, políticas de acceso y contratos. |
| `tasks.md` | **ORDEN** — Checklist ejecutable con trazabilidad `_(cubre Rx)_`, escenarios de test y Definition of Done. |

## Fuentes de verdad (no duplicar aquí)

| Tema | Fuente canónica | Notas |
|---|---|---|
| Convenciones de código (Actions, DTOs, Enums) | `.ai/guidelines/project-conventions.md` / `AGENTS.md` | Actualizar solo el guideline principal; referenciar desde specs. |
| Stack, Sail, Filament v4, Livewire, Boost | Laravel Boost + documentación de Filament v4 | Usar `search-docs` / Boost para verificar APIs. |
| Modelo de datos del dominio | `app/Models`, `app/Enums`, `database/migrations` | Cambios de esquema actualizan modelos, migrations y enums. |
| Roadmap y visión de producto | [`01-product-and-roadmap.md`](01-product-and-roadmap.md) | Única fuente de verdad de alcance y prioridades. |
| Criterios de calidad y auditoría SDD | [`02-feature-quality.md`](02-feature-quality.md) | Reglas EARS, mapa de trazabilidad y barra de calidad. |

**Regla anti-duplicación:** Si la información vive en el código o en las fuentes canónicas anteriores, en `specs/` solo se **referencia**.

## Convención de estados de feature

Tanto en `01-product-and-roadmap.md` como en el encabezado de cada `requirements.md`:

| Estado | Significado |
|---|---|
| `No iniciada` | Sin especificación o no priorizada aún. |
| `Specs en progreso` | Redactando o corrigiendo `requirements.md`, `design.md` o `tasks.md`. |
| `Lista para implementar` | Specs auditadas/aprobadas y prerequisitos cumplidos. |
| `En implementación` | Tareas en desarrollo activo. |
| `Completa` | DoD cumplido, tests verdes en Sail y merge a rama principal. |

## Layout del directorio de specs

```text
specs/
  _global/
    00-how-to-use.md          ← este archivo
    01-product-and-roadmap.md
    02-feature-quality.md
  features/
    01-vehicle-management/
      requirements.md
      design.md
      tasks.md
```
