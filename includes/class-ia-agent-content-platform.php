<?php

class IA_Agent_Content_Platform {

    protected $plugin_name;
    protected $version;
    protected $admin;

    /**
     * Constructor. Stores dependencies.
     * @param IACP_Admin $admin The admin class instance.
     */
    public function __construct($admin) {
        $this->plugin_name = 'ia-agent-content-platform';
        $this->version = '1.0.0';
        $this->admin = $admin;
    }

    /**
     * Registers all the hooks for the plugin.
     */
    public function run() {
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function define_admin_hooks() {
        // Use the injected admin dependency
        add_action( 'admin_menu', array( $this->admin, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_styles_and_scripts' ) );
        add_action( 'admin_init', array( $this->admin, 'register_settings' ) );

        // Agent Management AJAX hooks
        add_action( 'wp_ajax_iacp_create_agent', array( $this->admin, 'ajax_create_agent' ) );
        add_action( 'wp_ajax_iacp_get_agents', array( $this->admin, 'ajax_get_agents' ) );
        add_action( 'wp_ajax_iacp_get_agent', array( $this->admin, 'ajax_get_agent' ) );
        add_action( 'wp_ajax_iacp_update_agent', array( $this->admin, 'ajax_update_agent' ) );
        add_action( 'wp_ajax_iacp_delete_agent', array( $this->admin, 'ajax_delete_agent' ) );

        // Content Planner AJAX hooks
        add_action( 'wp_ajax_iacp_generate_ideas', array( $this->admin, 'ajax_generate_ideas' ) );
        add_action( 'wp_ajax_iacp_generate_content', array( $this->admin, 'ajax_generate_content' ) );
        add_action( 'wp_ajax_iacp_get_content', array( $this->admin, 'ajax_get_content' ) );
        add_action( 'wp_ajax_iacp_get_single_content', array( $this->admin, 'ajax_get_single_content' ) );

        // Social Media Planner AJAX hooks
        add_action( 'wp_ajax_iacp_schedule_post', array( $this->admin, 'ajax_schedule_post' ) );
        add_action( 'wp_ajax_iacp_get_scheduled_posts', array( $this->admin, 'ajax_get_scheduled_posts' ) );
        add_action( 'wp_ajax_iacp_delete_scheduled_post', array( $this->admin, 'ajax_delete_scheduled_post' ) );
        add_action( 'wp_ajax_iacp_generate_social_post_suggestion', array( $this->admin, 'ajax_generate_social_post_suggestion' ) );
        add_action( 'wp_ajax_iacp_update_scheduled_post_date', array( $this->admin, 'ajax_update_scheduled_post_date' ) );

        // Editorial Profile AJAX hooks
        add_action( 'wp_ajax_iacp_save_editorial_profile', array( $this->admin, 'ajax_save_editorial_profile' ) );

        // Content Management AJAX hooks
        add_action( 'wp_ajax_iacp_delete_content', array( $this->admin, 'ajax_delete_content' ) );
        add_action( 'wp_ajax_iacp_publish_wordpress_post', array( $this->admin, 'ajax_publish_wordpress_post' ) );
        add_action( 'wp_ajax_iacp_get_content_versions', array( $this->admin, 'ajax_get_content_versions' ) );
        add_action( 'wp_ajax_iacp_restore_content_version', array( $this->admin, 'ajax_restore_content_version' ) );
        add_action( 'wp_ajax_iacp_get_job_status', array( $this->admin, 'ajax_get_job_status' ) );
    }

    private function define_public_hooks() {
        add_filter( 'the_content', array( 'IACP_Content_Planner', 'track_post_view' ) );
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
}