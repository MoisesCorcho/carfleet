# Checklist Integral de QA y Matriz de Invariantes — CarFleet

> **Ambiente de Pruebas:** `https://carfleet.moisescorchodev.tech/`  
> **Versión del Sistema:** CarFleet v1.0 (Features F01 a F07)  
> **Propósito:** Guía de verificación funcional y validación de reglas de negocio/invariantes de dominio para asegurar cero regresiones antes del paso a producción.

---

## 🔑 Directorio de Accesos, Cuentas y Datos Sembrados (Seeders)

### 1. Cuentas Administrativas y Despacho

| Panel           | URL de Acceso                                 | Rol           | Email                              | Contraseña | Propósito en QA                                                  |
| --------------- | --------------------------------------------- | ------------- | ---------------------------------- | ---------- | ---------------------------------------------------------------- |
| **Panel Admin** | `https://carfleet.moisescorchodev.tech/admin` | `super_admin` | `admin@carfleet.test` _(o `.com`)_ | `password` | Control total, flota, conductores, viajes, tanqueos y auditoría. |
| **Panel Admin** | `https://carfleet.moisescorchodev.tech/admin` | `admin`       | `despacho@carfleet.test`           | `password` | Coordinador de despacho de viajes y asignación de recursos.      |

### 2. Cuentas de Conductores y Escenarios de Prueba

| Panel      | URL de Acceso                                  | Conductor               | Email                   | Contraseña | Categoría Licencia        | Estado en BD / Escenario de Prueba Asignado                                                           |
| ---------- | ---------------------------------------------- | ----------------------- | ----------------------- | ---------- | ------------------------- | ----------------------------------------------------------------------------------------------------- |
| **Driver** | `https://carfleet.moisescorchodev.tech/driver` | Carlos Andrés Rodríguez | `driver@carfleet.test`  | `password` | **C1** (Público)          | **Tiene viaje `TRIP-2026-0005` (Finalizado)** $\rightarrow$ Probar Firma Digital y Cierre.            |
| **Driver** | `https://carfleet.moisescorchodev.tech/driver` | Jorge Eliécer Gaitán    | `driver2@carfleet.test` | `password` | **C2** (Camiones/Público) | **Tiene viaje `TRIP-2026-0006` (En Curso)** en `RUN-808` $\rightarrow$ Probar Finalización / Tanqueo. |
| **Driver** | `https://carfleet.moisescorchodev.tech/driver` | María Fernanda Gómez    | `driver3@carfleet.test` | `password` | **C1** (Vans/Público)     | **Tiene viaje `TRIP-2026-0007` (Asignado)** en `ACT-707` $\rightarrow$ Probar Inicio de Servicio.     |
| **Driver** | `https://carfleet.moisescorchodev.tech/driver` | Juan Pablo Montoya      | `driver4@carfleet.test` | `password` | **C3** (Articulados)      | **Disponible** $\rightarrow$ Probar nueva asignación de viaje programado.                             |
| **Driver** | `https://carfleet.moisescorchodev.tech/driver` | Andrés Felipe Arias     | `driver5@carfleet.test` | `password` | **B1** (Solo Particular)  | **Disponible Particular** $\rightarrow$ Probar rechazo al asignar vehículo público.                   |
| **Driver** | `https://carfleet.moisescorchodev.tech/driver` | Ricardo Arjona Morales  | `driver6@carfleet.test` | `password` | **C1** (Vencida)          | **Inactivo** $\rightarrow$ Probar rechazo al intentar asignarle cualquier viaje.                      |

### 3. Flota Sembrada en Base de Datos

