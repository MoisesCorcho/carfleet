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

---

## Plan de Conventional Commits

| Commit Type & Scope | Mensaje de Commit Sugerido | Tareas | Entregables / Cambios Clave |
|---|---|---|---|
| `feat(invoicing)` | `feat(invoicing): create invoices and invoice_trip migrations with Invoice model` | 1.1, 1.2 | Migraciones de base de datos, llaves foráneas y modelo Eloquent `App\Models\Invoice`. |
| `feat(invoicing)` | `feat(invoicing): implement GenerateInvoiceAction and PDF blade template` | 2.1, 2.2 | Acción de dominio para consolidación de viajes cerrados, cálculo de totales y vista Blade de PDF. |
| `feat(invoicing)` | `feat(invoicing): create InvoiceResource with PDF download table action` | 3.1 | Recurso Filament v4 (`InvoiceResource`), tabla de facturas emitidas y acción de descarga de PDF. |
| `test(invoicing)` | `test(invoicing): add feature tests for invoice consolidation and sequence generation` | 4.1 | Tests con Pest para validación de inmutabilidad, generación de consecutivo `FACT-YYYY-NNNN` y consolidación. |

---

## Definition of Done (DoD)

- [ ] Pruebas en verde (`./vendor/bin/sail test`).
- [ ] Estilo de código verificado (`./vendor/bin/sail pint`).
