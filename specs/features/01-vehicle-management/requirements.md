> **Estado:** Completa  
> **ID:** F01 · **Slug:** `01-vehicle-management`  
> **Prerequisitos:** Fundación de dominio (`app/Models/Vehicle.php`, `database/migrations`)

# Requirements — F01: Gestión de Vehículos

## User Stories

- **US1.1**: Como administrador, quiero registrar vehículos en la flota indicando su placa (estándar colombiano), tipo de servicio (público/particular), tipo de carrocería, marca, modelo, año, tipo de combustible, kilometraje inicial y estado inicial, para mantener un control centralizado de los recursos de transporte en Colombia.
- **US1.2**: Como administrador, quiero consultar la lista de vehículos con su estado de disponibilidad (Disponible, Asignado, En Viaje, En Mantenimiento, Fuera de Servicio) y tipo de servicio para poder asignar únicamente vehículos aptos a los servicios de transporte.
- **US1.3**: Como administrador, quiero actualizar el estado o información de un vehículo cuando entra a mantenimiento o cambia de disponibilidad operacional, manteniendo protegido el odómetro contra modificaciones arbitrarias.

---

## Criterios de Aceptación (Sintaxis EARS)

### Happy Path

### R1 — Registro de Vehículo Nuevo
DONDE un administrador autenticado navega al recurso de Vehículos en Filament,  
CUANDO envía el formulario con datos válidos (placa única formato Colombia, tipo de servicio, carrocería, marca, modelo, año, kilometraje inicial ≥ 0, tipo de combustible),  
EL SISTEMA DEBE persistir el vehículo con el estado inicial `disponible`  
Y mostrar una notificación de confirmación en la interfaz.

### R2 — Consulta de Disponibilidad de Vehículos
DONDE un administrador consulta el listado o selector de vehículos para asignar a un viaje,  
CUANDO filtra por estado `disponible`, tipo de carrocería o tipo de servicio,  
EL SISTEMA DEBE mostrar únicamente los vehículos que coincidan con los filtros aplicados  
Y ocultar o deshabilitar aquellos en estado `en_viaje`, `mantenimiento` o `fuera_de_servicio`.

### R3 — Actualización de Kilometraje por Eventos Operativos
DONDE un viaje o registro de mantenimiento modifica el odómetro del vehículo,  
CUANDO se registra un nuevo kilometraje mayor o igual al actual,  
EL SISTEMA DEBE actualizar la lectura del odómetro en la entidad del vehículo.

---

### Validación y Errores

### R4 — Rechazo de Placa Duplicada
DONDE un administrador intenta registrar o editar un vehículo,  
CUANDO ingresa una placa o identificador que ya existe en la base de datos,  
EL SISTEMA DEBE rechazar la operación, señalar el error en el campo de la placa  
Y prevenir la duplicidad de registros.

### R5 — Invariante de Kilometraje No Decreciente
DONDE se intenta actualizar el kilometraje de un vehículo,  
CUANDO el valor ingresado es menor al kilometraje actual registrado,  
EL SISTEMA DEBE rechazar el registro y notificar que el kilometraje no puede ser menor a la lectura previa.

### R6 — Validación de Odómetro Inicial No Negativo
DONDE un administrador registra un nuevo vehículo,  
CUANDO ingresa un kilometraje inicial menor a 0,  
EL SISTEMA DEBE rechazar el formulario e indicar que el odómetro debe ser un entero mayor o igual a cero.

### R7 — Validación de Formato de Placa Colombiana
DONDE un administrador ingresa la placa de un vehículo,  
CUANDO el formato no corresponde a la nomenclatura estándar colombiana (`AAA-123` o `AAA123`),  
EL SISTEMA DEBE rechazar el formulario e indicar el formato correcto.

### R8 — Protección del Odómetro en Pantallas de Edición
DONDE un administrador edita los datos generales de un vehículo,  
EL SISTEMA DEBE deshabilitar la edición manual directa del campo de odómetro (`current_mileage`)  
Y preservar la lectura acumulada por viajes y mantenimientos.

---

## Decisiones de Producto

| ID | Pregunta / Ambivalencia | Decisión Aprobada |
|---|---|---|
| D1.1 | ¿Se requiere GPS en los vehículos? | No. La trazabilidad se realiza estrictamente por odómetro y evidencias fotográficas. |
| D1.2 | ¿Qué estados de vehículo son válidos? | Backed Enum `VehicleStatusEnum`: `disponible`, `asignado`, `en_viaje`, `mantenimiento`, `fuera_de_servicio`. |
| D1.3 | ¿Se almacena tipo de combustible predeterminado? | Sí (`gasolina`, `diesel`, `gas`, `electrico`), mediante `FuelTypeEnum`. |
| D1.4 | ¿Qué tipos de servicio operan en Colombia? | `ServiceTypeEnum`: `publico` (Placa Blanca) y `particular` (Placa Amarilla). |
| D1.5 | ¿Qué tipos de carrocería se soportan? | `VehicleTypeEnum`: `automovil`, `camioneta`, `van`, `microbus`, `buseta`, `camion`, `furgon`, `tractocamion`. |
| D1.6 | ¿Cómo se gestiona el borrado de vehículos? | Mediante `SoftDeletes` para preservar la trazabilidad histórica de viajes realizados. |
