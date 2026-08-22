# Checklist Integral de QA y Matriz de Invariantes — CarFleet (F08 - F09)

> **Ambiente de Pruebas:** `https://carfleet.moisescorchodev.tech/`  
> **Versión del Sistema:** CarFleet v1.0 (Features F08 & F09: Historial, Reportes de Rendimiento, Dashboard Analytics y Facturación Comercial)  
> **Propósito:** Guía de verificación funcional, auditoría de widgets/analytics, validación de reglas de negocio e invariantes de dominio para reportes, liquidación y facturación simulada en PDF sin regresiones.

---

## 🔑 Directorio de Accesos, Cuentas y Datos Sembrados (Seeders)

### 1. Cuentas Administrativas y Despacho
| Panel | URL de Acceso | Rol | Email | Contraseña | Propósito en QA |
|---|---|---|---|---|---|
| **Panel Admin** | `https://carfleet.moisescorchodev.tech/admin` | `super_admin` | `admin@carfleet.test` *(o `.com`)* | `password` | Acceso integral: Dashboard Analytics, Historiales, Métricas de Rendimiento y Facturación. |
| **Panel Admin** | `https://carfleet.moisescorchodev.tech/admin` | `admin` | `despacho@carfleet.test` | `password` | Coordinador de despacho de viajes, monitoreo de conductores activos y alertas operacionales. |

### 2. Cuentas de Conductores y Estado de Licencias (Auditoría de Alertas)
| Panel | URL de Acceso | Conductor | Email | Contraseña | Categoría | Estado de Licencia / Escenario de Prueba en Widget |
|---|---|---|---|---|---|---|
| **Driver** | `https://carfleet.moisescorchodev.tech/driver` | Carlos Andrés Rodríguez | `driver@carfleet.test` | `password` | **C1** | Licencia Vigente $\rightarrow$ Historial de viajes cerrados (`TRIP-2026-0001`, `TRIP-2026-0005`). |
| **Driver** | `https://carfleet.moisescorchodev.tech/driver` | Jorge Eliécer Gaitán | `driver2@carfleet.test` | `password` | **C2** | Licencia Vigente $\rightarrow$ Conduciendo `RUN-808` (`TRIP-2026-0006` En Viaje). |
| **Driver** | `https://carfleet.moisescorchodev.tech/driver` | María Fernanda Gómez | `driver3@carfleet.test` | `password` | **C1** | Licencia Vigente $\rightarrow$ Conductora en base con viaje asignado (`TRIP-2026-0007`). |
| **Driver** | `https://carfleet.moisescorchodev.tech/driver` | Juan Pablo Montoya | `driver4@carfleet.test` | `password` | **C3** | Licencia Vigente $\rightarrow$ Conductor disponible en patio. |
| **Driver** | `https://carfleet.moisescorchodev.tech/driver` | Andrés Felipe Arias | `driver5@carfleet.test` | `password` | **B1** | Licencia Particular $\rightarrow$ Conductor disponible particular. |
| **Driver** | `https://carfleet.moisescorchodev.tech/driver` | Ricardo Arjona Morales | `driver6@carfleet.test` | `password` | **C1** | **Licencia Vencida** $\rightarrow$ Debe figurar con alerta roja en `DriverLicenseAlertsWidget`. |

### 3. Clientes / Solicitantes Sembrados para Facturación
| Documento / NIT | Solicitante / Representante | Empresa / Razón Social | Teléfono / Correo | Viajes Cerrados Asociados en BD |
|---|---|---|---|---|
| `900123456-1` | Ing. Roberto Sánchez | Consorcio Vial de los Llanos S.A.S. | `operaciones@consorciovial.com` | `TRIP-2026-0001` (250 km) |
| `900234567-2` | Dra. Patricia Ortiz | Alimentos del Centro S.A. | `comercial@alimentoscentro.com` | `TRIP-2026-0002` (310 km) |
| `900345678-3` | Lic. Fernando Gómez | Logística Nacional & Carga S.A.S. | `logistica@logisticacarga.com` | `TRIP-2026-0003` (320 km) |
| `900456789-4` | Dra. Elena Vargas | Corporación de Servicios Empresariales | `rrhh@serviciosempresariales.com` | `TRIP-2026-0005` (150 km — una vez cerrado) |
| `900567890-5` | Arq. Mauricio Cárdenas | Constructora e Inmobiliaria Andina S.A. | `infraestructura@andina.com` | `TRIP-2026-0004` (250 km) |
| `52987654` | Dra. Carmen Cecilia Morales | Morales & Asociados | `auditoria@moralesauditores.com` | Sin viajes cerrados (No elegible para facturar) |

