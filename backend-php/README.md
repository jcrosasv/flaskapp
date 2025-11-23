# Backend PHP - Equivalente a Flask App

Esta es una versión en PHP de la aplicación de búsqueda y carga de archivos Excel.

## Requisitos

- PHP 7.4 o superior
- Composer (para manejar dependencias)
- SQLite o MySQL (según configuración)

## Instalación

### 1. Instalar dependencias

```bash
cd backend-php
composer install
```

### 2. Crear usuario administrador

```bash
php create_admin.php
```

Esto creará un usuario con:
- Username: `admin`
- Password: `admin123`

### 3. Ejecutar el servidor

**Opción 1: PHP Built-in Server (desarrollo)**

```bash
# Windows
run.bat

# Linux/Mac
bash run.sh
```

El servidor estará disponible en `http://localhost:8000`

**Opción 2: Apache/Nginx**

Configurar el DocumentRoot apuntando a la carpeta `backend-php` y asegurar que `mod_rewrite` esté habilitado.

## Estructura del proyecto

```
backend-php/
├── api.php              # Archivo principal con todas las rutas
├── config.php           # Configuración de base de datos y constantes
├── utils.php            # Funciones auxiliares
├── Session.php          # Clase para manejar sesiones
├── index.php            # Enrutador principal
├── create_admin.php     # Script para crear usuario admin
├── composer.json        # Dependencias PHP
├── .htaccess            # Configuración para Apache
├── run.bat              # Script para ejecutar en Windows
└── run.sh               # Script para ejecutar en Linux/Mac
```

## Endpoints disponibles

Todos los endpoints están en `/api/v1/`

### Autenticación

- `POST /api/v1/file/login` - Login de usuario
- `GET /api/v1/logout` - Cerrar sesión

### Búsqueda

- `GET /api/v1/file/search?searchText=xxx&page=1` - Buscar en datos
- `GET /api/v1/file/first-data` - Obtener primeros 50 registros

### Carga de archivos (solo admin)

- `POST /api/v1/file/upload` - Subir archivo Excel

### Gestión de usuarios (solo admin)

- `GET /api/v1/users` - Listar usuarios
- `POST /api/v1/register` - Crear usuario
- `POST /api/v1/change-password` - Cambiar contraseña
- `POST /api/v1/delete-user` - Eliminar usuario
- `GET /api/v1/upload-logs` - Ver logs de cargas

## Características

✅ Autenticación con sesiones
✅ Gestión de usuarios (admin/user)
✅ Carga de archivos Excel
✅ Validación de duplicados (cédula + comparendo)
✅ Búsqueda en datos cargados
✅ Logs de carga de archivos
✅ Soporte para SQLite y MySQL
✅ CORS habilitado
✅ Manejo de errores robusto

## Configuración de base de datos

### SQLite (por defecto en desarrollo)

No requiere configuración adicional. Se crea automáticamente en `users.db`

### MySQL (producción)

Establecer variables de entorno:

```bash
ENVIRONMENT=production
DB_HOST=localhost
DB_USER=root
DB_PASS=password
DB_NAME=flask_app
```

## Notas de seguridad

⚠️ Cambiar `SECRET_KEY` en config.php en producción
⚠️ Cambiar contraseña de admin después de primera instalación
⚠️ Usar HTTPS en producción
⚠️ Implementar rate limiting para login
⚠️ Usar variables de entorno para datos sensibles

## Comparación Flask vs PHP

| Aspecto | Flask | PHP |
|---------|-------|-----|
| Framework | Flask-SQLAlchemy | PDO |
| Sesiones | session | $_SESSION |
| Hashing | Werkzeug | password_hash |
| Excel | openpyxl | PhpSpreadsheet |
| Servidor | Flask built-in | PHP built-in / Apache / Nginx |

## Diferencias implementadas

1. **Variables globales**: En PHP se usan variables globales para almacenar datos en memoria
2. **Sesiones**: PHP nativo en lugar de Flask-Session
3. **Base de datos**: PDO en lugar de SQLAlchemy
4. **Lectura de Excel**: PhpSpreadsheet en lugar de openpyxl
5. **Enrutamiento**: Manual en lugar de decoradores Flask

Para aplicaciones en producción, considerar:
- Usar un framework como Laravel o Symfony
- Implementar caché (Redis) para datos en memoria
- Usar WebSockets para actualizaciones en tiempo real
