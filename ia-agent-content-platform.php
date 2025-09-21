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
    // Deactivation logic here.
}

register_activation_hook( __FILE__, 'activate_ia_agent_content_platform' );
register_deactivation_hook( __FILE__, 'deactivate_ia_agent_content_platform' );

/**
 * The core plugin class and its dependencies.
 */
require_once IACP_PLUGIN_DIR . 'includes/class-ia-agent-content-platform.php';
require_once IACP_PLUGIN_DIR . 'admin/class-iacp-admin.php';

/**
 * Begins execution of the plugin.
 */
function run_ia_agent_content_platform() {
    $admin = new IACP_Admin('ia-agent-content-platform', '1.0.0');
    $plugin = new IA_Agent_Content_Platform($admin);
    $plugin->run();
}
run_ia_agent_content_platform();