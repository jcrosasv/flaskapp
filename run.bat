@echo off
REM Script para instalar y ejecutar la aplicación Flask en Windows

echo ========================================
echo Aplicacion de Busqueda de Infracciones
echo ========================================
echo.

REM Verificar si Python está instalado
python --version >nul 2>&1
if errorlevel 1 (
    echo Error: Python no está instalado o no está en el PATH
    pause
    exit /b 1
)

REM Cambiar a la carpeta backend
cd backend

REM Crear entorno virtual si no existe
if not exist venv (
    echo Creando entorno virtual...
    python -m venv venv
)

REM Activar entorno virtual
echo Activando entorno virtual...
call venv\Scripts\activate.bat

REM Instalar dependencias
echo Instalando dependencias...
pip install -r requirements.txt

REM Ejecutar la aplicación
echo.
echo ========================================
echo Ejecutando aplicacion...
echo ========================================
echo.
echo Accede a: http://localhost:5000/login
echo Presiona Ctrl+C para detener la aplicacion
echo.
python app.py

pause
