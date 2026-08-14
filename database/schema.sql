-- ============================================================
-- SISTEMA DE GESTIÓN DE POLLADA PERUANA (SG_POLLADA)
-- Base de datos: sg_pollada
-- IMPORTANTE: Todo en minúsculas para compatibilidad Linux/Railway
-- ============================================================

-- IMPORTANTE: En InfinityFree (o hosting compartido), la BD se crea desde el panel de control.
-- Por lo tanto, comentamos estas líneas para que no de error en phpMyAdmin.
-- CREATE DATABASE IF NOT EXISTS sg_pollada CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE sg_pollada;

-- ============================================================
-- TABLA: configuracion
-- Almacena configuraciones del sistema (PIN, código inicio, etc.)
-- ============================================================
CREATE TABLE configuracion (
    clave VARCHAR(50) PRIMARY KEY,
    valor VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: cuadre_caja
-- Control de apertura/cierre de jornada con estadísticas
-- ============================================================
CREATE TABLE cuadre_caja (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hora_apertura DATETIME NOT NULL,
    hora_cierre DATETIME DEFAULT NULL,
    monto_total_ventas DECIMAL(10,2) DEFAULT 0.00,
    gasto_total DECIMAL(10,2) DEFAULT 0.00,
    ganancia_neta DECIMAL(10,2) DEFAULT 0.00,
    monto_efectivo DECIMAL(10,2) DEFAULT 0.00,
    monto_yape DECIMAL(10,2) DEFAULT 0.00,
    efectivo_contado DECIMAL(10,2) DEFAULT NULL,
    descuadre DECIMAL(10,2) DEFAULT NULL,
    estado ENUM('abierto', 'cerrado') DEFAULT 'abierto',
    total_entregas INT DEFAULT 0,
    total_con_arroz INT DEFAULT 0,
    total_pagados INT DEFAULT 0,
    total_pendientes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: categoria
-- Categorías de gastos: Abarrotes, Ave Entera, Pasajes, etc.
-- ============================================================
CREATE TABLE categoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: producto
-- Insumos: pollo, papa, lechuga, etc.
-- ============================================================
CREATE TABLE producto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    cantidad DECIMAL(10,2) DEFAULT NULL,
    kilo DECIMAL(10,3) DEFAULT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    comprado TINYINT(1) DEFAULT 0,
    fecha_compra DATE DEFAULT NULL,
    cuadre_caja_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categoria(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (cuadre_caja_id) REFERENCES cuadre_caja(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: egreso_imprevisto
-- Gastos no relacionados a productos: pasajes, imprevistos, etc.
-- ============================================================
CREATE TABLE egreso_imprevisto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(255) NOT NULL,
    categoria_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    salio_de_caja TINYINT(1) DEFAULT 1,
    cuadre_caja_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categoria(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (cuadre_caja_id) REFERENCES cuadre_caja(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: personal
-- Equipo: inversionistas y ayudantes
-- ============================================================
CREATE TABLE personal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    participacion ENUM('inversionista', 'ayudante') NOT NULL,
    reconocimiento_monetario DECIMAL(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: zona_entrega
-- Zonas de reparto: Tumbes, Puyango, Frontera
-- ============================================================
CREATE TABLE zona_entrega (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: platillo
-- Producto que se vende al cliente
-- ============================================================
CREATE TABLE platillo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 15.00,
    descripcion VARCHAR(255) DEFAULT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: cliente
-- Clientes con código de tarjeta de 4 dígitos (desde 0347+)
-- ============================================================
CREATE TABLE cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    codigo_4digitos CHAR(4) NOT NULL UNIQUE,
    zona_entrega_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (zona_entrega_id) REFERENCES zona_entrega(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: entrega_cliente
-- ============================================================
CREATE TABLE entrega_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    platillo_id INT NOT NULL,
    zona_entrega_id INT DEFAULT NULL,
    con_arroz TINYINT(1) DEFAULT 0,
    entregado TINYINT(1) DEFAULT 0,
    hora_entrega DATETIME DEFAULT NULL,
    observaciones VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (platillo_id) REFERENCES platillo(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (zona_entrega_id) REFERENCES zona_entrega(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: venta
-- Registro de venta generado al marcar entrega como completada
-- ============================================================
CREATE TABLE venta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entrega_cliente_id INT NOT NULL,
    cliente_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    estado_pago ENUM('efectivo', 'yape', 'pendiente') DEFAULT 'pendiente',
    fecha_venta DATETIME DEFAULT CURRENT_TIMESTAMP,
    cuadre_caja_id INT DEFAULT NULL,
    FOREIGN KEY (entrega_cliente_id) REFERENCES entrega_cliente(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (cuadre_caja_id) REFERENCES cuadre_caja(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATOS INICIALES (SEED)
-- ============================================================

INSERT INTO configuracion (clave, valor) VALUES 
('pin_cierre', '1013'),
('codigo_cliente_inicio', '0347'),
('nombre_evento', 'Pollada Familiar');

INSERT INTO categoria (nombre) VALUES 
('Abarrotes'), ('Ave Entera'), ('Pasajes'), ('Reconocimiento a Personal'), ('Otros');

INSERT INTO zona_entrega (nombre) VALUES 
('Tumbes'), ('Puyango'), ('Frontera');

INSERT INTO personal (nombres, participacion) VALUES 
('Sara', 'inversionista'), ('Andre', 'inversionista'), ('Sra. Charito', 'ayudante');

INSERT INTO platillo (nombre, precio, descripcion, estado) VALUES 
('Pollada', 15.00, 'Pollo a la brasa con guarniciones', 'activo');
