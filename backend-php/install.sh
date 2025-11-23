#!/bin/bash

# Script de instalación para Linux/Mac

echo ""
echo "========================================"
echo "Instalacion de Backend PHP"
echo "========================================"
echo ""

# Verificar si Composer está instalado
if ! command -v composer &> /dev/null; then
    echo "ERROR: Composer no está instalado"
    echo "Descárgalo desde: https://getcomposer.org"
    exit 1
fi

# Verificar si PHP está instalado
if ! command -v php &> /dev/null; then
    echo "ERROR: PHP no está instalado"
    echo "En macOS: brew install php"
    echo "En Linux: sudo apt-get install php php-cli php-sqlite3"
    exit 1
fi

echo "[OK] PHP y Composer encontrados"
echo ""

# Instalar dependencias
echo "Instalando dependencias..."
composer install

if [ $? -ne 0 ]; then
    echo "ERROR al instalar dependencias"
    exit 1
fi

echo "[OK] Dependencias instaladas"
echo ""

# Crear usuario admin
echo "Creando usuario administrador..."
php create_admin.php

if [ $? -ne 0 ]; then
    echo "ERROR al crear usuario"
    exit 1
fi

echo ""
echo "========================================"
echo "Instalacion completada"
echo "========================================"
echo ""
echo "Para iniciar el servidor, ejecuta:"
echo "  bash run.sh"
echo ""
echo "El servidor estará disponible en:"
echo "  http://localhost:8000"
echo ""
