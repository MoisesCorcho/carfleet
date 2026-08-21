> **Estado:** Completado  
> **ID:** F06 · **Slug:** `06-fuel-and-vouchers`  
> **Prerequisitos:** F01 (`Vehicle`), F04 (`Trip`)

# Requirements — F06: Tanqueos y Vouchers de Combustible

## User Stories

- **US6.1**: Como conductor, quiero registrar los abastecimientos de combustible realizados durante el servicio (fecha, cantidad de galones, monto, odómetro en el tanqueo y número de comprobante) para mantener un control de consumo.
- **US6.2**: Como conductor, quiero subir la foto del voucher de combustible pagado con la gasolinera convenio como soporte digital de la transacción.
- **US6.3**: Como administrador, quiero consultar los tanqueos asociados a cada vehículo y viaje para auditoría de costos y cálculo de rendimiento.

---

## Criterios de Aceptación (Sintaxis EARS)

### Happy Path

### R1 — Registro de Abastecimiento de Combustible
DONDE un conductor o administrador está registrando información de combustible para un viaje o vehículo,  
CUANDO ingresa la fecha, galones (decimates > 0), monto en dinero, kilometraje al momento del tanqueo y adjunta la foto del voucher,  
EL SISTEMA DEBE guardar el registro de tanqueo (`FuelLog`), asociar el voucher como evidencia  
Y actualizar las métricas de consumo del vehículo.

---

### Validación y Errores

### R2 — Validación de Cantidad de Combustible Positiva
DONDE se registra un tanqueo,  
CUANDO se ingresa una cantidad de galones ≤ 0 o un monto negativo,  
EL SISTEMA DEBE rechazar la transacción e informar que los valores deben ser estrictamente positivos.

---

## Decisiones de Producto

| ID | Pregunta / Ambivalencia | Decisión Aprobada |
|---|---|---|
| D6.1 | ¿Un voucher representa un solo tanqueo en el prototipo? | Sí. Para el prototipo se modela relación 1:1 entre registro de tanqueo y su voucher de evidencia. |
| D6.2 | ¿En qué unidad de medida se mide el combustible? | Galones (con precisión de 2 decimales). |
