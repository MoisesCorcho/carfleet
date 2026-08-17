> **Estado:** Specs en progreso  
> **ID:** F09 · **Slug:** `09-invoicing-simulated`  
> **Prerequisitos:** F04 (`Trip`), F07 (`DigitalSignature`)

# Requirements — F09: Facturación Simulada

## User Stories

- **US9.1**: Como administrador, quiero generar una factura comercial simulada en PDF a partir de uno o varios viajes cerrados para entregar la cuenta de cobro correspondiente al solicitante.

---

## Criterios de Aceptación (Sintaxis EARS)

### Happy Path

### R1 — Generación de Factura Simulada
DONDE un administrador selecciona uno o más viajes en estado `cerrado` pertenecientes a un solicitante,  
CUANDO ejecuta la acción "Generar Factura",  
EL SISTEMA DEBE consolidar la tarifa por servicio, asignar un consecutivo numérico único (`FACT-0001`),  
crear el registro de factura  
Y generar una vista previa/descargable en formato PDF.

---

## Decisiones de Producto

| ID | Pregunta / Ambivalencia | Decisión Aprobada |
|---|---|---|
| D9.1 | ¿Se incluye facturación electrónica DIAN en el prototipo? | No. En el prototipo se genera una factura comercial simulada en PDF sin integración fiscal electrónica. |
