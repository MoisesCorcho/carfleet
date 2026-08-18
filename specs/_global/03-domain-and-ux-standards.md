# Estándares de Dominio y UI/UX Premium (Colombia & Enterprise Grade) — CarFleet

Este documento establece las reglas arquitectónicas, regulatorias de transporte en **Colombia** y estándares de interfaz de usuario para todas las features del sistema.

---

## 1. Marco Regulatorio y Catálogos de Dominio (Colombia)

### 1.1 Documentos de Identidad (`DocumentTypeEnum`)
Todas las personas naturales o jurídicas asociadas a la operación de transporte se identifican bajo el estándar de la Registraduría Nacional y Migración Colombia:
* **`CC`**: Cédula de Ciudadanía.
* **`CE`**: Cédula de Extranjería.
* **`PA`**: Pasaporte.
* **`PPT`**: Permiso por Protección Temporal.
* **`PEP`**: Permiso Especial de Permanencia.

**Regla de DB:** Siempre se modela separado como `document_type` (Enum) y `document_number` (String) con índice único compuesto `$table->unique(['document_type', 'document_number'])`.

### 1.2 Categorías de Licencia de Conducción (`LicenseCategoryEnum`)
Reguladas por el Ministerio de Transporte / RUNT:
* **Servicio Particular:**
  * `B1`: Automóviles, camperos, camionetas y microbuses de servicio particular.
  * `B2`: Camiones rígidos, busetas y buses de servicio particular.
  * `B3`: Vehículos articulados y tractocamiones de servicio particular.
* **Servicio Público / Transporte Comercial:**
  * `C1`: Automóviles, camperos, camionetas y microbuses de servicio público.
  * `C2`: Camiones rígidos, busetas y buses de servicio público.
  * `C3`: Vehículos articulados de servicio público.

**Regla de Negocio:** Para asignar un conductor a un viaje en un vehículo de Servicio Público, su licencia debe pertenecer a la categoría `C` correspondiente (`C1`, `C2` o `C3`).

### 1.3 Tipos de Vehículo y Servicio (`VehicleTypeEnum` & `ServiceTypeEnum`)
* **Tipos de Carrocería / Vehículo (`VehicleTypeEnum`):**
  `automovil`, `camioneta`, `van`, `microbus`, `buseta`, `camion`, `furgon`, `tractocamion`.
* **Tipo de Servicio (`ServiceTypeEnum`):**
  * `publico` (Placa Blanca): Transporte especial / público empresarial.
  * `particular` (Placa Amarilla): Vehículos corporativos de uso propio.
* **Formato de Placas:** Normalización en mayúsculas con formato estándar colombiano `AAA-123` (automotor) o `AAA-12A` (motos).

---

## 2. Invariantes Globales de Dominio e Integridad

1. **Protección del Odómetro (`current_mileage`):**
   * El odómetro **NUNCA** se edita libremente en el formulario de edición de vehículo.
   * Solo muta por eventos de negocio:
     - Cierre de Viaje (`CompleteTripAction`).
     - Registro de Mantenimiento (`LogMaintenanceAction`).
     - Calibración excepcional auditada con justificación (`AdjustVehicleMileageAction`).
2. **Eliminación Segura (`SoftDeletes`):**
   * Las entidades con trazabilidad histórica (`vehicles`, `drivers`, `trips`, `requesters`) implementan `SoftDeletes` de Eloquent.
   * En la UI se prioriza el cambio a estados operacionales inactivos (`fuera_de_servicio`, `inactivo`, `suspendido`) antes que la eliminación física.
3. **Transaccionalidad en Mutaciones:**
   * Toda Action que modifique estado o múltiples modelos ejecuta sus operaciones dentro de `DB::transaction`.

---

## 3. Estándares de UI/UX Premium (Filament v4)

1. **Ergonomía y Jerarquía Visual:**
   * Formularios estructurados en `Section` con descripción clara y `Grid` responsivo (1 col en móvil, 2-3 col en desktop).
   * **Íconos de Contexto Semántico:**
     - Identificación / Documento: `prefixIcon('heroicon-m-identification')`
     - Teléfono: `prefixIcon('heroicon-m-phone')`
     - Usuario / Persona: `prefixIcon('heroicon-m-user')`
     - Vehículo / Placa: `prefixIcon('heroicon-m-truck')`
     - Fechas / Calendarios: `prefixIcon('heroicon-m-calendar-days')`
     - Correo / Contacto: `prefixIcon('heroicon-m-envelope')`
2. **Fricción Cero en Relaciones (`createOptionForm`):**
   * Cuando un formulario requiere una entidad relacionada (ej. vincular `User` en `Driver`), se provee `->createOptionForm(...)` para permitir el alta inline con rol y contraseña sin abandonar la pantalla.
3. **Asistencia al Operador (Placeholders & Helper Texts):**
   * Cada campo de entrada incluye un `placeholder` con un ejemplo real y un `helperText` explicativo para reducir errores humanos.
4. **Normalización Automática en Cliente:**
   * Textos como placas, documentos y licencias se convierten automáticamente a mayúsculas con `extraInputAttributes(['style' => 'text-transform: uppercase;'])` y `dehydrateStateUsing(fn ($state) => strtoupper(trim((string) $state)))`.
5. **Feedback y Badges de Alerta:**
   * Columnas de estado con badges y colores semánticos (`success`, `warning`, `danger`, `gray`, `info`).
   * Alertas explícitas con íconos o descripciones para licencias vencidas o mantenimientos pendientes.
