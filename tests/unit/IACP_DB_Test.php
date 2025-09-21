<?php
use PHPUnit\Framework\TestCase;

// Define constants needed by the classes we are about to include
if (!defined('IACP_PLUGIN_DIR')) {
    define('IACP_PLUGIN_DIR', dirname(dirname(__DIR__)) . '/');
}

// Include the actual classes we need to test against
require_once IACP_PLUGIN_DIR . 'includes/class-iacp-db.php';
require_once IACP_PLUGIN_DIR . 'includes/class-iacp-default-agents.php';

class IACP_DB_Test extends TestCase {

    protected $wpdb;

    protected function setUp(): void {
        parent::setUp();
        
        // Create a mock for the wpdb object
        $this->wpdb = $this->getMockBuilder(stdClass::class)
                           ->addMethods(['get_var', 'insert'])
                           ->getMock();

        $this->wpdb->prefix = 'wp_';
        
        // Make the mock global
        $GLOBALS['wpdb'] = $this->wpdb;
    }

    public function test_add_default_agents_does_nothing_if_agents_exist() {
        // Arrange: Mock get_var to return a count greater than 0
        $this->wpdb->expects($this->once())
                   ->method('get_var')
                   ->willReturn(5); // Simulate 5 agents already in DB

        // Assert: insert should NEVER be called
        $this->wpdb->expects($this->never())
                   ->method('insert');

        // Act: Call the method to be tested
        IACP_Db::add_default_agents();
    }

    public function test_add_default_agents_inserts_agents_if_table_is_empty() {
        // Arrange: Get the actual default agents from the real class
        $default_agents = IACP_Default_Agents::get_agents();
        $this->assertNotEmpty($default_agents, "Test prerequisite failed: No default agents found to test with.");

        // Arrange: Mock get_var to return 0
        $this->wpdb->expects($this->once())
                   ->method('get_var')
                   ->willReturn(0); // Simulate empty table

        // Assert: Use a callback to verify arguments for each call to insert()
        $call_index = 0;
        $this->wpdb->expects($this->exactly(count($default_agents)))
            ->method('insert')
            ->willReturnCallback(function ($table, $data) use ($default_agents, &$call_index) {
                $expected_table = 'wp_iacp_agents';
                $this->assertSame($expected_table, $table, "Failed asserting table name on call $call_index");

                $expected_data = [
                    'name' => $default_agents[$call_index]['name'],
                    'role' => $default_agents[$call_index]['role'],
                    'experience' => $default_agents[$call_index]['experience'],
                    'tasks' => $default_agents[$call_index]['tasks'],
                    'prompt' => $default_agents[$call_index]['prompt'],
                ];
                $this->assertSame($expected_data, $data, "Failed asserting data on call $call_index");

                $call_index++;
            });

        // Act: Call the method to be tested
        IACP_Db::add_default_agents();
    }
    
    // We are not testing create_tables in a unit test as it depends on dbDelta
    // and a real database connection, which is better for an integration test.
}
