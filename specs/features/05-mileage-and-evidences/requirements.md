> **Estado:** Specs en progreso  
> **ID:** F05 · **Slug:** `05-mileage-and-evidences`  
> **Prerequisitos:** F04 (`Trip`)

# Requirements — F05: Kilometraje y Evidencias Fotográficas

## User Stories

- **US5.1**: Como conductor, quiero registrar el kilometraje inicial al iniciar el viaje y adjuntar una foto del odómetro como evidencia para justificar el estado de salida del vehículo.
- **US5.2**: Como conductor, quiero registrar el kilometraje final al concluir el recorrido y adjuntar la foto correspondiente del odómetro para permitir el cálculo automático de los kilómetros recorridos.
- **US5.3**: Como administrador, quiero consultar el registro numérico de kilometrajes y sus evidencias fotográficas en el detalle del viaje para verificar la autenticidad del servicio.

---

## Criterios de Aceptación (Sintaxis EARS)

### Happy Path

### R1 — Registro de Kilometraje Inicial y Evidencia
DONDE un conductor está ejecutando un viaje en estado `asignado`,  
CUANDO ingresa el kilometraje inicial (≥ al kilometraje actual del vehículo) y adjunta la fotografía del odómetro,  
EL SISTEMA DEBE registrar la lectura de salida, almacenar la imagen en el disco persistente  
Y actualizar el odómetro del vehículo.

### R2 — Registro de Kilometraje Final y Cálculo de Distancia
DONDE un viaje se encuentra en estado `en_curso`,  
CUANDO el conductor ingresa el kilometraje final (> kilometraje inicial) y adjunta la fotografía del odómetro de llegada,  
EL SISTEMA DEBE calcular automáticamente los `kilometros_recorridos` (`final_km - initial_km`),  
transicionar el viaje al estado `finalizado`  
Y actualizar el kilometraje actual del vehículo con la lectura final.

---

### Validación y Errores

### R3 — Validación de Kilometraje Final Superior al Inicial
DONDE un conductor registra el kilometraje de llegada,  
CUANDO ingresa un valor menor o igual al kilometraje de salida registrado,  
EL SISTEMA DEBE rechazar la operación e indicar que el kilometraje final debe ser estrictamente mayor al de salida.

---

## Decisiones de Producto

| ID | Pregunta / Ambivalencia | Decisión Aprobada |
|---|---|---|
| D5.1 | ¿Dónde se almacenan las imágenes de evidencia? | Disco local/privado de Laravel (`storage/app/public/evidences`) expuesto mediante `storage:link`. |
| D5.2 | ¿Qué tipos de evidencia existen? | Backed Enum `EvidenceTypeEnum`: `kilometraje_salida`, `kilometraje_llegada`, `voucher_combustible`. |
