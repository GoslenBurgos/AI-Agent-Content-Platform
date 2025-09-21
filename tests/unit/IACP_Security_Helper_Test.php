<?php

// Importa la clase base de PHPUnit para poder extenderla.
use PHPUnit\Framework\TestCase;

if (!defined('IACP_PLUGIN_DIR')) {
    define('IACP_PLUGIN_DIR', dirname(dirname(__DIR__)) . '/');
}
require_once IACP_PLUGIN_DIR . 'includes/class-iacp-security-helper.php';

/**
 * Define la suite de pruebas para la clase IACP_Security_Helper.
 * El nombre de la clase DEBE coincidir con el nombre del archivo y terminar en "Test".
 */
class IACP_Security_Helper_Test extends TestCase
{
    /**
     * Este es el método de prueba real.
     * Su nombre DEBE comenzar con la palabra "test".
     *
     * Prueba el flujo completo: encripta un dato, luego lo desencripta
     * y finalmente verifica que el resultado es idéntico al original.
     */
    public function testEncryptAndDecryptSuccessfully()
    {
        // 1. Arrange (Preparar): Define los datos que vamos a usar.
        $originalData = 'Este es un secreto que nadie debe saber.';
        
        // Define una constante de WordPress que tu clase espera encontrar.
        // Esto es importante porque en el entorno de pruebas, WordPress no está cargado.
        if (!defined('AUTH_SALT')) {
            define('AUTH_SALT', 'una-clave-secreta-para-las-pruebas-no-importa-cual-sea');
        }

        // 2. Act (Actuar): Ejecuta el código que quieres probar.
        $encryptedData = IACP_Security_Helper::encrypt($originalData);
        $decryptedData = IACP_Security_Helper::decrypt($encryptedData);

        // 3. Assert (Verificar): Comprueba si el resultado es el esperado.
        // Afirmamos que el dato desencriptado DEBE SER IGUAL al dato original.
        $this->assertEquals($originalData, $decryptedData);
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testDecryptReturnsFalseForInvalidData($invalidData)
    {
        // Arrange
        if (!defined('AUTH_SALT')) {
            define('AUTH_SALT', 'una-clave-secreta-para-las-pruebas-no-importa-cual-sea');
        }

        // Act
        $result = IACP_Security_Helper::decrypt($invalidData);

        // Assert
        $this->assertFalse($result);
    }

    public static function invalidDataProvider()
    {
        return [
            'not base64' => ['datos invalidos'],
            'base64 without separator' => [base64_encode('datosvalidos')],
            'base64 with empty parts 1' => [base64_encode('::someiv')],
            'base64 with empty parts 2' => [base64_encode('somedata::')],
            'empty string' => [''],
        ];
    }

    public function testEncryptAndDecryptUsesSecureAuthSaltWhenAvailable()
    {
        $originalData = 'Data with secure salt';
        
        if (!defined('SECURE_AUTH_SALT')) {
            define('SECURE_AUTH_SALT', 'the-most-secure-salt-for-testing');
        }
        if (!defined('AUTH_SALT')) {
            define('AUTH_SALT', 'a-less-secure-salt-for-testing');
        }

        $encryptedData = IACP_Security_Helper::encrypt($originalData);
        $decryptedData = IACP_Security_Helper::decrypt($encryptedData);

        $this->assertEquals($originalData, $decryptedData);
    }

    public function testEncryptAndDecryptUsesFallbackKeyWhenSaltsAreMissing()
    {
        // Las constantes no se pueden "desdefinir", por lo que este test solo puede
        // ejecutarse si las sales no han sido definidas por un test anterior.
        if (defined('SECURE_AUTH_SALT') || defined('AUTH_SALT')) {
            $this->markTestSkipped('Cannot test fallback key if salts are already defined.');
        }

        $originalData = 'Data with fallback key';

        $encryptedData = IACP_Security_Helper::encrypt($originalData);
        $decryptedData = IACP_Security_Helper::decrypt($encryptedData);

        $this->assertEquals($originalData, $decryptedData);
    }
}