- **`ABC-123`**: Toyota Hilux 4x4 (Diésel, 12.500 km) $\rightarrow$ `Disponible` (Público).
- **`XYZ-789`**: Renault Master Furgón Maxi (Diésel, 28.400 km) $\rightarrow$ `Disponible` (Público).
- **`FLT-101`**: Chevrolet D-Max (Diésel, 18.200 km) $\rightarrow$ `Disponible` (Público).
- **`FLT-202`**: Nissan Frontier PRO-4X (Gasolina, 34.000 km) $\rightarrow$ `Disponible` (Particular).
- **`VAN-303`**: Mercedes-Benz Sprinter 19P (Diésel, 52.100 km) $\rightarrow$ `Disponible` (Público).
- **`SED-404`**: Toyota Corolla Automóvil (Gasolina, 8.900 km) $\rightarrow$ `Disponible` (Particular).
- **`SUV-505`**: Ford Explorer XLT (Gasolina, 22.300 km) $\rightarrow$ `Disponible` (Particular).
- **`TRK-606`**: Hino Dutro City (Diésel, 65.400 km) $\rightarrow$ `Mantenimiento` (En taller por frenos).
- **`ACT-707`**: Hyundai Staria 9P (Diésel, 14.800 km) $\rightarrow$ `Asignado` (Vinculado a `TRIP-2026-0007`).
- **`RUN-808`**: Renault Duster 4x4 (Gasolina, 26.500 km) $\rightarrow$ `En Viaje` (Vinculado a `TRIP-2026-0006`).

---

## 📑 Índice de Módulos a Auditar

