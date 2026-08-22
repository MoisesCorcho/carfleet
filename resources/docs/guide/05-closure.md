# ✍️ Firma Digital, Cierre Formal e Inmutabilidad

El protocolo de cierre formal es el hito que valida legal y contablemente la prestación del servicio.

---

## 📱 1. Captura de Firma Digital del Solicitante

1. Al finalizar el recorrido, el conductor solicita la firma de conformidad del cliente en su dispositivo móvil.
2. El solicitante firma directamente sobre el panel táctil en la pantalla.
3. El sistema convierte la firma vectorial (Base64) a formato imagen **PNG** y la almacena de manera segura en el Storage del sistema.

---

## 🔒 2. Invariantes para el Cierre Formal

Un viaje solo puede ser cerrado formalmente si cumple el **100%** de los siguientes requisitos:

| Requisito | Estado / Validación |
|---|---|
| **Estado del Viaje** | Debe encontrarse en estado `Finalizado` o con salida y llegada registradas. |
| **Odómetro de Salida** | Lectura numérica registrada y respaldada por foto de evidencia. |
| **Odómetro de Llegada** | Lectura numérica registrada (&ge; salida) y respaldada por foto. |
| **Firma Digital** | Firma del solicitante capturada y guardada en el registro. |

---

## 🛡️ 3. Inmutabilidad Absoluta

Una vez ejecutada la acción **"Cerrar Viaje"**:
* **Bloqueo del Registro:** El viaje pasa al estado terminal **`Cerrado`**. Queda estrictamente congelado contra cualquier modificación, eliminación o cambio de recursos.
* **Inmutabilidad Satélite:** Los vales de combustible, fotos de evidencia y firmas asociadas al viaje cerrado tampoco pueden ser modificados ni eliminados.
* **Liberación de Flota:** El vehículo asignado retorna automáticamente a estado **`Disponible`**, listo para ser despachado en un nuevo servicio.
