---
name: domain-integrity-tdd
description: >
  Mandatory architectural discipline, domain invariants analysis, anti-bypass verification,
  and strict TDD execution protocol for CarFleet. Apply before writing or modifying any code
  in domain logic, actions, models, Filament resources, or UI workflows. Prevents hallucinations,
  domain leaks, state corruption, and rushed implementations by enforcing pre-implementation
  invariants analysis, strict Red-Green-Refactor testing, and life-cycle teardown audits.
metadata:
  short-description: "Strict domain integrity, invariants analysis & TDD protocol for CarFleet"
  version: "1.0"
  stack: "pest php · laravel v13 · filament v4 · php 8.4"
---

# Domain Integrity & Strict TDD Protocol — CarFleet

Este estándar establece el protocolo **obligatorio y no negociable** de análisis de dominio, diseño de invariantes y ejecución mediante TDD en el proyecto CarFleet.

> **Principio Rector:** Pensar y auditar la arquitectura antes de escribir código. El código es la parte más rápida y económica; corregir agujeros de dominio en producción o estar parchando bugs lógicos en caliente es el costo más alto.

---

## 1. El Checklist de Análisis de Dominio (Obligatorio Pre-Codificación)

Antes de crear o modificar cualquier entidad, Action o formulario, se debe responder formalmente a estos **6 pilares de integridad**:

### 1. Inconcurrencia Física (Physical Non-Concurrency)
* **Pregunta:** ¿Puede este actor o recurso físico estar en dos estados activos o dos servicios simultáneos en el mundo real?
* **Regla:** Si un recurso es finito o una persona física (chofer, vehículo), el sistema **debe bloquear** que participe en más de un evento activo en paralelo (`TripStatusEnum::EN_CURSO`, `VehicleStatusEnum::EN_VIAJE`, etc.).
* **Mecanismo:** Validación en la Action correspondiente (`StartTripAction`) y lanzamiento de excepción tipada (`DriverAlreadyInTripException`).

### 2. Colisión de Ventanas Temporales (Time-Slot Overlap)
* **Pregunta:** Si el registro tiene fecha/hora programada de inicio y fin, ¿puede asignarse a un recurso que ya tiene otra asignación en ese rango?
* **Regla:** Calcular siempre la intersección de intervalos $[S_1, E_1]$ y $[S_2, E_2]$:
  $$\text{Overlap} \iff (S_1 < E_2) \land (E_1 > S_2)$$
* **Mecanismo:** Consulta preventiva en la Action de asignación (`AssignTripResourcesAction`) con bloqueo pesimista (`lockForUpdate`) y excepción explicativa (`DriverScheduleConflictException`).

### 3. Atomicidad de Agregados y Duplas Operativas (Coupled Resources)
* **Pregunta:** ¿La operación requiere más de un recurso conjunto para ser operacionalmente válida? (Ej: Vehículo + Conductor para despachar un viaje).
* **Regla:** Prohibidas las mutaciones o asignaciones parciales silenciosas. **O se asignan ambos o ninguno**.
* **Mecanismo:** 
  * En UI: Validación cruzada en tiempo real (`Select::requiredWith('other_field')`).
  * En Dominio: Excepción tipada (`IncompleteTripResourcesException`) si el DTO recibe un recurso huérfano.

### 4. Inmutabilidad y Máquinas de Estado
* **Pregunta:** ¿Cuáles son los estados terminales de la entidad (`cerrado`, `cancelado`, `baja`)?
* **Regla:** Todo registro en estado terminal es **estrictamente inmutable**. No se permiten reasignaciones, ediciones ni reaperturas sin un caso de uso explícito de reversión.
* **Mecanismo:** Método `$model->isImmutable()` verificado al inicio de toda Action (`TripImmutableException`) y botones ocultos en UI (`->visible(fn ($record) => !$record->isImmutable())`).

### 5. Teardown y Prevención de Estados Huérfanos (Lifecycle Cleanup)
* **Pregunta:** Si este registro se cancela, se reasigna o se elimina, ¿qué recursos dependientes quedan comprometidos?
* **Regla:** Ningún recurso secundario puede quedar bloqueado en un estado transitorio si la orden principal deja de existir.
* **Mecanismo:**
  * En Reasignación: Liberar el recurso anterior a `disponible` dentro de la misma transacción DB.
  * En Cancelación: Revertir recursos a `disponible` en la Action de cancelación.
  * En Eliminación: Hooks del modelo Eloquent (`static::deleting` en `booted()`) para garantizar liberación universal.