1. [Módulo 1: Autenticación, Seguridad y Aislamiento de Paneles](#módulo-1-autenticación-seguridad-y-aislamiento-de-paneles-rbac)
2. [Módulo 2: Gestión de Flota y Vehículos (F01)](#módulo-2-gestión-de-flota-y-vehículos-f01)
3. [Módulo 3: Gestión de Conductores y Licencias (F02)](#módulo-3-gestión-de-conductores-y-licencias-f02)
4. [Módulo 4: Solicitantes y Centros de Costo (F03)](#módulo-4-solicitantes-y-centros-de-costo-f03)
5. [Módulo 5: Planificación, Despacho y Asignación de Viajes (F04)](#módulo-5-planificación-despacho-y-asignación-de-viajes-f04)
6. [Módulo 6: Operación en Ruta y Evidencias de Odómetro (F05)](#módulo-6-operación-en-ruta-y-evidencias-de-odómetro-f05)
7. [Módulo 7: Tanqueos y Vouchers de Combustible (F06)](#módulo-7-tanqueos-y-vouchers-de-combustible-f06)
8. [Módulo 8: Firma Digital de Conformidad y Cierre Formal (F07)](#módulo-8-firma-digital-de-conformidad-y-cierre-formal-f07)
9. [Módulo 9: Integridad de Dominio, Teardown y UX Multi-Dispositivo](#módulo-9-integridad-de-dominio-teardown-y-ux-multi-dispositivo)

---

## Módulo 1: Autenticación, Seguridad y Aislamiento de Paneles (RBAC)

### QA-01.1: Acceso y Aislamiento del Panel Administrativo

- **Pasos:**
  1. Ingresar a `https://carfleet.moisescorchodev.tech/admin/login`.
  2. Autenticarse con `admin@carfleet.test` / `password`.
- **Resultado Esperado (Happy Path):**
  - [x] El sistema inicia sesión y redirige al Dashboard principal con menú completo (_Vehículos_, _Conductores_, _Solicitantes_, _Viajes_, _Tanqueos_).
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] Un usuario con rol `driver` (ej: `driver@carfleet.test`) **no debe poder acceder** a `/admin`. Si intenta ingresar, debe recibir HTTP 403 Forbidden o redirección.

### QA-01.2: Acceso y Aislamiento del Panel de Conductor

- **Pasos:**
  1. Ingresar a `https://carfleet.moisescorchodev.tech/driver/login`.
  2. Autenticarse con `driver@carfleet.test` / `password` (Carlos Andrés Rodríguez).
- **Resultado Esperado (Happy Path):**
  - [x] Se accede al panel móvil/desktop mostrando únicamente sus propios viajes asignados (`TRIP-2026-0001` y `TRIP-2026-0005`).
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] El conductor **nunca debe ver viajes de otros conductores** (ej: no debe ver el viaje `TRIP-2026-0006` de Jorge Gaitán).
  - [x] El conductor **nunca debe poder ver ni acceder** a las opciones administrativas de configuración, edición de tarifas o gestión de otros usuarios.

### QA-01.3: Principio de Mínimo Privilegio para Despacho / Operaciones (`admin`)

- **Pasos:**
  1. Ingresar a `https://carfleet.moisescorchodev.tech/admin/login`.
  2. Autenticarse con `despacho@carfleet.test` / `password` (rol `admin`).
- **Resultado Esperado (Happy Path):**
  - [x] El despachador tiene acceso completo a los módulos operativos: _Vehículos_, _Conductores_, _Solicitantes_, _Viajes_ y _Tanqueos_.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] **Acceso a Roles Denegado:** La sección **Roles y Permisos** **no debe aparecer en el menú lateral**.
  - [x] Si intenta navegar manualmente a `/admin/roles`, el sistema **debe bloquear el acceso (HTTP 403 Forbidden)** (aparece 404 en vez de 403, pero si, el acceso es denegado).

---

## Módulo 2: Gestión de Flota y Vehículos (F01)

### QA-02.1: Registro de Vehículo con Validación de Placa Colombiana

- **Pasos:**
  1. En el panel Admin, ir a **Gestión de Flota $\rightarrow$ Vehículos $\rightarrow$ Nuevo Vehículo**.
  2. Probar ingresar placa en minúsculas (ej: `flt-999` o `wxy123`) (Las placas no se pueden ingresar en minusculas, el campo fuerza las mayusculas).
  3. Completar marca `Toyota`, modelo `Prado`, año `2024`, tipo `Camioneta`, servicio `Público`, odómetro inicial `0` y combustible `Diésel`. Guardar.
- **Resultado Esperado (Happy Path):**
  - [x] El vehículo se crea en estado `Disponible` y la placa se sanitiza automáticamente en mayúsculas (`FLT-999` o `WXY-123`) (Meti la placa como wxy123 y asi se quedo, en mayusculas, si, pero sin el guion, se podria colocar que el campo ponga un guion automatico despues de escribir 3 caracteres? para efectos de continuar con el happy path lo corregi manualmente editando el registro).
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] **Placa Duplicada:** Intentar crear un vehículo con placa `ABC-123` (ya existente) debe ser rechazado por error de unicidad (marca un error "The placa del Vehiculo has already been taken" hay que cambiar el mensaje, pero la validacion esta bien).
  - [x] **Formato Inválido:** No debe aceptar placas con caracteres especiales (ej: `PLACA_#1`).
  - [x] **Odómetro Negativo:** No debe permitir odómetro inicial `< 0`.

### QA-02.2: Calibración y Ajuste Manual de Odómetro

- **Pasos:**
  1. En la tabla de vehículos, ubicar el vehículo `ABC-123` (en estado `Disponible`, odómetro actual 12.500 km).
  2. Seleccionar la acción de fila **Ajustar Odómetro**.
  3. Ingresar nuevo kilometraje `12550` y escribir motivo: _"Calibración de odómetro tras alineación y balanceo"_. Confirmar.
- **Resultado Esperado (Happy Path):**
  - [x] El odómetro del vehículo se actualiza a 12.550 km y en las notas del vehículo se estampa la traza de auditoría con fecha, hora y motivo.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] **Vehículo en Servicio Activo:** Intentar ajustar el odómetro sobre el vehículo `RUN-808` (en estado `En Viaje`) **debe ser bloqueado** indicando que el vehículo se encuentra en servicio activo.
  - [x] **Motivo Vacío:** No debe permitir procesar el ajuste con el campo motivo en blanco (Intente rellenar la informacion con espacios, pero no me lo permitio, salio el error "The motivo del ajuste / Justificacion field is required" , es decir, la validacion funciona).

### QA-02.3: Prevención de Eliminación de Vehículos en Servicio

- **Pasos:**
  1. Intentar eliminar el vehículo `RUN-808` (`En Viaje`) o `ACT-707` (`Asignado`).
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] **Eliminación con Viaje Activo:** El sistema **debe bloquear el borrado** arrojando excepción de dominio informando que el vehículo tiene servicios activos (efectivamente sale un error Internal Server Error y dice eso que tiene servicios activos asignados, hay que cambiar eso para que sea una notificacion toast como las otras).

---

## Módulo 3: Gestión de Conductores y Licencias (F02)

### QA-03.1: Alta de Conductor y Categorías de Licencia

- **Pasos:**
  1. En el panel Admin, ir a **Gestión de Flota $\rightarrow$ Conductores $\rightarrow$ Nuevo Conductor**.
  2. Ingresar nombre completo, cédula, teléfono, licencia `LIC-998877`, categoría `C2` y fecha de vencimiento futura. Guardar
     Nota: la licencia igual que las placas, la puse sin el guion y asi me dejo crearla, por el momento pondre el guion manualmente. Despues de editar le puse un guion y arroba al final al campo numero de documento "123123123-@" y me dejo guardarlo. Intente poner una arroba en todos los campos que pudiera escribir fuera de los de la parte de "Cuenta de Usuario" y solo el campo de telefono me marco error por el @.
- **Resultado Esperado (Happy Path):**
  - [x] Conductor registrado exitosamente en estado `Activo`.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] **Cédula Duplicada:** Intentar registrar conductor con cédula `1020304050` (de Carlos Andrés) debe arrojar error de duplicado (Intente ponerle la misma cuenta de usuario tambien "the cuenta de Usuario has already been taken." y "The número de Documento has already been taken.").
  - [x] **Licencia Vencida:** Si la fecha de vencimiento es pasada (como el caso de Ricardo Arjona `driver6`), el conductor no es elegible para asignarle viajes.

### QA-03.2: Prevención de Eliminación de Conductor en Servicio

- **Pasos:**
  1. Intentar eliminar al conductor Jorge Gaitán (quien está conduciendo el viaje `TRIP-2026-0006`).
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] El sistema **debe bloquear la eliminación** indicando que el conductor tiene servicios activos (efectivamente no se deja eliminar, pero en vez de una notificacion sale un Internal Server Error App\Exceptions\Trips\DriverNotEligibleException).

---

## Módulo 4: Solicitantes y Centros de Costo (F03)

### QA-04.1: Creación y Consulta de Solicitante

- **Pasos:**
  1. En el panel Admin, ir a **Gestión de Flota $\rightarrow$ Solicitantes**.
  2. Verificar los 6 solicitantes sembrados (_Consorcio Vial, Alimentos del Centro, Logística Nacional, etc._).
  3. Crear un nuevo solicitante: Tipo NIT, Documento `900999888-1`, Nombre `Ing. Laura Restrepo`, Empresa `Minas y Energía S.A.S.`, Teléfono `+57 318 000 1122`.
     NOTA: el solicitante Ing. Roberto Sanchez del Consocrcio Vial, tiene un viaje en curso el TRIP-2026-0006 y aun asi me permitio agregarle una @ a su NIT en la seccion de solicitantes, aparte al intentar borrarlo sale App\Exceptions\Requesters\InvalidRequesterException.
- **Resultado Esperado (Happy Path):**
  - [x] Registro creado y disponible inmediatamente en el formulario de viajes.

---

## Módulo 5: Planificación, Despacho y Asignación de Viajes (F04)

### QA-05.1: Creación de Solicitud de Viaje (`Programado`)

- **Pasos:**
  1. Ir a **Gestión de Flota $\rightarrow$ Viajes $\rightarrow$ Crear Viaje**.
  2. Seleccionar solicitante `Alimentos del Centro S.A.`, Origen `Bogotá`, Destino `Medellín` (los solicitantes en la lista desplegable solo se muestran por el nombre de la persona asociada a la empresa, no la empresa, por lo que buscar Alimentos del Centro es imposible, asi que por seguir el happy path pondre yo manualmente a la Dra. Carmen Cecilia que es quien esta asociada a esa empresa).
  3. Programar salida para mañana 08:00 AM y llegada mañana 06:00 PM. Dejar vehículo y chofer vacíos. Guardar.
     NOTA: al intentar cambiar a un solicitante en un viaje en curso sale el error App\Exceptions\Trips\InvalidTripStateException.
- **Resultado Esperado (Happy Path):**
  - [x] Viaje creado exitosamente en estado `Programado` con código correlativo (ej: `TRIP-2026-0011`).
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] **Inconsistencia Horaria:** Si la fecha/hora de llegada es anterior o igual a la salida, debe rechazar la creación.
  - [x] **Asignación Parcial:** Seleccionar vehículo sin chofer (o viceversa) debe exigir ambos recursos.

