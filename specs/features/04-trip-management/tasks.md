# Tasks — F04: Gestión y Asignación de Viajes

- [x] 1. Base de datos y Enums
  - [x] 1.1 Crear Enum `TripStatusEnum` en `app/Enums/Trips/`. _(cubre R1, R2, R3, R5, R6)_
  - [x] 1.2 Crear migración `create_trips_table` con llaves foráneas e índice único en `code`. _(cubre R1)_
  - [x] 1.3 Crear modelo `App\Models\Trip` con relaciones (`requester`, `vehicle`, `driver`). _(cubre R1, R2)_
  - [x] 1.4 Crear factory `TripFactory`. _(cubre R1)_

- [x] 2. Lógica de Dominio, Excepciones y Actions
  - [x] 2.1 Crear Excepciones de Dominio en `app/Exceptions/Trips/` (`VehicleNotAvailableException`, `DriverNotEligibleException`, `TripImmutableException`, `InvalidTripDatesException`). _(cubre R4, R5, R8)_
  - [x] 2.2 Crear DTO `CreateTripDTO`. _(cubre R1, D4.7)_
  - [x] 2.3 Implementar Action `CreateTripAction` con generación concurrente segura de código `TRIP-YYYY-NNNN`. _(cubre R1, D4.3, D4.7)_
  - [x] 2.4 Implementar Action `AssignTripResourcesAction` con validación de vehículo disponible, elegibilidad de conductor, tipo de servicio público y liberación de vehículo previo. _(cubre R2, R4, R7, R8)_
  - [x] 2.5 Implementar Action `StartTripAction`. _(cubre R3)_
  - [x] 2.6 Implementar Action `CancelTripAction` con liberación atómica de vehículo. _(cubre R6, D4.5)_

- [x] 3. Arquitectura Multi-Panel e Interfaz Filament v4
  - [x] 3.1 Configurar `DriverPanelProvider` (`/driver`) y restringir `AdminPanelProvider` (`/admin`) en `User::canAccessPanel`. _(cubre D4.4)_
  - [x] 3.2 Implementar `TripPolicy` para control de acceso y bloqueo de mutación en estados cerrados/cancelados. _(cubre R3, R5)_
  - [x] 3.3 Crear `TripResource` administrativo en `App\Filament\Resources\Trips\` con acciones de asignación y cancelación. _(cubre R1, R2, R6, R7)_
  - [x] 3.4 Crear `AssignedTripResource` en `App\Filament\Driver\Resources\Trips\` optimizado para dispositivos móviles con acción de inicio de viaje. _(cubre R3, D4.4)_

- [x] 4. Pruebas Automatizadas
  - [x] 4.1 Feature test: Creación, asignación de recursos, inicio y cancelación de viaje (`TripManagementTest`). _(cubre R1, R2, R3, R6, R7)_
  - [x] 4.2 Feature test: Rechazo de asignación de vehículo ocupado, chofer no apto e inmutabilidad de estado cerrado (`TripValidationTest`). _(cubre R4, R5, R8)_
  - [x] 4.3 Feature test: Aislamiento de paneles y flujo de conductor en `/driver` (`DriverPanelTripTest`). _(cubre R3, D4.2, D4.4)_

---

## Matriz de Trazabilidad

| Criterio | Tareas que lo cubren |
|---|---|
| R1 | 1.1, 1.2, 1.3, 1.4, 2.2, 2.3, 3.3, 4.1 |
| R2 | 1.1, 1.3, 2.4, 3.3, 4.1 |
| R3 | 1.1, 2.5, 3.1, 3.2, 3.4, 4.1, 4.3 |
| R4 | 2.1, 2.4, 4.2 |
| R5 | 1.1, 2.1, 3.2, 4.2 |
| R6 | 1.1, 2.6, 3.3, 4.1 |
| R7 | 2.4, 3.3, 4.1 |
| R8 | 2.1, 2.4, 4.2 |

## Definition of Done (DoD)

- [x] Pruebas en verde (`./vendor/bin/sail test`).
- [x] Estilo de código verificado (`./vendor/bin/sail pint`).


