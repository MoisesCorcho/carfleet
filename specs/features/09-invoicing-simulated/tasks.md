# Tasks — F09: Facturación Simulada

- [ ] 1. Base de datos y Modelos
  - [x] 1.1 Crear migraciones `create_invoices_table` y `create_invoice_trip_table`. _(cubre R1)_
  - [x] 1.2 Crear modelo `App\Models\Invoice` y relaciones. _(cubre R1)_

- [ ] 2. Lógica de Dominio y PDF
  - [ ] 2.1 Implementar Action `GenerateInvoiceAction`. _(cubre R1)_
  - [ ] 2.2 Crear plantilla Blade de factura PDF. _(cubre R1)_

- [ ] 3. Interfaz Administrativa (Filament v4)
  - [ ] 3.1 Crear `InvoiceResource` con acción de descarga de PDF. _(cubre R1)_

- [ ] 4. Pruebas Automatizadas
  - [ ] 4.1 Feature test: Consolidación de viajes cerrados y generación de consecutivo de factura. _(cubre R1)_

---

## Matriz de Trazabilidad

| Criterio | Tareas que lo cubren |
|---|---|
| R1 | 1.1, 1.2, 2.1, 2.2, 3.1, 4.1 |

## Definition of Done (DoD)

- [ ] Pruebas en verde (`./vendor/bin/sail test`).
- [ ] Estilo de código verificado (`./vendor/bin/sail pint`).
