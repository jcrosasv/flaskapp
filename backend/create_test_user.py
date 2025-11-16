"""
Script para crear un usuario de prueba en la base de datos
Ejecutar desde la carpeta backend: python create_test_user.py
"""

import os
import sys

# Agregar la carpeta actual al path
sys.path.insert(0, os.path.dirname(__file__))

from app import app, db, User

def create_test_user():
    """Crea un usuario de prueba"""
    with app.app_context():
        # Verificar si el usuario ya existe
        existing_user = User.query.filter_by(username='admin').first()
        if existing_user:
            print("⚠️  El usuario 'admin' ya existe")
            return
        
        # Crear usuario de prueba
        user = User(username='admin')
        user.set_password('admin123')
        
        db.session.add(user)
        db.session.commit()
        
        print("✓ Usuario de prueba creado exitosamente")
        print("  Usuario: admin")
        print("  Contraseña: admin123")
        print("\n⚠️  Cambia la contraseña después del primer login!")

if __name__ == '__main__':
    create_test_user()
