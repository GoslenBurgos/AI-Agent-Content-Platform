<?php

class IACP_Api_Cache {

    public static function get($prompt) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_api_cache';
        $prompt_hash = hash('sha256', $prompt);

        $sql = $wpdb->prepare(
            "SELECT response FROM $table_name WHERE prompt_hash = %s AND created_at > %s",
            $prompt_hash,
            date('Y-m-d H:i:s', strtotime('-24 hours'))
        );

        $result = $wpdb->get_var($sql);

        if ($result) {
            return $result;
        }

        return false;
    }

    public static function set($prompt, $response) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_api_cache';
        $prompt_hash = hash('sha256', $prompt);

        $wpdb->replace(
            $table_name,
            array(
                'prompt_hash' => $prompt_hash,
                'response'    => $response,
                'created_at'  => current_time('mysql', 1),
            ),
            array('%s', '%s', '%s')
        );
    }

    public static function clear_cache() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_api_cache';
        $wpdb->query("TRUNCATE TABLE $table_name");
    }
}
