-- Eliminar y crear la base de datos
DROP DATABASE IF EXISTS cine_online;
CREATE DATABASE cine_online;
USE cine_online;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    es_admin BOOLEAN DEFAULT FALSE
);

-- Tabla de películas
CREATE TABLE IF NOT EXISTS peliculas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    genero VARCHAR(100),
    duracion INT,
    clasificacion VARCHAR(10),
    imagen VARCHAR(255),
    estado ENUM('activa', 'proximamente', 'archivada') DEFAULT 'activa'
);

-- Tabla de salas
CREATE TABLE IF NOT EXISTS salas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    capacidad INT NOT NULL
);

-- Layout de salas
CREATE TABLE IF NOT EXISTS sala_layout (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sala_id INT NOT NULL,
    filas INT NOT NULL,
    columnas INT NOT NULL,
    FOREIGN KEY (sala_id) REFERENCES salas(id) ON DELETE CASCADE
);

-- Tabla de funciones
CREATE TABLE IF NOT EXISTS funciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelicula_id INT NOT NULL,
    sala_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pelicula_id) REFERENCES peliculas(id) ON DELETE CASCADE,
    FOREIGN KEY (sala_id) REFERENCES salas(id) ON DELETE CASCADE
);

-- Tabla de asientos
CREATE TABLE IF NOT EXISTS asientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sala_id INT NOT NULL,
    fila VARCHAR(5) NOT NULL,
    numero INT NOT NULL,
    estado ENUM('disponible', 'ocupado', 'reservado', 'deshabilitado') DEFAULT 'disponible',
    FOREIGN KEY (sala_id) REFERENCES salas(id) ON DELETE CASCADE,
    UNIQUE KEY (sala_id, fila, numero)
);

-- Tabla de reservas
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    funcion_id INT NOT NULL,
    fecha_reserva DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'confirmada', 'cancelada') DEFAULT 'pendiente',
    codigo_reserva VARCHAR(20) NOT NULL UNIQUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (funcion_id) REFERENCES funciones(id) ON DELETE CASCADE
);

-- Detalle de reservas (asientos reservados)
CREATE TABLE IF NOT EXISTS detalle_reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    asiento_id INT NOT NULL,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (asiento_id) REFERENCES asientos(id) ON DELETE CASCADE,
    UNIQUE KEY (reserva_id, asiento_id)
);

-- Insertar usuario administrador
INSERT INTO usuarios (nombre, email, password, es_admin) VALUES 
('Administrador', 'admin@cineonline.com', '$2y$10$WzPgKJJMlJ4I1a4Z9bHJ3.J7eblW6MhJwzgDYDQHYeRYgWYZU0Bnq', TRUE); -- Contraseña: admin123

-- Insertar películas
INSERT INTO peliculas (titulo, descripcion, genero, duracion, clasificacion, imagen) VALUES
('Avengers: Endgame', 'Los Vengadores restantes deben encontrar una manera de recuperar a sus aliados para un enfrentamiento épico con Thanos.', 'Acción, Aventura', 181, 'PG-13', 'avengers.jpg'),
('Joker', 'Arthur Fleck, un aspirante a comediante con problemas mentales, vive en una sociedad que lo trata como basura.', 'Drama, Crimen', 122, 'R', 'joker.jpg'),
('Toy Story 4', 'Woody, Buzz Lightyear y el resto de la pandilla se embarcan en un viaje por carretera con nuevos y viejos amigos.', 'Animación, Aventura', 100, 'G', 'toystory.jpg'),
('El Rey León', 'El león Simba debe recuperar su trono después de que su tío Scar lo usurpara.', 'Animación, Aventura', 118, 'PG', 'reyleon.jpg');

-- Insertar salas
INSERT INTO salas (nombre, capacidad) VALUES
('Sala 1', 60),
('Sala 2', 80),
('Sala 3', 120),
('Sala IMAX', 100);

-- Insertar layout de salas
INSERT INTO sala_layout (sala_id, filas, columnas) VALUES
(1, 6, 10),
(2, 8, 10),
(3, 10, 12),
(4, 10, 10);

-- Crear asientos automáticamente según layout
INSERT INTO asientos (sala_id, fila, numero, estado)
SELECT 
    sl.sala_id,
    CHAR(64 + f.n) AS fila,
    c.n AS numero,
    'disponible'
FROM sala_layout sl
JOIN (
    SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 
    UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
) f
JOIN (
    SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 
    UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
) c
WHERE f.n <= sl.filas AND c.n <= sl.columnas;

-- Insertar funciones (hoy)
INSERT INTO funciones (pelicula_id, sala_id, fecha, hora, precio) VALUES
(1, 4, CURDATE(), '14:30:00', 120.00),
(1, 4, CURDATE(), '18:00:00', 150.00),
(1, 4, CURDATE(), '21:30:00', 150.00),
(2, 2, CURDATE(), '15:00:00', 100.00),
(2, 2, CURDATE(), '19:00:00', 120.00),
(3, 1, CURDATE(), '13:00:00', 90.00),
(3, 1, CURDATE(), '16:30:00', 100.00),
(4, 3, CURDATE(), '14:00:00', 100.00),
(4, 3, CURDATE(), '17:30:00', 120.00),
(4, 3, CURDATE(), '20:30:00', 120.00);

-- Insertar funciones (mañana)
INSERT INTO funciones (pelicula_id, sala_id, fecha, hora, precio) VALUES
(1, 4, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:30:00', 120.00),
(1, 4, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00', 150.00),
(2, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', 100.00),
(3, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '13:00:00', 90.00),
(4, 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', 100.00);
