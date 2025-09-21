<?php
use PHPUnit\Framework\TestCase;

if (!defined('IACP_PLUGIN_DIR')) {
    define('IACP_PLUGIN_DIR', dirname(dirname(__DIR__)) . '/');
}
require_once IACP_PLUGIN_DIR . 'includes/class-iacp-default-agents.php';

class IACP_Default_Agents_Test extends TestCase
{
    public function test_get_agents_returns_structured_array()
    {
        // Act: Call the static method
        $agents = IACP_Default_Agents::get_agents();

        // Assert: Check that the result is a non-empty array
        $this->assertIsArray($agents);
        $this->assertNotEmpty($agents);

        // Assert: Check the structure of the first agent in the array
        $this->assertIsArray($agents[0]);
        $this->assertArrayHasKey('name', $agents[0]);
        $this->assertArrayHasKey('role', $agents[0]);
        $this->assertArrayHasKey('experience', $agents[0]);
        $this->assertArrayHasKey('tasks', $agents[0]);
        $this->assertArrayHasKey('prompt', $agents[0]);

        // Assert: Check that the values are strings
        $this->assertIsString($agents[0]['name']);
        $this->assertIsString($agents[0]['prompt']);
    }

    public function test_get_agents_contains_multiple_agents()
    {
        // Act
        $agents = IACP_Default_Agents::get_agents();

        // Assert
        $this->assertGreaterThan(1, count($agents), "There should be more than one default agent.");
    }
}
