Tarea: TASK-202 - Implementar Validación y Saneamiento Estricto
Objetivo: Reforzar la seguridad del plugin validando permisos y saneando todas las entradas de datos para prevenir vulnerabilidades.

Plan de Ejecución (Checkpoints)
[X] Checkpoint 1.1: Auditar todos los métodos ajax_* en admin/class-iacp-admin.php. Asegurarse de que la primera línea de cada método sea la llamada a _check_ajax_permissions(), que ya contiene current_user_can().

[X] Checkpoint 1.2: En includes/class-iacp-content-planner.php, dentro del método execute_content_workflow, sanear las variables $title y $theme que se concatenan en los prompts usando sanitize_text_field() y wp_kses_post() respectivamente.

[X] Checkpoint 1.3: Refactorizar el método clean_json_response en includes/class-iacp-content-planner.php. La nueva lógica debe usar una expresión regular para encontrar el primer bloque JSON válido (que empieza con { o [ y termina con } o ]) dentro de la cadena de respuesta, ignorando cualquier texto o markdown que lo rodee.

Archivos Relevantes
admin/class-iacp-admin.php

includes/class-iacp-content-planner.php

Estado Actual
Último Checkpoint Completado: 1.3

Próximo Checkpoint: Fin de la Tarea