> **Estado:** Completa  
> **ID:** F02 · **Slug:** `02-driver-management`  
> **Prerequisitos:** Fundación de dominio (`User` model, `database/migrations`)

# Requirements — F02: Gestión de Conductores

## User Stories

- **US2.1**: Como administrador, quiero registrar perfiles de conductor vinculados a sus cuentas de usuario para permitirles acceder al sistema y realizar la ejecución y reporte de viajes.
- **US2.2**: Como administrador, quiero gestionar los datos personales y laborales del conductor (nombre completo, tipo y número de documento, teléfono, número de licencia, fecha de vencimiento de licencia y estado operacional) para garantizar la trazabilidad legal y operacional en Colombia.
- **US2.3**: Como administrador, quiero filtrar y consultar conductores según su disponibilidad, tipo de documento y vigencia de licencia para asignar únicamente personal habilitado a los servicios de transporte.

---

## Criterios de Aceptación (Sintaxis EARS)

### Happy Path

### R1 — Registro de Conductor Vinculado a Usuario
DONDE un administrador autenticado navega al recurso de Conductores en Filament,  
CUANDO envía el formulario con datos válidos (usuario asociado no vinculado previamente, nombre completo, tipo de documento colombiano, número de documento único para ese tipo, teléfono con formato válido, número de licencia único, fecha de vencimiento de licencia opcional y estado),  
EL SISTEMA DEBE persistir el perfil del conductor asociado al usuario,  
Y mostrar una notificación de confirmación en la interfaz.

### R2 — Consulta y Filtrado de Disponibilidad de Conductores
DONDE un administrador consulta el listado de conductores en el panel administrativo,  
CUANDO aplica filtros por estado operacional (`activo`, `inactivo`, `suspendido`) o tipo de documento (`CC`, `CE`, `PA`, `PPT`, `PEP`),  
EL SISTEMA DEBE mostrar únicamente los registros que coincidan con los filtros seleccionados  
Y permitir la búsqueda por nombre, documento de identidad y número de licencia.

### R3 — Actualización de Perfil y Estado de Conductor
DONDE un administrador edita los datos de un conductor existente,  
CUANDO modifica información personal, teléfono, tipo o número de documento, vigencia de licencia o cambia su estado (ej. de `activo` a `suspendido` o `inactivo`),  
EL SISTEMA DEBE actualizar los datos del conductor en la base de datos  
Y reflejar los cambios en el panel.

---

### Validación y Errores

### R4 — Rechazo de Documento o Licencia Duplicada
DONDE se intenta registrar o actualizar un conductor,  
CUANDO la combinación de tipo y número de documento, o el número de licencia ya pertenece a otro conductor registrado,  
EL SISTEMA DEBE rechazar la operación, señalar el error en el campo correspondiente  
Y prevenir la duplicidad de registros.

### R5 — Rechazo de Usuario con Perfil de Conductor Existente
DONDE un administrador intenta registrar un nuevo perfil de conductor,  
CUANDO selecciona un usuario que ya cuenta con un perfil de conductor asociado,  
EL SISTEMA DEBE rechazar la creación e indicar que el usuario ya tiene un conductor registrado.

### R6 — Validación de Licencia Vencida
DONDE se consulta la elegibilidad de un conductor o se evalúa su habilitación,  
CUANDO la fecha de vencimiento de su licencia (`license_expires_at`) es anterior a la fecha actual,  
EL SISTEMA DEBE identificar la licencia como vencida mediante indicadores visuales en el panel y métodos de dominio.

---

## Decisiones de Producto

| ID | Pregunta / Ambivalencia | Decisión Aprobada |
|---|---|---|
| D2.1 | ¿El conductor tiene cuenta de usuario independiente? | Sí, el modelo `Driver` pertenece a un `User` (`belongsTo`) con relación 1:1 única (`user_id` unique). |
| D2.2 | ¿Qué estados operacionales puede tener un conductor? | Backed Enum `DriverStatusEnum`: `activo` (verde), `inactivo` (gris), `suspendido` (rojo). |
| D2.3 | ¿Es obligatoria la fecha de vencimiento de licencia? | Es opcional en el registro inicial, pero si se provee debe validarse su vigencia. |
| D2.4 | ¿Cómo se previene asignar conductores no aptos? | A través de scopes y métodos de verificación en el modelo (`isActive()`, `hasValidLicense()`, `isEligibleForTrip()`). |
| D2.5 | ¿Cómo se estructuran los documentos de identidad para Colombia? | Separados en `document_type` (`DocumentTypeEnum`: CC, CE, PA, PPT, PEP) y `document_number` (String), con índice único compuesto `['document_type', 'document_number']`. |