### QA-05.2: Asignación Atómica y Bloqueo por Incompatibilidad

- **Pasos:**
  1. En la lista de viajes, sobre el viaje `TRIP-2026-0008` (Programado), presionar **Asignar Recursos**.
  2. **Caso A (Incompatibilidad):** Seleccionar el vehículo público `ABC-123` y el chofer Andrés Felipe Arias (`driver5@carfleet.test` con licencia particular **B1**). Confirmar.
  3. **Caso B (Asignación Exitosa):** Cambiar chofer a Juan Pablo Montoya (`driver4` con licencia **C3**). Confirmar.
- **Resultado Esperado (Happy Path):**
  - [x] En Caso B, el viaje transiciona a `Asignado` y el vehículo `ABC-123` pasa automáticamente a `Asignado`.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] En Caso A, el sistema **debe rechazar la asignación** con error: _"El conductor no posee categoría de licencia autorizada para servicio público"_.
  - [x] **Vehículo Ocupado:** No debe permitir asignar `RUN-808` (en viaje) ni `TRK-606` (en mantenimiento) (A ver es que ni siquiera salen en la lista de Vehiculo Disponible).

### QA-05.3: Cancelación de Viaje y Liberación de Recursos

- **Pasos:**
  1. Sobre el viaje `TRIP-2026-0007` (en estado `Asignado` con vehículo `ACT-707`), ejecutar **Cancelar Viaje** indicando motivo.
