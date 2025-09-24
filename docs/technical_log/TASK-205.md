# Log Técnico: TASK-205 - Implementar Sistema de Caché Inteligente y Optimizar DB

**Fecha:** 2025-09-23

## Resumen de Cambios

Esta tarea se centró en reducir costos y latencia de la API cacheando respuestas, y mejorar el rendimiento de la base de datos.

### Fase 1: Caché de API

1.  **Nueva tabla de caché:** Se añadió una nueva tabla `iacp_api_cache` a la base de datos para almacenar las respuestas de la API. La tabla incluye campos para el hash del prompt, la respuesta y la fecha de creación.

2.  **Clase de manejo de caché:** Se creó una nueva clase `IACP_Api_Cache` con métodos estáticos `get`, `set` y `clear_cache` para manejar la lógica de la caché.

3.  **Integración con el cliente de API:** Se modificó la clase `IACP_Gemini_Client` para que, antes de hacer una llamada a la API, compruebe si existe una respuesta válida en la caché. Si es así, la utiliza. Si no, realiza la llamada y guarda la respuesta en la caché para futuras peticiones.

### Fase 2: Optimización de Base de Datos

1.  **Índices de base de datos:** Se añadieron índices a las columnas `status` en la tabla `iacp_content` y `publish_date` en la tabla `iacp_social_media` para mejorar el rendimiento de las consultas.

2.  **Funcionalidad para limpiar la caché:** Se añadió un botón en la página de "Settings" que permite a los administradores limpiar la caché de la API. Esto se implementó con un nuevo endpoint AJAX y una función en la clase `IACP_Api_Cache` que trunca la tabla de caché.
