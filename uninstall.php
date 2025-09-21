<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Ia_Agent_Content_Platform
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Drop custom database tables.
global $wpdb;
$table_name_agents = $wpdb->prefix . 'iacp_agents';
$table_name_content = $wpdb->prefix . 'iacp_content';
$table_name_schedule = $wpdb->prefix . 'iacp_schedule';
$table_name_social_media = $wpdb->prefix . 'iacp_social_media';
$table_name_versions = $wpdb->prefix . 'iacp_content_versions';

$wpdb->query( "DROP TABLE IF EXISTS $table_name_agents" );
$wpdb->query( "DROP TABLE IF EXISTS $table_name_content" );
$wpdb->query( "DROP TABLE IF EXISTS $table_name_schedule" );
$wpdb->query( "DROP TABLE IF EXISTS $table_name_social_media" );
$wpdb->query( "DROP TABLE IF EXISTS $table_name_versions" );
