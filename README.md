# Sistema de Búsqueda de Infracciones - Python

Aplicación web para consultar información de infracciones de tránsito. Los usuarios pueden crear cuentas, autenticarse, cargar archivos Excel con información de infracciones y buscar por nombre del infractor, cédula, comparendo o placa del vehículo.

## ✨ Características Principales

- ✅ **Autenticación de Usuarios**: Registro e inicio de sesión seguros
- ✅ **Búsqueda Inteligente**: Un único campo de búsqueda que detecta automáticamente si estás buscando por:
  - Nombre del infractor
  - Número de cédula
  - Número de comparendo
  - Placa del vehículo
- ✅ **Carga de Excel**: Sube periódicamente archivos con información actualizada
- ✅ **Almacenamiento en Memoria**: Los datos se cargan en memoria para acceso rápido (sin BD relacional para los datos)
- ✅ **Interfaz Responsiva**: Funciona en desktop, tablet y móvil
- ✅ **Seguridad**: Contraseñas encriptadas y sesiones seguras

## 📁 Estructura del Proyecto

```
back-front/
├── backend/                    # Aplicación Flask (Python)
│   ├── app.py                 # Aplicación principal
│   ├── config.py              # Configuración
│   ├── utils.py               # Funciones auxiliares
│   ├── init_db.py             # Script de inicialización
│   ├── requirements.txt        # Dependencias
│   ├── .env.example           # Variables de ambiente ejemplo
│   ├── users.db               # Base de datos SQLite
│   ├── uploads/               # Archivos Excel subidos
│   └── venv/                  # Entorno virtual Python
│
├── front/                      # Frontend (HTML/CSS/JS)
│   ├── index.html             # Página de búsqueda
│   ├── login.html             # Página de autenticación
│   ├── upload.html            # Página de carga de archivos
│   └── styles.css             # Estilos CSS
│
├── run.bat                     # Script para Windows (batch)
├── run.ps1                     # Script para Windows (PowerShell)
├── README.md                   # Este archivo
├── INSTALACION.md              # Guía detallada de instalación
└── .gitignore                  # Archivos a ignorar en Git
```

## 🚀 Inicio Rápido

### Requisitos
- Python 3.8+
- pip

### 1. Instalación (Windows)

```powershell
# Navegar a la carpeta del proyecto
cd "C:\Users\usuario\OneDrive\Download\back-front-2\back-front"

# Ejecutar script de instalación
.\run.ps1
```

### 2. Instalación Manual

```powershell
# Crear entorno virtual
python -m venv venv
.\venv\Scripts\Activate.ps1

# Instalar dependencias
cd backend
pip install -r requirements.txt

# Ejecutar
python app.py
```

### 3. Acceder a la aplicación

Abre tu navegador en: `http://localhost:5000/login`

## 💾 Formato del Archivo Excel

El archivo Excel debe tener las siguientes columnas (en cualquier orden):

| NOMBRE INFRACTOR | CEDULA | COMPARENDO | PLACA | MANDAMIENTO DE PAGO | FECHA MANDAMIENTO | NUMERO EMBARGO |
|---|---|---|---|---|---|---|
| Juan Pérez | 1234567 | COM2024001 | ABC123 | SI | 2024-01-15 | EMB001 |
| María López | 9876543 | COM2024002 | XYZ789 | NO | 2024-01-20 | EMB002 |

## 🔍 Cómo Usar

### 1. Registro/Login
- Primera vez: Haz clic en "Registrarse" y crea tu cuenta
- Siguientes veces: Inicia sesión con tus credenciales

### 2. Cargar Datos
- Ve a "Cargar Datos"
- Arrastra un archivo Excel o selecciona uno
- Los datos se cargan automáticamente en memoria

### 3. Buscar
- Ve a "Buscar"
- Ingresa cualquier valor (nombre, cédula, comparendo o placa)
- El sistema detecta automáticamente el tipo de búsqueda

## 📚 Tecnologías Utilizadas

**Backend:**
- Flask 2.3.3 - Framework web
- Flask-SQLAlchemy 3.0.5 - ORM para base de datos
- Flask-Login 0.6.2 - Gestión de sesiones
- Werkzeug 2.3.7 - Utilidades de seguridad
- pandas 2.0.3 - Procesamiento de datos
- openpyxl 3.1.2 - Lectura de Excel

**Frontend:**
- HTML5
- CSS3
- JavaScript (vanilla)

**Base de Datos:**
- SQLite (usuarios)
- Memoria (datos de Excel)

## 🔒 Seguridad

- **Contraseñas**: Encriptadas con Werkzeug (PBKDF2)
- **Sesiones**: Gestión segura con Flask
- **Validación**: Validación de archivos y entrada
- **CORS**: Control de acceso cruzado

## 🔧 API Endpoints

### Autenticación
```
POST /api/v1/file/login
POST /api/v1/file/register
GET /logout
```

### Búsqueda
```
GET /api/v1/file/search?searchText=valor
```

### Carga de Archivos
```
POST /api/v1/file/upload
```

## 📖 Documentación Adicional

Consulta `INSTALACION.md` para:
- Pasos detallados de instalación
- Solución de problemas
- Configuración avanzada
- Deployment en producción

## 🐛 Problemas Comunes

### Error: "Python no encontrado"
Verifica que Python está en tu PATH del sistema.

### Error: "Módulos no instalados"
```powershell
cd backend
pip install -r requirements.txt
```

### Puerto 5000 ya en uso
Edita `backend/app.py` y cambia el puerto en la última línea.

### Base de datos corrupta
```powershell
# Elimina users.db y reinicia
del backend\users.db
python backend\app.py
```

## 📝 Licencia

Este proyecto es de código abierto y está disponible bajo licencia MIT.

## 👨‍💻 Desarrollo

Para contribuir:
1. Haz un fork del proyecto
2. Crea una rama para tu feature
3. Haz commit de tus cambios
4. Push a la rama
5. Abre un Pull Request

## 📞 Soporte

Para soporte, abre un issue en el repositorio o contacta al equipo de desarrollo.

## 🎯 Roadmap

- [ ] Autenticación con OAuth2
- [ ] Búsqueda avanzada con filtros
- [ ] Exportar resultados a PDF
- [ ] Historial de búsquedas
- [ ] Dashboard administrativo
- [ ] API REST completa
- [ ] Documentación Swagger

---

**Última actualización**: Noviembre 2024

Hecho con ❤️ en Python
