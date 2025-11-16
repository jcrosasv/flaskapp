# 🏗️ ARQUITECTURA DEL SISTEMA

## 📐 Diagrama General

```
┌─────────────────────────────────────────────────────────┐
│                    NAVEGADOR WEB                         │
│           HTML, CSS, JavaScript (Frontend)              │
└────────────────────┬────────────────────────────────────┘
                     │ HTTP/AJAX
                     ↓
┌─────────────────────────────────────────────────────────┐
│         SERVIDOR FLASK (Backend Python)                 │
│                   Port: 5000                            │
├─────────────────────────────────────────────────────────┤
│ Routes:                                                 │
│ • /login (GET/POST)         → Autenticación            │
│ • /register (POST)          → Registro                 │
│ • /search (GET)             → Búsqueda                 │
│ • /upload (GET/POST)        → Carga archivos           │
│ • /logout (GET)             → Cierre sesión            │
├─────────────────────────────────────────────────────────┤
│ Datos:                                                  │
│ • SQLite: usuarios (encrypted)                         │
│ • Memoria: Excel cargado (búsqueda)                   │
└─────────────────────────────────────────────────────────┘
```

## 🔄 Flujos Principales

### 1. Flujo de Autenticación

```
Usuario escriba credenciales
        ↓
    login.html (AJAX POST)
        ↓
    /api/v1/file/login
        ↓
    Verificar en users.db
        ↓
    ✓ Válido → Crear sesión → Redirigir a /upload
    ✗ Inválido → Error 401 → Mostrar mensaje
```

### 2. Flujo de Carga de Archivo

```
Usuario selecciona archivo Excel
        ↓
    upload.html (AJAX POST)
        ↓
    /api/v1/file/upload
        ↓
    ├─ Validar sesión
    ├─ Validar archivo (.xlsx/.xls)
    ├─ Guardar en /uploads/
    ├─ Cargar en memoria (pandas)
    └─ Retornar resultado
```

### 3. Flujo de Búsqueda

```
Usuario ingresa término
        ↓
    index.html (AJAX GET)
        ↓
    /api/v1/file/search?searchText=valor
        ↓
    ├─ Validar sesión
    ├─ Normalizar término
    ├─ Buscar en datos de memoria
    ├─ Formatear resultados
    └─ Retornar JSON
        ↓
    Mostrar tabla en HTML
```

## 🗄️ Modelos de Datos

### Base de Datos (SQLite)

```
┌─────────────────────────────────┐
│         Tabla: user             │
├─────────────────────────────────┤
│ id (PK)                         │
│ username (UNIQUE)               │
│ password (HASHED)               │
│ created_at (TIMESTAMP)          │
└─────────────────────────────────┘
```

### Datos en Memoria (Excel)

```
┌─────────────────────────────────────────────────────────┐
│              Estructura de Excel                         │
├─────────────────────────────────────────────────────────┤
│ NOMBRE INFRACTOR  │ CEDULA │ COMPARENDO │ PLACA │ ...  │
├─────────────────────────────────────────────────────────┤
│ Juan Pérez        │ 123456 │ COM001     │ ABC12 │ ...  │
│ María López       │ 654321 │ COM002     │ XYZ98 │ ...  │
│ ...               │ ...    │ ...        │ ...   │ ...  │
└─────────────────────────────────────────────────────────┘

Almacenado en memoria como:
current_columns = [encabezados]
current_data = [[fila1], [fila2], ...]
```

## 🏭 Componentes del Sistema

### Backend (Python/Flask)

```
app.py
├── Imports y configuración
├── Modelos de BD
│   └── class User
├── Variables globales
│   ├── current_data
│   └── current_columns
├── Funciones auxiliares
│   ├── load_excel_data()
│   └── search_data()
└── Routes
    ├── GET  /
    ├── GET  /login
    ├── POST /login
    ├── POST /register
    ├── GET  /search
    ├── GET  /upload
    ├── POST /upload
    ├── GET  /logout
    ├── GET  /api/v1/file/login
    ├── GET  /api/v1/file/search
    └── POST /api/v1/file/upload
```

### Frontend (HTML/CSS/JS)

```
login.html
├── HTML
│   ├── Header
│   ├── Formulario login
│   ├── Formulario registro
│   └── Footer
└── JavaScript
    ├── switchTab()
    ├── loginForm (submit)
    └── registerForm (submit)

index.html
├── HTML
│   ├── Header con links
│   ├── Formulario de búsqueda
│   ├── Área de resultados
│   └── Footer
└── JavaScript
    ├── Validación
    ├── Fetch API
    ├── Renderizado dinámico
    └── Manejo de errores

upload.html
├── HTML
│   ├── Header con links
│   ├── Área drag & drop
│   ├── Selector de archivo
│   └── Botón de carga
└── JavaScript
    ├── Drag and drop
    ├── Fetch API
    ├── Validación de archivo
    └── Mensajes de estado
```

### Módulos de Soporte

```
config.py
├── class Config
│   ├── SECRET_KEY
│   ├── SQLALCHEMY_DATABASE_URI
│   └── UPLOAD_FOLDER
├── class DevelopmentConfig
├── class ProductionConfig
└── get_config()

utils.py
├── validate_excel_file()
├── read_excel_file()
├── normalize_search_text()
├── search_in_excel_data()
├── format_search_results()
└── allowed_file()
```

