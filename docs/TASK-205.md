Tarea: TASK-205 - Implementar Sistema de Caché Inteligente y Optimizar DB
Objetivo: Reducir costos y latencia de la API cacheando respuestas, y mejorar el rendimiento de la base de datos.

Plan de Ejecución (Checkpoints)
Fase 1: Caché de API
[ ] Checkpoint 1.1: En includes/class-iacp-db.php, añadir la creación de una nueva tabla {$wpdb->prefix}iacp_api_cache con campos: prompt_hash (VARCHAR 64, PRIMARY KEY), response (LONGTEXT), created_at (DATETIME).

[ ] Checkpoint 1.2: Crear una nueva clase includes/class-iacp-api-cache.php. Esta clase tendrá métodos get($prompt) y set($prompt, $response).

[ ] Checkpoint 1.3: El método get() generará un hash sha256 del prompt, buscará en la tabla de caché y devolverá la respuesta si es válida (ej. menos de 24h de antigüedad). El método set() guardará la nueva respuesta en la caché.

[ ] Checkpoint 1.4: En IACP_Gemini_Client, antes de llamar a la API, usar IACP_Api_Cache::get(). Si devuelve una respuesta, usarla. Si no, llamar a la API y luego usar IACP_Api_Cache::set() para guardar el resultado.

Fase 2: Optimización de Base de Datos
[ ] Checkpoint 2.1: Auditar los CREATE TABLE en includes/class-iacp-db.php. Añadir INDEX a las columnas status en iacp_content y publish_date en iacp_social_media.

[ ] Checkpoint 2.2: Crear un botón en la página de "Settings" (admin/views/settings.php) que, al ser presionado, llame a un nuevo endpoint AJAX ajax_clear_api_cache que trunque la tabla iacp_api_cache.

Archivos Relevantes
includes/class-iacp-db.php

includes/class-iacp-gemini-client.php

(Nuevo) includes/class-iacp-api-cache.php

admin/views/settings.php

admin/class-iacp-admin.php

Estado Actual
Último Checkpoint Completado: Ninguno.

Próximo Checkpoint: 1.1