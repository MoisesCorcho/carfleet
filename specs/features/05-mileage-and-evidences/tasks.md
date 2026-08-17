# Tasks — F05: Kilometraje y Evidencias Fotográficas

- [ ] 1. Base de datos y Enums
  - [x] 1.1 Crear Enum `EvidenceTypeEnum` en `app/Enums/Evidences/`. _(cubre R1, R2)_
  - [x] 1.2 Crear migración `create_trip_evidences_table` y añadir columnas de kilometraje a `trips`. _(cubre R1, R2)_
  - [x] 1.3 Crear modelo `App\Models\TripEvidence`. _(cubre R1, R2)_
  - [ ] 1.4 Crear factory `TripEvidenceFactory`. _(cubre R1)_

- [ ] 2. Lógica de Dominio y Actions
  - [ ] 2.1 Crear DTO `RecordMileageDTO`. _(cubre R1, R2, R3)_
  - [ ] 2.2 Implementar Action `RecordTripMileageAction` con cálculo automático de `distance_traveled`. _(cubre R1, R2, R3)_

- [ ] 3. Interfaz Administrativa (Filament v4)
  - [ ] 3.1 Integrar campos FileUpload y acciones de kilometraje en `TripResource`. _(cubre R1, R2, R3)_
  - [ ] 3.2 Añadir Infolist/RelationManager de Evidencias en el detalle del viaje. _(cubre R1, R2)_

- [ ] 4. Pruebas Automatizadas
  - [ ] 4.1 Feature test: Registro de kilometraje inicial/final, almacenamiento de foto y cálculo de distancia. _(cubre R1, R2)_
  - [ ] 4.2 Unit test: Rechazo de kilometraje final menor al inicial. _(cubre R3)_

---

## Matriz de Trazabilidad

| Criterio | Tareas que lo cubren |
|---|---|
| R1 | 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 3.1, 3.2, 4.1 |
| R2 | 1.1, 1.2, 1.3, 2.1, 2.2, 3.1, 3.2, 4.1 |
| R3 | 2.1, 2.2, 3.1, 4.2 |

## Definition of Done (DoD)

- [ ] Pruebas en verde (`./vendor/bin/sail test`).
- [ ] Formateo verificado (`./vendor/bin/sail pint`).
