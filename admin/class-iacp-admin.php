<?php

class IACP_Admin {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function add_admin_menu() {
        add_menu_page( __( 'IA Agent Content Platform', 'ia-agent-content-platform' ), __( 'IA Agent Platform', 'ia-agent-content-platform' ), 'manage_options', $this->plugin_name, array( $this, 'display_dashboard_page' ), 'dashicons-admin-generic', 6 );
        add_submenu_page( $this->plugin_name, __( 'Dashboard', 'ia-agent-content-platform' ), __( 'Dashboard', 'ia-agent-content-platform' ), 'manage_options', $this->plugin_name, array( $this, 'display_dashboard_page' ) );
        add_submenu_page( $this->plugin_name, __( 'Agent Management', 'ia-agent-content-platform' ), __( 'Agent Management', 'ia-agent-content-platform' ), 'manage_options', $this->plugin_name . '-agent-management', array( $this, 'display_agent_management_page' ) );
        add_submenu_page( $this->plugin_name, __( 'Content Planner', 'ia-agent-content-platform' ), __( 'Content Planner', 'ia-agent-content-platform' ), 'manage_options', $this->plugin_name . '-content-planner', array( $this, 'display_content_planner_page' ) );
        add_submenu_page( $this->plugin_name, __( 'Social Media Planner', 'ia-agent-content-platform' ), __( 'Social Media Planner', 'ia-agent-content-platform' ), 'manage_options', $this->plugin_name . '-social-media-planner', array( $this, 'display_social_media_planner_page' ) );
        add_submenu_page( $this->plugin_name, __( 'Log Viewer', 'ia-agent-content-platform' ), __( 'Log Viewer', 'ia-agent-content-platform' ), 'manage_options', $this->plugin_name . '-log-viewer', array( $this, 'display_log_viewer_page' ) );
        add_submenu_page( $this->plugin_name, __( 'Settings', 'ia-agent-content-platform' ), __( 'Settings', 'ia-agent-content-platform' ), 'manage_options', $this->plugin_name . '-settings', array( $this, 'display_settings_page' ) );
    }

