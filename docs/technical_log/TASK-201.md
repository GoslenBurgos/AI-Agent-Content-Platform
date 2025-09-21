# Log Técnico: TASK-201 - Almacenamiento Seguro y Cliente de API Robusto

**Fecha:** 2025-09-13

## Resumen de Cambios

Esta tarea se centró en dos objetivos principales: mejorar la seguridad del almacenamiento de la clave de API de Gemini y aumentar la resiliencia del cliente de la API frente a fallos de comunicación.

### Fase 1: Almacenamiento Seguro de Credenciales

1.  **Creación de `IACP_Security_Helper`**:
    *   Se creó la clase `IACP_Security_Helper` en `includes/class-iacp-security-helper.php`.
    *   Implementa los métodos estáticos `encrypt()` y `decrypt()` que utilizan `openssl_encrypt` y `openssl_decrypt`.
    *   Para mayor seguridad, estos métodos se basan en las sales de autenticación (`AUTH_KEY`, `SECURE_AUTH_KEY`, etc.) definidas en el archivo `wp-config.php` de WordPress.

2.  **Encriptación en el Guardado**:
    *   Se modificó el método `register_settings` en `admin/class-iacp-admin.php`.
    *   Se utilizó el `sanitize_callback` de `register_setting` para interceptar el valor de `iacp_gemini_api_key` antes de guardarlo.
    *   La nueva función `encrypt_api_key` llama a `IACP_Security_Helper::encrypt()` para cifrar la clave.
    *   Se añadió lógica para prevenir la doble encriptación si el usuario guarda la configuración sin cambiar la clave.

3.  **Desencriptación en el Uso**:
    *   Se modificó el método `get_api_key` en la clase del cliente de API.
    *   Ahora obtiene la clave cifrada de la base de datos y utiliza `IACP_Security_Helper::decrypt()` para devolverla en texto plano, lista para ser usada en las llamadas a la API.

### Fase 2: Cliente de API Robusto

1.  **Refactorización a `IACP_Gemini_Client`**:
    *   La clase `IACP_Gemini_Api` fue renombrada a `IACP_Gemini_Client` para reflejar mejor su propósito.
    *   El archivo `includes/class-iacp-gemini-api.php` fue renombrado a `includes/class-iacp-gemini-client.php`.
    *   Se actualizaron todas las referencias a la clase en el proyecto.

2.  **Mecanismo de Reintentos (Retry)**:
    *   Se implementó un bucle de reintentos en el método `generate_text()` de `IACP_Gemini_Client`.
    *   Realiza hasta 3 intentos de llamada a la API si la respuesta es un `WP_Error` o un código de estado HTTP 5xx.
    *   Utiliza un "backoff exponencial", duplicando el tiempo de espera (`sleep()`) entre cada reintento (1s, 2s) para no sobrecargar la API.

3.  **Patrón Circuit Breaker**:
    *   Se añadió una capa de protección "circuit breaker" utilizando `wp_transients`.
    *   Si se producen 3 fallos de API en un período de 5 minutos, el circuito se "abre" y la aplicación deja de realizar llamadas a la API durante los siguientes 5 minutos.
    *   Durante este período, cualquier intento de llamar a `generate_text()` devolverá un error inmediato, informando al usuario que el servicio está temporalmente no disponible.
    *   Una llamada exitosa a la API reinicia el contador de fallos, "cerrando" el circuito.
