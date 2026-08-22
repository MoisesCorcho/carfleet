# Plan de Refactorización Arquitectónica — Filament v4 & Encapsulamiento de Dominio

**Proyecto:** CarFleet (Gestión de Flota Empresarial)  
**Stack:** Laravel 13 · Filament v4 · Livewire v3 · PHP 8.4  
**Ubicación:** `specs/refactor/filament-architecture-refactor-plan.md`  
**Estado:** Propuesta de Arquitectura Aprobada (Actualizado con Features F01 a F09)  

---

## 1. Motivación y Justificación (El "Por Qué")

### 1.1 Diagnóstico del Estado Actual
El proyecto cuenta con una base de dominio robusta a nivel de backend mediante el uso de **Invokable Actions** y **DTOs** (`app/Actions/**`, `app/DTOs/**`). Sin embargo, a medida que la plataforma ha incorporado la totalidad de sus módulos operativos (incluyendo **F08: Historial y Reportes de Rendimiento** y **F09: Facturación Comercial Simulada**), la capa de Filament (`app/Filament/Resources/**` y `app/Filament/Widgets/**`) ha acumulado síntomas de **deuda técnica por acoplamiento UI-Dominio**:

1. **El "Infierno de Closures Inline":**
   * En `TripResource.php`, la regla de inmutabilidad `disabled(fn (?Trip $record) => $record?->isImmutable() ?? false)` está duplicada en más de 8 componentes individuales.
   * En `VehicleResource.php`, la comprobación de viajes activos para bloquear campos críticos se evalúa mediante `fn (?Vehicle $record) => $record?->trips()->whereIn('status', [...])->exists()`. Esta closure está repetida en 4 campos, lo que provoca **múltiples consultas SQL idénticas a la base de datos** cada vez que se instancia el formulario.
2. **Acciones de Tabla Monolíticas:**
   * En `TripResource.php` y `VehicleResource.php`, acciones modales complejas (`assignResources`, `cancelTrip`, `closeTrip`, `adjustMileage`) tienen sus formularios modales, esquemas, manejo de excepciones y notificaciones definidos inline dentro del método `table()`, inflando los archivos a más de 480 líneas.
   * En `InvoiceResource.php` (F09, ~368 líneas), las acciones `markAsPaid`, `cancelInvoice` (con formulario de motivo obligatorio) y `downloadPdf` están codificadas inline dentro de `table()`, mezclando presentación con invocación de acciones de dominio y manejo de `InvoiceImmutableException`.
3. **Fugas de Mutación Directa en la UI:**
   * En `DriverResource.php`, la creación rápida de usuarios dentro de `createOptionUsing` ejecuta `User::create` y asignación de roles directamente dentro del Resource con Eloquent crudo, saltándose la regla de negocio de canalizar mutaciones a través de `Actions`.
4. **Duplicación de Formatos, Monedas y Máscaras:**
   * Las reglas de validación regex, mayúsculas automáticas y formateo de placas colombianas y números de documento se repiten manualmente en múltiples formularios.
   * El formateo monetario en pesos colombianos (`'$ ' . number_format($val, 0, ',', '.') . ' COP'`) se encuentra duplicado en `InvoiceResource.php`, `TripsRelationManager` (de Invoices), `FleetOverviewWidget.php` y `VehiclePerformanceWidget.php`.
5. **Esquemas de Formularios Extensos en Facturación (F09):**
   * En `InvoiceResource.php`, el método `form()` contiene 3 secciones completas inline: información de cliente, selector reactivo de viajes cerrados no facturados (`eligibleForInvoicing()->uninvoiced()`) y liquidación de tarifas base/distancia con cálculos de subtotales.
6. **Estandarización de Widgets y Relaciones de Historial (F08):**
   * Los 7 widgets de Dashboard (`FleetOverviewWidget`, `FleetStatusDoughnutWidget`, `MonthlyFleetMileageChartWidget`, `TopRequestersChartWidget`, `ActiveDriversControlWidget`, `DriverLicenseAlertsWidget`, `OnboardingQuickStartWidget`) y las pestañas de historial en `VehicleResource` (`TripsRelationManager`, `FuelLogsRelationManager`) deben mantener una arquitectura homogénea: delegar cálculos a `FleetPerformanceCalculatorService` y blindar las tablas embebidas en modo estricto *Read-Only* para evitar bypass de dominio.

