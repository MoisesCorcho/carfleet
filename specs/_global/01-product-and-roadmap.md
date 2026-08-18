# Producto y Roadmap — CarFleet

## Visión del Producto

**CarFleet** es un sistema web de gestión de transporte para la digitalización y centralización operativa de servicios de transporte. Permite a los administradores controlar la flota de vehículos, conductores y servicios, mientras los conductores registran evidencias (kilometraje, fotografías, tanqueos de combustible y firma digital del solicitante) en tiempo real durante la ejecución de los viajes.

Actualmente se construye como un **prototipo funcional completo y robusto sobre Laravel 13, Filament v4, Sail y Boost**, dejando preparada la arquitectura y el modelo de dominio para su posterior escalabilidad a producción.

> **Nota de Alcance:** El sistema opera **sin GPS**, basando la trazabilidad operacional en registros de kilometraje, marcas de tiempo y evidencias fotográficas.

---

## Principios de Producto y Dominio

1. **Viaje como Entidad Central**: El **Viaje (`Trip`)** es el eje pivote alrededor del cual se conectan el Vehículo, el Conductor, el Solicitante, las Evidencias (fotografías de odómetro y vouchers), los Tanqueos y la Firma Digital.
2. **Dominio Primero, UI Después**: Las reglas de negocio residen en el modelo de dominio (`app/Models`) y clases Action invocables (`app/Actions/{Area}`). Filament v4 consume las Actions y DTOs sin acoplar la lógica al panel.
3. **Invariabilidad e Historial (*Snapshots*)**: Los viajes congelan el kilometraje inicial, kilometraje final, fechas reales y datos del solicitante para garantizar un historial inalterable sin recálculos erróneos por cambios futuros en catálogos.
4. **Ciclo de Vida Formal en Enums**: Todos los estados del viaje (`TripStatusEnum`), vehículo (`VehicleStatusEnum`) y evidencias se gestionan mediante backed enums de PHP mapeados a columnas `string` en BD.
5. **Una Action = Un Caso de Uso**: Cambios de estado (ej. `StartTripAction`, `CompleteTripAction`, `CloseTripAction`) ejecutan sus mutaciones en transacciones `DB::transaction`.
6. **Autorización en el Borde**: Políticas de acceso (Policies) para separar el rol **Administrador** (gestión total) del rol **Conductor** (vistas simplificadas de sus viajes asignados y captura de evidencias).

---

## Fundación de Dominio (Prerequisito Global)

| Entrega | Estado | Ubicación |
|---|---|---|
| Autenticación & Panel Admin Filament v4 | Completa | `app/Providers/Filament/AdminPanelProvider.php` |
| Estándares AI y Configuración MCP | Completa | `.ai/guidelines/project-conventions.md`, `AGENTS.md`, `.mcp.json` |
| Verificación Git Hooks (Lefthook) | Completa | `lefthook.yml` (Pint + Sail Test) |
| Enums del Dominio Base | En progreso | `app/Enums/{Area}/*Enum.php` |

---

## Roadmap de Features (MVP Prototipo Funcional)

| ID | Feature / Módulo | Fase | Estado | Prerequisitos |
|---|---|---|---|---|
| F01 | **Gestión de Vehículos** (`01-vehicle-management`) | 0 · Catálogos Base | Completa | Fundación de Dominio |
| F02 | **Gestión de Conductores** (`02-driver-management`) | 0 · Catálogos Base | Completa | Fundación de Dominio |
| F03 | **Gestión de Solicitantes** (`03-requester-management`) | 0 · Catálogos Base | Specs en progreso | Fundación de Dominio |
| F04 | **Gestión y Asignación de Viajes** (`04-trip-management`) | 1 · Operación Core | Specs en progreso | F01, F02, F03 |
| F05 | **Kilometraje y Evidencias Fotográficas** (`05-mileage-and-evidences`) | 1 · Operación Core | Specs en progreso | F04 |
| F06 | **Tanqueos y Vouchers de Combustible** (`06-fuel-and-vouchers`) | 2 · Combustible | Specs en progreso | F01, F04 |
| F07 | **Firma Digital y Cierre de Viaje** (`07-digital-signatures`) | 2 · Cierre Operativo | Specs en progreso | F04, F05, F06 |
| F08 | **Historial y Reporte de Rendimiento** (`08-history-and-reports`) | 3 · Analítica | Specs en progreso | F01..F07 |
| F09 | **Facturación Simulada** (`09-invoicing-simulated`) | 3 · Comercial | Specs en progreso | F04, F07 |

---

## Mapeo de Entidades del Dominio

```text
User (Admin / Driver)
  └── Driver Profile (driver_license, phone, status)
       └── performs ──> Trip
                          ├── Vehicle (plate, status, current_mileage)
                          ├── Requester (name, company, phone)
                          ├── Mileage Logs (initial_km, final_km)
                          ├── Photo Evidences (initial_odometer, final_odometer, voucher)
                          ├── Fuel Logs (refuel_date, gallons, amount, voucher_number)
                          ├── Digital Signature (signature_data, signed_at)
                          └── Invoice (simulated invoice_number, total_amount)
```
