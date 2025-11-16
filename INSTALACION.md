# Guía de Instalación - Sistema de Búsqueda de Infracciones

## 📋 Requisitos Previos

- **Python 3.8 o superior** - Descargar desde https://www.python.org/downloads/
- **pip** (gestor de paquetes de Python) - Generalmente viene con Python
- Navegador web moderno (Chrome, Firefox, Edge, Safari)

## 🚀 Pasos de Instalación

### Paso 1: Verificar Python

Abre una terminal (PowerShell en Windows) y verifica que Python está instalado:

```powershell
python --version
```

Deberías ver algo como: `Python 3.x.x`

### Paso 2: Navegar a la Carpeta del Proyecto

```powershell
cd "C:\Users\usuario\OneDrive\Download\back-front-2\back-front"
```

### Paso 3: Crear un Entorno Virtual (Recomendado)

```powershell
python -m venv venv
```

### Paso 4: Activar el Entorno Virtual

**En Windows (PowerShell):**
```powershell
.\venv\Scripts\Activate.ps1
```

**En Windows (CMD):**
```cmd
venv\Scripts\activate.bat
```

**En Mac/Linux:**
```bash
source venv/bin/activate
```

Deberías ver `(venv)` al inicio de la línea de comando.

### Paso 5: Instalar Dependencias

Navega a la carpeta backend:

```powershell
cd backend
```

Instala las dependencias:

```powershell
pip install -r requirements.txt
```

Este proceso descargará e instalará:
- Flask (framework web)
- Flask-SQLAlchemy (base de datos)
- pandas (procesamiento de Excel)
- openpyxl (lectura de Excel)
- Werkzeug (seguridad)

## 🎯 Ejecutar la Aplicación

### Opción 1: Script Automático (Windows)

Desde la carpeta raíz del proyecto:

```powershell
.\run.ps1
```

### Opción 2: Manualmente

1. Asegúrate que el entorno virtual está activado
2. Navega a la carpeta backend:
   ```powershell
   cd backend
   ```
3. Ejecuta la aplicación:
   ```powershell
   python app.py
   ```

### Opción 3: Script Batch (Windows CMD)

```cmd
run.bat
```

## 🌐 Acceder a la Aplicación

Una vez ejecutada, abre tu navegador y ve a:

```
http://localhost:5000/login
```

## 👤 Crear tu Primer Usuario

1. En la página de login, haz clic en la pestaña "Registrarse"
2. Ingresa un usuario y contraseña
3. Confirma la contraseña
4. Haz clic en "Registrarse"
5. La aplicación te iniciará sesión automáticamente

## 📤 Cargar un Archivo Excel

1. Después de iniciar sesión, haz clic en "Cargar Datos"
2. Arrastra un archivo Excel o haz clic para seleccionarlo
3. El archivo debe tener las siguientes columnas:
   - NOMBRE INFRACTOR
   - CEDULA
   - COMPARENDO
   - PLACA
   - MANDAMIENTO DE PAGO
   - FECHA MANDAMIENTO
   - NUMERO EMBARGO
4. Haz clic en "Cargar Archivo"

## 🔍 Usar el Buscador

1. Haz clic en "Buscar" en el menú
2. Ingresa un valor en el campo de búsqueda:
   - Nombre del infractor (ej: Juan Pérez)
   - Número de cédula (ej: 1234567)
   - Comparendo (ej: COM123456)
   - Placa del vehículo (ej: ABC123)
3. Presiona Enter o haz clic en "Buscar"
4. Los resultados se mostrarán en una tabla

## 🐛 Solucionar Problemas

### Error: "Python no encontrado"
- Asegúrate que Python está instalado
- Verifica que Python está en el PATH del sistema
- Reinicia la terminal

### Error: "Módulos no instalados"
- Ejecuta: `pip install -r requirements.txt`
- Verifica que está en la carpeta correcta (backend)

### Puerto 5000 ya en uso
Edita `backend/app.py` y cambia la última línea:
```python
app.run(debug=True, host='localhost', port=5001)  # Cambiar a otro puerto
```

### No puedo acceder a http://localhost:5000
- Asegúrate que la aplicación está ejecutándose
- Verifica que el puerto 5000 no está bloqueado por firewall
- Intenta con http://127.0.0.1:5000

### Base de datos corrupta
Elimina el archivo `backend/users.db` y reinicia la aplicación.

## 📁 Estructura de Carpetas

```
back-front/
├── backend/
│   ├── app.py              # Aplicación principal
│   ├── config.py           # Configuración
│   ├── utils.py            # Funciones auxiliares
│   ├── requirements.txt     # Dependencias
│   ├── users.db            # Base de datos (se crea automáticamente)
│   ├── uploads/            # Archivos Excel subidos
│   └── venv/               # Entorno virtual
└── front/
    ├── index.html          # Página de búsqueda
    ├── login.html          # Página de login
    ├── upload.html         # Página de carga
    └── styles.css          # Estilos
```

## 🔒 Seguridad

**Importante para Producción:**
1. Cambia `SECRET_KEY` en `config.py`
2. Establece `DEBUG = False`
3. Usa HTTPS en lugar de HTTP
4. Establece una contraseña fuerte para la base de datos
5. Configura variables de ambiente en lugar de códigos fijos

## 📞 Soporte

Si encuentras problemas:
1. Verifica que seguiste todos los pasos
2. Asegúrate que los puertos no están bloqueados
3. Revisa la consola para mensajes de error
4. Intenta reiniciar la aplicación

## 🛑 Detener la Aplicación

Presiona `Ctrl + C` en la terminal donde está ejecutándose la aplicación.

---

¡Listo! Tu aplicación está funcionando. Disfruta 🎉
