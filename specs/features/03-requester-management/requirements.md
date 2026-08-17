> **Estado:** Specs en progreso  
> **ID:** F03 · **Slug:** `03-requester-management`  
> **Prerequisitos:** Fundación de dominio

# Requirements — F03: Gestión de Solicitantes

## User Stories

- **US3.1**: Como administrador, quiero registrar a las personas o empresas que solicitan servicios de transporte (solicitantes) con su información básica de contacto, para poder vincularlas formalmente a las órdenes de viaje.

---

## Criterios de Aceptación (Sintaxis EARS)

### Happy Path

### R1 — Registro de Solicitante de Servicio
DONDE un administrador está en la sección de Solicitantes en Filament,  
CUANDO crea un nuevo solicitante ingresando nombre o razón social, número de identificación/NIT, teléfono y correo electrónico,  
EL SISTEMA DEBE almacenar el registro  
Y ponerlo a disposición para ser seleccionado en la creación de viajes.

---

### Validación y Errores

### R2 — Validación de Documento/NIT Único
DONDE un administrador intenta registrar un solicitante,  
CUANDO ingresa un documento/NIT ya existente en el sistema,  
EL SISTEMA DEBE rechazar el registro por duplicidad de identificación.

---

## Decisiones de Producto

| ID | Pregunta / Ambivalencia | Decisión Aprobada |
|---|---|---|
| D3.1 | ¿El solicitante interactúa directamente en el prototipo con login propio? | No. En el prototipo el solicitante es una entidad de referencia seleccionada por el administrador y que estampará su firma digital al finalizar el servicio. |
