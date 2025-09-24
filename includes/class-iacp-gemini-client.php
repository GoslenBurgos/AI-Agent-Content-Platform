<?php

require_once IACP_PLUGIN_DIR . 'includes/class-iacp-api-cache.php';

class IACP_Gemini_Client {

    private const API_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private const MODEL        = 'gemini-1.5-flash-latest';
    private const CIRCUIT_BREAKER_TRANSIENT = 'iacp_gemini_api_circuit_open';
    private const FAILURE_COUNT_TRANSIENT = 'iacp_gemini_api_failures';
    private const FAILURE_THRESHOLD = 3;
    private const CIRCUIT_OPEN_PERIOD = 5 * 60; // 5 minutes
    private const FAILURE_WINDOW = 5 * 60; // 5 minutes

    private static function get_api_key() {
        $encrypted_key = get_option( 'iacp_gemini_api_key' );
        if ( ! empty( $encrypted_key ) ) {
            return IACP_Security_Helper::decrypt( $encrypted_key );
        }
        return $encrypted_key;
    }

    public static function generate_text( $prompt ) {
        $cached_response = IACP_Api_Cache::get( $prompt );
        if ( $cached_response ) {
            return $cached_response;
        }

        if ( get_transient( self::CIRCUIT_BREAKER_TRANSIENT ) ) {
            return new WP_Error( 'circuit_breaker', 'La API de Gemini no está disponible temporalmente debido a fallos repetidos. Por favor, inténtalo de nuevo en unos minutos.' );
        }

        $api_key = self::get_api_key();

        if ( empty( $api_key ) ) {
            return new WP_Error( 'api_key_missing', 'La clave de la API de Gemini no está configurada en los ajustes del plugin.' );
        }

        $api_url = self::API_BASE_URL . self::MODEL . ':generateContent';
        $request_body = array('contents' => array(array('parts' => array(array('text' => $prompt)))));
        $args = array(
            'method'    => 'POST',
            'headers'   => array(
                'Content-Type' => 'application/json',
                'X-goog-api-key' => $api_key,
            ),
            'body'      => json_encode( $request_body ),
            'timeout'   => 60,
        );

        $max_retries = 3;
        $retry_delay = 1; // seconds

        for ( $i = 0; $i < $max_retries; $i++ ) {
            $response = wp_remote_post( $api_url, $args );

            $is_wp_error = is_wp_error( $response );
            $response_code_check = $is_wp_error ? 0 : wp_remote_retrieve_response_code( $response );
            $is_server_error = ( $response_code_check >= 500 && $response_code_check <= 599 );

            if ( $is_wp_error || $is_server_error ) {
                self::record_failure();
                if ( self::is_circuit_open() ) {
                    return new WP_Error( 'circuit_breaker', 'La API de Gemini no está disponible temporalmente debido a fallos repetidos. Por favor, inténtalo de nuevo en unos minutos.' );
                }

                if ( $i < $max_retries - 1 ) {
                    sleep( $retry_delay );
                    $retry_delay *= 2;
                    continue;
                } else {
                    $error_message = $is_wp_error ? $response->get_error_message() : 'La API de Gemini devolvió un error de servidor (' . $response_code_check . ') después de varios intentos.';
                    return new WP_Error( 'api_error', $error_message );
                }
            }

            // If we are here, the call was successful (or a non-5xx client error)
            self::reset_failures();
            break;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );
        $response_data = json_decode( $response_body, true );

        if ( $response_code != 200 ) {
            $error_message = isset( $response_data['error']['message'] ) ? $response_data['error']['message'] : $response_body;
            IACP_Logger::log('error', 'Gemini API Error (' . $response_code . ')', $response_data);
            return new WP_Error( 'api_error', 'La API de Gemini devolvió un error (' . $response_code . '): ' . $error_message );
        }

        if ( ! isset( $response_data['candidates'][0]['content']['parts'][0]['text'] ) || empty( $response_data['candidates'][0]['content']['parts'][0]['text'] ) ) {
            IACP_Logger::log('error', 'Unexpected or empty Gemini API response', $response_data);
            return new WP_Error( 'empty_or_invalid_response', 'La API de Gemini devolvió una respuesta vacía o inválida. Intenta con un prompt diferente o revisa los logs.' );
        }

        $generated_text = $response_data['candidates'][0]['content']['parts'][0]['text'];

        IACP_Api_Cache::set( $prompt, $generated_text );

        return $generated_text;
    }

    private static function record_failure() {
        $failures = get_transient( self::FAILURE_COUNT_TRANSIENT );
        $failures = is_array( $failures ) ? $failures : array();

        $failures[] = time();

        $window_start = time() - self::FAILURE_WINDOW;
        $failures = array_filter( $failures, function( $timestamp ) use ( $window_start ) {
            return $timestamp > $window_start;
        } );

        set_transient( self::FAILURE_COUNT_TRANSIENT, $failures, self::FAILURE_WINDOW );
    }

    private static function reset_failures() {
        delete_transient( self::FAILURE_COUNT_TRANSIENT );
    }

    private static function is_circuit_open() {
        $failures = get_transient( self::FAILURE_COUNT_TRANSIENT );
        if ( is_array( $failures ) && count( $failures ) >= self::FAILURE_THRESHOLD ) {
            set_transient( self::CIRCUIT_BREAKER_TRANSIENT, true, self::CIRCUIT_OPEN_PERIOD );
            return true;
        }
        return false;
    }
}