# Resumen del Ecosistema de Desarrollo

Estamos trabajando en un plugin de WordPress llamado `ia-agent-content-platform`. El entorno de desarrollo está completamente containerizado usando Docker y Docker Compose.

## Lenguaje Principal

*   **PHP**: Siguiendo las convenciones y estructura de un plugin de WordPress.

## Gestión de Dependencias

*   **Composer**: Para las dependencias de PHP (como PHPUnit).
*   **NPM/package.json**: Para herramientas de desarrollo y dependencias de JavaScript (como Playwright para tests E2E).

## Entorno de Desarrollo

*   `docker-compose.yml`: Orquesta los servicios, incluyendo un contenedor para la base de datos (`iacp_db`) y un contenedor CLI de PHP (`php-cli`) para ejecutar comandos y tests.

## Testing

*   **Tests Unitarios/Integración**: Usamos PHPUnit. Se ejecutan dentro del contenedor de Docker con el comando `docker-compose run --rm php-cli ./vendor/bin/phpunit`.
*   **Tests End-to-End**: El proyecto está configurado para usar Playwright.

## Integración Continua (CI)

*   Se utiliza GitHub Actions para automatizar la ejecución de tests, definido en `.github/workflows/ci.yml`.
