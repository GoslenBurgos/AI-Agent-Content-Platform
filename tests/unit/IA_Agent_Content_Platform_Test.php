<?php
use PHPUnit\Framework\TestCase;

// The bootstrap file handles loading all dependencies and mocks.

class IA_Agent_Content_Platform_Test extends TestCase
{
    private $admin_mock;

    protected function setUp(): void
    {
        global $mock_hook_calls;
        $mock_hook_calls = []; // Reset hook call recorder before each test

        // Create a mock for the IACP_Admin dependency
        $this->admin_mock = $this->createMock(IACP_Admin::class);
    }

    public function test_constructor_sets_properties()
    {
        $plugin = new IA_Agent_Content_Platform($this->admin_mock);

        $this->assertEquals('ia-agent-content-platform', $plugin->get_plugin_name());
        $this->assertEquals('1.0.0', $plugin->get_version());
    }

    public function test_run_registers_all_hooks()
    {
        global $mock_hook_calls;

        $plugin = new IA_Agent_Content_Platform($this->admin_mock);
        $plugin->run();

        // Verify that all actions and filters are added.
        // We count them to ensure the wiring logic is fully executed.
        $this->assertCount(22, $mock_hook_calls['action']);
        $this->assertCount(1, $mock_hook_calls['filter']);

        // Spot-check a few important hooks
        $registered_actions = array_map(fn($call) => $call['hook'], $mock_hook_calls['action']);
        $this->assertContains('admin_menu', $registered_actions);
        $this->assertContains('wp_ajax_iacp_generate_ideas', $registered_actions);

        $registered_filters = array_map(fn($call) => $call['hook'], $mock_hook_calls['filter']);
        $this->assertContains('the_content', $registered_filters);
    }
}
