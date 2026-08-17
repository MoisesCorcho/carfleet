# Tasks — F02: Gestión de Conductores

- [ ] 1. Base de datos y Enums
  - [x] 1.1 Crear Enum `DriverStatusEnum` en `app/Enums/Drivers/`. _(cubre R1, R2)_
  - [x] 1.2 Crear migración `create_drivers_table` con índices únicos en `document_number` y `license_number`. _(cubre R1, R3)_
  - [x] 1.3 Crear modelo `App\Models\Driver` y establecer relación `belongsTo(User::class)`. _(cubre R1)_
  - [ ] 1.4 Crear factory `DriverFactory`. _(cubre R1)_

- [ ] 2. Lógica de Dominio y Actions
  - [ ] 2.1 Crear DTO `RegisterDriverDTO`. _(cubre R1, R3)_
  - [ ] 2.2 Implementar Action `RegisterDriverAction` con validación de vigencia de licencia. _(cubre R1, R3, R4)_

- [ ] 3. Interfaz Administrativa (Filament v4)
  - [ ] 3.1 Crear `DriverResource` en Filament. _(cubre R1, R2, R4)_

- [ ] 4. Pruebas Automatizadas
  - [ ] 4.1 Feature test: Alta de conductor e inmutabilidad de documento/licencia duplicada. _(cubre R1, R3)_
  - [ ] 4.2 Feature test: Rechazo de asignación por licencia vencida. _(cubre R4)_

---

## Matriz de Trazabilidad

| Criterio | Tareas que lo cubren |
|---|---|
| R1 | 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 3.1, 4.1 |
| R2 | 1.1, 3.1 |
| R3 | 1.2, 2.1, 2.2, 4.1 |
| R4 | 2.2, 3.1, 4.2 |

## Definition of Done (DoD)

- [ ] Todos los criterios R1..R4 pasando en verde (`./vendor/bin/sail test`).
- [ ] Código verificado con `./vendor/bin/sail pint`.