### 6. Anti-Bypass de Dominio en UI (Filament / Livewire / API)
* **Pregunta:** ¿El formulario de Filament ejecuta un `$record->update()` o `$record->create()` crudo de Eloquent?
* **Regla:** **PROHIBIDO EL BYPASS DE ACCIONES EN UI**. Todo `CreateRecord` y `EditRecord` debe canalizar sus mutaciones a través de las **Actions de Dominio**.
* **Mecanismo:**
  * Sobrescribir obligatoriamente `handleRecordCreation(array $data)` en páginas `CreateRecord`.
  * Sobrescribir obligatoriamente `handleRecordUpdate(Model $record, array $data)` en páginas `EditRecord`.
  * Capturar las excepciones de dominio, notificar al operador con `Notification::danger()` y llamar a `$this->halt()`.
  * Sincronizar UI vs Payload: No confiar en máscaras CSS (`text-transform`), usar regex case-insensitive (`/i`) y sanitizar con `dehydrateStateUsing()`.

---

## 2. Protocolo TDD Estricto (Red — Green — Refactor)

La codificación debe respetar religiosamente este ciclo secuencial:

```mermaid
graph LR
    A["1. RED: Escribir Test que Falla"] --> B["2. GREEN: Mínimo Código en Dominio"]
    B --> C["3. REFACTOR & UI: Conectar y Pulir"]
    C --> D["4. VERIFY: Suite 100% Verde + Pint"]
```

### Fase 1: RED (Escribir el Test Primero)
1. Redactar el test en `tests/Feature/{Area}/{Entity}ValidationTest.php` o `{Entity}ManagementTest.php`.
2. Modelar el caso límite o la invariante exacta (ej: chofer intentando salir 2 veces, placa en minúsculas, eliminación de viaje asignado).
3. Correr `./vendor/bin/sail test --filter="nombre del test"` y **verificar que falla** con el error esperado (no por un error de sintaxis).

### Fase 2: GREEN (Implementar en el Dominio)
1. Crear la excepción de dominio tipada en `app/Exceptions/{Area}/{Name}Exception.php`.
2. Implementar la validación y transacción dentro de la **Action** correspondiente (`app/Actions/{Area}/...Action.php`).
3. Correr el test y verificar que pasa a **VERDE**.

### Fase 3: REFACTOR & UI INTEGRATION
1. Conectar las páginas de Filament (`CreateRecord`, `EditRecord`, `Actions` de tabla) con la Action de Dominio.
2. Añadir tests de Livewire simulando la interacción del formulario y verificando errores de validación (`assertHasFormErrors`) o ejecuciones exitosas (`assertHasNoFormErrors`).
3. Validar consistencia y feedback al usuario en español profesional.

### Fase 4: VERIFICATION
1. Correr toda la suite de pruebas: `./vendor/bin/sail test` $\rightarrow$ 100% de tests pasando en verde.
2. Formatear el código con el linter oficial: `./vendor/bin/sail pint`.
3. Revisar `git status` y verificar que no haya archivos huérfanos o sucios.

---

## 3. Matriz Rápida de Excepciones y Respuestas HTTP / UI

| Escenario de Inconsistencia | Tipo de Excepción | Comportamiento UI / Filament |
|---|---|---|
| Inconcurrencia / En curso | `DomainException` (ej: `DriverAlreadyInTripException`) | Modal detenido, notificación `danger()`, `$this->halt()` |
| Colisión de Horario | `DomainException` (ej: `DriverScheduleConflictException`) | Modal detenido, notificación `danger()`, `$this->halt()` |
| Asignación Parcial | `DomainException` (ej: `IncompleteTripResourcesException`) | Formulario bloqueado con `requiredWith()` |
| Mutación Inmutable | `DomainException` (ej: `TripImmutableException`) | Botón/Formulario oculto (`isImmutable()`) |
| Recurso No Disponible | `DomainException` (ej: `VehicleNotAvailableException`) | Notificación `danger()`, `$this->halt()` |

