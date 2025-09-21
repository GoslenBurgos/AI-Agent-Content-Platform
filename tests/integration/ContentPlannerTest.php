<?php

use PHPUnit\Framework\TestCase;

class ContentPlannerTest extends WP_UnitTestCase {

    protected $wpdb;

    public function setUp(): void {
        parent::setUp();
        global $wpdb;
        $this->wpdb = $wpdb;

        // Nos aseguramos de que las tablas existen y están limpias
        IACP_Db::create_tables();
        $this->wpdb->query("TRUNCATE TABLE {$this->wpdb->prefix}iacp_content");
        $this->wpdb->query("TRUNCATE TABLE {$this->wpdb->prefix}iacp_content_versions");
    }

    public function test_save_content_inserts_correct_data_into_tables() {
        // Arrange: Definimos los datos del contenido a guardar
        $title = "Test Title";
        $theme = "Test Theme";
        $content = "This is the test content.";
        $virality_score = 8;
        $status = "planned";
        $agent_id = 0; // Asumimos que no hay un agente específico para este test

        // Act: Llamamos al método save_content
        $content_id = IACP_Content_Planner::save_content($title, $theme, $content, $virality_score, $status, $agent_id);

        // Assert: Verificamos que el contenido se guardó correctamente en la tabla principal
        $this->assertIsInt($content_id);
        $this->assertGreaterThan(0, $content_id);

        $saved_content = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$this->wpdb->prefix}iacp_content WHERE id = %d", $content_id));

        $this->assertNotNull($saved_content);
        $this->assertEquals($title, $saved_content->title);
        $this->assertEquals($theme, $saved_content->theme);
        $this->assertEquals($content, $saved_content->content);
        $this->assertEquals($virality_score, $saved_content->virality_score);
        $this->assertEquals($status, $saved_content->status);

        // Assert: Verificamos que se creó una versión correspondiente en la tabla de versiones
        $saved_version = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$this->wpdb->prefix}iacp_content_versions WHERE content_id = %d", $content_id));

        $this->assertNotNull($saved_version);
        $this->assertEquals($content_id, $saved_version->content_id);
        $this->assertEquals($content, $saved_version->content);
        $this->assertEquals('Initial version', $saved_version->version_note);
    }

    public function tearDown(): void {
        // Limpiamos las tablas después del test
        $this->wpdb->query("TRUNCATE TABLE {$this->wpdb->prefix}iacp_content");
        $this->wpdb->query("TRUNCATE TABLE {$this->wpdb->prefix}iacp_content_versions");
        parent::tearDown();
    }
}
