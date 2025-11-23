@echo off
REM Script de instalación y configuración inicial para Windows

echo.
echo ========================================
echo Instalacion de Backend PHP
echo ========================================
echo.

REM Verificar si Composer está instalado
composer --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Composer no está instalado o no está en el PATH
    echo Descárgalo desde: https://getcomposer.org
    pause
    exit /b 1
)

REM Verificar si PHP está instalado
php --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP no está instalado o no está en el PATH
    echo Descárgalo desde: https://www.php.net
    pause
    exit /b 1
)

echo [OK] PHP y Composer encontrados
echo.

REM Instalar dependencias
echo Instalando dependencias...
call composer install

if errorlevel 1 (
    echo ERROR al instalar dependencias
    pause
    exit /b 1
)

echo [OK] Dependencias instaladas
echo.

REM Crear usuario admin
echo Creando usuario administrador...
php create_admin.php

if errorlevel 1 (
    echo ERROR al crear usuario
    pause
    exit /b 1
)

echo.
echo ========================================
echo Instalacion completada
echo ========================================
echo.
echo Para iniciar el servidor, ejecuta:
echo   run.bat
echo.
echo El servidor estará disponible en:
echo   http://localhost:8000
echo.
pause
