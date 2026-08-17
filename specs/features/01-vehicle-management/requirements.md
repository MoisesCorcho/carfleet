> **Estado:** Specs auditadas y refinadas  
> **ID:** F01 · **Slug:** `01-vehicle-management`  
> **Prerequisitos:** Fundación de dominio (`app/Models/Vehicle.php`, `database/migrations`)

# Requirements — F01: Gestión de Vehículos

## User Stories

- **US1.1**: Como administrador, quiero registrar vehículos en la flota indicando su placa/identificador, marca, modelo, año, tipo de combustible, kilometraje actual y estado inicial, para mantener un control centralizado de los recursos de transporte.
- **US1.2**: Como administrador, quiero consultar la lista de vehículos con su estado de disponibilidad (Disponible, Asignado, En Viaje, En Mantenimiento, Fuera de Servicio) para poder asignar únicamente vehículos aptos a los servicios.
- **US1.3**: Como administrador, quiero actualizar el estado o información de un vehículo cuando entra a mantenimiento o cambia de disponibilidad operacional.

---

## Criterios de Aceptación (Sintaxis EARS)

### Happy Path

### R1 — Registro de Vehículo Nuevo
DONDE un administrador autenticado navega al recurso de Vehículos en Filament,  
CUANDO envía el formulario con datos válidos (placa única, marca, modelo, año, kilometraje inicial ≥ 0, tipo de combustible),  
EL SISTEMA DEBE persistir el vehículo con el estado inicial `disponible`  
Y mostrar una notificación de confirmación en la interfaz.

### R2 — Consulta de Disponibilidad de Vehículos
DONDE un administrador consulta el listado o selector de vehículos para asignar a un viaje,  
CUANDO filtra por estado `disponible`,  
EL SISTEMA DEBE mostrar únicamente los vehículos cuyo estado operacional sea `disponible`  
Y ocultar o deshabilitar aquellos en estado `en_viaje`, `mantenimiento` o `fuera_de_servicio`.

### R3 — Actualización de Kilometraje Actual
DONDE un viaje o registro de mantenimiento modifica el odómetro del vehículo,  
CUANDO se registra un nuevo kilometraje mayor al kilometraje actual guardado,  
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

---

## Decisiones de Producto

| ID | Pregunta / Ambivalencia | Decisión Aprobada |
|---|---|---|
| D1.1 | ¿Se requiere GPS en los vehículos? | No. La trazabilidad se realiza estrictamente por odómetro y evidencias fotográficas. |
| D1.2 | ¿Qué estados de vehículo son válidos? | Backed Enum `VehicleStatusEnum`: `disponible`, `asignado`, `en_viaje`, `mantenimiento`, `fuera_de_servicio`. |
| D1.3 | ¿Se almacena tipo de combustible predeterminado? | Sí (`gasolina`, `diesel`, `gas`, `electrico`), mediante `FuelTypeEnum`. |
