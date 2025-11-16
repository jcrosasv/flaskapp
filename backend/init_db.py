#!/usr/bin/env python
"""
Script para inicializar la base de datos
Ejecutar desde la carpeta backend: python init_db.py
"""

import os
import sys

# Agregar la carpeta actual al path
sys.path.insert(0, os.path.dirname(__file__))

from app import app, db, User

def init_database():
    """Inicializa la base de datos"""
    with app.app_context():
        print("Creando tablas de la base de datos...")
        db.create_all()
        print("✓ Base de datos inicializada correctamente")
        
        # Crear carpeta de uploads
        if not os.path.exists(app.config['UPLOAD_FOLDER']):
            os.makedirs(app.config['UPLOAD_FOLDER'])
            print(f"✓ Carpeta '{app.config['UPLOAD_FOLDER']}' creada")
        
        # Mostrar información
        print("\nEstadísticas de la base de datos:")
        user_count = User.query.count()
        print(f"  - Usuarios registrados: {user_count}")
        
        print("\nBase de datos lista para usar!")

if __name__ == '__main__':
    init_database()
