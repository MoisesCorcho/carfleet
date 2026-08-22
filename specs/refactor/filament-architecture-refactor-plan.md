# Plan de Refactorización Arquitectónica — Filament v4 & Encapsulamiento de Dominio

**Proyecto:** CarFleet (Gestión de Flota Empresarial)  
**Stack:** Laravel 13 · Filament v4 · Livewire v3 · PHP 8.4  
**Ubicación:** `specs/refactor/filament-architecture-refactor-plan.md`  
**Estado:** Propuesta de Arquitectura Aprobada  

---

## 1. Motivación y Justificación (El "Por Qué")

### 1.1 Diagnóstico del Estado Actual
El proyecto cuenta con una base de dominio robusta a nivel de backend mediante el uso de **Invokable Actions** y **DTOs** (`app/Actions/**`, `app/DTOs/**`). Sin embargo, a medida que la interfaz de usuario ha crecido en complejidad operativa (bloqueo condicional de campos, interacciones entre rutas/conductores/vehículos y modales con formularios), la capa de Filament (`app/Filament/Resources/**`) ha acumulado síntomas de **deuda técnica por acoplamiento UI-Dominio**:

1. **El "Infierno de Closures Inline":**
   * En `TripResource.php`, la regla de inmutabilidad `disabled(fn (?Trip $record) => $record?->isImmutable() ?? false)` está duplicada en más de 8 componentes individuales.
   * En `VehicleResource.php`, la comprobación de viajes activos para bloquear campos críticos se evalúa mediante `fn (?Vehicle $record) => $record?->trips()->whereIn('status', [...])->exists()`. Esta closure está repetida en 4 campos, lo que provoca **múltiples consultas SQL idénticas a la base de datos** cada vez que se instancia el formulario.
2. **Acciones de Tabla Monolíticas:**
   * En `TripResource.php` y `VehicleResource.php`, acciones modales complejas (`assignResources`, `cancelTrip`, `closeTrip`, `adjustMileage`) tienen sus formularios modales, esquemas, manejo de excepciones y notificaciones definidos inline dentro del método `table()`, inflando los archivos a más de 480 líneas.
3. **Fugas de Mutación Directa en la UI:**
   * En `DriverResource.php`, la creación rápida de usuarios dentro de `createOptionUsing` ejecuta `User::create` y asignación de roles directamente dentro del Resource con Eloquent crudo, saltándose la regla de negocio de canalizar mutaciones a través de `Actions`.