- **Resultado Esperado (Happy Path):**
  - [x] El viaje pasa a `Cancelado` y el vehículo `ACT-707` **queda liberado inmediatamente en estado `Disponible`**.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] No debe permitir cancelar viajes `En Curso` o `Cerrados`.

---

## Módulo 6: Operación en Ruta y Evidencias de Odómetro (F05)

### QA-06.1: Inicio de Servicio desde Panel Conductor (`/driver`)

- **Pasos:**
  1. Iniciar sesión en `/driver` con `driver3@carfleet.test` (María Fernanda Gómez, tiene viaje `TRIP-2026-0007`).
     NOTA: Como ese viaje lo cancele por otra prueba, pues le asigne uno de los 2 que estaban sin asignar.
  2. Presionar **Iniciar Servicio**.
  3. Ingresar odómetro de salida `14800` (odómetro actual del vehículo `ACT-707`) y adjuntar fotografía. Confirmar.
- **Resultado Esperado (Happy Path):**
  - [x] El viaje pasa a `En Curso` y el vehículo `ACT-707` pasa a `En Viaje` (como tuve que asignar un viaje distinto tengo el vehiculo ABC-123, pero todo parece en orden).
  - [ ] Se genera la evidencia fotográfica de salida. (No puedo ver la foto que subi, por alguna razon, al tratar de clicar en ella me sale un error del Localhost "Vaya… no se puede obtener acceso a esta página
        localhost rechazó la conexión." Y en la miniatura tampoco)
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] **Odómetro Menor:** Si se ingresa un odómetro menor a 14.800 km (ej: `14000`), el sistema debe rechazar el inicio.
  - [x] **Odómetro Menor:** Si se ingresa un odómetro menor o igual a 14.800 km (ej: `14000`), el sistema debe rechazar el cierre del viaje.

