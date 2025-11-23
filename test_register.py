#!/usr/bin/env python3
"""Script para probar el endpoint /register"""

import sys
import os

# Agregar ruta al backend
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'backend'))

from app import app, db, User, get_current_user
import json

# Crear contexto de aplicación
with app.app_context():
    # Probar que la función existe
    print("✓ get_current_user función importada correctamente")
    
    # Verificar que User model existe
    print("✓ User model importado correctamente")
    
    # Verificar conexión a BD
    try:
        users = User.query.all()
        print(f"✓ BD conectada. Total usuarios: {len(users)}")
        for user in users:
            print(f"  - {user.username} (admin: {user.is_admin()})")
    except Exception as e:
        print(f"✗ Error al conectar BD: {e}")
        import traceback
        traceback.print_exc()
    
    # Simular un POST a /register
    print("\n=== Probando endpoint /register ===")
    with app.test_client() as client:
        # Primero login para obtener sesión
        login_resp = client.post(
            '/api/v1/file/login',
            json={'username': 'admin', 'password': 'admin123'},
            content_type='application/json'
        )
        print(f"Login response: {login_resp.status_code}")
        if login_resp.status_code == 200:
            print(f"  Respuesta: {login_resp.get_json()}")
            
            # Ahora intentar crear usuario
            register_resp = client.post(
                '/register',
                json={'username': 'testuser', 'password': 'test123', 'role': 'user'},
                content_type='application/json'
            )
            print(f"Register response: {register_resp.status_code}")
            print(f"  Respuesta: {register_resp.get_json()}")
        else:
            print(f"  Error en login: {login_resp.get_json()}")