    public function display_dashboard_page() { require_once IACP_PLUGIN_DIR . 'admin/views/dashboard.php'; }
    public function display_agent_management_page() { require_once IACP_PLUGIN_DIR . 'admin/views/agent-management.php'; }
    public function ajax_publish_wordpress_post() {
        $this->_check_ajax_permissions();
        $content_id = intval( $_POST['content_id'] );
        $post_id = IACP_Content_Planner::publish_content_as_post( $content_id );
        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( array( 'message' => sprintf( __( 'Error publishing post to WordPress: %s', 'ia-agent-content-platform' ), $post_id->get_error_message() ) ) );
        } else {
            wp_send_json_success( array( 'post_id' => $post_id ) );
        }
    }

    public function display_content_planner_page() { require_once IACP_PLUGIN_DIR . 'admin/views/content-planner.php'; }
    public function display_log_viewer_page() {
        // Check if the user wants to clear the log
        if ( isset( $_POST['iacp_clear_log_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['iacp_clear_log_nonce'] ), 'iacp_clear_log_action' ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                $log_file = IACP_Logger::get_log_file_path();
                if ( file_exists( $log_file ) ) {
                    // Clear the file content
                    file_put_contents( $log_file, '' );
                    // Show an admin notice
                    add_action( 'admin_notices', function() {
                        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Logs cleared successfully.', 'ia-agent-content-platform' ) . '</p></div>';
                    } );
                }
            }
        }
        require_once IACP_PLUGIN_DIR . 'admin/views/log-viewer.php';
    }
    public function display_social_media_planner_page() { require_once IACP_PLUGIN_DIR . 'admin/views/social-media-planner.php'; }
    public function display_settings_page() { require_once IACP_PLUGIN_DIR . 'admin/views/settings.php'; }

    public function register_settings() {
        register_setting( 'iacp_settings_group', 'iacp_gemini_api_key', array( 'sanitize_callback' => array( $this, 'encrypt_api_key' ) ) );
        register_setting( 'iacp_settings_group', 'iacp_gemini_model_name' );
    }

    public function encrypt_api_key( $api_key ) {
        if ( empty( $api_key ) ) {
            return '';
        }
        $current_key = get_option( 'iacp_gemini_api_key' );
        if ( $api_key === $current_key ) {
            return $api_key;
        }
        return IACP_Security_Helper::encrypt( $api_key );
    }

    public function enqueue_styles_and_scripts( $hook_suffix ) {
        // Get the current screen object.
        $screen = get_current_screen();

        // Check if the current screen's ID contains the plugin's unique slug.
        // This ensures the scripts and styles only load on the plugin's pages.
        if ( $screen && strpos( $screen->id, 'ia-agent-content-platform' ) !== false ) {
            wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/admin-styles.css', array(), $this->version, 'all' );
            wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/admin-scripts.js', array( 'jquery' ), time(), false );
            wp_localize_script( $this->plugin_name, 'iacp_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'iacp_ajax_nonce' ) ) );

            // Load FullCalendar assets only on the Dashboard page
            if ( $screen->id === $this->plugin_name ) { // The main plugin page is the Dashboard
                wp_enqueue_style( 'fullcalendar-main', plugin_dir_url( __FILE__ ) . '../public/lib/fullcalendar/main.min.css', array(), '6.1.11', 'all' );
                wp_enqueue_script( 'fullcalendar-main', plugin_dir_url( __FILE__ ) . '../public/lib/fullcalendar/main.min.js', array( 'jquery' ), '6.1.11', true );
                wp_enqueue_script( 'fullcalendar-daygrid', plugin_dir_url( __FILE__ ) . '../public/lib/fullcalendar/daygrid/main.min.js', array( 'fullcalendar-main' ), '6.1.11', true );
                wp_enqueue_script( 'fullcalendar-interaction', plugin_dir_url( __FILE__ ) . '../public/lib/fullcalendar/interaction/main.min.js', array( 'fullcalendar-main' ), '6.1.11', true );
            }
        }
    }

    public function ajax_create_agent() {
        $this->_check_ajax_permissions();
        $agent_id = IACP_Agents::create_agent( sanitize_text_field( $_POST['name'] ), sanitize_textarea_field( $_POST['role'] ), sanitize_textarea_field( $_POST['experience'] ), sanitize_textarea_field( $_POST['tasks'] ), sanitize_textarea_field( $_POST['prompt'] ) );
        if ( ! $agent_id ) {
            wp_send_json_error( array( 'message' => __( 'Failed to create agent in the database.', 'ia-agent-content-platform' ) ) );
        }
        wp_send_json_success( array( 'agent_id' => $agent_id ) );
    }

    public function ajax_get_agents() {
        $this->_check_ajax_permissions();
        wp_send_json_success( IACP_Agents::get_all_agents() );
    }

    public function ajax_get_agent() {
        $this->_check_ajax_permissions();
        $agent_id = intval( $_POST['agent_id'] );
        $agent = IACP_Agents::get_agent( $agent_id );
        if ( $agent ) {
            wp_send_json_success( $agent );
        } else {
            wp_send_json_error( array( 'message' => sprintf( __( 'Agent with ID %d not found.', 'ia-agent-content-platform' ), $agent_id ) ) );
        }
    }

    public function ajax_update_agent() {
        $this->_check_ajax_permissions();
        $agent_id = intval( $_POST['agent_id'] );
        $name = sanitize_text_field( $_POST['name'] );
        $role = sanitize_textarea_field( $_POST['role'] );
        $experience = sanitize_textarea_field( $_POST['experience'] );
        $tasks = sanitize_textarea_field( $_POST['tasks'] );
        $prompt = sanitize_textarea_field( $_POST['prompt'] );

        $result = IACP_Agents::update_agent( $agent_id, $name, $role, $experience, $tasks, $prompt );
        if ( false === $result ) {
            wp_send_json_error( array( 'message' => __( 'Failed to update agent in the database.', 'ia-agent-content-platform' ) ) );
        }
        wp_send_json_success();
    }

    public function ajax_delete_agent() {
        $this->_check_ajax_permissions();
        $result = IACP_Agents::delete_agent( intval( $_POST['agent_id'] ) );
        if ( false === $result ) {
            wp_send_json_error( array( 'message' => __( 'Failed to delete agent from the database.', 'ia-agent-content-platform' ) ) );
        }
        wp_send_json_success();
    }

    public function ajax_save_editorial_profile() {
        $this->_check_ajax_permissions();

        $fields = array(
            'iacp_editorial_profile_target_audience',
            'iacp_editorial_profile_voice_tone',
            'iacp_editorial_profile_style_guide',
            'iacp_editorial_profile_banned_words'
        );

        foreach ( $fields as $field ) {
            if ( isset( $_POST[ str_replace('iacp_editorial_profile_', '', $field) ] ) ) {
                $value = sanitize_text_field( $_POST[ str_replace('iacp_editorial_profile_', '', $field) ] );
                update_option( $field, $value );
            }
        }

        wp_send_json_success( array( 'message' => __( 'Editorial profile saved successfully.', 'ia-agent-content-platform' ) ) );
    }

    public function ajax_generate_ideas() {
        $this->_check_ajax_permissions();
        $keywords = sanitize_text_field( $_POST['keywords'] );
        $ideas = IACP_Content_Planner::generate_ideas( $keywords );
        if ( is_wp_error( $ideas ) ) {
            wp_send_json_error( array( 'message' => sprintf( __( 'API Error: %s', 'ia-agent-content-platform' ), $ideas->get_error_message() ), 'code' => $ideas->get_error_code() ) );
        } else {
            wp_send_json_success( $ideas );
        }
    }

    public function ajax_generate_content() {
        $this->_check_ajax_permissions();
        $draft_agent_id = isset( $_POST['draft_agent_id'] ) ? intval( $_POST['draft_agent_id'] ) : 0;
        $seo_agent_id = isset( $_POST['seo_agent_id'] ) ? intval( $_POST['seo_agent_id'] ) : 0;
        $copy_agent_id = isset( $_POST['copy_agent_id'] ) ? intval( $_POST['copy_agent_id'] ) : 0;
        $image_agent_id = isset( $_POST['image_agent_id'] ) ? intval( $_POST['image_agent_id'] ) : 0;
        $title_agent_id = isset( $_POST['title_agent_id'] ) ? intval( $_POST['title_agent_id'] ) : 0;

        $title = sanitize_text_field( $_POST['title'] );
        $theme = sanitize_textarea_field( $_POST['theme'] );
        $virality_score = intval( $_POST['virality_score'] );
        $status = sanitize_text_field( $_POST['content_status'] );

        $content = IACP_Content_Planner::execute_content_workflow( $title, $theme, $draft_agent_id, $seo_agent_id, $copy_agent_id, $image_agent_id, $title_agent_id );
        if ( is_wp_error( $content ) ) {
            wp_send_json_error( array( 'message' => sprintf( __( 'API Error: %s', 'ia-agent-content-platform' ), $content->get_error_message() ), 'code' => $content->get_error_code() ) );
        }
        $content_id = IACP_Content_Planner::save_content( $title, $theme, $content, $virality_score, $status, $draft_agent_id );
        wp_send_json_success( array( 'content_id' => $content_id ) );
    }

    public function ajax_get_content() {
        $this->_check_ajax_permissions();
        wp_send_json_success( IACP_Content_Planner::get_all_content() );
    }

    public function ajax_get_single_content() {
        $this->_check_ajax_permissions();
        $content_id = intval( $_POST['content_id'] );
        $content = IACP_Content_Planner::get_content( $content_id );
        if ( $content ) {
            wp_send_json_success( $content );
        } else {
            wp_send_json_error( array( 'message' => sprintf( __( 'Content with ID %d not found.', 'ia-agent-content-platform' ), $content_id ) ) );
        }
    }

    public function ajax_delete_content() {
        $this->_check_ajax_permissions();
        $content_id = intval( $_POST['content_id'] );
        $result = IACP_Content_Planner::delete_content( $content_id );
        if ( false === $result ) {
            wp_send_json_error( array( 'message' => __( 'Failed to delete content from the database.', 'ia-agent-content-platform' ) ) );
        }
        wp_send_json_success();
    }

    public function ajax_schedule_post() {
        $this->_check_ajax_permissions();
        $content_id = intval( $_POST['content_id'] );
        $platforms = isset( $_POST['platforms'] ) && is_array($_POST['platforms']) ? array_map('sanitize_text_field', $_POST['platforms']) : array();
        $message = sanitize_textarea_field( $_POST['message'] );
        $publish_date = sanitize_text_field( $_POST['publish_date'] );

        // --- Server-side validation ---
        if ( empty( $content_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Please select an article to schedule.', 'ia-agent-content-platform' ) ) );
        }
        if ( empty( $publish_date ) ) {
            wp_send_json_error( array( 'message' => __( 'Please specify a publication date.', 'ia-agent-content-platform' ) ) );
        }
        if ( empty( $platforms ) ) {
            wp_send_json_error( array( 'message' => __( 'Please select at least one platform.', 'ia-agent-content-platform' ) ) );
        }

        // Message is only required if a social media platform is selected
        $social_platforms = array_filter($platforms, function($p) {
            return $p !== 'blog';
        });
        if ( !empty($social_platforms) && empty($message) ) {
            wp_send_json_error( array( 'message' => __( 'The message field is required when social media platforms are selected.', 'ia-agent-content-platform' ) ) );
        }

        $results = array();

        foreach ( $platforms as $platform ) {
            if ( $platform === 'blog' ) {
                $post_id = IACP_Content_Planner::publish_content_as_post( $content_id, $publish_date );
                if ( is_wp_error( $post_id ) ) {
                    $results['blog'] = array( 'success' => false, 'message' => $post_id->get_error_message() );
                } else {
                    $results['blog'] = array( 'success' => true, 'post_id' => $post_id );
                }
            } else {
                // For other platforms, save to iacp_social_media table
                $social_post_id = IACP_Social_Media_Planner::schedule_post( $content_id, $platform, $message, $publish_date );
                if ( $social_post_id ) {
                    $results[$platform] = array( 'success' => true, 'id' => $social_post_id );
                } else {
                    $results[$platform] = array( 'success' => false, 'message' => sprintf( __( 'Database error while scheduling for %s.', 'ia-agent-content-platform' ), $platform ) );
                }
            }
        }
        wp_send_json_success( $results );
    }

    public function ajax_get_scheduled_posts() {
        $this->_check_ajax_permissions();
        wp_send_json_success( IACP_Social_Media_Planner::get_all_scheduled_posts() );
    }

    public function ajax_delete_scheduled_post() {
        $this->_check_ajax_permissions();
        $result = IACP_Social_Media_Planner::delete_scheduled_post( intval( $_POST['post_id'] ) );
        if ( false === $result ) {
            wp_send_json_error( array( 'message' => __( 'Failed to delete scheduled post from the database.', 'ia-agent-content-platform' ) ) );
        }
        wp_send_json_success();
    }

    public function ajax_generate_social_post_suggestion() {
        $this->_check_ajax_permissions();

        $content_id = intval( $_POST['content_id'] );
        if ( empty( $content_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Content ID is missing.', 'ia-agent-content-platform' ) ) );
        }

        $suggestion = IACP_Social_Media_Planner::generate_social_post_suggestion( $content_id );

        if ( is_wp_error( $suggestion ) ) {
            wp_send_json_error( array( 'message' => $suggestion->get_error_message() ) );
        }

        wp_send_json_success( array( 'suggestion' => $suggestion ) );
    }

    public function ajax_update_scheduled_post_date() {
        $this->_check_ajax_permissions( __( 'You do not have sufficient permissions to perform this action.', 'ia-agent-content-platform' ) );

        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        $new_date = isset( $_POST['new_date'] ) ? sanitize_text_field( $_POST['new_date'] ) : '';

        if ( empty( $post_id ) || empty( $new_date ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid data provided for rescheduling.', 'ia-agent-content-platform' ) ) );
        }

        $result = IACP_Social_Media_Planner::update_scheduled_post_date( $post_id, $new_date );

        if ( false === $result ) {
            wp_send_json_error( array( 'message' => __( 'Failed to update the post date in the database.', 'ia-agent-content-platform' ) ) );
        }

        wp_send_json_success( array( 'message' => __( 'Post rescheduled successfully.', 'ia-agent-content-platform' ) ) );
    }

    public function ajax_get_content_versions() {
        $this->_check_ajax_permissions( __( 'You do not have sufficient permissions to perform this action.', 'ia-agent-content-platform' ) );
        $content_id = intval( $_POST['content_id'] );
        $versions = IACP_Content_Planner::get_content_versions( $content_id );
        wp_send_json_success( $versions );
    }

    public function ajax_restore_content_version() {
        $this->_check_ajax_permissions( __( 'You do not have sufficient permissions to perform this action.', 'ia-agent-content-platform' ) );
        $version_id = intval( $_POST['version_id'] );
        $result = IACP_Content_Planner::restore_content_version( $version_id );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }
        wp_send_json_success( array( 'message' => 'Content restored successfully.' ) );
    }

    /**
     * Helper function to check nonce and user capabilities for AJAX requests.
     */
    private function _check_ajax_permissions( $capability = 'manage_options', $message = '' ) {
        check_ajax_referer( 'iacp_ajax_nonce', 'nonce' );
        if ( ! current_user_can( $capability ) ) {
            $error_message = $message ?: __( 'You do not have sufficient permissions to access this page.', 'ia-agent-content-platform' );
            wp_send_json_error( array( 'message' => $error_message ), 403 );
        }
    }
}
