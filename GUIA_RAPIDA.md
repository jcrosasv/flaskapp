# 🎯 Guía Rápida - Nuevas Funcionalidades

## 📌 ¿Qué ha cambiado?

Tu sistema de búsqueda de infracciones ahora tiene un **sistema de roles** con dos tipos de usuarios:

### 👤 Usuario Regular
Solo puede **buscar** en los datos cargados

### 👨‍💼 Usuario Administrador  
Puede **buscar**, **cargar archivos** y **crear usuarios**

---

## 🚀 Cómo usar

### Acceso Inicial
1. Ve a `http://localhost:5000`
2. Inicia sesión con:
   - **Usuario**: `admin`
   - **Contraseña**: `admin123`

### Como Administrador 👨‍💼
Después de login, verás dos opciones en la página de búsqueda:

```
[Cargar Datos] [Cerrar Sesión]
```

#### 1️⃣ Cargar Archivos Excel
- Haz clic en "Cargar Datos"
- Selecciona o arrastra tu archivo Excel
- El archivo debe tener columnas con datos de infracciones

#### 2️⃣ Crear Nuevos Usuarios
- Usa una herramienta como **Postman** o **cURL**
- Endpoint: `POST http://localhost:5000/register`
- Ejemplo:
```json
{
    "username": "juan123",
    "password": "password123",
    "role": "user"
}
```

#### 3️⃣ Ver Usuarios Creados
- Endpoint: `GET http://localhost:5000/api/v1/users`
- Retorna lista de todos los usuarios

### Como Usuario Regular 👤
Después de login, solo verás:

```
[Cerrar Sesión]
```

- Buscas en los datos cargados
- Ves los primeros 50 registros automáticamente
- No puedes cargar archivos ni crear usuarios

---

## 🔍 Página de Búsqueda

### Lo que verás al entrar:

```
┌─────────────────────────────────────────────┐
│  Buscador de Infracciones                   │
│  [Cargar Datos] [Cerrar Sesión]  (Si eres admin)
├─────────────────────────────────────────────┤
│  💡 Busca por nombre, cédula, etc.          │
│  ┌─────────────────────┐ ┌────────┐        │
│  │  Ej: Juan Pérez     │ │ Buscar │        │
│  └─────────────────────┘ └────────┘        │
├─────────────────────────────────────────────┤
│  Primeros 50 registros cargados:            │
│  ┌───────────────────────────────────────┐  │
│  │ Nombre │ Cedula │ Comparendo │ Placa │  │
│  ├───────────────────────────────────────┤  │
│  │ Juan   │ 1234   │ COM123     │ ABC12 │  │
│  │ Maria  │ 5678   │ COM124     │ XYZ98 │  │
│  └───────────────────────────────────────┘  │
└─────────────────────────────────────────────┘
```

---

## 🔐 Seguridad

- Cada usuario tiene su propia cuenta
- Las contraseñas se guardan encriptadas
- Solo admins pueden crear usuarios
- El acceso se verifica en el servidor (backend)

---

## ⚡ Flujo de Login

```
1. Abres http://localhost:5000/login
2. Ingresas usuario y contraseña
3. El servidor valida credenciales
4. Si es correcto, te redirige a la página de búsqueda
5. Se guarda tu rol (admin/usuario) en el navegador
6. Se muestran/ocultan botones según tu rol
```

---

## 📝 Cambios de URL

| Acción | Antes | Ahora |
|--------|-------|-------|
| Login | `/login` | `/login` ✅ |
| Después de login | `/upload` | `/search` |
| Búsqueda | `/search` (nueva) | `/search` |
| Carga archivos | `/upload` | `/upload` (solo admin) |

---

## 🆘 Solución Rápida de Problemas

### El botón "Cargar Datos" no aparece
```
→ Borra el cache del navegador (Ctrl+Shift+Delete)
→ Cierra sesión y vuelve a iniciar
```

### No puedo cargar un archivo
```
→ Verifica que eres admin (usuario: admin)
→ Verifica que el archivo es Excel (.xlsx o .xls)
→ Verifica el tamaño (máximo 50MB)
```

### La búsqueda no devuelve resultados
```
→ Primero, carga un archivo Excel (Como admin)
→ Revisa que el archivo tenga los datos correctos
→ Intenta buscar por valores que sepas que existen
```

---

## 💡 Tips

- Usa **admin** para cargar datos y crear usuarios
- Crea usuarios con rol `"user"` para búsqueda simple
- Los primeros 50 registros se cargan automáticamente
- Puedes buscar mientras están cargando otros datos

---

## 📞 Credenciales de Prueba

| Rol | Usuario | Contraseña |
|-----|---------|-----------|
| Admin | `admin` | `admin123` |

Para crear más usuarios, usa el endpoint `/register` como admin.

---

**Última actualización**: Noviembre 2024  
**Versión**: 1.0
