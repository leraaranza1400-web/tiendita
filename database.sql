-- ============================================================
--  SISTEMA DE GESTIÓN DE TIENDA — Script SQL para MariaDB
--  Versión: 1.0  |  Motor: InnoDB  |  Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS tienda_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tienda_db;

-- ─────────────────────────────────────────────
--  1. ROLES
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS roles (
  id_rol      TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(30) NOT NULL UNIQUE,
  descripcion VARCHAR(120)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles (nombre, descripcion) VALUES
  ('Administrador', 'Control total del sistema'),
  ('Empleado',      'Operaciones diarias de venta y consulta');

-- ─────────────────────────────────────────────
--  2. USUARIOS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
  id_usuario  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(80)  NOT NULL,
  apellido    VARCHAR(80)  NOT NULL,
  email       VARCHAR(120) NOT NULL UNIQUE,
  username    VARCHAR(40)  NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,          -- bcrypt hash
  id_rol      TINYINT UNSIGNED NOT NULL DEFAULT 2,
  activo      TINYINT(1) NOT NULL DEFAULT 1,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contraseña de prueba para AMBOS usuarios: "Admin123!"
-- Hash bcrypt generado con password_hash('Admin123!', PASSWORD_DEFAULT)
INSERT INTO usuarios (nombre, apellido, email, username, password, id_rol) VALUES
  ('Carlos', 'Ramírez', 'admin@tienda.com',    'admin',     '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', 1),
  ('Laura',  'Mendoza', 'empleado@tienda.com', 'empleado1', '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', 2);
-- NOTA: El hash anterior corresponde a "secret" de example. Usa el script PHP 
--       para generar el hash correcto de "Admin123!" en tu servidor.

-- ─────────────────────────────────────────────
--  3. CATEGORÍAS DE PRODUCTOS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categorias (
  id_categoria  SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre        VARCHAR(60) NOT NULL UNIQUE,
  descripcion   VARCHAR(150)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categorias (nombre, descripcion) VALUES
  ('Electrónica',     'Dispositivos electrónicos y accesorios'),
  ('Ropa',            'Prendas de vestir para todo público'),
  ('Hogar',           'Artículos para el hogar y decoración'),
  ('Alimentos',       'Productos alimenticios y bebidas'),
  ('Papelería',       'Útiles escolares y de oficina');

-- ─────────────────────────────────────────────
--  4. PRODUCTOS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS productos (
  id_producto   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo        VARCHAR(20)    NOT NULL UNIQUE,
  nombre        VARCHAR(120)   NOT NULL,
  descripcion   TEXT,
  precio        DECIMAL(10,2)  NOT NULL CHECK (precio >= 0),
  stock         INT UNSIGNED   NOT NULL DEFAULT 0,
  stock_minimo  INT UNSIGNED   NOT NULL DEFAULT 5,
  id_categoria  SMALLINT UNSIGNED NOT NULL,
  activo        TINYINT(1) NOT NULL DEFAULT 1,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_producto_categoria FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO productos (codigo, nombre, descripcion, precio, stock, stock_minimo, id_categoria) VALUES
  ('ELEC-001', 'Audífonos Bluetooth Pro',    'Audífonos inalámbricos con cancelación de ruido', 899.00, 35, 5, 1),
  ('ELEC-002', 'Cargador USB-C 65W',         'Cargador rápido universal USB-C',                  299.00, 60, 10, 1),
  ('ELEC-003', 'Teclado Mecánico RGB',       'Teclado mecánico con retroiluminación RGB',         1250.00, 18, 5, 1),
  ('ROPA-001', 'Playera Casual Unisex',      'Playera 100% algodón tallas S-XL',                  180.00, 100, 15, 2),
  ('ROPA-002', 'Sudadera con capucha',       'Sudadera de algodón grueso',                         450.00, 45, 10, 2),
  ('HOGA-001', 'Lámpara de escritorio LED',  'Lámpara de escritorio con control táctil',           350.00, 25, 5, 3),
  ('HOGA-002', 'Organizador de cajón',       'Set de 6 organizadores de bambú',                    220.00, 40, 8, 3),
  ('ALIM-001', 'Café molido 500g',           'Café arábica de altura, tostado medio',              185.00, 80, 15, 4),
  ('ALIM-002', 'Agua natural 24 pack',       'Agua purificada 600ml c/u',                           98.00, 120, 20, 4),
  ('PAPE-001', 'Cuaderno profesional A5',    'Cuaderno de 200 hojas, pasta dura',                   75.00, 90, 15, 5);

-- ─────────────────────────────────────────────
--  5. CLIENTES
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS clientes (
  id_cliente  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(80)  NOT NULL,
  apellido    VARCHAR(80)  NOT NULL,
  email       VARCHAR(120) UNIQUE,
  telefono    VARCHAR(20),
  direccion   VARCHAR(200),
  rfc         VARCHAR(15),
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO clientes (nombre, apellido, email, telefono, direccion) VALUES
  ('Ana',      'García',    'ana.garcia@mail.com',   '7221234567', 'Av. Lerdo 12, Toluca'),
  ('Roberto',  'López',     'rlopez@mail.com',       '7229876543', 'Calle Juárez 55, Metepec'),
  ('Patricia', 'Herrera',   'p.herrera@mail.com',    '7225551234', 'Blvd. Tollocan 88, Toluca'),
  ('Miguel',   'Torres',    NULL,                    '7224449900', NULL),
  ('Sofía',    'Martínez',  'sofia.m@mail.com',      '7223330011', 'Calle Independencia 3, Zinacantepec');

-- ─────────────────────────────────────────────
--  6. VENTAS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ventas (
  id_venta      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  folio         VARCHAR(20)   NOT NULL UNIQUE,
  id_cliente    INT UNSIGNED  NOT NULL,
  id_usuario    INT UNSIGNED  NOT NULL,           -- empleado que registró
  subtotal      DECIMAL(10,2) NOT NULL DEFAULT 0,
  descuento     DECIMAL(10,2) NOT NULL DEFAULT 0,
  total         DECIMAL(10,2) NOT NULL DEFAULT 0,
  metodo_pago   ENUM('Efectivo','Tarjeta','Transferencia','Otro') NOT NULL DEFAULT 'Efectivo',
  estado        ENUM('Completada','Cancelada','Pendiente') NOT NULL DEFAULT 'Completada',
  notas         TEXT,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_venta_cliente  FOREIGN KEY (id_cliente)  REFERENCES clientes(id_cliente),
  CONSTRAINT fk_venta_usuario  FOREIGN KEY (id_usuario)  REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────
--  7. DETALLES DE VENTA
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS detalles_venta (
  id_detalle    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_venta      INT UNSIGNED  NOT NULL,
  id_producto   INT UNSIGNED  NOT NULL,
  cantidad      INT UNSIGNED  NOT NULL CHECK (cantidad > 0),
  precio_unit   DECIMAL(10,2) NOT NULL,            -- precio al momento de la venta
  subtotal      DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_detalle_venta    FOREIGN KEY (id_venta)    REFERENCES ventas(id_venta) ON DELETE CASCADE,
  CONSTRAINT fk_detalle_producto FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────
--  8. DATOS DE PRUEBA — VENTAS
-- ─────────────────────────────────────────────
INSERT INTO ventas (folio, id_cliente, id_usuario, subtotal, total, metodo_pago) VALUES
  ('VTA-2025-0001', 1, 2, 1079.00, 1079.00, 'Efectivo'),
  ('VTA-2025-0002', 3, 2,  530.00,  530.00, 'Tarjeta');

INSERT INTO detalles_venta (id_venta, id_producto, cantidad, precio_unit, subtotal) VALUES
  (1, 1, 1, 899.00, 899.00),
  (1, 10, 2, 75.00, 150.00),
  (2, 4, 2, 180.00, 360.00),
  (2, 8, 1, 185.00, 185.00);

-- Actualizar stock correspondiente a las ventas de prueba
UPDATE productos SET stock = stock - 1 WHERE id_producto = 1;
UPDATE productos SET stock = stock - 2 WHERE id_producto = 10;
UPDATE productos SET stock = stock - 2 WHERE id_producto = 4;
UPDATE productos SET stock = stock - 1 WHERE id_producto = 8;

-- ─────────────────────────────────────────────
--  9. VISTAS ÚTILES
-- ─────────────────────────────────────────────
CREATE OR REPLACE VIEW v_ventas_detalle AS
SELECT
  v.id_venta, v.folio, v.created_at AS fecha,
  CONCAT(c.nombre,' ',c.apellido) AS cliente,
  CONCAT(u.nombre,' ',u.apellido) AS empleado,
  v.total, v.metodo_pago, v.estado
FROM ventas v
JOIN clientes  c ON c.id_cliente  = v.id_cliente
JOIN usuarios  u ON u.id_usuario  = v.id_usuario
ORDER BY v.created_at DESC;

CREATE OR REPLACE VIEW v_stock_bajo AS
SELECT id_producto, codigo, nombre, stock, stock_minimo, id_categoria
FROM productos
WHERE stock <= stock_minimo AND activo = 1;
