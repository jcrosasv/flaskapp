"""
Punto de entrada para Gunicorn en producción
"""
import os
import sys
from pathlib import Path

# Agregar el directorio backend al path
BASE_DIR = Path(__file__).parent
BACKEND_DIR = BASE_DIR / 'backend'
sys.path.insert(0, str(BACKEND_DIR))

# Cambiar a directorio backend para que Flask encuentre los archivos
os.chdir(BACKEND_DIR)

# Importar la app
from app import app

if __name__ == '__main__':
    app.run()