### QA-06.2: Finalización de Servicio desde Panel Conductor

- **Pasos:**
  1. Iniciar sesión en `/driver` con `driver2@carfleet.test` (Jorge Gaitán, viaje en curso `TRIP-2026-0006` en `RUN-808` con km salida 26.500).
  2. Presionar **Finalizar Servicio**.
  3. Ingresar odómetro de llegada `26720` (distancia 220 km) y adjuntar foto del odómetro final. Confirmar.
- **Resultado Esperado (Happy Path):**
  - [x] El viaje pasa a `Finalizado`.
  - [x] La distancia recorrida se calcula automáticamente en 220 km.
  - [x] El vehículo `RUN-808` regresa a estado `Disponible` con odómetro en 26.720 km.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] **Odómetro Llegada $\le$ Salida:** Ingresar un valor $\le 26.500$ km debe ser rechazado inmediatamente.

---

## Módulo 7: Tanqueos y Vouchers de Combustible (F06)

### QA-07.1: Registro de Tanqueo durante el Viaje

- **Pasos:**
  1. En el viaje `TRIP-2026-0006` (o en `/admin/fuel-logs/create`), abrir **Registrar Tanqueo**.
  2. Asociar el viaje `TRIP-2026-0006`, fecha/hora actual, galones `12.5`, costo `$180.000`, odómetro `26600` y voucher `V-PRUEBA-01`. Adjuntar foto del recibo. Guardar.
- **Resultado Esperado (Happy Path):**
  - [x] Se crea el registro de combustible y se vincula automáticamente como evidencia al expediente del viaje.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] **Fecha Anterior a Salida:** Ingresar una fecha de ayer (antes de que el viaje iniciara) debe ser **bloqueado con error** (Lo probe tambien colocando una fecha y hora posterior al viaje).
  - [x] **Odómetro fuera del Viaje:** Ingresar un odómetro superior al kilometraje final del viaje debe ser **bloqueado**.

### QA-07.2: Inmutabilidad de Tanqueos en Viajes Cerrados

- **Pasos:**
  1. En `/admin/fuel-logs`, buscar el voucher `V-BOG-78901` (perteneciente al viaje cerrado `TRIP-2026-0001`).
- **Resultado Esperado (Happy Path):**
  - [x] Los botones de **Editar** y **Eliminar** en la fila de la tabla están ocultos (incluso si se entra a la visualizadion del detalle del registro no deja modificarlo porque al intentar guardar cambios dice que el viaje asociado ya fue cerrado).

---

## Módulo 8: Firma Digital de Conformidad y Cierre Formal (F07)

### QA-08.1: Captura de Firma Digital en Viaje Finalizado

- **Pasos:**
  1. Iniciar sesión en `/driver` con `driver@carfleet.test` (o en `/admin/trips`).
  2. Abrir el viaje `TRIP-2026-0005` (en estado `Finalizado`).
  3. Presionar **Firmar Conformidad**.
  4. Ingresar nombre del firmante: `Dra. Elena Vargas (Directora RRHH)`.
  5. Dibujar la firma en el canvas blanco y presionar **Guardar Firma**.
- **Resultado Esperado (Happy Path):**
  - [ ] La firma se almacena en `Storage` y se despliega la tarjeta de vista previa con fondo blanco nítido, trazo negro y sello de auditoría (No se por que, pero a mi no se me muestra ninguna vista previa, estoy revisandolo desde el usuario despacho@carfleet.test en la seccion de viajes y sale como si no hubiera imagen, es decir, no encuentra tal cosa para mostrarme, al decirle que la abra en otra pestaña vuelve a decirme que error de localhost como con el caso anterior mencionado).
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [ ] **Lienzo en Blanco:** Si no se dibuja ningún trazo, debe exigir firmar antes de enviar (No veo que me marque ningun error, pero es real que no me deja avanzar dandole en el boton "Guardar Firma").
  - [x] **Firmar Viaje en Estado No Finalizado:** No debe estar disponible la opción en viajes `Programados` o `En Curso` (tampoco se puede cuando estan en estado "Asignado" o "Cerrado").

