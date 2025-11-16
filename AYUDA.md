# 🚀 AYUDA RÁPIDA - Primeros Pasos

## ⚡ Ejecutar en 30 segundos

### Windows (PowerShell)
```powershell
cd "C:\Users\usuario\OneDrive\Download\back-front-2\back-front"
.\run.ps1
```

### Windows (CMD)
```cmd
cd C:\Users\usuario\OneDrive\Download\back-front-2\back-front
run.bat
```

### Mac/Linux
```bash
cd ~/Downloads/back-front
python backend/app.py
```

## 🌐 Abrir en el Navegador

Una vez ejecutada, abre tu navegador en:
```
http://localhost:5000/login
```

## 📝 Crear Tu Primera Cuenta

1. En la página de login, haz clic en **"Registrarse"**
2. Ingresa un usuario (ej: `admin`)
3. Ingresa una contraseña (ej: `123456`)
4. Confirma la contraseña
5. Haz clic en **"Registrarse"**
6. ¡Listo! Ya estás dentro

## 📤 Cargar un Archivo Excel

1. Haz clic en **"Cargar Datos"** en el menú
2. Arrastra un archivo Excel o haz clic para seleccionarlo
3. El archivo debe tener estas columnas:
   - NOMBRE INFRACTOR
   - CEDULA
   - COMPARENDO
   - PLACA
   - MANDAMIENTO DE PAGO
   - FECHA MANDAMIENTO
   - NUMERO EMBARGO
4. Haz clic en **"Cargar Archivo"**

## 🔍 Buscar Información

1. Haz clic en **"Buscar"** en el menú
2. Ingresa cualquier valor:
   - **Nombre**: Juan Pérez
   - **Cédula**: 1234567
   - **Comparendo**: COM123456
   - **Placa**: ABC123
3. Presiona **Enter** o haz clic en **"Buscar"**

## ❌ Cerrar Sesión

Haz clic en **"Cerrar Sesión"** en la esquina superior derecha

## 🆘 Problemas

### La aplicación no inicia
1. Verifica que Python 3.8+ está instalado:
   ```
   python --version
   ```
2. Instala las dependencias:
   ```
   cd backend
   pip install -r requirements.txt
   ```

### Puerto 5000 ya en uso
Intenta con un puerto diferente:
```
Edita backend/app.py última línea:
app.run(debug=True, host='localhost', port=5001)
```

### Archivo Excel no se carga
Verifica que:
- Es un archivo `.xlsx` o `.xls`
- Tiene las columnas correctas
- No tiene caracteres especiales en los nombres

## 📂 Archivos Importantes

| Archivo | Descripción |
|---|---|
| `backend/app.py` | Aplicación principal |
| `backend/config.py` | Configuración |
| `backend/utils.py` | Funciones auxiliares |
| `front/index.html` | Página de búsqueda |
| `front/login.html` | Página de login |
| `front/upload.html` | Página de carga |
| `README.md` | Documentación completa |
| `INSTALACION.md` | Guía de instalación |

## 🔐 Seguridad

- Usa contraseñas fuertes en producción
- No compartas la clave secreta de Flask
- Cambia `SECRET_KEY` en `backend/config.py`

## 📚 Más Información

Lee los siguientes archivos para más detalles:
- **INSTALACION.md** - Guía completa de instalación
- **README.md** - Documentación del proyecto
- **CAMBIOS.md** - Resumen de cambios

## 💡 Tips Útiles

✅ Puedes cargar múltiples archivos Excel (se reemplaza el anterior)
✅ La búsqueda es exacta (no parcial)
✅ Los datos se guardan en memoria mientras la app está ejecutándose
✅ Para una mejor experiencia, usa archivos con máximo 50MB

## 🎯 Próximos Pasos

1. ✅ Instalar Python
2. ✅ Ejecutar `run.ps1` o `run.bat`
3. ✅ Abrir `http://localhost:5000/login`
4. ✅ Crear una cuenta
5. ✅ Cargar un archivo Excel
6. ✅ Hacer búsquedas

---

**¿Necesitas ayuda?** Consulta `INSTALACION.md` para más detalles

**¡Disfruta de la aplicación! 🎉**
