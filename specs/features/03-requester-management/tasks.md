# Tasks — F03: Gestión de Solicitantes

- [ ] 1. Base de datos y Modelos
  - [ ] 1.1 Crear migración `create_requesters_table` con índice único en `document_number`. _(cubre R1, R2)_
  - [ ] 1.2 Crear modelo `App\Models\Requester`. _(cubre R1)_
  - [ ] 1.3 Crear factory `RequesterFactory`. _(cubre R1)_

- [ ] 2. Lógica de Dominio y Actions
  - [ ] 2.1 Crear DTO `UpsertRequesterDTO`. _(cubre R1, R2)_
  - [ ] 2.2 Implementar Action `RegisterRequesterAction`. _(cubre R1, R2)_

- [ ] 3. Interfaz Administrativa (Filament v4)
  - [ ] 3.1 Crear `RequesterResource` en Filament. _(cubre R1)_

- [ ] 4. Pruebas Automatizadas
  - [ ] 4.1 Feature test: Alta exitosa de solicitante y rechazo de documento duplicado. _(cubre R1, R2)_

---

## Matriz de Trazabilidad

| Criterio | Tareas que lo cubren |
|---|---|
| R1 | 1.1, 1.2, 1.3, 2.1, 2.2, 3.1, 4.1 |
| R2 | 1.1, 2.1, 2.2, 4.1 |

## Definition of Done (DoD)

- [ ] Pruebas en verde (`./vendor/bin/sail test`).
- [ ] Código formateado (`./vendor/bin/sail pint`).
