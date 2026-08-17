# Tasks — F08: Historial y Reporte de Rendimiento

- [ ] 1. Lógica de Servicio e Indicadores
  - [ ] 1.1 Crear Service `FleetPerformanceCalculatorService`. _(cubre R1, R2)_

- [ ] 2. Interfaz Administrativa (Filament v4 Widgets)
  - [ ] 2.1 Crear widget de estadísticas `FleetOverviewWidget` en Filament. _(cubre R2)_
  - [ ] 2.2 Agregar pestaña / vista de Historial de Viajes en `VehicleResource`. _(cubre R1)_

- [ ] 3. Pruebas Automatizadas
  - [ ] 3.1 Unit test: Cálculo exacto de km/galón dividiendo distancia entre galones. _(cubre R2)_
  - [ ] 3.2 Feature test: Consulta de historial filtrada por vehículo y rango de fechas. _(cubre R1)_

---

## Matriz de Trazabilidad

| Criterio | Tareas que lo cubren |
|---|---|
| R1 | 1.1, 2.2, 3.2 |
| R2 | 1.1, 2.1, 3.1 |

## Definition of Done (DoD)

- [ ] Pruebas en verde (`./vendor/bin/sail test`).
- [ ] Estilo verificado (`./vendor/bin/sail pint`).
