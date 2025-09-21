#!/bin/bash
# Este script configura el entorno de testing de WordPress.
# Descarga la biblioteca de tests de WP y un WordPress limpio.
# Luego, genera el archivo wp-tests-config.php para la conexión a la base de datos.

set -e

# --- Variables de Configuración ---

# Lee las variables del entorno o usa los valores por defecto de nuestro docker-compose.yml
DB_NAME=${WP_TESTS_DB_NAME:-wordpress_test}
DB_USER=${WP_TESTS_DB_USER:-user}
DB_PASS=${WP_TESTS_DB_PASS:-password}
DB_HOST=${WP_TESTS_DB_HOST:-iacp_db}
WP_VERSION=${1:-latest}

# Directorios de destino dentro del contenedor
WP_TESTS_DIR=/tmp/wordpress-tests-lib
WP_CORE_DIR=/tmp/wordpress/

# --- Lógica del Script ---

# Asegura que SVN esté instalado, ya que es necesario para descargar la biblioteca de tests
if ! command -v svn &> /dev/null; then
    echo "SVN no está instalado. Instalando..."
    apt-get update > /dev/null
    apt-get install -y subversion > /dev/null
    echo "SVN instalado."
fi

# Descarga la biblioteca de tests de WordPress si no existe
if [ -d "$WP_TESTS_DIR" ]; then
    echo "La biblioteca de tests de WordPress ya existe en $WP_TESTS_DIR."
else
    echo "Descargando la biblioteca de tests de WordPress..."
    svn co --quiet https://develop.svn.wordpress.org/trunk/ "$WP_TESTS_DIR"
fi

# Descarga y extrae la versión especificada de WordPress si no existe
if [ -d "$WP_CORE_DIR" ] && [ -f "$WP_CORE_DIR/wp-includes/version.php" ]; then
    echo "WordPress core ya existe en $WP_CORE_DIR."
else
    echo "Descargando WordPress v$WP_VERSION..."
    mkdir -p $WP_CORE_DIR

    # Asegura que unzip esté instalado para manejar los archivos .zip de 'latest'
    if ! command -v unzip &> /dev/null; then
        echo "unzip no está instalado. Instalando..."
        apt-get update > /dev/null
        apt-get install -y unzip > /dev/null
        echo "unzip instalado."
    fi

    if [ "$WP_VERSION" == "latest" ]; then
        TMP_FILE=/tmp/wordpress.zip
        LATEST_URL=$(curl -s https://api.wordpress.org/core/version-check/1.7/ | jq -r '[ .offers[] | select( .response == "upgrade" ) ][0] | .download')
        echo "URL de descarga (latest): $LATEST_URL"
        curl -L "$LATEST_URL" -o "$TMP_FILE"
        
        echo "Inspeccionando el archivo descargado:"
        file "$TMP_FILE"
        
        echo "Extrayendo archivo ZIP..."
        # Extraer a un directorio temporal para poder mover el contenido y simular --strip-components=1
        EXTRACT_TEMP_DIR=$(mktemp -d)
        unzip -q "$TMP_FILE" -d "$EXTRACT_TEMP_DIR"
        # Mover el contenido de la carpeta 'wordpress' que viene en el zip
        mv "$EXTRACT_TEMP_DIR"/wordpress/* "$WP_CORE_DIR/"
        rm -rf "$EXTRACT_TEMP_DIR"

    else
        TMP_FILE=/tmp/wordpress.tar.gz
        curl -L "https://wordpress.org/wordpress-${WP_VERSION}.tar.gz" -o "$TMP_FILE"
        
        echo "Inspeccionando el archivo descargado:"
        file "$TMP_FILE"

        echo "Extrayendo archivo tar.gz..."
        tar --strip-components=1 -zx -f "$TMP_FILE" -C "$WP_CORE_DIR"
    fi
    
    rm "$TMP_FILE"
fi

# Crea el archivo de configuración para los tests
# Este archivo le dice a la suite de tests cómo conectarse a nuestra base de datos de Docker
cat > $WP_TESTS_DIR/wp-tests-config.php <<-"EOF"
<?php
// Configuración de la Base de Datos para los Tests
define( 'DB_NAME', '$DB_NAME' );
define( 'DB_USER', '$DB_USER' );
define( 'DB_PASSWORD', '$DB_PASS' );
define( 'DB_HOST', '$DB_HOST' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

// Rutas y URLs para el entorno de testing
define( 'ABSPATH', '${WP_CORE_DIR}src/' );
define( 'WP_TESTS_DOMAIN', 'localhost' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WPLANG', '' );

// Obligatorio: La ruta al directorio de WordPress que acabamos de descargar
define( 'WP_CORE_DIR', '$WP_CORE_DIR' );

// Obligatorio: La ruta a la biblioteca de tests que acabamos de descargar
define( 'WP_TESTS_DIR', '$WP_TESTS_DIR' );

// Configuración de Debugging
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );

$table_prefix = 'wptests_';
EOF

echo "¡Entorno de testing de WordPress configurado con éxito en /tmp/!"
