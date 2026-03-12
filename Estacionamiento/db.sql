-- BASE DE DATOS: ESTACIONAMIENTO_CECYT9


DROP DATABASE IF EXISTS estacionamiento_cecyt9;
CREATE DATABASE estacionamiento_cecyt9 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE estacionamiento_cecyt9;


USE estacionamiento_cecyt9;

-- TABLA ROLES
CREATE TABLE roles(
    id_rol INT PRIMARY KEY AUTO_INCREMENT,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles(nombre_rol) VALUES 
('usuario'), 
('administrador'), 
('guardia');

-- TABLA USUARIOS
CREATE TABLE usuarios(
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre_completo VARCHAR(100) NOT NULL,
    correo_institucional VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL,
    matricula VARCHAR(20),
    telefono VARCHAR(15),
    estado ENUM('activo','inactivo') DEFAULT 'activo',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(id_rol) REFERENCES roles(id_rol)
);

-- Usuarios de prueba (contraseñas: admin1, guardia1, usuario1)
INSERT INTO usuarios(nombre_completo, correo_institucional, contrasena, id_rol, matricula, telefono, estado) VALUES 
('Admin Principal','admin@ipn.mx', '$2y$10$XqF7u3JZx6Kp9YrNQvVbOeCzW1sT4R5U6I7O8P9A0B1C2D3E4F5G6H7', 2,'ADM001','5512345678','activo'), 
('Guardia de Seguridad','guardia@ipn.mx', '$2y$10$MnOpQrStUvWxYzAbCdEfGhIjKlMnOpQrStUvWxYzAbCdEfGhIjKl', 3, 'GUA001','5587654321','activo'), 
('Juan Pérez','juan.perez@ipn.mx', '$2y$10$LmNoPqRsTuVwXyZaBcDeFgHiJkLmNoPqRsTuVwXyZaBcDeFgHiJk', 1, 'DOC001','5511223344','activo');

-- TABLA VEHICULOS
CREATE TABLE vehiculos(
    id_vehiculo INT PRIMARY KEY AUTO_INCREMENT,
    placa VARCHAR(10) NOT NULL UNIQUE,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    anio YEAR NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    color VARCHAR(30),
    id_usuario INT NOT NULL,
    estado_registro ENUM('activo','inactivo') DEFAULT 'activo',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
);

-- TABLA ESPACIOS (72 ESPACIOS TOTALES)
CREATE TABLE espacios(
    id_espacio INT PRIMARY KEY AUTO_INCREMENT,
    codigo_espacio VARCHAR(10) NOT NULL UNIQUE,
    tipo VARCHAR(50) NOT NULL,
    ubicacion VARCHAR(100),
    disponibilidad ENUM('disponible','ocupado') DEFAULT 'disponible',
    estado ENUM('activo','inactivo') DEFAULT 'activo'
);

-- Eliminar espacios existentes
DELETE FROM espacios;

-- Reiniciar el auto_increment
ALTER TABLE espacios AUTO_INCREMENT = 1;

-- Insertar Zona A: 24 espacios (A1-A24)
INSERT INTO espacios(codigo_espacio, tipo, ubicacion, disponibilidad, estado) VALUES 
('A1','estandar','Zona A - Fila 1','disponible','activo'),
('A2','estandar','Zona A - Fila 1','disponible','activo'),
('A3','discapacitado','Zona A - Fila 1 - Cerca de entrada','disponible','activo'),
('A4','estandar','Zona A - Fila 1','disponible','activo'),
('A5','estandar','Zona A - Fila 1','disponible','activo'),
('A6','estandar','Zona A - Fila 1','disponible','activo'),
('A7','estandar','Zona A - Fila 2','disponible','activo'),
('A8','estandar','Zona A - Fila 2','disponible','activo'),
('A9','estandar','Zona A - Fila 2','disponible','activo'),
('A10','estandar','Zona A - Fila 2','disponible','activo'),
('A11','estandar','Zona A - Fila 2','disponible','activo'),
('A12','estandar','Zona A - Fila 2','disponible','activo'),
('A13','estandar','Zona A - Fila 3','disponible','activo'),
('A14','estandar','Zona A - Fila 3','disponible','activo'),
('A15','estandar','Zona A - Fila 3','disponible','activo'),
('A16','estandar','Zona A - Fila 3','disponible','activo'),
('A17','estandar','Zona A - Fila 3','disponible','activo'),
('A18','estandar','Zona A - Fila 3','disponible','activo'),
('A19','estandar','Zona A - Fila 4','disponible','activo'),
('A20','estandar','Zona A - Fila 4','disponible','activo'),
('A21','estandar','Zona A - Fila 4','disponible','activo'),
('A22','estandar','Zona A - Fila 4','disponible','activo'),
('A23','estandar','Zona A - Fila 4','disponible','activo'),
('A24','estandar','Zona A - Fila 4','disponible','activo');

-- Insertar Zona B: 24 espacios (B1-B24)
INSERT INTO espacios(codigo_espacio, tipo, ubicacion, disponibilidad, estado) VALUES 
('B1','estandar','Zona B - Fila 1','disponible','activo'),
('B2','estandar','Zona B - Fila 1','disponible','activo'),
('B3','discapacitado','Zona B - Fila 1 - Cerca de entrada','disponible','activo'),
('B4','estandar','Zona B - Fila 1','disponible','activo'),
('B5','estandar','Zona B - Fila 1','disponible','activo'),
('B6','estandar','Zona B - Fila 1','disponible','activo'),
('B7','estandar','Zona B - Fila 2','disponible','activo'),
('B8','estandar','Zona B - Fila 2','disponible','activo'),
('B9','estandar','Zona B - Fila 2','disponible','activo'),
('B10','estandar','Zona B - Fila 2','disponible','activo'),
('B11','estandar','Zona B - Fila 2','disponible','activo'),
('B12','estandar','Zona B - Fila 2','disponible','activo'),
('B13','estandar','Zona B - Fila 3','disponible','activo'),
('B14','estandar','Zona B - Fila 3','disponible','activo'),
('B15','estandar','Zona B - Fila 3','disponible','activo'),
('B16','estandar','Zona B - Fila 3','disponible','activo'),
('B17','estandar','Zona B - Fila 3','disponible','activo'),
('B18','estandar','Zona B - Fila 3','disponible','activo'),
('B19','estandar','Zona B - Fila 4','disponible','activo'),
('B20','estandar','Zona B - Fila 4','disponible','activo'),
('B21','estandar','Zona B - Fila 4','disponible','activo'),
('B22','estandar','Zona B - Fila 4','disponible','activo'),
('B23','estandar','Zona B - Fila 4','disponible','activo'),
('B24','estandar','Zona B - Fila 4','disponible','activo');

-- Insertar Zona C: 24 espacios (C1-C24)
INSERT INTO espacios(codigo_espacio, tipo, ubicacion, disponibilidad, estado) VALUES 
('C1','estandar','Zona C - Fila 1','disponible','activo'),
('C2','estandar','Zona C - Fila 1','disponible','activo'),
('C3','discapacitado','Zona C - Fila 1 - Cerca de entrada','disponible','activo'),
('C4','estandar','Zona C - Fila 1','disponible','activo'),
('C5','estandar','Zona C - Fila 1','disponible','activo'),
('C6','estandar','Zona C - Fila 1','disponible','activo'),
('C7','estandar','Zona C - Fila 2','disponible','activo'),
('C8','estandar','Zona C - Fila 2','disponible','activo'),
('C9','estandar','Zona C - Fila 2','disponible','activo'),
('C10','estandar','Zona C - Fila 2','disponible','activo'),
('C11','estandar','Zona C - Fila 2','disponible','activo'),
('C12','estandar','Zona C - Fila 2','disponible','activo'),
('C13','estandar','Zona C - Fila 3','disponible','activo'),
('C14','estandar','Zona C - Fila 3','disponible','activo'),
('C15','estandar','Zona C - Fila 3','disponible','activo'),
('C16','estandar','Zona C - Fila 3','disponible','activo'),
('C17','estandar','Zona C - Fila 3','disponible','activo'),
('C18','estandar','Zona C - Fila 3','disponible','activo'),
('C19','estandar','Zona C - Fila 4','disponible','activo'),
('C20','estandar','Zona C - Fila 4','disponible','activo'),
('C21','estandar','Zona C - Fila 4','disponible','activo'),
('C22','estandar','Zona C - Fila 4','disponible','activo'),
('C23','estandar','Zona C - Fila 4','disponible','activo'),
('C24','estandar','Zona C - Fila 4','disponible','activo');

-- TABLA RESERVAS
-- Tabla reservas
CREATE TABLE reservas(
    id_reserva INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_vehiculo INT NOT NULL,
    id_espacio INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    tipo_persona ENUM('directivo','administrador','maestro','invitado') NOT NULL,
    discapacidad ENUM('si','no') NOT NULL DEFAULT 'no',
    estado ENUM('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY(id_vehiculo) REFERENCES vehiculos(id_vehiculo),
    FOREIGN KEY(id_espacio) REFERENCES espacios(id_espacio)
);

ALTER TABLE reservas 
ADD COLUMN tipo_persona ENUM('directivo','administrador','maestro','invitado') NOT NULL AFTER hora_fin,
ADD COLUMN discapacidad ENUM('si','no') NOT NULL DEFAULT 'no' AFTER tipo_persona;

-- TABLA HISTORIAL_ACCESOS
CREATE TABLE historial_accesos(
    id_acceso INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_vehiculo INT NOT NULL,
    id_espacio INT,
    tipo_movimiento ENUM('entrada','salida') NOT NULL,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    resultado ENUM('exitoso','fallido') NOT NULL,
    motivo_fallo VARCHAR(255),
    FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY(id_vehiculo) REFERENCES vehiculos(id_vehiculo),
    FOREIGN KEY(id_espacio) REFERENCES espacios(id_espacio)
);

-- TABLA QR_ACCESO
CREATE TABLE qr_acceso(
    id_qr INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    codigo VARCHAR(255) NOT NULL UNIQUE,
    estado ENUM('activo','inactivo') DEFAULT 'activo',
    fecha_generacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
);

-- TABLA INFRACCIONES
CREATE TABLE infracciones(
    id_infraccion INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_vehiculo INT NOT NULL,
    tipo_infraccion VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_infraccion DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('reportada','resuelta') DEFAULT 'reportada',
    FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY(id_vehiculo) REFERENCES vehiculos(id_vehiculo)
);

-- ÍNDICES PARA RENDIMIENTO
CREATE INDEX idx_usuarios_correo ON usuarios(correo_institucional);
CREATE INDEX idx_historial_fecha ON historial_accesos(fecha_hora);
CREATE INDEX idx_reservas_fecha ON reservas(fecha);
CREATE INDEX idx_espacios_disponibilidad ON espacios(disponibilidad, estado);

-- VERIFICACIÓN FINAL
SELECT 'Total de espacios:' as Concepto, COUNT(*) as Cantidad FROM espacios WHERE estado='activo'
UNION ALL
SELECT 'Espacios disponibles:', COUNT(*) FROM espacios WHERE disponibilidad='disponible' AND estado='activo';