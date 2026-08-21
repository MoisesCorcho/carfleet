# Tasks — F06: Tanqueos y Vouchers de Combustible

- [x] 1. Base de datos y Modelos
  - [x] 1.1 Crear migración `create_fuel_logs_table` con relaciones a `vehicles`, `trips` y `drivers`. _(cubre R1)_
  - [x] 1.2 Crear modelo `App\Models\FuelLog`. _(cubre R1)_
  - [x] 1.3 Crear factory `FuelLogFactory`. _(cubre R1)_

- [x] 2. Lógica de Dominio y Actions
  - [x] 2.1 Crear DTO `RegisterFuelLogDTO`. _(cubre R1, R2)_
  - [x] 2.2 Implementar Action `RegisterFuelLogAction`. _(cubre R1, R2)_

- [x] 3. Interfaz Administrativa (Filament v4)
  - [x] 3.1 Crear `FuelLogResource` y RelationManagers en `VehicleResource`/`TripResource`. _(cubre R1)_

- [x] 4. Pruebas Automatizadas
  - [x] 4.1 Feature test: Registro exitoso de tanqueo con carga de voucher. _(cubre R1)_
  - [x] 4.2 Unit test: Validación de galones positivos. _(cubre R2)_

---

## Matriz de Trazabilidad

| Criterio | Tareas que lo cubren |
|---|---|
| R1 | 1.1, 1.2, 1.3, 2.1, 2.2, 3.1, 4.1 |
| R2 | 2.1, 2.2, 4.2 |

## Definition of Done (DoD)

- [x] Pruebas pasando en verde (`./vendor/bin/sail test`).
- [x] Estilo de código verificado (`./vendor/bin/sail pint`).
