# 🚗 Flota, Conductores y Normativa RUNT

El módulo de recursos administra la base física y humana de la empresa conforme a la regulación del **Ministerio de Transporte de Colombia**.

---

## 🚙 1. Vehículos y Protección de Odómetro

### Normativa de Placas y Servicios:
* **Placa Colombiana:** Validación estricta de nomenclatura estándar nacional (`AAA-123` o `AAA123`).
* **Tipo de Servicio:**
  * **Particular (Placa Amarilla):** Transporte privado o corporativo interno.
  * **Público (Placa Blanca):** Transporte especial de pasajeros con exigencia legal de licencia categoría C.

### Invariantes de Odómetro:
* **Protección contra Edición Arbitraria:** El campo `current_mileage` está bloqueado en el formulario estándar de edición para evitar fraudes o errores humanos de digitación.
* **Progresión Operativa:** El odómetro solo se incrementa de forma natural mediante:
  1. Registro de kilometraje de salida/llegada en viajes.
  2. Registro de vales de combustible con lectura superior.
* **Ajuste por Auditoría:** Si se cometió un error de digitación justificado, los administradores disponen de la acción modal **"Ajustar Odómetro"** en la tabla de vehículos, la cual exige una justificación escrita obligatoria para el registro de auditoría.

---

## 👨‍✈️ 2. Conductores y Licencias RUNT

### Relación de Usuarios:
* Cada perfil de conductor pertenece a una cuenta de usuario (`User`) única vinculada mediante relación 1:1, permitiéndole autenticarse exclusivamente en el panel `/driver`.

### Matriz de Habilitación de Licencias:

| Categoría RUNT | Tipo de Vehículo Autorizado | ¿Apto para Servicio Público (Placa Blanca)? |
|---|---|---|
| **B1** | Automóviles, camperos y camionetas particulares | ❌ No |
| **B2** | Camiones rígidos y busetas particulares | ❌ No |
| **B3** | Vehículos articulados particulares | ❌ No |
| **C1** | Automóviles, camperos y camionetas de servicio público | ✅ Sí |
| **C2** | Camiones rígidos, busetas y buses de servicio público | ✅ Sí |
| **C3** | Tractocamiones y articulados de servicio público | ✅ Sí |

> **Regla de Bloqueo Automático:** Si un despachador intenta asignar un conductor con licencia B1/B2/B3 a un vehículo de placa blanca, el sistema rechazará la asignación impidiendo la infracción de transporte.

---

## 🏢 3. Solicitantes (Clientes Corporativos)
* Representan las empresas o clientes que contratan los servicios de transporte.
* Soportan identificación por `NIT`, `Cédula de Ciudadanía`, `Cédula de Extranjería`, `Pasaporte` o `PPT`.
* Se vinculan a las facturas comerciales para liquidar lotes de viajes concluidos.
