<?php
/**
 * Bootstrap para PHPUnit.
 *
 * Este archivo es incluido por el bootstrap principal de la suite de tests de WordPress.
 * Su trabajo es cargar el plugin que estamos probando.
 */

// Obtenemos el directorio de los tests de WordPress desde una variable de entorno.
$_tests_dir = getenv( 'WP_TESTS_DIR' );

// Si no se define, usamos una ruta por defecto en el directorio temporal.
if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Verificación de seguridad: si el archivo de funciones de WP no existe, algo ha ido mal.
if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
    echo "No se pudo encontrar $_tests_dir/includes/functions.php." . PHP_EOL;
    echo "Por favor, asegúrate de haber ejecutado el script bin/install-wp-tests.sh" . PHP_EOL;
    exit( 1 );
}

// Incluimos las funciones de la suite de tests, como tests_add_filter().
require_once $_tests_dir . '/includes/functions.php';

/**
 * Carga manualmente nuestro plugin para que esté activo durante los tests.
 */
function _manually_load_plugin() {
    require dirname( dirname( __FILE__ ) ) . '/ia-agent-content-platform.php';
}

// Usamos el hook 'muplugins_loaded' para cargar nuestro plugin en el momento adecuado.
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// El bootstrap principal de la suite de tests de WordPress se encarga del resto.