## 🔐 Seguridad

### Autenticación

```
Contraseña → Werkzeug.security.generate_password_hash()
                        ↓
            PBKDF2 con SHA-256 + salt
                        ↓
            Hash almacenado en BD
```

### Sesiones

```
Login exitoso → flask.session['user_id'] = user.id
                 flask.session['username'] = user.username
                        ↓
              Validar en cada petición
                        ↓
              Logout → session.clear()
```

### Validación de Archivos

```
Archivo → Extensión (.xlsx/.xls)
        → Tamaño (máx 50MB)
        → Formato Excel válido
        → openpyxl.load_workbook()
```

## 📊 Base de Datos

### Esquema SQLite

```sql
CREATE TABLE user (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(80) UNIQUE NOT NULL,
    password VARCHAR(120) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### Ciclo de Vida

```
1. Inicialización → db.create_all() en app context
2. Operación → CRUD mediante SQLAlchemy ORM
3. Persistencia → Archivos .db en disco
4. Limpieza → DELETE o db.drop_all() en testing
```

## 🔄 Flujo de Datos Completo

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │ HTTP Request
       ↓
┌──────────────────────┐
│  Frontend (HTML/JS)  │
│  Validación local    │
└──────┬───────────────┘
       │ AJAX JSON
       ↓
┌──────────────────────┐
│ Flask Routes         │
│ (Endpoint)           │
└──────┬───────────────┘
       │
       ├─→ ┌─────────────────┐
       │   │ Validar sesión  │
       │   └────────┬────────┘
       │            ↓
       │   ┌─────────────────┐
       │   │ Procesar datos  │
       │   └────────┬────────┘
       │            ↓
       ├─→ ┌─────────────────┐
           │ Acceder BD/Mem  │
           └────────┬────────┘
                    ↓
           ┌─────────────────┐
           │ SQLite / Memoria│
           └────────┬────────┘
                    ↓
           ┌─────────────────┐
           │ Formatear JSON  │
           └────────┬────────┘
                    ↓
       ┌────────────────────┐
       │ HTTP Response JSON │
       └────────┬───────────┘
               ↓
       ┌────────────────────┐
       │ Frontend procesa   │
       │ Renderiza HTML     │
       └────────┬───────────┘
               ↓
       ┌────────────────────┐
       │   Mostrar al user  │
       └────────────────────┘
```

## 🎯 Patrones de Diseño

### MVC (Model-View-Controller)

```
Model
├── app.py (User model)
├── config.py (Configuration model)
└── users.db (Data model)

View
├── login.html
├── index.html
├── upload.html
└── styles.css

Controller
└── app.py (Routes y lógica)
```

### Separación de Responsabilidades

```
app.py
├── Rutas y endpoints
└── Lógica de negocio

config.py
└── Configuración centralizada

utils.py
├── Funciones reutilizables
└── Lógica auxiliar

Frontend
├── Presentación
└── Validación local
```

## 📈 Escalabilidad

### Horizontal

```
Actual:
┌───────────┐
│ 1 proceso │ Port 5000
└───────────┘

Futuro con Load Balancer:
┌───────────┐
│ Proceso 1 │ Port 5001
├───────────┤ 
│ Proceso 2 │ Port 5002  ← Load Balancer
├───────────┤
│ Proceso 3 │ Port 5003
└───────────┘
```

### Vertical

```
Actual: SQLite (Archivo)
  ↓
Futuro: PostgreSQL/MySQL (Servidor)
  ↓
Futuro: Redis (Cache)
```

## 🚀 Deployment

### Desarrollo
```
python backend/app.py
```

### Producción
```
Gunicorn/uWSGI:
gunicorn --workers 4 --bind 0.0.0.0:5000 app:app

Nginx (reverse proxy):
proxy_pass http://localhost:5000;
```

### Docker
```dockerfile
FROM python:3.10
WORKDIR /app
COPY requirements.txt .
RUN pip install -r requirements.txt
COPY . .
CMD ["gunicorn", "--bind", "0.0.0.0:5000", "app:app"]
```

## 📊 Performance

### Optimizaciones

1. **Búsqueda**: O(n) en memoria (muy rápido)
2. **Autenticación**: Hash PBKDF2 (seguro)
3. **Sesiones**: En memoria (rápido)
4. **Archivos**: Almacenamiento temporal (limpiable)

### Límites

```
Archivo máximo: 50MB
Registros en memoria: depende de RAM
Usuarios simultáneos: ilimitado
Conexiones simultáneas: 100+ (configurable)
```

## 🔧 Mantenimiento

### Logs

```
STDOUT: Mensajes de la aplicación
STDERR: Errores
Access logs: Peticiones HTTP (opcional con Gunicorn)
```

### Monitoreo

```
Recursos:
├── CPU: flask app
├── RAM: current_data + users.db
└── Disco: uploads/ + users.db

Métricas:
├── Tiempo de respuesta
├── Errores 4xx/5xx
├── Usuarios activos
└── Búsquedas por segundo
```

---

**Última actualización**: Noviembre 2024

Esta arquitectura está diseñada para ser:
- 🟢 Simple y clara
- 🟢 Mantenible y escalable
- 🟢 Segura y confiable
- 🟢 Eficiente y rápida
