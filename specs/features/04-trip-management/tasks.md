# Tasks — F04: Gestión y Asignación de Viajes

- [ ] 1. Base de datos y Enums
  - [x] 1.1 Crear Enum `TripStatusEnum` en `app/Enums/Trips/`. _(cubre R1, R2, R3, R5)_
  - [x] 1.2 Crear migración `create_trips_table` con llaves foráneas e índice único en `code`. _(cubre R1)_
  - [x] 1.3 Crear modelo `App\Models\Trip` con relaciones (`requester`, `vehicle`, `driver`). _(cubre R1, R2)_
  - [ ] 1.4 Crear factory `TripFactory`. _(cubre R1)_

- [ ] 2. Lógica de Dominio y Actions
  - [ ] 2.1 Crear DTO `CreateTripDTO`. _(cubre R1)_
  - [ ] 2.2 Implementar Action `CreateTripAction` con generación de código consecutivo `TRIP-YYYY-NNNN`. _(cubre R1)_
  - [ ] 2.3 Implementar Action `AssignTripResourcesAction` con validaciones de disponibilidad. _(cubre R2, R4)_
  - [ ] 2.4 Implementar Action `StartTripAction`. _(cubre R3)_

- [ ] 3. Interfaz Administrativa (Filament v4)
  - [ ] 3.1 Crear `TripResource` en Filament. _(cubre R1, R2, R3)_
  - [ ] 3.2 Implementar `TripPolicy` para restringir vista de conductores a sus viajes asignados e inmutabilidad de estados cerrados. _(cubre R3, R5)_

- [ ] 4. Pruebas Automatizadas
  - [ ] 4.1 Feature test: Creación, asignación de recursos e inicio de viaje. _(cubre R1, R2, R3)_
  - [ ] 4.2 Feature test: Rechazo de asignación de vehículo ocupado e inmutabilidad de estado cerrado. _(cubre R4, R5)_

---

## Matriz de Trazabilidad

| Criterio | Tareas que lo cubren |
|---|---|
| R1 | 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 3.1, 4.1 |
| R2 | 1.1, 1.3, 2.3, 3.1, 4.1 |
| R3 | 1.1, 2.4, 3.1, 3.2, 4.1 |
| R4 | 2.3, 4.2 |
| R5 | 1.1, 3.2, 4.2 |

## Definition of Done (DoD)

- [ ] Pruebas en verde (`./vendor/bin/sail test`).
- [ ] Estilo de código verificado (`./vendor/bin/sail pint`).
