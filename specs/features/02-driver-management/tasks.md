# Tasks — F02: Gestión de Conductores

- [x] 1. Base de datos, Modelo y Enums
  - [x] 1.1 Validar Enum `DriverStatusEnum` en `app/Enums/Drivers/`. _(cubre R1, R2)_
  - [x] 1.2 Validar migración `create_drivers_table` con índices únicos en `document_number`, `license_number` y `user_id`. _(cubre R1, R4, R5)_
  - [x] 1.3 Actualizar modelo `App\Models\Driver` con scopes (`active`, `eligibleForTrip`), helper methods (`isActive`, `hasValidLicense`, `isEligibleForTrip`) y casts. _(cubre R1, R2, R6)_
  - [x] 1.4 Crear factory `DriverFactory` con states `active`, `inactive`, `suspended`, `expiredLicense` y `validLicense`. _(cubre R1, R6)_
  - [x] 1.5 Registrar permisos para la entidad `Driver` en `RoleSeeder` y crear `DriverPolicy`. _(cubre R1, R2, R3)_

- [x] 2. Lógica de Dominio y Actions
  - [x] 2.1 Crear excepción de dominio `InvalidDriverException` en `app/Exceptions/Drivers/`. _(cubre R4, R5)_
  - [x] 2.2 Crear DTO `UpsertDriverDTO` en `app/DTOs/Drivers/`. _(cubre R1, R3, R4, R5)_
  - [x] 2.3 Implementar Action `RegisterDriverAction` con transacción y validación de unicidad de usuario. _(cubre R1, R4, R5)_
  - [x] 2.4 Implementar Action `UpdateDriverAction` con transacción para actualización de datos. _(cubre R3, R4)_

- [x] 3. Interfaz Administrativa (Filament v4)
  - [x] 3.1 Crear `DriverResource` con Form Schema responsivo y Table en `app/Filament/Resources/Drivers/`. _(cubre R1, R2, R3, R6)_
  - [x] 3.2 Implementar páginas `ListDrivers`, `CreateDriver`, `EditDriver` y `ViewDriver` integrando Actions y DTOs. _(cubre R1, R2, R3)_
  - [x] 3.3 Añadir filtros de disponibilidad por estado operacional y vigencia de licencia. _(cubre R2, R6)_

- [x] 4. Pruebas Automatizadas
  - [x] 4.1 Unit Tests: `DriverActionTest` para `RegisterDriverAction`, `UpdateDriverAction`, DTOs y scopes de elegibilidad. _(cubre R1, R3, R5, R6)_
  - [x] 4.2 Feature Tests: `DriverManagementTest` para operaciones del panel Filament (List, Create, Edit, View, Filtros, Unicidad de campos y Autorización). _(cubre R1, R2, R3, R4, R5, R6)_

---

## Matriz de Trazabilidad

| Criterio | Tareas que lo cubren |
|---|---|
| R1 | 1.1, 1.2, 1.3, 1.4, 1.5, 2.2, 2.3, 3.1, 3.2, 4.1, 4.2 |
| R2 | 1.1, 1.3, 1.5, 3.1, 3.2, 3.3, 4.2 |
| R3 | 1.5, 2.2, 2.4, 3.1, 3.2, 4.1, 4.2 |
| R4 | 1.2, 2.1, 2.2, 2.3, 2.4, 3.1, 4.2 |
| R5 | 1.2, 2.1, 2.2, 2.3, 4.1, 4.2 |
| R6 | 1.3, 1.4, 3.1, 3.3, 4.1, 4.2 |

## Definition of Done (DoD)

- [x] Todos los criterios R1..R6 cubiertos por pruebas pasando en verde (`./vendor/bin/sail test`).
- [x] Código formateado con `./vendor/bin/sail pint`.
