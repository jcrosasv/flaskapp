#!/usr/bin/env python
"""
Script para verificar el estado de la base de datos
"""

import sys
import os
sys.path.insert(0, os.path.dirname(__file__))

from backend.app import app, db, ExcelRecord

with app.app_context():
    # Crear tabla si no existe
    db.create_all()
    
    # Contar registros actuales
    record_count = db.session.query(ExcelRecord).count()
    print(f"📊 Registros actuales en BD: {record_count}")
    
    if record_count > 0:
        records = ExcelRecord.get_all()
        print(f"\n📋 Primeros 5 registros:")
        for i, record in enumerate(records[:5]):
            print(f"  {i+1}. {record}")
    
    print("\n✅ BD verificada correctamente")
