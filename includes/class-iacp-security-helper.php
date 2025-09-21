<?php
class IACP_Security_Helper {
    public static function encrypt($data) {
        $salt = self::get_wp_salt();
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $salt, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    public static function decrypt($data) {
        $salt = self::get_wp_salt();
        
        // Strict base64 decoding
        $decoded_data = base64_decode($data, true);
        if ($decoded_data === false || !str_contains($decoded_data, '::')) {
            return false;
        }

        $parts = explode('::', $decoded_data, 2);
        if (count($parts) !== 2) {
            return false;
        }

        list($encrypted_data, $iv) = $parts;
        if (empty($encrypted_data) || empty($iv)) {
            return false;
        }

        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $salt, 0, $iv);
    }

    private static function get_wp_salt() {
        // Intenta obtener las sales de seguridad de WordPress en un orden específico.
        if (defined('SECURE_AUTH_SALT') && SECURE_AUTH_SALT) {
            return SECURE_AUTH_SALT;
        } elseif (defined('AUTH_SALT') && AUTH_SALT) {
            return AUTH_SALT;
        }
        // Fallback a una clave genérica si no se encuentran las sales.
        // Esto es menos seguro y debería evitarse en producción.
        return 'a-fallback-key-that-is-not-secure';
    }
}
