# 📍 Despacho, Viajes y Operación en Campo

El núcleo operativo de CarFleet gestiona la asignación de recursos y la prevención activa de colisiones horarias y físicas.

---

## ⚡ 1. Motores de Protección y Asignación

### Inconcurrencia Física del Conductor:
* En el mundo real, un chofer no puede estar conduciendo dos vehículos al mismo tiempo.
* Si un chofer tiene un viaje en estado **`En Curso`**, el sistema bloqueará que inicie cualquier otro servicio hasta que concluya el viaje activo.

### Detección de Conflictos de Horario (Time-Slot Overlap):
* Al asignar un chofer a un viaje programado, el sistema calcula la intersección temporal:
  $$\text{Conflicto} \iff (S_1 < E_2) \land (E_1 > S_2)$$
* Si los rangos horarios programados se solapan, la asignación es rechazada, indicando el código y horario del viaje en conflicto.

### Consecutivo Atómico Anual:
* Cada servicio recibe un código único estructurado: `TRIP-YYYY-NNNN` (ej: `TRIP-2026-0001`).
* Se genera mediante transacciones pesimistas (`lockForUpdate`) para evitar saltos o duplicidades en despachos concurrentes.

---

## 📱 2. Operación Móvil del Conductor (`/driver`)

Los conductores acceden a una interfaz optimizada para teléfonos celulares:

1. **Consulta de Asignaciones:** El chofer únicamente visualiza los servicios asignados a su propio usuario.
2. **Inicio del Viaje (Salida):**
   * Digita el odómetro de salida.
   * Toma o sube la foto del tablero del vehículo como evidencia obligatoria.
   * El vehículo transiciona de inmediato a `En Viaje`.
3. **Fin del Viaje (Llegada):**
   * Digita el odómetro de llegada (validación: $\text{Odómetro Final} \ge \text{Odómetro Inicial}$).
   * Sube la foto del odómetro final.
   * El sistema calcula y persiste la **distancia total recorrida** en kilómetros.