### QA-08.2: Cierre Formal de Viaje (`Cerrado` / Inmutable)

- **Pasos:**
  1. Con el viaje `TRIP-2026-0005` firmado y con sus fotos de odómetro completas, presionar **Cerrar Viaje**.
  2. Confirmar el modal de cierre definitivo.
- **Resultado Esperado (Happy Path):**
  - [x] El viaje transiciona a estado `Cerrado`.
  - [x] El expediente se bloquea contra cualquier modificación futura.
- **Lo que NUNCA debe suceder (Invariantes / Edge Cases):**
  - [x] **Cerrar sin Firma:** Si un viaje finalizado no tiene firma capturada, el botón de cierre debe fallar con `TripMissingSignatureException` (El driver no tiene la opcion de cerrar el viaje si este no tiene firma).
  - [x] **Cerrar sin Evidencias:** Si faltara la foto de salida o llegada, debe arrojar `TripMissingEvidenceException` (el campo de imagen es requerido, por lo que no permite iniciar un viaje sin la foto ni marcarlo como finalizado sin ella).

---

## Módulo 9: Integridad de Dominio, Teardown y UX Multi-Dispositivo

### QA-09.1: Visibilidad en Modo Oscuro (Dark Theme)

- **Pasos:**
  1. En el panel Admin o Driver, activar **Dark Mode**.
  2. Abrir el modal de firma digital y consultar los expedientes de los viajes cerrados (`TRIP-2026-0001` a `TRIP-2026-0004`).
- **Resultado Esperado (Happy Path):**
  - [x] El canvas y las firmas se muestran sobre hoja blanca con trazo negro perfectamente contrastado y legible (el canvas se ve siempre blanco porque el modo oscuro es lo que he usado durante todas estas pruebas, pero las firmas no se me muestran por este error de las imagenes que menciono, aparece que el localhost rechazo la conexion, no se a que se debe).

### QA-09.2: Responsividad en Pantallas Móviles

- **Pasos:**
  1. Abrir `/driver` en un smartphone (o emulador de 360px de ancho).
  2. Verificar los componentes de tabla, modales de inicio/fin y el pad de firma.
- **Resultado Esperado (Happy Path):**
  - [x] Ningún elemento produce scroll horizontal ni cortes visuales.

---

## 📊 Matriz Resumen de Ejecución QA

| Módulo Auditado                       | Casos Totales | Aprobados (Pass) | Fallidos (Fail) | Observaciones de Regresión            |
| ------------------------------------- | ------------- | ---------------- | --------------- | ------------------------------------- |
| **1. Autenticación y RBAC**           | 3             | [ ]              | [ ]             |                                       |
| **2. Flota y Vehículos (F01)**        | 3             | [ ]              | [ ]             |                                       |
| **3. Conductores y Licencias (F02)**  | 2             | [ ]              | [ ]             |                                       |
| **4. Solicitantes (F03)**             | 1             | [ ]              | [ ]             |                                       |
| **5. Planificación y Despacho (F04)** | 3             | [ ]              | [ ]             |                                       |
| **6. Operación y Odómetro (F05)**     | 2             | [ ]              | [ ]             |                                       |
| **7. Combustible y Vouchers (F06)**   | 2             | [ ]              | [ ]             |                                       |
| **8. Firma y Cierre (F07)**           | 2             | [ ]              | [ ]             |                                       |
| **9. Integridad y UX**                | 2             | [x]              | [ ]             |                                       |
| **TOTAL GENERAL**                     | **20 Casos**  | **[ ]**          | **[ ]**         | **Aprobado para Producción:** SI / NO |
