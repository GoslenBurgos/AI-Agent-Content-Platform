<?php
use PHPUnit\Framework\TestCase;

// The unified bootstrap file (tests/bootstrap.php) now handles all mock functions and autoloading.

require_once IACP_PLUGIN_DIR . 'includes/class-iacp-content-planner.php';

class IACP_Content_Planner_Test extends TestCase
{
    private $wpdb_mock;

    protected function setUp(): void
    {
        $this->wpdb_mock = $this->getMockBuilder(stdClass::class)
                                 ->addMethods(['insert', 'prepare', 'get_row', 'get_results', 'update', 'delete'])
                                 ->getMock();
        $this->wpdb_mock->prefix = 'wp_';
        global $wpdb;
        $wpdb = $this->wpdb_mock;
    }

    // Refactored tests for generate_ideas
    public function test_generate_ideas_success()
    {
        $result = IACP_Content_Planner::generate_ideas('test', fn($p) => '```json[{"title":"Mock"}]```');
        $this->assertIsArray($result);
        $this->assertEquals('Mock', $result[0]['title']);
    }

    public function test_generate_ideas_api_error()
    {
        $result = IACP_Content_Planner::generate_ideas('test', fn($p) => new WP_Error('api_error'));
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    public function test_generate_ideas_bad_json_response()
    {
        $result = IACP_Content_Planner::generate_ideas('test', fn($p) => 'not json');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    // New tests for execute_content_workflow
    public function test_execute_content_workflow_success_full_run()
    {
        $draft_agent = (object)['prompt' => 'Draft for [TITULO]'];
        $seo_agent = (object)['prompt' => 'SEO for [BORRADOR_ARTICULO]'];

        $agent_retriever = function($id) use ($draft_agent, $seo_agent) {
            if ($id === 1) return $draft_agent;
            if ($id === 2) return $seo_agent;
            return null;
        };

        $text_generator = function($prompt) {
            if (str_contains($prompt, 'Draft for')) return 'Initial Draft.';
            if (str_contains($prompt, 'SEO for')) return 'SEO Content.';
            return 'Default generated text.';
        };

        $final_content = IACP_Content_Planner::execute_content_workflow('Test Title', 'Test Theme', 1, 2, 0, 0, 0, $agent_retriever, $text_generator);

        $this->assertStringContainsString('Initial Draft.', $final_content);
        $this->assertStringContainsString('--- SEO ANALYSIS ---', $final_content);
        $this->assertStringContainsString('SEO Content.', $final_content);
    }

    public function test_execute_content_workflow_no_draft_agent_id()
    {
        $result = IACP_Content_Planner::execute_content_workflow('T', 'T', 0);
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('agent_not_selected', $result->get_error_code());
    }

    public function test_execute_content_workflow_draft_agent_not_found()
    {
        $agent_retriever = fn($id) => null;
        $result = IACP_Content_Planner::execute_content_workflow('T', 'T', 99, 0, 0, 0, 0, $agent_retriever);
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('agent_not_found', $result->get_error_code());
    }

    // Tests for DB methods
    public function test_save_content()
    {
        $this->wpdb_mock->expects($this->exactly(2))->method('insert');
        $this->wpdb_mock->insert_id = 42;
        $result = IACP_Content_Planner::save_content('T','T','C', 8, 'draft', 0);
        $this->assertEquals(42, $result);
    }

    public function test_save_content_version_with_agent()
    {
        $agent_retriever = fn($id) => (object)['name' => 'Test Agent'];
        $this->wpdb_mock->expects($this->once())
            ->method('insert')
            ->with(
                'wp_iacp_content_versions',
                $this->callback(fn($data) => str_contains($data['version_note'], 'Test Agent'))
            );
        IACP_Content_Planner::save_content_version(1, 'content', 1, '', $agent_retriever);
    }

    public function test_get_all_content()
    {
        $this->wpdb_mock->expects($this->once())->method('get_results');
        IACP_Content_Planner::get_all_content();
    }

    public function test_get_content()
    {
        $this->wpdb_mock->expects($this->once())->method('get_row');
        IACP_Content_Planner::get_content(1);
    }

    public function test_update_content_status()
    {
        $this->wpdb_mock->expects($this->once())->method('update');
        IACP_Content_Planner::update_content_status(5, 'published');
    }

    public function test_delete_content()
    {
        $this->wpdb_mock->expects($this->exactly(2))->method('delete');
        IACP_Content_Planner::delete_content(10);
    }

    public function test_get_content_versions()
    {
        $this->wpdb_mock->expects($this->once())->method('get_results');
        IACP_Content_Planner::get_content_versions(7);
    }

    public function test_restore_content_version_success()
    {
        $version_data = (object) ['id' => 15, 'content_id' => 7, 'content' => 'Restored', 'created_at' => '2025-09-15 10:00:00'];
        $this->wpdb_mock->method('get_row')->willReturn($version_data);
        $this->wpdb_mock->expects($this->once())->method('update');
        $this->wpdb_mock->expects($this->once())->method('insert');
        $result = IACP_Content_Planner::restore_content_version(15, fn($id) => null);
        $this->assertTrue($result);
    }

    public function test_restore_content_version_not_found()
    {
        $this->wpdb_mock->method('get_row')->willReturn(null);
        $result = IACP_Content_Planner::restore_content_version(99);
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /** @dataProvider cleanJsonResponseProvider */
    public function test_clean_json_response($response_string, $expected_array)
    {
        $method = new ReflectionMethod(IACP_Content_Planner::class, 'clean_json_response');
        $result = $method->invoke(null, $response_string);
        $this->assertEquals($expected_array, $result);
    }

    public static function cleanJsonResponseProvider()
    {
        return [
            'standard json' => ['[{"key":"value"}]', [['key' => 'value']]],
            'wrapped in markdown' => ["```json\n[{\"key\":\"value\"}]\n```", [['key' => 'value']]],
            'malformed json' => ['{"key": "value"', null],
            'empty string' => ['', null],
        ];
    }
}
