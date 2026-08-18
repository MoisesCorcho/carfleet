> **Estado:** Completa  
> **ID:** F03 · **Slug:** `03-requester-management`  
> **Prerequisitos:** Fundación de dominio (`database/migrations`)

# Requirements — F03: Gestión de Solicitantes

## User Stories

- **US3.1**: Como administrador, quiero registrar y gestionar a las personas naturales o empresas que solicitan servicios de transporte (solicitantes) con su tipo y número de documento colombiano, razón social o nombre de contacto, teléfono y correo electrónico, para poder vincularlas formalmente a las órdenes de viaje y facturación comercial.
- **US3.2**: Como administrador, quiero consultar, filtrar por estado y actualizar los datos de los solicitantes garantizando la trazabilidad histórica de los servicios prestados.

---

## Criterios de Aceptación (Sintaxis EARS)

### Happy Path

### R1 — Registro de Solicitante de Servicio
DONDE un administrador autenticado navega a la sección de Solicitantes en Filament,  
CUANDO envía el formulario ingresando nombre de contacto, razón social (opcional si es persona natural), tipo de documento (`NIT`, `CC`, `CE`, `PA`, `PPT`, `PEP`), número de documento único para ese tipo, teléfono válido, correo electrónico opcional y notas,  
EL SISTEMA DEBE persistir el solicitante en la base de datos  
Y ponerlo a disposición inmediata para la creación y despacho de viajes.

### R2 — Consulta, Filtrado y Búsqueda de Solicitantes
DONDE un administrador consulta el listado de solicitantes en el panel,  
CUANDO aplica filtros por estado (`activo`, `inactivo`), tipo de documento o papelera (`SoftDeletes`),  
EL SISTEMA DEBE listar únicamente los registros correspondientes  
Y permitir la búsqueda rápida por nombre, razón social, número de documento, teléfono o correo.

### R3 — Actualización de Perfil y Estado Operativo
DONDE un administrador edita un solicitante existente,  
CUANDO modifica sus datos de contacto, razón social, notas o cambia su estado de habilitación (`is_active`),  
EL SISTEMA DEBE actualizar el registro  
Y reflejar los cambios en el panel administrativo.

---

### Validación y Errores

### R4 — Validación de Documento/NIT Único Compuesto
DONDE se intenta registrar o actualizar un solicitante,  
CUANDO la combinación de `document_type` y `document_number` ya existe en otro registro no eliminado,  
EL SISTEMA DEBE rechazar la operación por duplicidad de identificación  
Y notificar al usuario en el formulario.

### R5 — Protección de Integridad Referencial Histórica (SoftDeletes)
DONDE se ejecuta la acción de eliminación de un solicitante que posee viajes o facturas históricas asociadas,  
CUANDO se procesa la solicitud en el panel,  
EL SISTEMA DEBE aplicar borrado lógico (`SoftDeletes`)  
Y preservar la integridad de los viajes y facturas pasadas.

---

## Decisiones de Producto

| ID | Pregunta / Ambivalencia | Decisión Aprobada |
|---|---|---|
| D3.1 | ¿El solicitante interactúa directamente en el prototipo con login propio? | No. En el prototipo el solicitante es una entidad de referencia seleccionada por el administrador y que estampará su firma digital al finalizar el servicio. |
| D3.2 | ¿Cómo se estructura la identificación para personas naturales y jurídicas? | Mediante `document_type` (`RequesterDocumentTypeEnum`: `NIT`, `CC`, `CE`, `PA`, `PPT`, `PEP`) y `document_number` (string normalizado), con índice único compuesto `['document_type', 'document_number']`. |
| D3.3 | ¿Cómo se diferencian el contacto físico y la razón social? | `company_name` almacena la Razón Social (para facturación F09) y `name` el Nombre de Contacto/Solicitante (quien firma en campo F07). Si es persona natural, `company_name` puede omitirse. |
| D3.4 | ¿Cómo se controla la habilitación de un solicitante para nuevos viajes? | Mediante un campo booleano `is_active` (default `true`) y el scope de dominio `scopeActive()`. |
| D3.5 | ¿Cómo se preserva la trazabilidad histórica? | Implementando `SoftDeletes` en base de datos, modelo y panel Filament (`TrashedFilter`, `RestoreAction`). |