4. **Duplicación de Formatos y Máscaras:**
   * Las reglas de validación regex, mayúsculas automáticas y formateo de placas colombianas y números de documento se repiten manualmente en múltiples formularios.

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
│  Ej: $vehicle->isLockedForService(), $trip->isImmutable()               │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │
┌────────────────────────────────────▼────────────────────────────────────┐
│                    CAPA 2: ESQUEMAS MODULARES                           │
│  Clases dedicadas por sección de formulario (Filament v4 Schemas)       │
│  app/Filament/Resources/{Area}/Schemas/*Schema.php                      │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │
┌────────────────────────────────────▼────────────────────────────────────┐
│                 CAPA 3: COMPONENTES UI REUTILIZABLES                    │
│  Campos de formulario tipados y autovalidados                           │
│  app/Filament/Forms/Components/ (PlateInput, MileageInput, etc.)        │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │
┌────────────────────────────────────▼────────────────────────────────────┐
│                  CAPA 4: ACCIONES FILAMENT DEDICADAS                    │
│  Modales de tabla y páginas con su propio ciclo de vida y UI           │
│  app/Filament/Resources/{Area}/Actions/*FilamentAction.php              │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │
┌────────────────────────────────────▼────────────────────────────────────┐
│                 CAPA 5: REGLAS DE VALIDACIÓN LARAVEL                    │
│  Validación cruzada entre campos aislada y testeable con Pest           │
│  app/Rules/{Area}/*Rule.php                                             │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │
┌────────────────────────────────────▼────────────────────────────────────┐
│               CAPA 6: ACCIONES DE DOMINIO & DTOs (CORE)                 │
│  Mutación de datos, transacciones DB, eventos y lógica de negocio pura  │
│  app/Actions/{Area}/*Action.php                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Inventario Detallado de Archivos

### 3.1 Archivos Existentes a Modificar (5 a 7 archivos)

| Archivo | Objetivo de la Modificación |
|---|---|
| `app/Models/Vehicle.php` | Agregar métodos de consulta de estado (`isLockedForService()`, `hasActiveTrips()`). |
| `app/Models/Driver.php` | Agregar helpers de estado (`hasActiveTrip()`, `isEligibleForTrip()`). |
| `app/Filament/Resources/Trips/TripResource.php` | Reducir de ~480 líneas a <90 líneas delegando `form()` a `Schemas/` y `table()` a `Actions/`. |
| `app/Filament/Resources/Vehicles/VehicleResource.php` | Reducir de ~390 líneas a <90 líneas delegando `form()` a `Schemas/` y `adjustMileage` a `Actions/`. |
| `app/Filament/Resources/Drivers/DriverResource.php` | Reducir de ~350 líneas a <90 líneas delegando `form()` a `Schemas/` y canalizando `User::create` por Action. |
| `app/Filament/Resources/FuelLogs/FuelLogResource.php` | *(Secundario)* Migrar inputs a componentes reutilizables (`MileageInput`). |
| `app/Filament/Resources/Requesters/RequesterResource.php` | *(Secundario)* Migrar inputs a componentes reutilizables (`DocumentNumberInput`). |

---

### 3.2 Archivos Nuevos a Crear (14 a 16 archivos)

#### A. Componentes Reutilizables de Formulario (`app/Filament/Forms/Components/`)
1. `PlateInput.php` — Input con máscara colombiana (`AAA-123` / `AAA-12A`), mayúsculas automáticas, regex y helper text.
2. `DocumentNumberInput.php` — Input para cédulas/NITs con normalización y reglas de unicidad compuestas.
3. `MileageInput.php` — Input numérico con sufijo `km`, formato de millares y validación de enteros positivos.

#### B. Esquemas Modulares de Formularios (`app/Filament/Resources/{Area}/Schemas/`)
* **Viajes (`Trips/Schemas/`):**
  4. `TripServiceInformationSchema.php` — Sección de Solicitante y Código Consecutivo.
  5. `TripRouteAndScheduleSchema.php` — Sección de Origen, Destino y Fechas Programadas.
  6. `TripResourceAssignmentSchema.php` — Sección de Selección reactiva de Vehículo y Conductor elegibles.
  7. `TripMileageControlSchema.php` — Sección de Lectura de Odómetro Inicial, Final y Distancia.
* **Vehículos (`Vehicles/Schemas/`):**
  8. `VehicleIdentificationSchema.php` — Sección de Placa, Servicio, Carrocería, Marca, Modelo y Año.
  9. `VehicleOperationSchema.php` — Sección de Odómetro Inicial, Estado Operacional y Combustible.
* **Conductores (`Drivers/Schemas/`):**
  10. `DriverPersonalInformationSchema.php` — Sección de Cuenta de Usuario, Nombre, Documento y Teléfono.
  11. `DriverLicenseSchema.php` — Sección de Licencia de Conducción, Categoría, Vencimiento y Estado.

#### C. Acciones Modales de Filament Dedicadas (`app/Filament/Resources/{Area}/Actions/`)
12. `AssignTripResourcesFilamentAction.php` — Modal de asignación de recursos con validación y manejo de excepciones de dominio.
13. `CancelTripFilamentAction.php` — Modal con motivo obligatorio y confirmación.
14. `CloseTripFilamentAction.php` — Modal de cierre formal definitivo con validación de odómetro y firma.
15. `AdjustVehicleMileageFilamentAction.php` — Modal de calibración de odómetro con justificación de auditoría.

#### D. Reglas de Validación Laravel (`app/Rules/{Area}/`)
16. `ValidColombianPlateRule.php` — Regla aislada de validación de formato vehicular.
17. `UniqueDocumentForTypeRule.php` — Regla de unicidad para la tupla `(document_type, document_number)`.

---

## 4. Plan de Ejecución por Fases

### Fase 1: Core de Alto Impacto (Trips & Vehicles)
* **Objetivo:** Resolver los dos recursos más grandes y acoplados, eliminando las consultas SQL duplicadas en closures.
1. Implementar métodos de estado en `Vehicle.php` (`isLockedForService()`).
2. Crear componentes base en `app/Filament/Forms/Components/` (`PlateInput`, `MileageInput`).
3. Crear clases de acción en `app/Filament/Resources/Trips/Actions/` (`AssignTripResourcesFilamentAction`, `CancelTripFilamentAction`, `CloseTripFilamentAction`).
4. Crear clase de acción en `app/Filament/Resources/Vehicles/Actions/` (`AdjustVehicleMileageFilamentAction`).
5. Extraer esquemas modulares en `Trips/Schemas/` y `Vehicles/Schemas/`.
6. Refactorizar `TripResource.php` y `VehicleResource.php` para consumir las nuevas clases.
7. Validar con tests automatizados (`pest`) y formateo de código (`pint`).

### Fase 2: Gestión de Conductores y Estandarización Restante (Drivers & Components)
* **Objetivo:** Refactorizar el alta de conductores y desacoplar la creación de usuarios.
1. Implementar helpers en `Driver.php` (`hasActiveTrip()`, `isEligibleForTrip()`).
2. Crear componente `DocumentNumberInput.php` y regla `UniqueDocumentForTypeRule.php`.
3. Extraer esquemas en `Drivers/Schemas/` (`DriverPersonalInformationSchema`, `DriverLicenseSchema`).
4. Refactorizar el `createOptionUsing` de `user_id` para invocar una Action de creación de usuario.
5. Refactorizar `DriverResource.php`.
6. Validar que `FuelLogResource` y `RequesterResource` consuman los componentes compartidos.
7. Ejecutar suite completa de tests de regresión y análisis estático.

---

## 5. Criterios de Aceptación y Checklist de Calidad

- [ ] Ningún `Resource` supera las **100 líneas** de código.
- [ ] Cero consultas SQL inline (`Model::query()->...->exists()`) dentro de closures `disabled()` o `visible()`. Todas delegadas al modelo Eloquent.
- [ ] Cero mutaciones directas a base de datos (`Model::create()`) dentro de closures de Filament. Todas canalizadas a través de `Actions`.
- [ ] Formularios de modales de tabla encapsulados en clases `FilamentAction` individuales.
- [ ] Formatos legales colombianos (placas, documentos) centralizados en componentes reutilizables.
- [ ] Todos los archivos PHP inician con `declare(strict_types=1);` y tipado estricto.
- [ ] Suite de pruebas de Pest ejecutando en verde al 100% sin regresiones.
- [ ] Formato de código verificado con Laravel Pint.
