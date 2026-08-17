# Tasks — F07: Firma Digital y Cierre de Viaje

- [ ] 1. Base de datos y Modelos
  - [x] 1.1 Crear migración `create_digital_signatures_table` con relación unique a `trips`. _(cubre R1)_
  - [x] 1.2 Crear modelo `App\Models\DigitalSignature`. _(cubre R1)_
  - [ ] 1.3 Crear factory `DigitalSignatureFactory`. _(cubre R1)_

- [ ] 2. Lógica de Dominio y Actions
  - [ ] 2.1 Crear DTO `CaptureSignatureDTO`. _(cubre R1)_
  - [ ] 2.2 Implementar Action `CaptureTripSignatureAction`. _(cubre R1)_
  - [ ] 2.3 Implementar Action `CloseTripAction` con validaciones previas completas. _(cubre R2, R3)_

- [ ] 3. Interfaz Administrativa (Filament v4)
  - [ ] 3.1 Integrar componente de lienzo de firma en Filament. _(cubre R1)_
  - [ ] 3.2 Implementar acción de tabla/formulario `CloseTrip` en `TripResource`. _(cubre R2, R3)_

- [ ] 4. Pruebas Automatizadas
  - [ ] 4.1 Feature test: Captura de firma Base64 y guardado de PNG. _(cubre R1)_
  - [ ] 4.2 Feature test: Cierre exitoso del viaje y liberación de vehículo. _(cubre R2)_
  - [ ] 4.3 Unit test: Rechazo de cierre si falta firma o kilometraje final. _(cubre R3)_

---

## Matriz de Trazabilidad

| Criterio | Tareas que lo cubren |
|---|---|
| R1 | 1.1, 1.2, 1.3, 2.1, 2.2, 3.1, 4.1 |
| R2 | 2.3, 3.2, 4.2 |
| R3 | 2.3, 3.2, 4.3 |

## Definition of Done (DoD)

- [ ] Pruebas en verde (`./vendor/bin/sail test`).
- [ ] Estilo verificado (`./vendor/bin/sail pint`).
