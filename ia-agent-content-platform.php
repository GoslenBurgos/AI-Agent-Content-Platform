<?php
/**
 * Plugin Name: IA Agent Content Platform
 * Plugin URI: https://goslen.com/
 * Description: A multi-agent platform for automated content creation using Gemini API.
 * Version: 1.0.0
 * Author: Goslen Burgos
 * Author URI: https://goslen.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ia-agent-content-platform
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Define plugin constants.
 */
define( 'IACP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_ia_agent_content_platform() {
    require_once IACP_PLUGIN_DIR . 'includes/class-iacp-db.php';
    IACP_Db::create_tables();
    IACP_Db::add_default_agents();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_ia_agent_content_platform() {
    wp_clear_scheduled_hook('iacp_process_job_queue');
}

register_activation_hook( __FILE__, 'activate_ia_agent_content_platform' );
register_deactivation_hook( __FILE__, 'deactivate_ia_agent_content_platform' );

/**
 * The core plugin class and its dependencies.
 */
require_once IACP_PLUGIN_DIR . 'includes/class-ia-agent-content-platform.php';
require_once IACP_PLUGIN_DIR . 'admin/class-iacp-admin.php';
require_once IACP_PLUGIN_DIR . 'includes/class-iacp-job-worker.php';
require_once IACP_PLUGIN_DIR . 'includes/class-iacp-agents.php';
require_once IACP_PLUGIN_DIR . 'includes/class-iacp-content-planner.php';
require_once IACP_PLUGIN_DIR . 'includes/class-iacp-social-media-planner.php';

/**
 * Begins execution of the plugin.
 */
function run_ia_agent_content_platform() {
    global $wpdb;

    // Instantiate logic classes
    $agents_manager = new IACP_Agents($wpdb);
    $content_planner = new IACP_Content_Planner($wpdb);
    $social_media_planner = new IACP_Social_Media_Planner($wpdb);

    $admin = new IACP_Admin('ia-agent-content-platform', '1.0.0', $agents_manager, $content_planner, $social_media_planner);
    $plugin = new IA_Agent_Content_Platform($admin);
    $plugin->run();

    if ( ! wp_next_scheduled( 'iacp_process_job_queue' ) ) {
        wp_schedule_event( time(), 'minutely', 'iacp_process_job_queue' );
    }
    add_action( 'iacp_process_job_queue', array( 'IACP_Job_Worker', 'process_queue' ) );
}
run_ia_agent_content_platform();