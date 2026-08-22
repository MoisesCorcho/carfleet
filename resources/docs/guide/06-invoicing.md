# 💵 Facturación Comercial y Cuentas de Cobro

La liquidación comercial permite consolidar viajes concluidos en una cuenta de cobro formal con soporte en PDF.

---

## 📋 1. Reglas de Liquidación Comercial

* **Solo Viajes Cerrados:** Solo los viajes que cuenten con su ciclo formal completo (odómetros y firma) pueden ser incluidos en una factura.
* **Cliente Único:** Todos los viajes agrupados en una factura deben pertenecer al mismo solicitante/empresa.
* **Prevención de Doble Facturación:** Un viaje facturado no puede volver a seleccionarse en otra factura activa.

---

## 💰 2. Tarificación y Cálculo de Subtotales

El sistema utiliza un esquema híbrido de cobro por distancia con tarifa mínima base de servicio:

$$\text{Subtotal por Viaje} = \max(\text{Tarifa Base}, \text{Distancia Recorrida} \times \text{Tarifa/Km})$$

* **Tarifa Base por Defecto:** $50.000 COP (Piso mínimo garantizado).
* **Tarifa por Kilómetro por Defecto:** $3.500 COP / km.
* **Monto Total:** Suma exacta de los subtotales liquidados en unidades monetarias enteras (COP).

---

## 🔄 3. Ciclo de Vida y Anulación de Facturas

```text
[ EMITIDA (issued) ] ────(Registrar Pago)────> [ PAGADA (paid) ] ──> Inmutable
         │
         └───────────────(Anular)───────────> [ ANULADA (cancelled) ]
                                                        │
                                                        ▼
                                       (Viajes Liberados para Refacturar)
```

* **Emitida:** Estado inicial al generar la cuenta de cobro.
* **Pagada:** Estado final al confirmar el pago en tesorería. Bloquea la anulación de la factura.
* **Anulación:** Al anular una factura emitida, se registra el motivo de anulación y **todos los viajes incluidos quedan liberados automáticamente** para poder ser incluidos en una nueva factura corregida.
