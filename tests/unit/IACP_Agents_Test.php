<?php

require_once dirname( __FILE__ ) . '/../../includes/class-iacp-agents.php';
require_once dirname( __FILE__ ) . '/../../includes/class-iacp-db.php';

use PHPUnit\Framework\TestCase;

class IACP_Agents_Test extends TestCase {

    protected $wpdb;

    protected function setUp(): void {
        parent::setUp();

        // Create a mock for the wpdb object
        $this->wpdb = $this->getMockBuilder(stdClass::class)
                           ->addMethods(['insert', 'get_row', 'get_results', 'update', 'delete', 'prepare'])
                           ->getMock();

        $this->wpdb->prefix = 'wp_';
        $this->wpdb->insert_id = 0; // Initialize insert_id for create_agent tests

        // Make the mock global
        $GLOBALS['wpdb'] = $this->wpdb;
    }

    public function test_create_agent() {
        $name = 'Test Agent';
        $role = 'Developer';
        $experience = 'Senior';
        $tasks = 'Coding, Testing';
        $prompt = 'You are a helpful assistant.';
        $expected_agent_id = 1;

        $this->wpdb->expects($this->once())
                   ->method('insert')
                   ->with(
                       $this->wpdb->prefix . 'iacp_agents',
                       [
                           'name' => $name,
                           'role' => $role,
                           'experience' => $experience,
                           'tasks' => $tasks,
                           'prompt' => $prompt,
                       ]
                   )
                   ->willReturn(1); // Simulate successful insert

        $this->wpdb->insert_id = $expected_agent_id; // Set the insert_id for the mock

        // Mock prepare for get_agent call
        $this->wpdb->expects($this->once())
                   ->method('prepare')
                   ->with("SELECT * FROM {$this->wpdb->prefix}iacp_agents WHERE id = %d", $expected_agent_id)
                   ->willReturn("SELECT * FROM {$this->wpdb->prefix}iacp_agents WHERE id = {$expected_agent_id}");

        // Mock get_row for get_agent call
        $this->wpdb->expects($this->once())
                   ->method('get_row')
                   ->willReturn((object)[
                       'id' => $expected_agent_id,
                       'name' => $name,
                       'role' => $role,
                       'experience' => $experience,
                       'tasks' => $tasks,
                       'prompt' => $prompt,
                   ]);

        $agent_id = IACP_Agents::create_agent( $name, $role, $experience, $tasks, $prompt );

        $this->assertIsInt( $agent_id );
        $this->assertEquals( $expected_agent_id, $agent_id );

        $agent = IACP_Agents::get_agent( $agent_id );

        $this->assertIsObject( $agent );
        $this->assertEquals( $name, $agent->name );
        $this->assertEquals( $role, $agent->role );
        $this->assertEquals( $experience, $agent->experience );
        $this->assertEquals( $tasks, $agent->tasks );
        $this->assertEquals( $prompt, $agent->prompt );
    }

    public function test_get_agent() {
        $existing_agent_id = 1;
        $existing_agent_data = (object)[
            'id' => $existing_agent_id,
            'name' => 'Agent 1',
            'role' => 'Role 1',
            'experience' => 'Exp 1',
            'tasks' => 'Tasks 1',
            'prompt' => 'Prompt 1',
        ];

        // Mock prepare calls
        $this->wpdb->expects($this->exactly(2))
                   ->method('prepare')
                   ->willReturnOnConsecutiveCalls(
                       "SELECT * FROM {$this->wpdb->prefix}iacp_agents WHERE id = {$existing_agent_id}", // For existing agent
                       "SELECT * FROM {$this->wpdb->prefix}iacp_agents WHERE id = 9999" // For non-existent agent
                   );

        // Mock get_row calls
        $this->wpdb->expects($this->exactly(2))
                   ->method('get_row')
                   ->willReturnOnConsecutiveCalls(
                       $existing_agent_data, // For existing agent
                       null                   // For non-existent agent
                   );

        // Get existing agent
        $agent = IACP_Agents::get_agent( $existing_agent_id );
        $this->assertIsObject( $agent );
        $this->assertEquals( $existing_agent_id, $agent->id );
        $this->assertEquals( 'Agent 1', $agent->name );

        // Get non-existent agent
        $non_existent_agent = IACP_Agents::get_agent( 9999 );
        $this->assertNull( $non_existent_agent );
    }

