#!/usr/bin/env python
"""
Script para resetear la base de datos y crear usuario admin
"""
import os
import sys

# Agregar la carpeta actual al path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app import app, db, User

def reset_database():
    """Elimina la BD anterior y crea una nueva"""
    with app.app_context():
        # Eliminar todas las tablas
        db.drop_all()
        print("✅ Tablas eliminadas")
        
        # Crear nuevas tablas con el nuevo esquema
        db.create_all()
        print("✅ Nueva estructura de BD creada")
        
        # Crear usuario admin
        admin_user = User(
            username='admin',
            role='admin'
        )
        admin_user.set_password('admin123')
        
        db.session.add(admin_user)
        db.session.commit()
        
        print("✅ Usuario admin creado exitosamente")
        print("   Usuario: admin")
        print("   Contraseña: admin123")
        print("   Rol: admin")

if __name__ == '__main__':
    try:
        reset_database()
        print("\n✅ Base de datos lista para usar")
    except Exception as e:
        print(f"❌ Error: {e}")
        sys.exit(1)
