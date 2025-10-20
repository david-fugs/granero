-- Base de datos Granero - Sistema de Control de Inventario de Frutas
-- Fecha: 2025-10-19

DROP DATABASE IF EXISTS granero;
CREATE DATABASE granero CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE granero;

-- Tabla de Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('admin', 'vendedor', 'almacen', 'visualizador') DEFAULT 'visualizador',
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_tipo (tipo_usuario)
) ENGINE=InnoDB;

-- Tabla de Clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_cliente VARCHAR(50) UNIQUE NOT NULL,
    nombre_cliente VARCHAR(200) NOT NULL,
    nif VARCHAR(20) UNIQUE,
    paga_transporte ENUM('si', 'no') DEFAULT 'no',
    importe_riesgo DECIMAL(10,2) DEFAULT 0.00,
    deuda DECIMAL(10,2) DEFAULT 0.00,
    plat VARCHAR(100),
    carga VARCHAR(100),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo_cliente),
    INDEX idx_nombre (nombre_cliente)
) ENGINE=InnoDB;

-- Tabla de Artículos (Productos/Frutas)
CREATE TABLE articulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_articulo VARCHAR(200) NOT NULL,
    stock_disponible DECIMAL(10,2) DEFAULT 0.00,
    cantidad_albaranes DECIMAL(10,2) DEFAULT 0.00,
    reservado DECIMAL(10,2) DEFAULT 0.00,
    nombre_comercial VARCHAR(200),
    stock_sage DECIMAL(10,2) DEFAULT 0.00,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre_articulo),
    INDEX idx_comercial (nombre_comercial)
) ENGINE=InnoDB;

-- Tabla de Stock por Artículo/Partida
CREATE TABLE stock_partidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    articulo_id INT NOT NULL,
    partida VARCHAR(100) NOT NULL,
    fecha_partida DATE NOT NULL,
    stock_disponible DECIMAL(10,2) DEFAULT 0.00,
    cantidad_albaranes DECIMAL(10,2) DEFAULT 0.00,
    reservado DECIMAL(10,2) DEFAULT 0.00,
    nombre_comercial VARCHAR(200),
    stock_sage DECIMAL(10,2) DEFAULT 0.00,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (articulo_id) REFERENCES articulos(id) ON DELETE CASCADE,
    INDEX idx_articulo (articulo_id),
    INDEX idx_partida (partida),
    INDEX idx_fecha (fecha_partida)
) ENGINE=InnoDB;

-- Tabla de Movimientos de Stock
CREATE TABLE movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    articulo_id INT NOT NULL,
    stock_disponible DECIMAL(10,2) DEFAULT 0.00,
    cantidad_albaranes DECIMAL(10,2) DEFAULT 0.00,
    reservado DECIMAL(10,2) DEFAULT 0.00,
    nombre_comercial VARCHAR(200),
    stock_sage DECIMAL(10,2) DEFAULT 0.00,
    tipo_movimiento ENUM('entrada', 'salida', 'ajuste') NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    observaciones TEXT,
    usuario_id INT,
    fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (articulo_id) REFERENCES articulos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_articulo (articulo_id),
    INDEX idx_fecha (fecha_movimiento)
) ENGINE=InnoDB;

-- Tabla de Comerciales
CREATE TABLE comerciales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_identificacion VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_numero (numero_identificacion)
) ENGINE=InnoDB;

-- Tabla de Mozos
CREATE TABLE mozos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_identificacion VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_numero (numero_identificacion)
) ENGINE=InnoDB;

