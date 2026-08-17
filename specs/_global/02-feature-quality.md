# Calidad de Features SDD (EARS, Audit y Corrección) — CarFleet

**Propósito:** Define el contrato de calidad, robustez y formato que deben cumplir todas las especificaciones de características en `specs/features/<slug>/`.

---

## Estructura de los 3 Artefactos Obligatorios

Cada característica debe estructurarse estrictamente en tres archivos:

| Archivo | Propósito | Prohibido incluir |
|---|---|---|
| `requirements.md` | **QUÉ** observable por el negocio y usuario. | Clases PHP, migraciones, sintaxis de código, "usar Action X". |
| `design.md` | **CÓMO** técnico (Filament Schemas, Actions, DTOs, Policies). | User stories sin decisiones técnicas; contradecir convenciones del proyecto. |
| `tasks.md` | **ORDEN** ejecutable y ordenado por capas. | Tareas sin referencia a criterios `_(cubre Rx)_`, "hacer tests" genérico. |

Mezclar el **QUÉ** y el **CÓMO** (ejemplo: poner `Eloquent SoftDeletes` dentro de un requerimiento funcional) es considerado un fallo prioritario de auditoría.

---

## Criterios de Aceptación en Sintaxis EARS e IDs

### Formato de Criterios

Cada requerimiento en `requirements.md` debe cumplir:

1. **ID Estable**: Encabezado `### R{N} — Título`.
2. **Plantilla EARS**:
   - **CUANDO** [evento desencadenante],
   - **MIENTRAS** [condición de estado],
   - **DONDE** [contexto de UI o rol de usuario],
   - **EL SISTEMA DEBE** [respuesta observable del sistema].
3. **Testabilidad Directa**: Cada criterio debe mapear a uno o más escenarios de prueba en `tasks.md`.
4. **Sin Lenguaje Vago**: Prohibido usar términos ambiguos como "rápido", "fácil", "eficiente" o "adecuado" sin métricas concretas.

### Ejemplo EARS (Gestión de Flotas)

```markdown
### R1 — Registro de vehículo con placa única

DONDE un administrador autenticado está en el panel de Filament,
CUANDO envía el formulario de alta de vehículo con una placa no registrada previamente,
EL SISTEMA DEBE guardar el vehículo en estado "Disponible", registrar el kilometraje inicial
Y mostrar una notificación de éxito en la interfaz.
```

```markdown
### R2 — Rechazo de placa duplicada

DONDE un administrador intenta registrar un vehículo,
CUANDO ingresa una placa que ya existe en la base de datos,
EL SISTEMA DEBE rechazar la operación, resaltar el campo de la placa
Y prevenir la creación del registro duplicado.
```

---

## Granularidad de Tareas y Trazabilidad

En `tasks.md`:
- Jerarquía máxima de **2 niveles**.
- **Obligatorio**: Cada tarea debe declarar qué criterio(s) cubre: `_(cubre R1, R2)_`.
- **Orden por capas**:
  1. Base de datos (Migrations, Enums, Factories, Models).
  2. Lógica de Dominio (Actions, DTOs, Policies).
  3. Interfaz Administrativa (Filament Resources, Schemas, Tables, Pages).
  4. Pruebas Automatizadas (Feature / Unit tests en Pest / PHPUnit).

### Tabla de Trazabilidad y Definition of Done (DoD)

Al final de `tasks.md` debe incluirse la matriz:

| Criterio | Tareas que lo cubren |
|---|---|
| R1 | 1.1, 2.1, 4.1 |
| R2 | 2.1, 4.2 |

**Definition of Done (DoD):**
- [ ] Todos los criterios R1..Rn probados y pasando en verde mediante `./vendor/bin/sail test`.
- [ ] Código formateado según estándares con `./vendor/bin/sail pint`.
- [ ] Cambios reflejados en specs si hubo desviaciones aprobadas.

---

## Protocolo de Auditoría y Corrección de Specs

### Auditoría (Solo Lectura)
Antes de implementar una feature, se evalúa contra las 6 reglas:
1. **Estructura de 3 Artefactos**: Existen `requirements.md`, `design.md` y `tasks.md`.
2. **Sintaxis EARS**: Estructura válida y sin ambigüedades.
3. **Trazabilidad**: Todas las tareas enlazadas a criterios R1..Rn.
4. **Resolución de Ambigüedades**: Preguntas de borde resueltas en tabla de decisiones.
5. **Alineación con Steering**: No contradice `01-product-and-roadmap.md` ni las convenciones.
6. **Validación Primero**: Happy path y casos de error explicitados.

### Corrección (Edición de Specs)
Si se detectan fallos en la auditoría, se corrigen los tres archivos en el orden: `requirements.md` → `design.md` → `tasks.md` antes de escribir la primera línea de código de la feature.