    public function test_get_all_agents() {
        // Mock get_results to return empty array initially
        $this->wpdb->expects($this->exactly(2)) // Called twice: once for empty, once for populated
                   ->method('get_results')
                   ->willReturnOnConsecutiveCalls(
                       [], // First call: no agents
                       [ // Second call: two agents
                           (object)['id' => 1, 'name' => 'Agent A', 'role' => 'Role A', 'experience' => 'Exp A', 'tasks' => 'Tasks A', 'prompt' => 'Prompt A'],
                           (object)['id' => 2, 'name' => 'Agent B', 'role' => 'Role B', 'experience' => 'Exp B', 'tasks' => 'Tasks B', 'prompt' => 'Prompt B'],
                       ]
                   );

        // No agents initially
        $agents = IACP_Agents::get_all_agents();
        $this->assertIsArray( $agents );
        $this->assertEmpty( $agents );

        // Simulate adding agents (these calls won't actually hit the DB due to mocking)
        // We don't need to mock insert here as we are testing get_all_agents, not create_agent
        // The return values for get_results are already set up.

        $agents = IACP_Agents::get_all_agents();
        $this->assertIsArray( $agents );
        $this->assertCount( 2, $agents );

        $this->assertEquals( 'Agent A', $agents[0]->name );
        $this->assertEquals( 'Agent B', $agents[1]->name );
    }

    public function test_update_agent() {
        $agent_id = 1; // Assume an agent with ID 1 exists for this test
        $old_name = 'Old Name';
        $old_role = 'Old Role';
        $old_experience = 'Old Exp';
        $old_tasks = 'Old Tasks';
        $old_prompt = 'Old Prompt';

        $new_name = 'New Name';
        $new_role = 'New Role';
        $new_experience = 'New Exp';
        $new_tasks = 'New Tasks';
        $new_prompt = 'New Prompt';

        // Mock update call
        $this->wpdb->expects($this->once())
                   ->method('update')
                   ->with(
                       $this->wpdb->prefix . 'iacp_agents',
                       [
                           'name' => $new_name,
                           'role' => $new_role,
                           'experience' => $new_experience,
                           'tasks' => $new_tasks,
                           'prompt' => $new_prompt,
                       ],
                       ['id' => $agent_id],
                       ['%s', '%s', '%s', '%s', '%s'], // Data formats
                       ['%d']  // Where format
                   )
                   ->willReturn(1); // Simulate 1 row updated

        // Mock prepare for get_agent call
        $this->wpdb->expects($this->once())
                   ->method('prepare')
                   ->with("SELECT * FROM {$this->wpdb->prefix}iacp_agents WHERE id = %d", $agent_id)
                   ->willReturn("SELECT * FROM {$this->wpdb->prefix}iacp_agents WHERE id = {$agent_id}");

        // Mock get_row to return the updated agent
        $this->wpdb->expects($this->once())
                   ->method('get_row')
                   ->willReturn((object)[
                       'id' => $agent_id,
                       'name' => $new_name,
                       'role' => $new_role,
                       'experience' => $new_experience,
                       'tasks' => $new_tasks,
                       'prompt' => $new_prompt,
                   ]);

        IACP_Agents::update_agent( $agent_id, $new_name, $new_role, $new_experience, $new_tasks, $new_prompt );

        $updated_agent = IACP_Agents::get_agent( $agent_id );

        $this->assertIsObject( $updated_agent );
        $this->assertEquals( $new_name, $updated_agent->name );
        $this->assertEquals( $new_role, $updated_agent->role );
        $this->assertEquals( $new_experience, $updated_agent->experience );
        $this->assertEquals( $new_tasks, $updated_agent->tasks );
        $this->assertEquals( $new_prompt, $updated_agent->prompt );
    }

    public function test_delete_agent() {
        $agent_id = 1; // Assume an agent with ID 1 exists for this test
        $agent_data = (object)[
            'id' => $agent_id,
            'name' => 'Agent to Delete',
            'role' => 'Role',
            'experience' => 'Exp',
            'tasks' => 'Tasks',
            'prompt' => 'Prompt',
        ];

        // Mock get_row to return the agent before deletion
        $this->wpdb->expects($this->exactly(2)) // Called twice: once before delete, once after
                   ->method('get_row')
                   ->willReturnOnConsecutiveCalls(
                       $agent_data, // First call: agent exists
                       null          // Second call: agent is deleted
                   );

        // Mock prepare for get_agent calls
        $this->wpdb->expects($this->exactly(2))
                   ->method('prepare')
                   ->with("SELECT * FROM {$this->wpdb->prefix}iacp_agents WHERE id = %d", $agent_id)
                   ->willReturn("SELECT * FROM {$this->wpdb->prefix}iacp_agents WHERE id = {$agent_id}");

        // Mock delete call
        $this->wpdb->expects($this->once())
                   ->method('delete')
                   ->with(
                       $this->wpdb->prefix . 'iacp_agents',
                       ['id' => $agent_id],
                       ['%d']
                   )
                   ->willReturn(1); // Simulate 1 row deleted

        $this->assertIsObject( IACP_Agents::get_agent( $agent_id ) );

        IACP_Agents::delete_agent( $agent_id );

        $this->assertNull( IACP_Agents::get_agent( $agent_id ) );
    }
}