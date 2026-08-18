# Tasks — F03: Gestión de Solicitantes

- [x] 1. Base de datos y Modelos
  - [x] 1.1 Actualizar migración `2026_08_17_000003_create_requesters_table.php` con `document_type`, `document_number`, `is_active`, `softDeletes` y clave única compuesta `['document_type', 'document_number']`. _(cubre R1, R4, R5)_
  - [x] 1.2 Crear Enum `App\Enums\Requesters\RequesterDocumentTypeEnum`. _(cubre R1, R2)_
  - [x] 1.3 Actualizar modelo `App\Models\Requester` con casts, scopes (`scopeActive`), mutadores y `SoftDeletes`. _(cubre R1, R2, R3, R5)_
  - [x] 1.4 Crear/Actualizar factory `RequesterFactory`. _(cubre R1, R2)_

- [x] 2. Lógica de Dominio y Actions
  - [x] 2.1 Crear DTO `UpsertRequesterDTO`. _(cubre R1, R3, R4)_
  - [x] 2.2 Crear excepción `InvalidRequesterException`. _(cubre R4)_
  - [x] 2.3 Implementar Action `RegisterRequesterAction`. _(cubre R1, R4)_
  - [x] 2.4 Implementar Action `UpdateRequesterAction`. _(cubre R3, R4)_

- [x] 3. Interfaz Administrativa (Filament v4)
  - [x] 3.1 Crear `RequesterResource` bajo `app/Filament/Resources/Requesters/` con íconos, placeholders, helper texts y filtros (`TrashedFilter`, `is_active`, `document_type`). _(cubre R1, R2, R3, R4, R5)_
  - [x] 3.2 Crear páginas `ListRequesters`, `CreateRequester`, `EditRequester`, `ViewRequester`. _(cubre R1, R2, R3)_

- [x] 4. Pruebas Automatizadas
  - [x] 4.1 Unit tests: Enum, DTO, Model scopes y métodos. _(cubre R1, R2)_
  - [x] 4.2 Feature test: Actions `RegisterRequesterAction` y `UpdateRequesterAction` (alta, edición, validación documento duplicado y soft-deletes). _(cubre R1, R3, R4, R5)_
  - [x] 4.3 Feature test: `RequesterResource` en Filament (listado, creación, edición, borrado lógico y restauración). _(cubre R1, R2, R3, R4, R5)_

---

## Matriz de Trazabilidad

| Criterio | Tareas que lo cubren |
|---|---|
| R1 (Registro) | 1.1, 1.2, 1.3, 1.4, 2.1, 2.3, 3.1, 3.2, 4.1, 4.2, 4.3 |
| R2 (Consulta / Filtros) | 1.2, 1.3, 1.4, 3.1, 3.2, 4.1, 4.3 |
| R3 (Actualización / Estado) | 1.3, 2.1, 2.4, 3.1, 3.2, 4.2, 4.3 |
| R4 (Validación Duplicados) | 1.1, 2.1, 2.2, 2.3, 2.4, 3.1, 4.2, 4.3 |
| R5 (SoftDeletes / Integridad) | 1.1, 1.3, 3.1, 3.2, 4.2, 4.3 |

## Definition of Done (DoD)

- [x] Pruebas en verde (`./vendor/bin/sail test` o `php artisan test`).
- [x] Código formateado (`./vendor/bin/sail pint` o `vendor/bin/pint`).

