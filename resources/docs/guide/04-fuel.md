# ⛽ Combustible, Vouchers y Rendimiento

El control de combustible permite monitorear el consumo en galones y calcular el rendimiento energético de la flota en kilómetros por galón (**km/gal**).

---

## 🧾 1. Registro de Tanqueos y Vales

### Reglas de Registro:
* **Identificador de Vale Único:** Cada vale entregado en estación de servicio debe tener un número de comprobante único para evitar cobros dobles.
* **Vinculación a Viaje:** Un tanqueo puede realizarse como evento libre de flota o vincularse a un viaje específico del vehículo.
* **Soporte Fotográfico:** Es obligatorio adjuntar la foto del recibo o voucher emitido por la estación.

---

## 📈 2. Invariante de Odómetro en Tanqueos

* El kilometraje ingresado al momento de tanquear no puede ser inferior al odómetro actual del vehículo.
* Si el tanqueo registra una lectura mayor, el odómetro del vehículo **se actualiza automáticamente** al valor superior reportado.
* Si el tanqueo está asociado a un viaje, la fecha y el kilometraje deben ubicarse estrictamente dentro del rango de salida y llegada del viaje.

---

## 📊 3. Métricas de Rendimiento en el Dashboard

El servicio `FleetPerformanceCalculatorService` procesa las estadísticas de consumo:
$$\text{Rendimiento (km/gal)} = \frac{\sum \text{Distancia de Viajes Completados}}{\sum \text{Galones Consumidos}}$$

Estas métricas alimentan en tiempo real los gráficos mensuales y el widget general de control de flota en el Dashboard.
