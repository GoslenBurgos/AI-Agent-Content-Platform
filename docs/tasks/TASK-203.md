Tarea: TASK-203 - Implementar Sistema de Cola de Tareas Asíncrono
Objetivo: Transformar el flujo de generación de contenido de síncrono a asíncrono para eliminar timeouts de AJAX y mejorar drásticamente la experiencia de usuario en tareas largas.

Plan de Ejecución (Checkpoints)
Fase 1: Infraestructura de la Cola
[X] Checkpoint 1.1: En includes/class-iacp-db.php, añadir la creación de una nueva tabla {$wpdb->prefix}iacp_jobs con los campos: id, content_id, status (pending, processing, completed, failed), payload (TEXT), logs (TEXT), created_at, processed_at.

[X] Checkpoint 1.2: Refactorizar el endpoint ajax_generate_content en admin/class-iacp-admin.php. En lugar de llamar a execute_content_workflow, ahora debe:

Serializar los datos de la tarea (título, tema, agentes) en un JSON (payload).

Insertar una nueva fila en la tabla iacp_jobs con estado pending.

Devolver inmediatamente el job_id al frontend.

Fase 2: El Worker Asíncrono
[X] Checkpoint 2.1: Crear una nueva clase includes/class-iacp-job-worker.php. Esta clase tendrá un método process_queue().

[X] Checkpoint 2.2: El método process_queue() buscará en la DB un trabajo con estado pending, lo marcará como processing, y luego ejecutará la lógica original de execute_content_workflow. Si tiene éxito, actualizará el trabajo a completed; si falla, a failed, guardando el error en el campo logs.

[X] Checkpoint 2.3: En la clase principal del plugin, registrar un nuevo evento de WP-Cron (add_action('iacp_process_job_queue', ...)) que se ejecute cada minuto y llame al método IACP_Job_Worker::process_queue().

Fase 3: Feedback en el Frontend
[X] Checkpoint 3.1: En admin/class-iacp-admin.php, crear un nuevo endpoint AJAX ajax_get_job_status que reciba un job_id y devuelva su estado desde la DB.

[X] Checkpoint 3.2: En admin/js/admin-scripts.js, después de recibir el job_id de ajax_generate_content, iniciar un "poller" (setInterval) que llame a ajax_get_job_status cada 5 segundos.

[X] Checkpoint 3.3: Actualizar la UI en la tabla de contenidos para mostrar un spinner o un texto "Procesando..." basado en el estado del trabajo. Cuando el estado cambie a completed o failed, detener el poller y refrescar la tabla de contenidos.

Archivos Relevantes
includes/class-iacp-db.php

admin/class-iacp-admin.php

(Nuevo) includes/class-iacp-job-worker.php

admin/js/admin-scripts.js

Estado Actual
Último Checkpoint Completado: 3.3

Próximo Checkpoint: Fin de la Tarea