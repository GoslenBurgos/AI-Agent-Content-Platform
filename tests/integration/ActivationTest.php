<?php

class ActivationTest extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        global $wpdb;
        $this->wpdb = $wpdb;
        
        // Asegurarnos de que las tablas existen para la prueba
        IACP_Db::create_tables();

        // Limpiamos la tabla de agentes antes de cada test
        $table_name = $this->wpdb->prefix . 'iacp_agents';
        $this->wpdb->query("TRUNCATE TABLE $table_name");
    }

    public function test_plugin_activation_adds_default_agents_to_db() {
        // Arrange: Obtenemos los agentes por defecto que esperamos
        $default_agents = IACP_Default_Agents::get_agents();
        $expected_agent_count = count($default_agents);

        // Act: Ejecutamos la función de activación del plugin
        activate_ia_agent_content_platform();

        // Assert: Verificamos que los agentes se hayan añadido a la BD
        $table_name = $this->wpdb->prefix . 'iacp_agents';
        $actual_agents = $this->wpdb->get_results("SELECT * FROM $table_name");
        $actual_agent_count = count($actual_agents);

        $this->assertEquals($expected_agent_count, $actual_agent_count, "El número de agentes en la BD no coincide con el esperado.");

        // Verificación extra: comparamos los nombres para estar seguros
        $default_agent_names = wp_list_pluck($default_agents, 'name');
        $actual_agent_names = wp_list_pluck($actual_agents, 'name');
        sort($default_agent_names);
        sort($actual_agent_names);

        $this->assertEquals($default_agent_names, $actual_agent_names, "Los nombres de los agentes en la BD no coinciden con los esperados.");
    }

    public function tearDown(): void {
        // Limpiamos la tabla después de la prueba para no afectar a otros tests
        $table_name = $this->wpdb->prefix . 'iacp_agents';
        $this->wpdb->query("TRUNCATE TABLE $table_name");
        parent::tearDown();
    }
}
