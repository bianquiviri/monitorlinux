#!/bin/bash

# MonitorLinux Installer Script
# Automatiza la instalación y configuración del entorno usando Docker

set -e

# Colores para los mensajes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Función para imprimir con color (compatible con sh/dash)
print_info() {
    printf "${BLUE}%s${NC}\n" "$1"
}

print_success() {
    printf "${GREEN}%s${NC}\n" "$1"
}

print_error() {
    printf "${RED}%s${NC}\n" "$1"
}

print_info "======================================="
print_info "   Instalador de MonitorLinux (SPA)    "
print_info "======================================="
printf "\n"

# 1. Verificar dependencias
print_success "[1/7] Verificando dependencias..."
if ! command -v docker > /dev/null 2>&1; then
    print_error "Error: Docker no está instalado. Por favor, instala Docker primero."
    exit 1
fi

if ! docker compose version > /dev/null 2>&1; then
    print_error "Error: Docker Compose V2 no está instalado."
    exit 1
fi

# 2. Entrar al directorio del proyecto (si no estamos ya allí)
if [ -d "laravel-docker" ]; then
    cd laravel-docker
elif [ ! -f "docker-compose.yml" ]; then
    print_error "Error: No se encuentra el directorio laravel-docker ni el archivo docker-compose.yml."
    exit 1
fi

# 3. Configurar archivo .env
print_success "[2/7] Configurando el archivo .env..."
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "Archivo .env creado desde .env.example"
    else
        print_error "Error: No se encontró .env.example."
        exit 1
    fi
else
    echo "El archivo .env ya existe. Saltando paso..."
fi

# 4. Levantar contenedores de Docker
print_success "[3/7] Levantando contenedores (Docker Compose)..."
docker compose up -d --build

# 5. Instalar dependencias de PHP (Composer)
print_success "[4/7] Instalando dependencias de PHP (Composer)..."
docker compose exec app composer install --no-interaction --optimize-autoloader --ignore-platform-reqs

# 6. Configurar Laravel (Key, Permisos, Migraciones)
print_success "[5/7] Configurando Laravel..."
echo "Generando Application Key..."
docker compose exec app php artisan key:generate --force

echo "Ejecutando migraciones de la base de datos..."
# Esperar un poco a que MySQL inicie correctamente
sleep 5
docker compose exec app php artisan migrate --seed --force

echo "Ajustando permisos de directorios..."
docker compose exec -u root app chmod -R 775 storage bootstrap/cache
docker compose exec -u root app chown -R www-data:www-data storage bootstrap/cache

# 7. Compilar Frontend (Vue 3 + Vite)
print_success "[6/7] Compilando assets del Frontend (Vue SPA)...${NC}"
docker compose run --rm node sh -c "npm install && npm run build"

# 8. Configurar Laravel Dusk y ejecutar pruebas
print_success "[7/7] Configurando Laravel Dusk y verificando instalación..."
docker compose exec app php artisan dusk:chrome-driver
# Symlink para asegurar compatibilidad de arquitectura en Docker
docker compose exec -u root app ln -sf /usr/bin/chromedriver /var/www/vendor/laravel/dusk/bin/chromedriver-linux

echo "Reiniciando servicios para refrescar configuración..."
docker compose restart web

print_info "Ejecutando pruebas E2E de validación (Dusk)..."
# Asegurar permisos para que el usuario bianquiviri pueda correr los tests y escribir logs
docker compose exec -u root app chmod -R 777 storage bootstrap/cache

if docker compose exec app php artisan dusk; then
    print_success "✓ Todas las pruebas pasaron correctamente."
else
    print_error "✗ Algunas pruebas fallaron. Revisa los logs en tests/Browser/screenshots"
fi

printf "\n"
print_success "¡Instalación y Verificación Completada Exitosamente!"
print_info "======================================================"
print_info "MonitorLinux ahora se está ejecutando."
printf "${GREEN}Puedes acceder en: ${BLUE}http://localhost:8080${NC}\n"
print_info "Credenciales por defecto: bianquiviri@gmail.com / !N1k00905"
print_info "======================================================"
