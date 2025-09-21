Tarea: TASK-201 - Implementar Almacenamiento Seguro y Cliente de API Robusto
Objetivo: Proteger las credenciales del usuario y hacer que la comunicación con la API de Gemini sea resiliente a fallos.

Plan de Ejecución (Checkpoints)
Fase 1: Almacenamiento Seguro de Credenciales
[X] Checkpoint 1.1: Crear un nuevo archivo includes/class-iacp-security-helper.php. Dentro, crear una clase IACP_Security_Helper con dos métodos estáticos: encrypt($data) y decrypt($data). Estos métodos deben usar openssl_encrypt y openssl_decrypt con las sales definidas en wp-config.php (AUTH_KEY, SECURE_AUTH_KEY, etc.) para una mayor seguridad.

[X] Checkpoint 1.2: Modificar el método register_settings en admin/class-iacp-admin.php. Interceptar el guardado de iacp_gemini_api_key para usar IACP_Security_Helper::encrypt() antes de que se guarde en la base de datos.

[X] Checkpoint 1.3: Modificar includes/class-iacp-gemini-api.php. Reemplazar el método get_api_key() para que obtenga el valor cifrado de la base de datos y lo descifre usando IACP_Security_Helper::decrypt() antes de devolverlo.

Fase 2: Cliente de API Robusto
[X] Checkpoint 2.1: Renombrar la clase IACP_Gemini_Api a IACP_Gemini_Client. Mover el archivo class-iacp-gemini-api.php a class-iacp-gemini-client.php. Actualizar todas las referencias a esta clase en el proyecto.

[X] Checkpoint 2.2: Dentro de IACP_Gemini_Client, implementar un bucle de reintentos en el método generate_text(). Debe intentar la llamada a wp_remote_post hasta 3 veces si la respuesta es un error de WP o un código de estado 5xx. Implementar un sleep() incremental entre reintentos (ej. 1s, 2s, 4s) para crear un "backoff exponencial".

[X] Checkpoint 2.3 (Opcional - Avanzado): Implementar un "circuit breaker" simple. Usar wp_transients para registrar los fallos. Si hay 3 fallos consecutivos en menos de 5 minutos, la función generate_text() debe devolver un error inmediatamente sin intentar llamar a la API durante los próximos 5 minutos.

Archivos Relevantes
admin/class-iacp-admin.php

includes/class-iacp-gemini-api.php (a ser renombrado)

(Nuevo) includes/class-iacp-security-helper.php

Estado Actual
Último Checkpoint Completado: 2.3

Próximo Checkpoint: 2.3