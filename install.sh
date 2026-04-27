#!/bin/bash

# MonitorLinux Installer Script
# Automatiza la instalación y configuración del entorno usando Docker

set -e

# Colores para los mensajes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}=======================================${NC}"
echo -e "${BLUE}   Instalador de MonitorLinux (SPA)    ${NC}"
echo -e "${BLUE}=======================================${NC}\n"

# 1. Verificar dependencias
echo -e "${GREEN}[1/7] Verificando dependencias...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Error: Docker no está instalado. Por favor, instala Docker primero.${NC}"
    exit 1
fi

if ! docker compose version &> /dev/null; then
    echo -e "${RED}Error: Docker Compose V2 no está instalado.${NC}"
    exit 1
fi

# 2. Entrar al directorio del proyecto
cd laravel-docker || { echo -e "${RED}Error: No se encuentra el directorio laravel-docker.${NC}"; exit 1; }

# 3. Configurar archivo .env
echo -e "${GREEN}[2/7] Configurando el archivo .env...${NC}"
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "Archivo .env creado desde .env.example"
    else
        echo -e "${RED}Error: No se encontró .env.example.${NC}"
        exit 1
    fi
else
    echo "El archivo .env ya existe. Saltando paso..."
fi

# 4. Levantar contenedores de Docker
echo -e "${GREEN}[3/7] Levantando contenedores (Docker Compose)...${NC}"
docker compose up -d --build

# 5. Instalar dependencias de PHP (Composer)
echo -e "${GREEN}[4/7] Instalando dependencias de PHP (Composer)...${NC}"
docker compose exec app composer install --no-interaction --optimize-autoloader --ignore-platform-reqs

# 6. Configurar Laravel (Key, Permisos, Migraciones)
echo -e "${GREEN}[5/7] Configurando Laravel...${NC}"
echo "Generando Application Key..."
docker compose exec app php artisan key:generate --force

echo "Ejecutando migraciones de la base de datos..."
# Esperar un poco a que MySQL inicie correctamente
sleep 5
docker compose exec app php artisan migrate --force

echo "Ajustando permisos de directorios..."
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache

# 7. Compilar Frontend (Vue 3 + Vite)
echo -e "${GREEN}[6/7] Compilando assets del Frontend (Vue SPA)...${NC}"
# Como no hay un contenedor Node, usamos la imagen oficial de Node de manera temporal
docker run --rm -v $(pwd):/var/www -w /var/www node:20 sh -c "npm install && npm run build"

echo -e "\n${GREEN}[7/7] ¡Instalación Completada Exitosamente!${NC}"
echo -e "======================================================"
echo -e "MonitorLinux ahora se está ejecutando."
echo -e "Puedes acceder en: ${BLUE}http://localhost:8080${NC}"
echo -e "======================================================"