### 4. Flota Sembrada y Consumos de Combustible en Base de Datos
- **`ABC-123`** (Toyota Hilux): 250 km recorridos en `TRIP-2026-0001` + 14.50 gal ($215.000) en ruta + 15.00 gal ($220.000) en base $\rightarrow$ Rendimiento evaluable en `VehiclePerformanceWidget`.
- **`XYZ-789`** (Renault Master): 310 km recorridos en `TRIP-2026-0002` + 18.20 gal ($270.000) en ruta $\rightarrow$ Rendimiento estimado: ~17.03 km/gal.
- **`FLT-101`** (Chevrolet D-Max): 320 km recorridos en `TRIP-2026-0003` + 11.80 gal ($175.000) en ruta $\rightarrow$ Rendimiento estimado: ~27.12 km/gal.
- **`VAN-303`** (Mercedes-Benz Sprinter): 250 km recorridos en `TRIP-2026-0004` + 22.50 gal ($330.000) en base $\rightarrow$ Rendimiento estimado: ~11.11 km/gal.
- **`SED-404`** (Toyota Corolla): 150 km recorridos en `TRIP-2026-0005` (Finalizado).

---

## 📑 Índice de Módulos a Auditar

1. [Módulo 10: Dashboard Operativo, Analytics y Widgets de Flota (F08)](#módulo-10-dashboard-operativo-analytics-y-widgets-de-flota-f08)
2. [Módulo 11: Historial Detallado y Rendimiento por Vehículo (F08)](#módulo-11-historial-detallado-y-rendimiento-por-vehículo-f08)
3. [Módulo 12: Facturación Comercial Simulada y Cuentas de Cobro (F09)](#módulo-12-facturación-comercial-simulada-y-cuentas-de-cobro-f09)
4. [Módulo 13: Invariantes de Dominio, Reglas de Negocio y Manejo de Excepciones (F08 - F09)](#módulo-13-invariantes-de-dominio-reglas-de-negocio-y-manejo-de-excepciones-f08---f09)
5. [Módulo 14: UX, Modo Oscuro y Adaptabilidad Multi-Dispositivo](#módulo-14-ux-modo-oscuro-y-adaptabilidad-multi-dispositivo)

---

## Módulo 10: Dashboard Operativo, Analytics y Widgets de Flota (F08)

### QA-10.1: Tarjetas de Resumen General (`FleetOverviewWidget`)
- **Pasos:**
  1. Iniciar sesión como `admin@carfleet.test` en `https://carfleet.moisescorchodev.tech/admin`.
  2. Ubicar la sección superior del Dashboard principal (**Resumen de Flota y Operaciones**).
- **Resultado Esperado (Happy Path):**
  - [ ] **Kilometraje Recorrido:** Muestra la sumatoria total de kilómetros de viajes completados y cerrados (ej: `1.130 km`) con subtítulo indicando cantidad de viajes completados.
  - [ ] **Rendimiento Promedio:** Muestra el cálculo de eficiencia en formato decimal `XX,XX km/gal` calculado como `total_km / total_galones`.
  - [ ] **Combustible Total:** Muestra los galones totales acumulados con el costo consolidado en pesos colombianos (`$ XXX.XXX COP`).
  - [ ] **Flota Operativa:** Muestra la relación `Vehículos Activos / Total Vehículos` (ej: `9 / 10`) con icono de camión y color primario.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [ ] **División por Cero:** Si la base de datos no tuviera registros de combustible o estuviera vacía, el widget **debe mostrar `0,00 km/gal` de forma segura** sin arrojar `DivisionByZeroError` ni pantalla de error HTTP 500.
  - [ ] **Viajes No Finalizados:** El conteo de kilómetros no debe sumar distancias de viajes `Programados`, `Asignados` ni `Cancelados`.

### QA-10.2: Gráfico de Distribución de Estado de la Flota (`FleetStatusDoughnutWidget`)
- **Pasos:**
  1. En el Dashboard principal, ubicar el gráfico circular tipo dona **Distribución de Estado de Flota**.
- **Resultado Esperado (Happy Path):**
  - [ ] Muestra la proporción exacta de vehículos según su estado actual: `Disponible` (verde), `Asignado` (azul/info), `En Viaje` (amarillo/warning), `Mantenimiento` (rojo/danger) y `Desincorporado` (gris).
  - [ ] Al pasar el cursor sobre cada segmento (hover), muestra la etiqueta del estado y la cantidad exacta de vehículos.

### QA-10.3: Histórico Mensual de Kilometraje (`MonthlyFleetMileageChartWidget`) y Filtros
- **Pasos:**
  1. En el Dashboard principal, ubicar el gráfico de barras/línea **Kilometraje Mensual de la Flota**.
  2. Probar alternar el filtro desplegable de periodo entre:
     - *Últimos 6 meses* (`6_months`)
     - *Últimos 12 meses* (`12_months`)
     - *Año actual* (`this_year`)
- **Resultado Esperado (Happy Path):**
  - [ ] El gráfico se actualiza dinámicamente recalculando los kilómetros recorridos agrupados por mes cronológico.
  - [ ] Cada barra o punto refleja fielmente la suma de distancias de los viajes con fecha de salida o llegada en el mes correspondiente.

### QA-10.4: Top Clientes Solicitantes por Viajes (`TopRequestersChartWidget`)
- **Pasos:**
  1. En el Dashboard, ubicar el gráfico horizontal **Top Clientes Solicitantes**.
  2. Alternar entre el filtro *Mes Actual* y *Histórico Total*.
- **Resultado Esperado (Happy Path):**
  - [ ] Clasifica a las empresas o clientes que más viajes han completado (ej: *Consorcio Vial*, *Alimentos del Centro*, *Logística Nacional*).
  - [ ] En *Mes Actual*, filtra únicamente los viajes concluidos en el mes en curso.

### QA-10.5: Control de Conductores en Ruta y Tiempos en Tránsito (`ActiveDriversControlWidget`)
- **Pasos:**
  1. En el Dashboard, ubicar la tabla de monitoreo en vivo **Control de Conductores Activos**.
  2. Filtrar por estado operacional: `En Viaje` y `Disponible`.
- **Resultado Esperado (Happy Path):**
  - [ ] Para conductores en viaje (ej: Jorge Gaitán en `RUN-808`), se visualiza el código del viaje (`TRIP-2026-0006`), el destino (`Sede Operativa Ibagué`), la placa asignada, el teléfono de contacto y el tiempo transcurrido en ruta calculado dinámicamente (ej: `3h 15m`).
  - [ ] Para conductores disponibles (ej: Juan Pablo Montoya), se visualiza el badge verde `Disponible`.
  - [ ] Si un conductor tiene la licencia vencida (ej: Ricardo Arjona), se destaca con badge rojo `Licencia Vencida`.

### QA-10.6: Monitor de Alertas de Vencimiento de Licencias (`DriverLicenseAlertsWidget`)
- **Pasos:**
  1. En el Dashboard, consultar el widget **Alertas de Vencimiento de Licencias**.
- **Resultado Esperado (Happy Path):**
  - [ ] Lista de manera destacada a los conductores cuya licencia de conducción ya venció o se encuentra próxima a vencer en los próximos 30 días.
  - [ ] Se muestra el nombre del conductor, número de documento, categoría (`C1`, `C2`, etc.), fecha de expiración y badge con días restantes o indicador de vencida.
  - [ ] Los conductores con licencias vigentes por más de 30 días no saturan este widget.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [ ] Si todos los conductores están al día, el widget debe mostrar el estado vacío informativo: *"Todas las licencias al día"*.

### QA-10.7: Asistente de Puesta en Marcha / Onboarding y Guía del Sistema (`SystemGuidePage`)
- **Pasos:**
  1. En el Dashboard, ubicar el widget **Primeros Pasos / Puesta en Marcha**.
  2. Verificar la barra de progreso de configuración inicial (Vehículos $\rightarrow$ Conductores $\rightarrow$ Solicitantes $\rightarrow$ Despacho).
  3. Hacer clic en el enlace a la **Guía del Sistema** o navegar en el menú lateral a **Guía del Sistema** (`/admin/system-guide`).
- **Resultado Esperado (Happy Path):**
  - [ ] Se carga la página interactiva con la documentación operativa de CarFleet: Flujo de vida de viajes, diagrama de estados, políticas de combustible, matriz de roles y checklist de puesta en marcha.

---

## Módulo 11: Historial Detallado y Rendimiento por Vehículo (F08)

### QA-11.1: Consulta y Filtrado de Historial de Viajes en la Ficha del Vehículo
- **Pasos:**
  1. En el panel Admin, ir a **Gestión de Flota $\rightarrow$ Vehículos**.
  2. Seleccionar el vehículo `ABC-123` (Toyota Hilux) y presionar la acción **Ver** (`/admin/vehicles/{id}`).
  3. Navegar a la pestaña/sección **Historial de Viajes Realizados** (`TripsRelationManager`).
  4. Probar los filtros de tabla:
     - Filtrar por Conductor: Seleccionar *Carlos Andrés Rodríguez*.
     - Filtrar por Rango de Fechas: Seleccionar desde hace 10 días hasta hoy.
- **Resultado Esperado (Happy Path):**
  - [ ] La tabla lista el viaje cerrado `TRIP-2026-0001` mostrando Solicitante, Conductor, Odómetros (12.000 km $\rightarrow$ 12.250 km), Distancia (250 km), Fecha Real de Salida/Llegada y Estado `Cerrado`.
  - [ ] La acción de fila **Ver Detalle del Viaje** permite consultar el expediente completo sin salir del contexto de flota.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [ ] **Anti-Bypass de Dominio:** La relación de viajes en la ficha del vehículo **es estrictamente de solo lectura (Read-Only)**. No deben existir botones de "Crear Viaje", "Editar Viaje" ni "Eliminar Viaje" dentro de esta tabla para evitar saltarse las validaciones de despacho.

### QA-11.2: Métricas de Rendimiento Específicas del Vehículo (`VehiclePerformanceWidget`)
- **Pasos:**
  1. En la misma vista de detalle del vehículo `ABC-123` (o `XYZ-789`), observar las tarjetas de métricas en la cabecera:
- **Resultado Esperado (Happy Path):**
  - [ ] **Kilómetros Recorridos:** Muestra la distancia acumulada de los viajes finalizados/cerrados de ese vehículo específico.
  - [ ] **Rendimiento de Combustible:** Despliega los `km/galón` específicos del automotor calculados sobre sus abastecimientos.
  - [ ] **Galones Consumidos:** Sumatoria de galones registrados en sus vouchers de combustible.
  - [ ] **Costo por Kilómetro:** Valor monetario promedio por cada kilómetro recorrido (`$ / km`).

### QA-11.3: Historial de Tanqueos y Vouchers del Vehículo (`FuelLogsRelationManager`)
- **Pasos:**
  1. En la vista del vehículo `ABC-123`, navegar a la pestaña **Historial de Tanqueos**.
- **Resultado Esperado (Happy Path):**
  - [ ] Se listan los registros de combustible asociados (ej: `V-BOG-78901` de 14.50 gal y `V-BASE-0091` de 15.00 gal).
  - [ ] Se reflejan los costos, odómetros de carga y las fechas de los vouchers.

---

## Módulo 12: Facturación Comercial Simulada y Cuentas de Cobro (F09)

### QA-12.1: Emisión y Consolidación de Factura Comercial (`InvoiceResource`)
- **Pasos:**
  1. En el panel Admin, ir a **Gestión de Flota $\rightarrow$ Facturación $\rightarrow$ Nueva Factura** (`/admin/invoices/create`).
  2. En el campo **Cliente / Solicitante**, seleccionar a `Consorcio Vial de los Llanos S.A.S.` (Ing. Roberto Sánchez).
  3. Observar el campo **Viajes a Consolidar** (selector reactivo múltiple).
  4. Seleccionar el viaje cerrado `TRIP-2026-0001` (250 km).
  5. Configurar tarifas:
     - Tarifa Base Mínima: `$ 50.000` COP
     - Tarifa por Kilómetro: `$ 3.500` COP / km
  6. Escribir observaciones: *"Liquidación quincenal de transporte técnico a estación de bombeo"*.
  7. Presionar **Crear Factura**.
- **Resultado Esperado (Happy Path):**
  - [ ] La factura se crea exitosamente en estado `Emitida` (badge amarillo).
  - [ ] Se genera automáticamente el consecutivo correlativo único con formato `FACT-YYYY-NNNN` (ej: `FACT-2026-0001`).
  - [ ] El valor subtotal del servicio se liquida correctamente:  
        $$\text{Subtotal} = \max(50.000, 250 \text{ km} \times 3.500) = \$ 875.000\text{ COP}$$
  - [ ] El monto total de la factura queda guardado en `$ 875.000 COP`.
  - [ ] En la lista de facturas (`/admin/invoices`), el registro aparece ordenado con badge de estado, cliente, conteo de servicios (1 servicio) y total formateado.

### QA-12.2: Generación, Renderizado y Descarga de Factura en PDF
- **Pasos:**
  1. En la tabla de facturas (`/admin/invoices`), sobre la factura recién creada `FACT-2026-0001`, abrir el menú de acciones y seleccionar **Ver / Imprimir Factura**.
  2. Se abre una nueva pestaña con la ruta `/invoices/{id}/pdf`.
- **Resultado Esperado (Happy Path):**
  - [ ] La vista renderiza una factura comercial con diseño corporativo:
    - Encabezado institucional: *CARFLEET TRANSPORTATION S.A.S.*, NIT, dirección y correo de contacto.
    - Bloque de Factura: Número correlativo (`FACT-2026-0001`), fecha de emisión y badge de estado `EMITIDA`.
    - Tarjeta de Cliente: Nombre del solicitante (*Ing. Roberto Sánchez*), Empresa (*Consorcio Vial*), NIT (*900123456-1*), teléfono y correo.
    - Tabla detallada de servicios: Código del viaje (`TRIP-2026-0001`), Ruta (*Sede Principal Bogotá $\rightarrow$ Estación Villavicencio*), Vehículo y Placa (`ABC-123 Toyota Hilux`), Fecha de salida, Distancia recorrida (`250 km`) y Subtotal liquidado (`$ 875.000`).
    - Cuadro de Liquidación: Subtotal, Impuestos (0%) y Total a Pagar (`$ 875.000 COP`).
    - Observaciones de la liquidación y pie de página.
  - [ ] El botón superior **🖨️ Imprimir / Guardar como PDF** abre el cuadro de diálogo de impresión nativo del navegador sin desconfigurar los estilos.
  - [ ] Al imprimir o exportar a PDF, el botón de impresión se oculta automáticamente (`@media print`).

### QA-12.3: Registro de Pago e Inmutabilidad Financiera (`MarkInvoicePaidAction`)
- **Pasos:**
  1. En la tabla de facturas (`/admin/invoices`), ubicar la factura `FACT-2026-0001` en estado `Emitida`.
  2. En el menú de acciones de fila, presionar **Marcar Pagada**.
  3. Confirmar el modal de confirmación (*"¿Confirmar Pago?"*).
- **Resultado Esperado (Happy Path):**
  - [ ] La factura transiciona a estado `Pagada` con badge verde.
  - [ ] El sistema emite notificación de éxito: *"La factura FACT-2026-0001 ha sido marcada como pagada"*.
  - [ ] Los botones de acción para **Marcar Pagada** y **Anular Factura** desaparecen de la fila.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [ ] **Inmutabilidad de Factura Pagada:** Una factura en estado `Pagada` **nunca debe poder anularse ni modificarse**. Si se intentara forzar la anulación por backend, debe arrojar `InvoiceImmutableException`.

### QA-12.4: Anulación de Factura y Liberación Atómica de Viajes (`CancelInvoiceAction`)
- **Pasos:**
  1. Crear una nueva factura temporal para el cliente `Alimentos del Centro S.A.` consolidando el viaje cerrado `TRIP-2026-0002` (310 km). Factura generada: `FACT-2026-0002` en estado `Emitida`.
  2. En la fila de `FACT-2026-0002`, seleccionar la acción **Anular Factura**.
  3. En el modal emergente, ingresar motivo obligatorio: *"Error en tarifa pactada con el cliente comercial"*. Confirmar.
  4. Verificar el estado de la factura `FACT-2026-0002`.
  5. Ir a **Nueva Factura** (`/admin/invoices/create`), seleccionar nuevamente a `Alimentos del Centro S.A.` y desplegar el selector de viajes a consolidar.
- **Resultado Esperado (Happy Path):**
  - [ ] La factura `FACT-2026-0002` pasa a estado `Anulada` (badge rojo) y en sus notas queda anexada la traza: *"Motivo anulación: Error en tarifa pactada..."*.
  - [ ] **Liberación Inmediata de Viajes:** El viaje `TRIP-2026-0002` vuelve a figurar disponible en el selector de viajes para ser facturado en una nueva cuenta de cobro sin bloqueos.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [ ] **Anulación sin Motivo:** No debe permitir anular una factura si el campo de justificación se deja en blanco.
  - [ ] **Viajes Huérfanos/Bloqueados:** Los viajes de una factura anulada no deben quedar bloqueados permanentemente.

---

## Módulo 13: Invariantes de Dominio, Reglas de Negocio y Manejo de Excepciones (F08 - F09)

### QA-13.1: Bloqueo de Facturación para Viajes No Cerrados (`TripNotEligibleForInvoicingException`)
- **Pasos:**
  1. En `/admin/invoices/create`, seleccionar un solicitante que tenga viajes activos (ej: `Consorcio Vial de los Llanos`).
  2. Intentar facturar un viaje en estado `En Curso` (`TRIP-2026-0006`) o `Asignado` (`TRIP-2026-0007`).
- **Resultado Esperado (Happy Path / Blindaje UI):**
  - [ ] El selector reactivo de Filament **solo lista viajes en estado `Cerrado`** (`eligibleForInvoicing`), excluyendo automáticamente viajes programados, en curso, finalizados sin cerrar o cancelados.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [ ] Si se forzara el envío del ID de un viaje abierto por API o DTO, la acción de dominio `GenerateInvoiceAction` **debe lanzar inmediatamente `TripNotEligibleForInvoicingException`**, abortando la transacción de base de datos.

### QA-13.2: Prevención de Doble Facturación de un Mismo Viaje (`TripAlreadyInvoicedException`)
- **Pasos:**
  1. Con el viaje `TRIP-2026-0001` ya facturado en la factura activa `FACT-2026-0001` (`Emitida` o `Pagada`), ir a `/admin/invoices/create`.
  2. Seleccionar al solicitante `Consorcio Vial de los Llanos S.A.S.`.
- **Resultado Esperado (Happy Path):**
  - [ ] El viaje `TRIP-2026-0001` **no aparece en la lista de opciones** porque el scope `uninvoiced()` lo filtra automáticamente.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [ ] Si dos despachadores intentan emitir una factura simultáneamente con el mismo viaje, la base de datos y el bloqueo `lockForUpdate()` deben rechazar la segunda solicitud con `TripAlreadyInvoicedException: El viaje TRIP-XXXX ya se encuentra facturado en la factura FACT-YYYY-NNNN`.

### QA-13.3: Integridad de Solicitante Único en Factura (`RequesterMismatchException`)
- **Pasos:**
  1. Verificar el comportamiento del formulario al seleccionar viajes.
- **Resultado Esperado (Happy Path):**
  - [ ] El selector de viajes depende reactivamente del cliente seleccionado en el formulario y únicamente carga viajes asociados al `requester_id` actual.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [ ] Si se intentara enviar un DTO con viajes pertenecientes a dos solicitantes distintos (ej: viaje de Consorcio Vial + viaje de Alimentos del Centro), el dominio **debe arrojar `RequesterMismatchException`** indicando que todos los servicios de una factura deben pertenecer al mismo cliente.

### QA-13.4: Inmutabilidad de Facturas Pagadas y Anuladas (`InvoiceImmutableException`)
- **Pasos:**
  1. Abrir la vista de una factura pagada o anulada en Filament (`/admin/invoices/{id}`).
- **Resultado Esperado (Happy Path):**
  - [ ] El formulario y campos de observaciones se presentan deshabilitados.
  - [ ] Las acciones de cambio de estado están ocultas.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [ ] No debe ser posible re-anular una factura pagada ni marcar como pagada una factura que ya fue anulada.

### QA-13.5: Tolerancia a Cero Divisiones en Métricas de Rendimiento (`FleetPerformanceCalculatorService`)
- **Pasos:**
  1. Registrar un vehículo nuevo de prueba (ej: `TEST-001`) sin viajes finalizados ni tanqueos de combustible.
  2. Acceder a la vista del vehículo (`/admin/vehicles/{id}`).
- **Resultado Esperado (Happy Path):**
  - [ ] Las tarjetas de rendimiento muestran `0 km`, `0,00 km/gal`, `0,00 gal` y `$ 0 COP` con total estabilidad visual.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [ ] Ninguna consulta matemática debe generar excepciones de división por cero (`DivisionByZeroError`) en PHP.

### QA-13.6: Aislamiento de Seguridad RBAC en Facturación y Reportes ante Conductor
- **Pasos:**
  1. Iniciar sesión como conductor `driver@carfleet.test` en `https://carfleet.moisescorchodev.tech/driver`.
  2. Intentar navegar manualmente en la barra de direcciones del navegador a:
     - `https://carfleet.moisescorchodev.tech/admin/invoices`
     - `https://carfleet.moisescorchodev.tech/invoices/1/pdf`
- **Resultado Esperado (Happy Path):**
  - [ ] El sistema bloquea el acceso inmediatamente retornando **HTTP 403 Forbidden** o redirigiendo al panel autorizado del conductor.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [ ] Un conductor **nunca debe poder ver tarifas, montos facturados, facturas de clientes ni reportes globales de costos de la flota**.

---

## Módulo 14: UX, Modo Oscuro y Adaptabilidad Multi-Dispositivo

### QA-14.1: Renderizado de Widgets, Gráficos y Tablas en Modo Oscuro (Dark Theme)
- **Pasos:**
  1. En el panel Admin, activar el interruptor de **Modo Oscuro (Dark Mode)**.
  2. Revisar el Dashboard principal:
     - Tarjetas de estadísticas (`FleetOverviewWidget`).
     - Gráficos Chart.js (Dona de Flota, Kilometraje Mensual, Top Clientes).
     - Tabla de control de conductores y alertas de licencias.
  3. Revisar la tabla de Facturación (`/admin/invoices`) y la vista detallada de factura.
- **Resultado Esperado (Happy Path):**
  - [ ] Todos los textos, bordes, badges y contrastes de los gráficos se adaptan correctamente al fondo oscuro sin textos ilegibles, fondos blancos residuales ni problemas de contraste WCAG AA.

### QA-14.2: Responsividad de Gráficos y Tablas de Facturación en Pantallas Medianas y Móviles
- **Pasos:**
  1. Redimensionar la ventana del navegador a resolución de tablet (768px) y smartphone (375px).
  2. Inspeccionar la grilla del Dashboard y la tabla de facturación.
- **Resultado Esperado (Happy Path):**
  - [ ] Los widgets del dashboard se apilan verticalmente en una sola columna limpia.
  - [ ] Las tablas de Filament activan desplazamiento horizontal suave o modo tarjeta según corresponda, sin romper el layout ni desbordar la pantalla.

---

## 📊 Matriz Resumen de Ejecución QA (Features F08 - F09)

| Módulo Auditado | Casos Totales | Aprobados (Pass) | Fallidos (Fail) | Observaciones de Regresión |
|---|---|---|---|---|
| **10. Dashboard Analytics y Widgets (F08)** | 7 | [ ] | [ ] | |
| **11. Historial y Rendimiento de Vehículos (F08)** | 3 | [ ] | [ ] | |
| **12. Facturación Comercial Simulada (F09)** | 4 | [ ] | [ ] | |
| **13. Invariantes de Dominio y Excepciones (F08 - F09)** | 6 | [ ] | [ ] | |
| **14. UX, Modo Oscuro y Multi-Dispositivo** | 2 | [ ] | [ ] | |
| **TOTAL GENERAL F08 - F09** | **22 Casos** | **[ ]** | **[ ]** | **Aprobado para Producción:** SI / NO |
