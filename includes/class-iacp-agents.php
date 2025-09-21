<?php

class IACP_Agents {

    public static function create_agent( $name, $role, $experience, $tasks, $prompt ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_agents';

        $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'role' => $role,
                'experience' => $experience,
                'tasks' => $tasks,
                'prompt' => $prompt,
            )
        );
        return $wpdb->insert_id;
    }

    public static function get_agent( $agent_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_agents';

        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $agent_id ) );
    }

    public static function get_all_agents() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_agents';

        return $wpdb->get_results( "SELECT * FROM $table_name" );
    }

    public static function update_agent( $agent_id, $name, $role, $experience, $tasks, $prompt ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_agents';

        $wpdb->update(
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

    public static function delete_agent( $agent_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_agents';

        return $wpdb->delete( $table_name, array( 'id' => $agent_id ), array( '%d' ) );
    }
}
