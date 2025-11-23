#!/usr/bin/env python3
"""Script para resetear contraseña del admin"""

import sys
import os

sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'backend'))

from app import app, db, User

with app.app_context():
    admin = User.query.filter_by(username='admin').first()
    if admin:
        admin.set_password('admin123')
        db.session.commit()
        print("✓ Contraseña del admin reseteada a: admin123")
    else:
        print("✗ Admin no encontrado")
