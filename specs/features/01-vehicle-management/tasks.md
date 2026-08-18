# Tasks — F01: Gestión de Vehículos

- [x] 1. Base de datos y Enums
  - [x] 1.1 Crear Enums `VehicleStatusEnum` y `FuelTypeEnum` en `app/Enums/Vehicles/`. _(cubre R1, R2)_
  - [x] 1.2 Crear migración `create_vehicles_table` con índices únicos en `plate_number`. _(cubre R1, R4)_
  - [x] 1.3 Crear modelo `App\Models\Vehicle` con casts y relaciones. _(cubre R1, R3)_
  - [x] 1.4 Crear factory `VehicleFactory` para pruebas. _(cubre R1)_

- [x] 2. Lógica de Dominio y Actions
  - [x] 2.1 Crear DTO `UpsertVehicleDTO`. _(cubre R1, R4, R6)_
  - [x] 2.2 Implementar Action `RegisterVehicleAction` con validación de kilometraje no decreciente y no negativo. _(cubre R1, R3, R5, R6)_
  - [x] 2.3 Implementar Action `UpdateVehicleMileageAction`. _(cubre R3, R5)_

- [x] 3. Interfaz Administrativa (Filament v4)
  - [x] 3.1 Generar `VehicleResource` con Form Schema y Table. _(cubre R1, R2)_
  - [x] 3.2 Añadir filtros de disponibilidad por `status`. _(cubre R2)_

- [x] 4. Pruebas Automatizadas
  - [x] 4.1 Feature test: Registro exitoso de vehículo e inmutabilidad de placa única. _(cubre R1, R4)_
  - [x] 4.2 Unit test: Validación de kilometraje no decreciente y no negativo. _(cubre R3, R5, R6)_

---

## Matriz de Trazabilidad

| Criterio | Tareas que lo cubren |
|---|---|
| R1 | 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 3.1, 4.1 |
| R2 | 1.1, 3.1, 3.2 |
| R3 | 1.3, 2.2, 2.3, 4.2 |
| R4 | 1.2, 2.1, 4.1 |
| R5 | 2.2, 2.3, 4.2 |
| R6 | 2.1, 2.2, 4.2 |

## Definition of Done (DoD)

- [x] Todos los criterios R1..R6 cubiertos por pruebas pasando en verde (`./vendor/bin/sail test`).
- [x] Código formateado con `./vendor/bin/sail pint`.
