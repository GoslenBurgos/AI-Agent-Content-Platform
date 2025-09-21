<?php

class IACP_Db {

    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_name_agents = $wpdb->prefix . 'iacp_agents';
        $sql_agents = "CREATE TABLE $table_name_agents (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            role text NOT NULL,
            experience text NOT NULL,
            tasks text NOT NULL,
            prompt text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        $table_name_content = $wpdb->prefix . 'iacp_content';
        $sql_content = "CREATE TABLE $table_name_content (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title text NOT NULL,
            theme text NOT NULL,
            content longtext NOT NULL,
            virality_score tinyint(2) DEFAULT 0,
            status tinytext NOT NULL,
            views INT(11) NOT NULL DEFAULT 0,
            post_id bigint(20) UNSIGNED DEFAULT 0 NOT NULL,
            KEY post_id (post_id), /* Index for faster view tracking lookups */
            PRIMARY KEY  (id)
        ) $charset_collate;";

        $table_name_schedule = $wpdb->prefix . 'iacp_schedule';
        $sql_schedule = "CREATE TABLE $table_name_schedule (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            content_id mediumint(9) NOT NULL,
            publish_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        $table_name_social_media = $wpdb->prefix . 'iacp_social_media';
        $sql_social_media = "CREATE TABLE $table_name_social_media (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            content_id mediumint(9) NOT NULL,
            platform tinytext NOT NULL,
            message text NOT NULL,
            publish_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        $table_name_versions = $wpdb->prefix . 'iacp_content_versions';
        $sql_versions = "CREATE TABLE $table_name_versions (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            content_id mediumint(9) NOT NULL,
            content longtext NOT NULL,
            version_note text,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            created_by bigint(20) UNSIGNED NOT NULL,
            PRIMARY KEY  (id),
            KEY content_id (content_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql_agents );
        dbDelta( $sql_content );
        dbDelta( $sql_schedule );
        dbDelta( $sql_social_media );
        dbDelta( $sql_versions );
    }

    public static function add_default_agents() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_agents';

        // Check if agents already exist
        $agent_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

        if ( $agent_count == 0 ) {
            require_once IACP_PLUGIN_DIR . 'includes/class-iacp-default-agents.php';
            $default_agents = IACP_Default_Agents::get_agents();

            foreach ( $default_agents as $agent ) {
                $wpdb->insert(
                    $table_name,
                    array(
                        'name' => $agent['name'],
                        'role' => $agent['role'],
                        'experience' => $agent['experience'],
                        'tasks' => $agent['tasks'],
                        'prompt' => $agent['prompt'],
                    )
                );
            }
        }
    }
}
