# 🗺️ Flujo de Operaciones Extremo a Extremo

CarFleet está diseñado bajo una **arquitectura multi-panel desacoplada** para separar las responsabilidades de la oficina central de la operación táctil en campo.

---

## 🏢 1. División de Roles y Paneles

| Panel | Destinatarios | Responsabilidad Principal | Interfaz |
|---|---|---|---|
| **`/admin` (Panel Central)** | Despachadores, Administradores y Directores de Flota | Alta de flota, asignación de servicios, control de combustible, reportes de rendimiento y facturación comercial. | Escritorio completo con tablas, filtros avanzados y widgets de auditoría. |
| **`/driver` (Panel Móvil)** | Conductores en Campo | Visualización de servicios asignados, registro fotográfico de odómetros (salida y llegada), y captura táctil de firma digital. | Interfaz móvil simplificada optimizada para teléfonos celulares. |

---

## 🔄 2. Ciclo de Vida de un Servicio de Transporte

```text
[ 1. ALTA DE RECURSOS ] ──> [ 2. PROGRAMACIÓN ] ──> [ 3. ASIGNACIÓN ]
       (Vehículos, Choferes, Solicitantes)           (TRIP-YYYY-NNNN)          (Vehículo + Chofer)
                                                                                       │
                                                                                       ▼
[ 6. FACTURACIÓN ] <── [ 5. CIERRE FORMAL ] <── [ 4. EJECUCIÓN EN CAMPO ] <────────────┘
 (Consolidación)          (Firma + Inmutabilidad)   (/driver: Odómetro Salida/Llegada)
```

### Paso a Paso Operativo:

1. **Alta de Recursos:** Se registran vehículos, choferes habilitados con usuario y solicitantes corporativos.
2. **Programación del Viaje:** Se crea el servicio generando el consecutivo anual atómico `TRIP-YYYY-NNNN`.
3. **Asignación de Recursos:** Se asocia un vehículo `Disponible` y un conductor `Activo` con licencia válida para el tipo de servicio. El vehículo pasa automáticamente a `Asignado`.
4. **Inicio y Salida (Móvil):** El conductor ingresa a `/driver`, inicia el servicio, digita el odómetro de salida y adjunta la foto del tablero. El vehículo pasa a `En Viaje` y el viaje a `En Curso`.
5. **Llegada y Combustible:** Si tanquea, registra el vale. Al llegar a destino, digita el odómetro final y sube la foto del odómetro.
6. **Firma Digital y Cierre Formal:** El solicitante firma en pantalla de conformidad. Se ejecuta el cierre formal, bloqueando el viaje de forma **inmutable** y liberando el vehículo a `Disponible`.
7. **Facturación Comercial:** Se consolidan múltiples viajes cerrados de un cliente en una factura formal con código `FACT-YYYY-NNNN` y exportación a PDF.
