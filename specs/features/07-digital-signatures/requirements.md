> **Estado:** Specs en progreso  
> **ID:** F07 · **Slug:** `07-digital-signatures`  
> **Prerequisitos:** F04 (`Trip`), F05 (`Mileage`)

# Requirements — F07: Firma Digital y Cierre de Viaje

## User Stories

- **US7.1**: Como conductor, quiero presentar un lienzo de firma digital en pantalla al solicitante del servicio para que estampé su firma de conformidad al finalizar el trayecto.
- **US7.2**: Como conductor, quiero proceder al cierre formal del viaje una vez validadas las evidencias de kilometraje inicial, kilometraje final y la firma del solicitante.
- **US7.3**: Como administrador, quiero verificar la firma digital capturada en el expediente del viaje cerrado para tener respaldo auditable del servicio.

---

## Criterios de Aceptación (Sintaxis EARS)

### Happy Path

### R1 — Captura de Firma Digital del Solicitante
DONDE un viaje se encuentra en estado `finalizado` (con kilometraje inicial y final registrados),  
CUANDO el solicitante dibuja su firma en el lienzo interactivo y se envía la confirmación,  
EL SISTEMA DEBE convertir el trazo en una imagen codificada (Base64/PNG),  
almacenarla como entidad `DigitalSignature` asociada al viaje  
Y permitir el cierre del servicio.

### R2 — Cierre Formal del Viaje
DONDE un viaje cuenta con kilometraje inicial, kilometraje final, evidencias fotográficas y firma del solicitante,  
CUANDO el conductor o administrador ejecuta la acción de cierre,  
EL SISTEMA DEBE transicionar el estado del viaje a `cerrado`,  
liberar el vehículo cambiando su estado a `disponible`  
Y marcar la fecha y hora real de finalización.

---

### Validación y Errores

### R3 — Bloqueo de Cierre Sin Firma o Evidencias
DONDE un usuario intenta cerrar un viaje,  
CUANDO falta la firma del solicitante o la fotografía del odómetro final,  
EL SISTEMA DEBE rechazar la acción de cierre e informar los requisitos faltantes.

---

## Decisiones de Producto

| ID | Pregunta / Ambivalencia | Decisión Aprobada |
|---|---|---|
| D7.1 | ¿Cómo se captura la firma en la web? | Componente de lienzo Canvas (Signature Pad en JS / Livewire / Filament Custom Form Field). |
| D7.2 | ¿Qué liberaciones ocurren al cerrar un viaje? | El viaje pasa a `cerrado` y el vehículo regresa a estado `disponible`. |
