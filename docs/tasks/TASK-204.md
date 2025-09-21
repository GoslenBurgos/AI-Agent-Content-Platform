Tarea: TASK-204 - Refactorizar a Inyección de Dependencias y Patrón Repositorio
Objetivo: Desacoplar el código, eliminar el uso de métodos estáticos y variables globales, y mejorar la testeabilidad de la aplicación.

Plan de Ejecución (Checkpoints)
[ ] Checkpoint 1.1: Modificar la clase principal IA_Agent_Content_Platform. En su método run(), instanciar todas las clases de lógica (ej. $agents_manager = new IACP_Agents($wpdb);) en lugar de llamar a métodos estáticos.

[ ] Checkpoint 1.2: Refactorizar la clase IACP_Agents. Eliminar todos los static de los métodos. Crear un constructor __construct(wpdb $db) y almacenar $wpdb en una propiedad de la clase. Reemplazar todas las llamadas a global $wpdb; por $this->db.

[ ] Checkpoint 1.3: Repetir el proceso del Checkpoint 1.2 para las clases IACP_Content_Planner y IACP_Social_Media_Planner.

[ ] Checkpoint 1.4 (Patrón Repositorio): Crear una nueva clase includes/repositories/class-iacp-content-repository.php. Mover toda la lógica de base de datos de IACP_Content_Planner (get, save, update, delete) a esta nueva clase. La clase IACP_Content_Planner ahora usará el repositorio para interactuar con la DB, separando la lógica de negocio del acceso a datos.

Archivos Relevantes
includes/class-ia-agent-content-platform.php

includes/class-iacp-agents.php

includes/class-iacp-content-planner.php

(Nuevo) includes/repositories/class-iacp-content-repository.php

Estado Actual
Último Checkpoint Completado: Ninguno.

Próximo Checkpoint: 1.1