> **Estado:** Specs en progreso  
> **ID:** F08 · **Slug:** `08-history-and-reports`  
> **Prerequisitos:** F01..F07

# Requirements — F08: Historial y Reporte de Rendimiento

## User Stories

- **US8.1**: Como administrador, quiero consultar el historial completo de viajes realizados por cada vehículo especificando rango de fechas y conductor, para analizar la utilización de la flota.
- **US8.2**: Como administrador, quiero ver indicadores de rendimiento de combustible (kilometros recorridos / galones consumidos) por vehículo para detectar ineficiencias operacionales.

---

## Criterios de Aceptación (Sintaxis EARS)

### Happy Path

### R1 — Consulta de Historial por Vehículo
DONDE un administrador consulta el detalle o reporte de un vehículo en Filament,  
CUANDO selecciona un rango de fechas de consulta,  
EL SISTEMA DEBE listar todos los viajes completados y cerrados por dicho vehículo, mostrando solicitante, conductor, kilometraje recorrido y combustible consumido.

### R2 — Cálculo de Rendimiento de Combustible (km/galón)
DONDE un vehículo tiene registros de tanqueos y viajes finalizados con kilometraje recorrido acumulado,  
CUANDO se calcula el reporte de rendimiento,  
EL SISTEMA DEBE calcular la métrica `kilometros_totales / galones_totales`  
Y presentar el resultado con 2 decimales en el dashboard del vehículo.

---

## Decisiones de Producto

| ID | Pregunta / Ambivalencia | Decisión Aprobada |
|---|---|---|
| D8.1 | ¿Cómo se calcula el rendimiento si no hay medidor electrónico de combustible? | Rendimiento estimado operacional: `Suma de distancia recorrida (km) / Suma de galones tanqueados en el periodo`. |
