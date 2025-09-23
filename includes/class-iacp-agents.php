<?php

class IACP_Agents {

    private $db;

    public function __construct(wpdb $db) {
        $this->db = $db;
    }

    public function create_agent( $name, $role, $experience, $tasks, $prompt ) {
        $table_name = $this->db->prefix . 'iacp_agents';

        $this->db->insert(
            $table_name,
            array(
                'name' => $name,
                'role' => $role,
                'experience' => $experience,
                'tasks' => $tasks,
                'prompt' => $prompt,
            )
        );
        return $this->db->insert_id;
    }

    public function get_agent( $agent_id ) {
        $table_name = $this->db->prefix . 'iacp_agents';

        return $this->db->get_row( $this->db->prepare( "SELECT * FROM $table_name WHERE id = %d", $agent_id ) );
    }

    public function get_all_agents() {
        $table_name = $this->db->prefix . 'iacp_agents';

        return $this->db->get_results( "SELECT * FROM $table_name" );
    }

    public function update_agent( $agent_id, $name, $role, $experience, $tasks, $prompt ) {
        $table_name = $this->db->prefix . 'iacp_agents';

        $this->db->update(
            $table_name,
            array(
                'name' => $name,
                'role' => $role,
                'experience' => $experience,
                'tasks' => $tasks,
                'prompt' => $prompt,
            ),
            array( 'id' => $agent_id ),
            array( '%s', '%s', '%s', '%s', '%s' ), // Data formats
            array( '%d' )  // Where format
        );
    }

    public function delete_agent( $agent_id ) {
        $table_name = $this->db->prefix . 'iacp_agents';

        return $this->db->delete( $table_name, array( 'id' => $agent_id ), array( '%d' ) );
    }
}