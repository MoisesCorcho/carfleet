> **Estado:** Specs auditadas y refinadas  
> **ID:** F02 · **Slug:** `02-driver-management`  
> **Prerequisitos:** Fundación de dominio (`User` model, `Driver` model)

# Requirements — F02: Gestión de Conductores

## User Stories

- **US2.1**: Como administrador, quiero registrar perfiles de conductor vinculados a sus cuentas de usuario para permitirles acceder al sistema y realizar la ejecución de viajes.
- **US2.2**: Como administrador, quiero gestionar los datos personales y laborales del conductor (nombre, documento, teléfono, número de licencia, fecha de vencimiento de licencia y estado de habilitación) para garantizar que solo conductores activos reciban asignaciones.

---

## Criterios de Aceptación (Sintaxis EARS)

### Happy Path

### R1 — Registro de Conductor Vinculado a Usuario
DONDE un administrador está en el recurso de Conductores en Filament,  
CUANDO crea un conductor proporcionando usuario, nombre completo, documento, teléfono y licencia válida,  
EL SISTEMA DEBE crear el perfil de conductor, asociar el usuario  
Y marcar su estado operacional como `activo`.

### R2 — Filtro de Conductores Activos para Asignación
DONDE un administrador asigna un conductor a un viaje,  
CUANDO despliega la lista de selección de conductores,  
EL SISTEMA DEBE presentar únicamente conductores con estado `activo` y licencia vigente  
Y excluir conductores en estado `inactivo` o `suspendido`.

---

### Validación y Errores

### R3 — Rechazo de Licencia o Documento Duplicado
DONDE se registra o modifica un conductor,  
CUANDO el número de documento de identidad o número de licencia ya existe en otro registro,  
EL SISTEMA DEBE rechazar la transacción e informar el conflicto de duplicidad.

### R4 — Advertencia de Licencia de Conducir Vencida
DONDE un conductor intenta ser asignado a un viaje,  
CUANDO la fecha de vencimiento de su licencia (`license_expires_at`) es menor a la fecha actual,  
EL SISTEMA DEBE señalar la advertencia y prevenir la asignación del conductor deshabilitado.

---

## Decisiones de Producto

| ID | Pregunta / Ambivalencia | Decisión Aprobada |
|---|---|---|
| D2.1 | ¿El conductor tiene cuenta de usuario independiente? | Sí, el modelo `Driver` pertenece a un `User` (`belongsTo`). |
| D2.2 | ¿Qué estados puede tener un conductor? | Backed Enum `DriverStatusEnum`: `activo`, `inactivo`, `suspendido`. |