### 1.2 Por qué NO usar Mixins / Traits para Reglas de Negocio
Se analizó la opción de utilizar *Mixins / PHP Traits* para compartir estas reglas y se descartó como solución principal por las siguientes razones de diseño:
* **Falsa Encapsulación y Acoplamiento Oculto:** Los Traits comparten implícitamente `$this` y dependen del estado interno de Livewire (`$get`, `$set`), dificultando el análisis estático (PHPStan/Larastan) y la trazabilidad en IDEs.
* **Colisión de Hooks de Ciclo de Vida:** Múltiples Traits manipulando hooks de ciclo de vida del formulario generan conflictos de precedencia difíciles de depurar.
* **Violación de SRP (Single Responsibility Principle):** Mover 200 líneas de closures a un Trait no modulariza el sistema, solo traslada el desorden fuera del archivo principal.
* *Uso válido de Traits:* Únicamente para comportamientos cosméticos y transversales sin estado de negocio (e.g. `HasStatusBadgeColors`, `HasAuditColumns`).

---

## 2. Arquitectura Objetivo (Las 6 Capas de Encapsulamiento)

La refactorización establece una separación estricta de responsabilidades en 6 niveles:

```text
┌─────────────────────────────────────────────────────────────────────────┐
│                      CAPA 1: MODELO DE DOMINIO                          │
│  Métodos de consulta de estado e invariantes (Rich Eloquent Model)       │
│  Ej: $vehicle->isLockedForService(), $trip->isImmutable(),              │
│      $invoice->canBeCancelled(), $invoice->isImmutable()                │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │
┌────────────────────────────────────▼────────────────────────────────────┐
│                    CAPA 2: ESQUEMAS MODULARES                           │
│  Clases dedicadas por sección de formulario (Filament v4 Schemas)       │
│  app/Filament/Resources/{Area}/Schemas/*Schema.php                      │
│  (Trips, Vehicles, Drivers, Invoices)                                   │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │
┌────────────────────────────────────▼────────────────────────────────────┐
│             CAPA 3: COMPONENTES Y COLUMNAS UI REUTILIZABLES             │
│  Campos de formulario tipados y columnas con formateo estandarizado     │
│  app/Filament/Forms/Components/ (PlateInput, MileageInput, CurrencyInput)│
│  app/Filament/Tables/Columns/ (CopMoneyColumn)                          │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │
┌────────────────────────────────────▼────────────────────────────────────┐
│                  CAPA 4: ACCIONES FILAMENT DEDICADAS                    │
│  Modales de tabla y páginas con su propio ciclo de vida y UI            │
│  app/Filament/Resources/{Area}/Actions/*FilamentAction.php              │
│  (AssignResources, CancelTrip, CloseTrip, MarkPaid, CancelInvoice, etc.)│
└────────────────────────────────────┬────────────────────────────────────┘
                                     │
┌────────────────────────────────────▼────────────────────────────────────┐
│                 CAPA 5: REGLAS DE VALIDACIÓN LARAVEL                    │
│  Validación cruzada entre campos aislada y testeable con Pest           │
│  app/Rules/{Area}/*Rule.php                                             │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │
┌────────────────────────────────────▼────────────────────────────────────┐
│               CAPA 6: SERVICIOS, ACCIONES DE DOMINIO & DTOs             │
│  Mutación de datos, transacciones DB, cálculo de métricas de flota      │
│  app/Actions/{Area}/*Action.php · app/Services/Reports/*Service.php     │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Inventario Detallado de Archivos

### 3.1 Archivos Existentes a Modificar (7 a 9 archivos)

| Archivo | Objetivo de la Modificación |
|---|---|
| `app/Models/Vehicle.php` | Agregar métodos de consulta de estado (`isLockedForService()`, `hasActiveTrips()`). |
| `app/Models/Driver.php` | Agregar helpers de estado (`hasActiveTrip()`, `isEligibleForTrip()`). |
| `app/Models/Invoice.php` | Estandarizar métodos de inmutabilidad y consulta de transición de estado. |
| `app/Filament/Resources/Trips/TripResource.php` | Reducir de ~480 líneas a <90 líneas delegando `form()` a `Schemas/` y `table()` a `Actions/`. |
| `app/Filament/Resources/Vehicles/VehicleResource.php` | Reducir de ~390 líneas a <90 líneas delegando `form()` a `Schemas/` y `adjustMileage` a `Actions/`. |
| `app/Filament/Resources/Drivers/DriverResource.php` | Reducir de ~350 líneas a <90 líneas delegando `form()` a `Schemas/` y canalizando `User::create` por Action. |
| `app/Filament/Resources/Invoices/InvoiceResource.php` | Reducir de ~368 líneas a <90 líneas delegando `form()` a `Schemas/` y acciones de pago/anulación a `Actions/`. |
| `app/Filament/Resources/FuelLogs/FuelLogResource.php` | *(Secundario)* Migrar inputs a componentes reutilizables (`MileageInput`, `CurrencyInput`). |
| `app/Filament/Resources/Requesters/RequesterResource.php` | *(Secundario)* Migrar inputs a componentes reutilizables (`DocumentNumberInput`). |
| `app/Filament/Resources/Invoices/RelationManagers/TripsRelationManager.php` | Consumir `CopMoneyColumn` para visualización uniforme de subtotales. |
| `app/Filament/Resources/Vehicles/RelationManagers/TripsRelationManager.php` | Estandarizar filtros y columnas de solo lectura. |

---

### 3.2 Archivos Nuevos a Crear (20 a 23 archivos)

#### A. Componentes Reutilizables de Formulario (`app/Filament/Forms/Components/`)
1. `PlateInput.php` — Input con máscara colombiana (`AAA-123` / `AAA-12A`), mayúsculas automáticas, regex y helper text.
2. `DocumentNumberInput.php` — Input para cédulas/NITs con normalización y reglas de unicidad compuestas.
3. `MileageInput.php` — Input numérico con sufijo `km`, formato de millares y validación de enteros positivos.
4. `CurrencyInput.php` — Input monetario para pesos colombianos (`COP`), prefijo `$`, sufijo `COP`, formato numérico y validación de mínimos.

#### B. Columnas Reutilizables de Tabla (`app/Filament/Tables/Columns/`)
5. `CopMoneyColumn.php` — Columna de tabla tipada para formato `$ 1.234.567 COP`, alineación derecha y soporte de colores semánticos.

#### C. Esquemas Modulares de Formularios (`app/Filament/Resources/{Area}/Schemas/`)
* **Viajes (`Trips/Schemas/`):**
  6. `TripServiceInformationSchema.php` — Sección de Solicitante y Código Consecutivo.
  7. `TripRouteAndScheduleSchema.php` — Sección de Origen, Destino y Fechas Programadas.
  8. `TripResourceAssignmentSchema.php` — Sección de Selección reactiva de Vehículo y Conductor elegibles.
  9. `TripMileageControlSchema.php` — Sección de Lectura de Odómetro Inicial, Final y Distancia.
* **Vehículos (`Vehicles/Schemas/`):**
  10. `VehicleIdentificationSchema.php` — Sección de Placa, Servicio, Carrocería, Marca, Modelo y Año.
  11. `VehicleOperationSchema.php` — Sección de Odómetro Inicial, Estado Operacional y Combustible.
* **Conductores (`Drivers/Schemas/`):**
  12. `DriverPersonalInformationSchema.php` — Sección de Cuenta de Usuario, Nombre, Documento y Teléfono.
  13. `DriverLicenseSchema.php` — Sección de Licencia de Conducción, Categoría, Vencimiento y Estado.
* **Facturación (`Invoices/Schemas/` — F09):**
  14. `InvoiceClientInformationSchema.php` — Sección de Cliente / Solicitante, Fecha de Emisión, Número Autogenerado y Estado.
  15. `InvoiceTripConsolidationSchema.php` — Sección reactiva de Viajes Cerrados Elegibles a consolidar (filtrados dinámicamente por cliente y scope `uninvoiced()`).
  16. `InvoicePricingSchema.php` — Sección de Tarifas Base por Viaje, Tarifa por Kilómetro, Total Liquidado y Observaciones.

#### D. Acciones Modales de Filament Dedicadas (`app/Filament/Resources/{Area}/Actions/`)
* **Viajes (`Trips/Actions/`):**
  17. `AssignTripResourcesFilamentAction.php` — Modal de asignación de recursos con validación y manejo de excepciones de dominio.
  18. `CancelTripFilamentAction.php` — Modal con motivo obligatorio y confirmación.
  19. `CloseTripFilamentAction.php` — Modal de cierre formal definitivo con validación de odómetro y firma.
* **Vehículos (`Vehicles/Actions/`):**
  20. `AdjustVehicleMileageFilamentAction.php` — Modal de calibración de odómetro con justificación de auditoría.
* **Facturación (`Invoices/Actions/` — F09):**
  21. `MarkInvoicePaidFilamentAction.php` — Acción con modal de confirmación, invocación de `MarkInvoicePaidAction`, manejo de `InvoiceImmutableException` y notificación.
  22. `CancelInvoiceFilamentAction.php` — Acción con modal de motivo obligatorio, invocación de `CancelInvoiceAction`, manejo de `InvoiceImmutableException` y notificación.
  23. `DownloadInvoicePdfFilamentAction.php` — Acción de redirección a la ruta de vista previa e impresión de PDF (`/invoices/{record}/pdf`).

#### E. Reglas de Validación Laravel (`app/Rules/{Area}/`)
24. `ValidColombianPlateRule.php` — Regla aislada de validación de formato vehicular.
25. `UniqueDocumentForTypeRule.php` — Regla de unicidad para la tupla `(document_type, document_number)`.

---

## 4. Plan de Ejecución por Fases

### Fase 1: Core de Alto Impacto (Trips & Vehicles — F01 a F07)
* **Objetivo:** Resolver los dos recursos más grandes y acoplados, eliminando las consultas SQL duplicadas en closures.
1. Implementar métodos de estado en `Vehicle.php` (`isLockedForService()`).
2. Crear componentes base en `app/Filament/Forms/Components/` (`PlateInput`, `MileageInput`).
3. Crear clases de acción en `app/Filament/Resources/Trips/Actions/` (`AssignTripResourcesFilamentAction`, `CancelTripFilamentAction`, `CloseTripFilamentAction`).
4. Crear clase de acción en `app/Filament/Resources/Vehicles/Actions/` (`AdjustVehicleMileageFilamentAction`).
5. Extraer esquemas modulares en `Trips/Schemas/` y `Vehicles/Schemas/`.
6. Refactorizar `TripResource.php` y `VehicleResource.php` para consumir las nuevas clases.
7. Validar con tests automatizados (`pest`) y formateo de código (`pint`).

### Fase 2: Gestión de Conductores y Estandarización Restante (Drivers & Components — F02 a F06)
* **Objetivo:** Refactorizar el alta de conductores y desacoplar la creación de usuarios.
1. Implementar helpers en `Driver.php` (`hasActiveTrip()`, `isEligibleForTrip()`).
2. Crear componente `DocumentNumberInput.php` y regla `UniqueDocumentForTypeRule.php`.
3. Extraer esquemas en `Drivers/Schemas/` (`DriverPersonalInformationSchema`, `DriverLicenseSchema`).
4. Refactorizar el `createOptionUsing` de `user_id` para invocar una Action de creación de usuario.
5. Refactorizar `DriverResource.php`.
6. Validar que `FuelLogResource` y `RequesterResource` consuman los componentes compartidos.
7. Ejecutar suite completa de tests de regresión y análisis estático.

### Fase 3: Facturación Comercial, Analytics y Widgets (Invoices, Reports & Widgets — F08 & F09)
* **Objetivo:** Modularizar el módulo de facturación comercial, estandarizar formateo monetario y consolidar arquitectura de widgets.
1. Crear componente `CurrencyInput.php` y columna `CopMoneyColumn.php`.
2. Extraer esquemas modulares en `Invoices/Schemas/` (`InvoiceClientInformationSchema`, `InvoiceTripConsolidationSchema`, `InvoicePricingSchema`).
3. Crear clases de acción en `Invoices/Actions/` (`MarkInvoicePaidFilamentAction`, `CancelInvoiceFilamentAction`, `DownloadInvoicePdfFilamentAction`).
4. Refactorizar `InvoiceResource.php` reduciendo el archivo de 368 líneas a <90 líneas.
5. Estandarizar `Invoices/RelationManagers/TripsRelationManager.php` y `Vehicles/RelationManagers/TripsRelationManager.php` usando `CopMoneyColumn`.
6. Homogeneizar widgets de Dashboard (`FleetOverviewWidget`, `VehiclePerformanceWidget`, etc.) para canalizar cálculos a través de `FleetPerformanceCalculatorService`.
7. Validar suite de pruebas de facturación (`InvoiceFilamentTest`, `InvoiceManagementTest`, `FleetOverviewWidgetTest`) y verificar estilo con Laravel Pint.

---

## 5. Criterios de Aceptación y Checklist de Calidad

- [ ] Ningún `Resource` (`TripResource`, `VehicleResource`, `DriverResource`, `InvoiceResource`) supera las **100 líneas** de código.
- [ ] Cero consultas SQL inline (`Model::query()->...->exists()`) dentro de closures `disabled()` o `visible()`. Todas delegadas al modelo Eloquent.
- [ ] Cero mutaciones directas a base de datos (`Model::create()`) dentro de closures de Filament. Todas canalizadas a través de `Actions`.
- [ ] Formularios de modales de tabla encapsulados en clases `FilamentAction` individuales.
- [ ] Formatos legales colombianos (placas, documentos, moneda COP) centralizados en componentes y columnas reutilizables.
- [ ] Los RelationManagers de histórico (`TripsRelationManager`, `FuelLogsRelationManager`) operan en modo estrictamente de solo lectura (*read-only*) para prevenir bypass de dominio.
- [ ] Todos los archivos PHP inician con `declare(strict_types=1);` y tipado estricto.
- [ ] Suite de pruebas de Pest ejecutando en verde al 100% (38+ tests) sin regresiones.
- [ ] Formato de código verificado con Laravel Pint.
