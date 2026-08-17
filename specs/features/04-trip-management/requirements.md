> **Estado:** Specs auditadas y refinadas  
> **ID:** F04 · **Slug:** `04-trip-management`  
> **Prerequisitos:** F01 (`Vehicle`), F02 (`Driver`), F03 (`Requester`)

# Requirements — F04: Gestión y Asignación de Viajes

## User Stories

- **US4.1**: Como administrador, quiero crear una solicitud de viaje indicando el solicitante, origen, destino y fecha/hora programada para iniciar el ciclo de vida del servicio con un consecutivo único (`TRIP-2026-0001`).
- **US4.2**: Como administrador, quiero asignar un vehículo disponible y un conductor activo al viaje, cambiando su estado de `programado` a `asignado`.
- **US4.3**: Como conductor, quiero consultar únicamente los viajes que me han sido asignados y ver el detalle (origen, destino, solicitante, vehículo) para iniciar el servicio en campo.

---

## Criterios de Aceptación (Sintaxis EARS)

### Happy Path

### R1 — Creación de Viaje Programado
DONDE un administrador está en la sección de Viajes en Filament,  
CUANDO crea un viaje seleccionando el solicitante, ingresando origen, destino y la fecha/hora programada de salida,  
EL SISTEMA DEBE generar automáticamente el código único de viaje (`TRIP-YYYY-NNNN`), registrar el viaje con estado `programado`  
Y permitir la posterior asignación de recursos.

### R2 — Asignación de Recursos (Vehículo y Conductor)
DONDE un viaje se encuentra en estado `programado`,  
CUANDO el administrador asigna un vehículo en estado `disponible` y un conductor en estado `activo` con licencia vigente,  
EL SISTEMA DEBE asociar ambos recursos al viaje,  
transicionar el estado del viaje a `asignado`  
Y cambiar el estado del vehículo a `asignado`.

### R3 — Inicio del Servicio por el Conductor
DONDE un conductor autenticado consulta sus viajes asignados en el panel,  
CUANDO selecciona un viaje en estado `asignado` e inicia la salida,  
EL SISTEMA DEBE cambiar el estado del viaje a `en_curso`,  
marcar la fecha y hora real de salida  
Y cambiar el estado del vehículo a `en_viaje`.

---

### Validación y Errores

### R4 — Impedir Asignación de Vehículo No Disponible
DONDE un administrador intenta asignar un vehículo a un nuevo viaje,  
CUANDO el vehículo seleccionado está en estado `en_viaje`, `mantenimiento` o `fuera_de_servicio`,  
EL SISTEMA DEBE rechazar la asignación e informar que el vehículo no está disponible.

### R5 — Invariabilidad de Estado de Viajes Cerrados o Cancelados
DONDE un viaje se encuentra en estado `cerrado` o `cancelado`,  
CUANDO se intenta revertir su estado o modificar sus asignaciones de recursos principales,  
EL SISTEMA DEBE rechazar la mutación para preservar la integridad histórica del servicio.

---

## Decisiones de Producto

| ID | Pregunta / Ambivalencia | Decisión Aprobada |
|---|---|---|
| D4.1 | ¿Cuáles son los estados del ciclo de vida del viaje? | Backed Enum `TripStatusEnum`: `programado`, `asignado`, `en_curso`, `finalizado`, `cerrado`, `cancelado`. |
| D4.2 | ¿El conductor ve viajes asignados a otros conductores? | No. La Policy de Filament restringe las consultas del conductor a sus propios registros (`driver_id == current_user->driver->id`). |
| D4.3 | ¿Cómo se genera el código del viaje? | Formato secuencial anual automático: `TRIP-YYYY-NNNN`. |
