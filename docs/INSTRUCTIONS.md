SOP del Agente de IA (v2)
Este es el Procedimiento Operativo Estándar para el desarrollo, testing y documentación del proyecto. Debe seguirse para cada tarea.

Ciclo de Desarrollo por Checkpoint
Este es el flujo de trabajo principal para la implementación de código. Se ejecuta para un checkpoint a la vez.

FASE DE LECTURA (Contexto):

Lee el docs/ROADMAP.md para identificar la TASK-ID activa.

Abre el "Plan de Ataque" correspondiente (ej. docs/tasks/TASK-201.md).

Identifica el próximo checkpoint a ejecutar basado en la sección "Estado Actual".

FASE DE EJECUCIÓN (Código):

Implementa los cambios de código necesarios para completar únicamente el checkpoint actual.

Asegúrate de seguir las mejores prácticas de codificación (nomenclatura clara, comentarios donde sea necesario).

FASE DE VALIDACIÓN (Revisión Humana):

Informa al desarrollador senior que el checkpoint ha sido completado y está listo para revisión.

ESPERA la aprobación del desarrollador. Él es responsable de revisar el código, marcar [x] en el checkpoint completado y actualizar el "Próximo Checkpoint" en el archivo del "Plan de Ataque".

Ciclo de Finalización de Tarea
Este flujo se ejecuta una sola vez, cuando todos los checkpoints de un "Plan de Ataque" han sido completados y validados.

FASE DE DOCUMENTACIÓN TÉCNICA:

Abre el archivo de log técnico correspondiente (ej. docs/technical_log/01_core_and_db.md).

Añade una nueva entrada con la fecha, resumiendo de forma técnica todos los cambios realizados en la tarea.

FASE DE DOCUMENTACIÓN PÚBLICA:

Abre docs/CHANGELOG.md.

Añade una línea de resumen en la sección [Unreleased], enlazando al log técnico para más detalles.

FASE DE CONTROL DE CALIDAD (Testing):

Instruye al desarrollador: "La Tarea [TASK-ID] está completa y documentada. Por favor, ejecuta el conjunto completo de pruebas con el comando npm test y proporcióname la salida para su análisis."

ESPERA el resultado. Si los tests fallan, inicia un nuevo ciclo de desarrollo para corregir los errores.

FASE DE COMMIT:

Una vez que los tests pasen, prepara el mensaje de commit usando el estándar de Conventional Commits.

Instruye al desarrollador: "Los tests han pasado con éxito. Por favor, ejecuta el siguiente comando para registrar los cambios: git add . && git commit -m \"feat(security): Implement API Key Encryption\""

Comandos de Distribución
Instruye al desarrollador: "Para crear el paquete instalable del plugin, ejecuta: npm run build"