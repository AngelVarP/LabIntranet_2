-- Limpia la tabla de usuarios antes de insertar nuevos datos
DROP DATABASE IF EXISTS labintranet;
-- Script para crear la base de datos y la tabla de usuarios
CREATE DATABASE IF NOT EXISTS labintranet;
USE labintranet;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','profesor','tecnico','estudiante') NOT NULL
);

-- Usuario de ejemplo (contraseña: admin123)
-- Contraseña: 123456 (hash generado con password_hash('123456', PASSWORD_DEFAULT))
INSERT INTO usuarios (code, nombre, correo, password, rol) VALUES
('A001', 'Ana Torres', 'ana.torres@lab.com', '123456', 'admin'), -- contraseña: 123456
('P001', 'Juan Pérez', 'juan.perez@lab.com', '123456', 'profesor'); -- contraseña: 123456




-- Puedes generar contraseñas con password_hash('tu_contraseña', PASSWORD_DEFAULT)
