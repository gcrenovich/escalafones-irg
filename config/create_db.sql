CREATE DATABASE IF NOT EXISTS escalafones_irg
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE escalafones_irg;

-- Tabla empleados
CREATE TABLE empleados (
    legajo INT PRIMARY KEY,                  -- Identificador único (ej: 5395)
    nombre VARCHAR(100) NOT NULL,
    categoria ENUM('planta','eventual') NOT NULL,
    fecha_ingreso DATE NOT NULL,
    dias_actuales INT DEFAULT 0,             -- días acumulados en el escalafón actual
    dias_totales INT DEFAULT 0,              -- suma histórica de todos los días
    escalafon INT DEFAULT 0,                 -- nivel de escalafón (sube cada 270 días)
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Tabla registros
CREATE TABLE registros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    legajo INT NOT NULL,
    fecha DATE NOT NULL,                     -- fecha del registro (día trabajado)
    horas DECIMAL(5,2) DEFAULT 0,            -- horas cargadas (8h = 1 día)
    dias_calculados DECIMAL(5,2) DEFAULT 0,  -- días derivados (o cargados directamente)
    concepto VARCHAR(100) DEFAULT NULL,      -- opcional: "Normal", "Día completo", etc.
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT registros_ibfk_1 FOREIGN KEY (legajo) 
        REFERENCES empleados (legajo) ON DELETE CASCADE
);


-- Tabla usuarios (para login)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','user') DEFAULT 'user',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
