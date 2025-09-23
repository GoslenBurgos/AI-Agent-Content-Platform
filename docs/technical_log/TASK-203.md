# Log Técnico: TASK-203 - Implementar Sistema de Cola de Tareas Asíncrono

**Fecha:** 2025-09-22

## Resumen de Cambios

Esta tarea se centró en transformar el flujo de generación de contenido de síncrono a asíncrono para eliminar timeouts de AJAX y mejorar drásticamente la experiencia de usuario en tareas largas.

### Fase 1: Infraestructura de la Cola

1.  **Nueva Tabla `iacp_jobs`**:
    *   En `includes/class-iacp-db.php`, se añadió la creación de una nueva tabla `{$wpdb->prefix}iacp_jobs` en la base de datos.
    *   La tabla almacena trabajos de generación de contenido con su estado (`pending`, `processing`, `completed`, `failed`), el payload con los datos de la tarea, y logs.

2.  **Refactorización del Endpoint `ajax_generate_content`**:
    *   El endpoint `ajax_generate_content` en `admin/class-iacp-admin.php` fue refactorizado.
    *   En lugar de ejecutar el proceso de generación de contenido directamente, ahora serializa los datos de la tarea y crea un nuevo trabajo en la tabla `iacp_jobs` con estado `pending`.
    *   Devuelve inmediatamente un `job_id` al frontend para su seguimiento.

### Fase 2: El Worker Asíncrono

1.  **Nueva Clase `IACP_Job_Worker`**:
    *   Se creó la nueva clase `IACP_Job_Worker` en `includes/class-iacp-job-worker.php`.
    *   Esta clase contiene el método `process_queue`, que es el corazón del sistema de procesamiento asíncrono.

2.  **Lógica de `process_queue`**:
    *   El método `process_queue` busca trabajos pendientes en la base de datos.
    *   Marca un trabajo como `processing` para evitar que otros procesos lo tomen.
    *   Ejecuta la lógica de `execute_content_workflow` con los datos del payload del trabajo.
    *   Si la generación de contenido tiene éxito, el trabajo se marca como `completed`. Si falla, se marca como `failed` y se guarda el mensaje de error en los logs.

3.  **Integración con WP-Cron**:
    *   Se registró un nuevo evento de WP-Cron, `iacp_process_job_queue`, que se ejecuta cada minuto.
    *   Este evento llama al método `IACP_Job_Worker::process_queue` para procesar los trabajos en la cola.

### Fase 3: Feedback en el Frontend

1.  **Nuevo Endpoint `ajax_get_job_status`**:
    *   Se creó un nuevo endpoint AJAX, `ajax_get_job_status`, en `admin/class-iacp-admin.php`.
    *   Este endpoint recibe un `job_id` y devuelve el estado actual del trabajo desde la base de datos.

2.  **Polling en el Frontend**:
    *   En `admin/js/admin-scripts.js`, se implementó un "poller" que se inicia después de que se crea un nuevo trabajo de generación de contenido.
    *   El poller llama al endpoint `ajax_get_job_status` cada 5 segundos para obtener el estado más reciente del trabajo.

3.  **Actualización de la Interfaz de Usuario**:
    *   La interfaz de usuario ahora muestra un estado de "Procesando..." para los trabajos que se están ejecutando.
    *   Una vez que el trabajo se completa o falla, el poller se detiene y la tabla de contenido se actualiza para reflejar el resultado.
