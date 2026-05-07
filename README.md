# 🛍️ Tienda Manager — Sistema de Gestión de Tienda

## Árbol de Archivos

```
tienda/
│
├── index.php                  ← Login / Registro (punto de entrada)
├── panel.php                  ← Panel principal (Admin + Empleado)
├── database.sql               ← Script SQL completo con datos de prueba
│
├── config/
│   ├── database.php           ← Conexión PDO a MariaDB
│   └── session.php            ← Helpers de sesión, CSRF, autenticación
│
├── auth/
│   └── auth.php               ← Login, Registro y Logout (JSON API)
│
├── api/
│   ├── productos.php          ← CRUD completo de productos
│   ├── ventas.php             ← Registro y consulta de ventas
│   ├── clientes.php           ← CRUD de clientes
│   └── usuarios.php          ← CRUD de usuarios
│
└── assets/                    ← (Opcional) CSS/JS/img externos
    ├── css/
    ├── js/
    └── img/
```

---

## ⚙️ Instalación

### 1. Requisitos del servidor
- PHP 7.4+ con extensión PDO y PDO_MySQL
- MariaDB 10.4+ (o MySQL 5.7+)
- Apache/Nginx con mod_rewrite (opcional para rutas limpias)

### 2. Base de datos
```sql
-- En tu cliente MySQL/MariaDB:
SOURCE /ruta/al/proyecto/database.sql;
```

O importa el archivo `database.sql` desde phpMyAdmin.

### 3. Configuración de conexión
Edita `config/database.php` con tus credenciales:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
define('DB_NAME', 'tienda_db');
```

### 4. Generar hashes de contraseñas correctos
El script SQL incluye un hash de ejemplo. Para producción, ejecuta este snippet PHP una sola vez para actualizar las contraseñas:

```php
<?php
require 'config/database.php';
$db = getDB();
// Contraseña: Admin123!
$hash = password_hash('Admin123!', PASSWORD_BCRYPT, ['cost' => 12]);
$db->prepare("UPDATE usuarios SET password = ? WHERE username IN ('admin','empleado1')")->execute([$hash]);
echo "Contraseñas actualizadas.";
```

### 5. Credenciales de prueba
| Usuario    | Contraseña | Rol           |
|------------|------------|---------------|
| `admin`    | Admin123!  | Administrador |
| `empleado1`| Admin123!  | Empleado      |

---

## 🔐 Seguridad implementada

- **PDO con prepared statements** — prevención de SQL Injection
- **password_hash / password_verify** — bcrypt con cost=12
- **session_regenerate_id** al hacer login
- **Cookies httponly + samesite=Lax**
- **Validación de roles** en cada endpoint de API
- **CSRF token** generado por sesión (listo para conectar con formularios)
- **Soft delete** en productos y usuarios (no se borra físicamente)

---

## 📋 Mapa de Casos de Uso

### Administrador
| Módulo       | Crear | Leer | Editar | Eliminar |
|--------------|-------|------|--------|----------|
| Usuarios     | ✅    | ✅   | ✅     | ✅ (soft)|
| Productos    | ✅    | ✅   | ✅     | ✅ (soft)|
| Clientes     | ✅    | ✅   | ✅     | —        |
| Ventas       | ✅    | ✅   | —      | —        |

### Empleado
| Módulo           | Crear | Leer | Editar | Eliminar |
|------------------|-------|------|--------|----------|
| Ventas           | ✅    | ✅*  | —      | —        |
| Clientes         | ✅    | ✅   | —      | —        |
| Productos        | —     | ✅   | —      | —        |
| Mi Perfil        | —     | ✅   | ✅     | —        |

\* Empleados solo ven sus propias ventas

---

## 🛒 Flujo de Nueva Venta
1. Buscar y seleccionar cliente (o registrar uno nuevo)
2. Buscar productos y agregarlos al carrito
3. Ajustar cantidades (JS valida contra stock disponible)
4. El total se calcula en tiempo real
5. Seleccionar método de pago
6. Confirmar → el backend:
   - Usa transacción SQL con `BEGIN/COMMIT`
   - Valida stock con `FOR UPDATE` (bloqueo de fila)
   - Inserta venta y detalles
   - **Descuenta automáticamente el stock** de cada producto

---

## 🔌 Endpoints de la API

Todos los endpoints devuelven JSON.

```
GET  api/productos.php              → Lista productos (query: ?q=, ?categoria=)
GET  api/productos.php?id=N         → Producto individual
POST api/productos.php?action=crear
POST api/productos.php?action=editar
POST api/productos.php?action=eliminar

GET  api/clientes.php               → Lista clientes
POST api/clientes.php?action=crear
POST api/clientes.php?action=editar

GET  api/ventas.php                 → Lista ventas
POST api/ventas.php?action=registrar

GET  api/usuarios.php               → Lista usuarios (admin)
POST api/usuarios.php?action=editar
POST api/usuarios.php?action=eliminar

POST auth/auth.php                  → action: login | register | logout
GET  auth/auth.php?action=logout
```
