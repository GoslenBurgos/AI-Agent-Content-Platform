<?php

/**
 * Class IACP_Logger
 *
 * Handles logging for the plugin to a dedicated file.
 */
class IACP_Logger {

    private static $log_file;

    /**
     * Gets the determined log file path, initializing it if necessary.
     * This function centralizes the logic for determining the log path,
     * making the system more robust and easier to maintain.
     *
     * @return string The absolute path to the log file.
     */
    public static function get_log_file_path() {
        if ( ! self::$log_file ) {
            self::init();
        }
        return self::$log_file;
    }

    /**
     * Initializes the logger. It first tries to create a log directory in the WordPress
     * uploads folder, which is the standard practice. If that directory is not writable,
     * it falls back to creating a 'logs' directory inside the plugin's own folder.
     */
    private static function init() {
        if ( self::$log_file ) {
            return;
        }

        // Try uploads directory first (best practice)
        $upload_dir = wp_upload_dir();
        $log_dir_uploads = $upload_dir['basedir'] . '/iacp-logs';

        if ( ! is_dir( $log_dir_uploads ) ) {
            // Try to create the directory
            wp_mkdir_p( $log_dir_uploads );
        }

        // Check if the uploads directory is writable
        if ( is_dir( $log_dir_uploads ) && is_writable( $log_dir_uploads ) ) {
            self::$log_file = $log_dir_uploads . '/development.log';
        } else {
            // Fallback to plugin directory if uploads is not writable
            $log_dir_plugin = IACP_PLUGIN_DIR . 'logs';
            if ( ! is_dir( $log_dir_plugin ) ) {
                wp_mkdir_p( $log_dir_plugin );
            }
            self::$log_file = $log_dir_plugin . '/development.log';
        }
    }

    /**
     * Formats a log entry string.
     *
     * @param string $level   The log level.
     * @param string $message The message to log.
     * @param mixed  $data    Optional data.
     * @return string The formatted log entry.
     */
    public static function format_log_entry($level, $message, $data = null) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] [" . strtoupper($level) . "] - {$message}";
        if (!is_null($data)) {
            $log_entry .= " | Data: " . print_r($data, true);
        }
        return $log_entry;
    }

    /**
     * Writes a message to the log file.
     *
     * @param string $level   The log level (e.g., ERROR, INFO, DEBUG).
     * @param string $message The message to log.
     * @param mixed  $data    Optional. Additional data to log (e.g., an array or object).
     */
    public static function log( $level, $message, $data = null ) {
        self::init();
        $log_entry = self::format_log_entry($level, $message, $data);

        // Use error_log as a fallback if file_put_contents fails, for maximum robustness.
        @file_put_contents( self::$log_file, $log_entry . PHP_EOL, FILE_APPEND );
    }
}