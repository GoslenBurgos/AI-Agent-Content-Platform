<div class="wrap">
    <h1><?php esc_html_e( 'Log Viewer', 'ia-agent-content-platform' ); ?></h1>
    <p><?php esc_html_e( 'This page displays the contents of the plugin\'s log file for debugging purposes.', 'ia-agent-content-platform' ); ?></p>

    <div id="log-container" style="background-color: #fff; border: 1px solid #ccd0d4; padding: 15px; max-height: 600px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word;">
        <pre>
<?php
    $log_file = IACP_Logger::get_log_file_path();
    if ( file_exists( $log_file ) && filesize( $log_file ) > 0 ) {
        // Read and display the file content in reverse order (newest first)
        $file_content = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($file_content) {
            echo esc_html( implode( "\n", array_reverse( $file_content ) ) );
        } else {
            esc_html_e( 'The log file is empty.', 'ia-agent-content-platform' );
        }
    } else {
        esc_html_e( 'The log file is empty or does not exist.', 'ia-agent-content-platform' );
    }
?>
        </pre>
    </div>

    <form method="post" action="">
        <?php wp_nonce_field( 'iacp_clear_log_action', 'iacp_clear_log_nonce' ); ?>
        <p class="submit">
            <input type="submit" name="clear_log" class="button button-danger" value="<?php esc_attr_e( 'Clear Log', 'ia-agent-content-platform' ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to clear the log file? This action cannot be undone.', 'ia-agent-content-platform' ) ); ?>');">
        </p>
    </form>
</div>