-- Tabla de Reservas
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_reserva VARCHAR(50) UNIQUE NOT NULL,
    numero_lineas INT DEFAULT 0,
    fecha DATE NOT NULL,
    cliente_id INT NOT NULL,
    estado ENUM('reservado', 'enviado') DEFAULT 'reservado',
    comercial_id INT,
    transportista VARCHAR(200),
    plataforma_carga VARCHAR(200),
    faltan_precios ENUM('si', 'no') DEFAULT 'no',
    prepago ENUM('si', 'no') DEFAULT 'no',
    numero_albaran VARCHAR(50),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (comercial_id) REFERENCES comerciales(id) ON DELETE SET NULL,
    INDEX idx_numero (numero_reserva),
    INDEX idx_cliente (cliente_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB;

-- Tabla de Albaranes
CREATE TABLE albaranes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_albaran VARCHAR(50) UNIQUE NOT NULL,
    fecha DATE NOT NULL,
    multipedido VARCHAR(50),
    comercial_id INT,
    mozo_id INT,
    cliente_id INT NOT NULL,
    faltan_precios TINYINT(1) DEFAULT 0,
    preparado TINYINT(1) DEFAULT 0,
    unificado TINYINT(1) DEFAULT 0,
    prepago TINYINT(1) DEFAULT 0,
    estado ENUM('pendiente', 'en_preparacion', 'pendiente_facturar', 'facturado', 'pendiente_precio') DEFAULT 'pendiente',
    total_general DECIMAL(10,2) DEFAULT 0.00,
    total_peso DECIMAL(10,2) DEFAULT 0.00,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (comercial_id) REFERENCES comerciales(id) ON DELETE SET NULL,
    FOREIGN KEY (mozo_id) REFERENCES mozos(id) ON DELETE SET NULL,
    INDEX idx_numero (numero_albaran),
    INDEX idx_estado (estado),
    INDEX idx_cliente (cliente_id),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB;

-- Tabla de Artículos de Albarán (Líneas de Albarán)
CREATE TABLE albaran_articulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    albaran_id INT NOT NULL,
    articulo_id INT NOT NULL,
    partida VARCHAR(100),
    unidades DECIMAL(10,2) DEFAULT 0.00,
    peso DECIMAL(10,2) DEFAULT 0.00,
    precio DECIMAL(10,2) DEFAULT 0.00,
    importe_transporte DECIMAL(10,2) DEFAULT 0.00,
    importe DECIMAL(10,2) DEFAULT 0.00,
    estado TINYINT(1) DEFAULT 0,
    peso_unificado TINYINT(1) DEFAULT 0,
    pesos_correctos TINYINT(1) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (albaran_id) REFERENCES albaranes(id) ON DELETE CASCADE,
    FOREIGN KEY (articulo_id) REFERENCES articulos(id) ON DELETE CASCADE,
    INDEX idx_albaran (albaran_id),
    INDEX idx_articulo (articulo_id)
) ENGINE=InnoDB;

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, email, password, tipo_usuario) VALUES 
('Administrador', 'admin@granero.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password: password

-- Insertar datos de ejemplo para comerciales
INSERT INTO comerciales (numero_identificacion, nombre) VALUES 
('COM001', 'Juan Pérez'),
('COM002', 'María García'),
('COM003', 'Carlos Rodríguez');

-- Insertar datos de ejemplo para mozos
INSERT INTO mozos (numero_identificacion, nombre) VALUES 
('MOZ001', 'Pedro Martínez'),
('MOZ002', 'Ana López'),
('MOZ003', 'Luis Fernández');

-- Insertar datos de ejemplo para clientes
INSERT INTO clientes (codigo_cliente, nombre_cliente, nif, paga_transporte, importe_riesgo, deuda, plat, carga) VALUES 
('CLI001', 'Supermercado El Frutal', 'B12345678', 'si', 5000.00, 1200.00, 'Plataforma A', 'Camión 1'),
('CLI002', 'Frutas y Verduras Norte', 'B87654321', 'no', 3000.00, 0.00, 'Plataforma B', 'Camión 2'),
('CLI003', 'Mercado Central', 'B11223344', 'si', 8000.00, 2500.00, 'Plataforma C', 'Camión 3');

-- Insertar datos de ejemplo para artículos
INSERT INTO articulos (nombre_articulo, stock_disponible, cantidad_albaranes, reservado, nombre_comercial, stock_sage) VALUES 
('Manzana Golden', 500.00, 50.00, 100.00, 'Manzana Dorada Premium', 450.00),
('Naranja Valencia', 800.00, 100.00, 150.00, 'Naranja Natural', 750.00),
('Plátano Canarias', 600.00, 75.00, 80.00, 'Plátano Fresco', 545.00),
('Pera Conference', 400.00, 40.00, 60.00, 'Pera Dulce', 340.00),
('Fresa', 200.00, 20.00, 30.00, 'Fresa Premium', 170.00),
('Sandía', 300.00, 30.00, 50.00, 'Sandía Fresca', 270.00),
('Melón', 350.00, 35.00, 45.00, 'Melón Dulce', 315.00),
('Kiwi', 250.00, 25.00, 35.00, 'Kiwi Verde', 225.00);
