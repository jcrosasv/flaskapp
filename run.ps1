# Script PowerShell para instalar y ejecutar la aplicación Flask

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Aplicacion de Busqueda de Infracciones" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Verificar si Python está instalado
try {
    $pythonVersion = python --version 2>&1
    Write-Host "Python encontrado: $pythonVersion" -ForegroundColor Green
} catch {
    Write-Host "Error: Python no está instalado o no está en el PATH" -ForegroundColor Red
    Read-Host "Presiona Enter para salir"
    exit 1
}

# Cambiar a la carpeta backend
Set-Location backend

# Crear entorno virtual si no existe
if (-not (Test-Path venv)) {
    Write-Host "Creando entorno virtual..." -ForegroundColor Yellow
    python -m venv venv
}

# Activar entorno virtual
Write-Host "Activando entorno virtual..." -ForegroundColor Yellow
& .\venv\Scripts\Activate.ps1

# Instalar dependencias
Write-Host "Instalando dependencias..." -ForegroundColor Yellow
pip install -r requirements.txt

# Ejecutar la aplicación
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Ejecutando aplicacion..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Accede a: http://localhost:5000/login" -ForegroundColor Green
Write-Host "Presiona Ctrl+C para detener la aplicacion" -ForegroundColor Yellow
Write-Host ""

python app.py
