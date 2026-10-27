-- ============================================================
-- BASE DE DATOS: finanzas_personales
-- MOTOR: MySQL 8.0+
-- RELACION: RUBROS (1) ---- (N) MOVIMIENTOS
-- ============================================================

DROP DATABASE IF EXISTS finanzas_personales;

CREATE DATABASE finanzas_personales
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE finanzas_personales;

-- ============================================================
-- 1. CREACION DE TABLAS
-- ============================================================

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    presupuesto_mensual DECIMAL(12,2),

    CONSTRAINT chk_usuarios_presupuesto
        CHECK (presupuesto_mensual >= 0)
) ENGINE=InnoDB;


CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255),

    CONSTRAINT fk_categorias_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE rubros (
    id_rubro INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    monto_proyectado DECIMAL(12,2) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,

    CONSTRAINT fk_rubros_categoria
        FOREIGN KEY (id_categoria)
        REFERENCES categorias(id_categoria)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_rubros_monto
        CHECK (monto_proyectado >= 0),

    CONSTRAINT chk_rubros_fechas
        CHECK (fecha_fin >= fecha_inicio)
) ENGINE=InnoDB;


CREATE TABLE movimientos (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_rubro INT NOT NULL,
    tipo ENUM('ingreso','gasto') NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    descripcion VARCHAR(255),

    CONSTRAINT fk_movimientos_rubros
        FOREIGN KEY (id_rubro)
        REFERENCES rubros(id_rubro)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_movimientos_monto
        CHECK (monto > 0),

    INDEX idx_movimientos_fecha (fecha),
    INDEX idx_movimientos_rubro_fecha (id_rubro, fecha)
) ENGINE=InnoDB;


CREATE TABLE historial_presupuesto (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    presupuesto_anterior DECIMAL(12,2) NOT NULL,
    presupuesto_nuevo DECIMAL(12,2) NOT NULL,
    fecha_cambio DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_historial_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabla: solo se guarda el HASH del token, nunca el token.
-- ON DELETE CASCADE: al borrar un usuario se borran sus tokens,
-- asi sp_eliminar_usuario sigue funcionando sin modificarlo.
CREATE TABLE IF NOT EXISTS recuperacion_contrasena (
    id_recuperacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expira_en DATETIME NOT NULL,
    usado_en DATETIME NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_recuperacion_usuario (id_usuario, creado_en),

    CONSTRAINT fk_recuperacion_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB;
-- ============================================================
-- 2. INSERTAR USUARIOS
-- ============================================================

INSERT INTO usuarios
(nombre, correo, contrasena, fecha_registro, presupuesto_mensual)
VALUES
('Julio Casanova', 'julioacr8@gmail.com',        'clave123', '2026-01-05 08:30:00', 1500000),
('Jaime Ladino', 'ladinomontealegre07@gmail.com', 'clave123', '2026-01-06 09:15:00', 1200000),
('Laura Isanoa', 'danielaisanoa255@gmail.com',                'clave123', '2026-01-07 10:00:00', 1800000),
('Juan Barreiro', 'barreiropolojuanmanuel@gmail.com',                  'clave123', '2026-01-08 11:20:00', 1400000),
('Karoll Coronado', 'karollcoronado91@gmail.com',               'clave123', '2026-01-09 14:10:00', 1600000),
('Sofia Ramirez', 'sofia@gmail.com',                'clave123', '2026-01-10 15:45:00', 1300000),
('Pepe Rodríguez', 'pepe@gmail.com',                'clave123', '2026-01-11 08:00:00', 2000000),
('Camila Diaz', 'camila@gmail.com',                 'clave123', '2026-01-12 12:30:00', 1700000),
('Miguel Castro', 'miguel@gmail.com',               'clave123', '2026-01-13 16:00:00', 1100000),
('Valentina Silva', 'valentina@gmail.com',          'clave123', '2026-01-14 17:15:00', 1900000);


-- ============================================================
-- 3. INSERTAR CATEGORIAS
-- ============================================================

INSERT INTO categorias
(id_usuario, nombre, descripcion)
VALUES
(1, 'Alimentacion', 'Compras de comida y mercado'),
(1, 'Transporte', 'Gastos de transporte publico y gasolina'),
(2, 'Vivienda', 'Arriendo y servicios del hogar'),
(2, 'Entretenimiento', 'Cine, juegos y actividades recreativas'),
(3, 'Educacion', 'Cursos, libros y materiales de estudio'),
(3, 'Salud', 'Medicamentos y consultas medicas'),
(4, 'Ahorro', 'Dinero destinado al ahorro'),
(5, 'Tecnologia', 'Compra de dispositivos y accesorios'),
(6, 'Ropa', 'Compra de prendas y calzado'),
(7, 'Servicios', 'Internet, telefono y otros servicios');


-- ============================================================
-- 4. INSERTAR RUBROS
-- Se crean antes de movimientos porque movimientos depende
-- de rubros mediante una clave foranea.
-- ============================================================

INSERT INTO rubros
(id_categoria, nombre, monto_proyectado, fecha_inicio, fecha_fin)
VALUES
(1, 'Mercado mensual', 250000, '2026-02-01', '2026-12-31'),
(2, 'Transporte mensual', 100000, '2026-02-01', '2026-12-31'),
(3, 'Arriendo mensual', 450000, '2026-02-01', '2026-12-31'),
(4, 'Diversion', 100000, '2026-02-01', '2026-12-31'),
(5, 'Material educativo', 150000, '2026-02-01', '2026-12-31'),
(6, 'Atencion medica', 120000, '2026-02-01', '2026-12-31'),
(7, 'Fondo de ahorro', 200000, '2026-02-01', '2026-12-31'),
(8, 'Servicios digitales', 80000, '2026-02-01', '2026-12-31'),
(9, 'Ropa y calzado', 300000, '2026-02-01', '2026-12-31'),
(10, 'Servicios del hogar', 100000, '2026-02-01', '2026-12-31');


-- ============================================================
-- 5. INSERTAR MOVIMIENTOS
-- Un rubro puede tener muchos movimientos.
-- Cada movimiento pertenece a un solo rubro.
-- ============================================================

INSERT INTO movimientos
(id_rubro, tipo, monto, fecha, descripcion)
VALUES
(7, 'ingreso', 2500000, '2026-02-01 08:00:00', 'Salario mensual'),
(1, 'gasto', 180000, '2026-02-02 12:30:00', 'Compra de mercado'),
(2, 'gasto', 50000, '2026-02-03 07:45:00', 'Transporte semanal'),
(7, 'ingreso', 350000, '2026-02-04 15:00:00', 'Trabajo independiente'),
(3, 'gasto', 450000, '2026-02-05 10:00:00', 'Pago de arriendo'),
(4, 'gasto', 80000, '2026-02-06 18:30:00', 'Salida al cine'),
(8, 'ingreso', 200000, '2026-02-07 09:15:00', 'Venta de un articulo'),
(5, 'gasto', 120000, '2026-02-08 14:20:00', 'Compra de libros'),
(6, 'gasto', 95000, '2026-02-09 16:45:00', 'Consulta medica'),
(10, 'gasto', 60000, '2026-02-10 11:30:00', 'Pago de internet');


-- ============================================================
-- 6. CONSULTAS DE EJEMPLO
-- ============================================================

-- 6.1 Categorias de un usuario

SELECT c.id_categoria, c.nombre, c.descripcion
FROM categorias c
WHERE c.id_usuario = 1;


-- 6.2 Rubros con su categoria y usuario

SELECT
    u.nombre AS usuario,
    c.nombre AS categoria,
    r.nombre AS rubro,
    r.monto_proyectado,
    r.fecha_inicio,
    r.fecha_fin
FROM rubros r
INNER JOIN categorias c
    ON c.id_categoria = r.id_categoria
INNER JOIN usuarios u
    ON u.id_usuario = c.id_usuario
ORDER BY u.nombre, c.nombre;


-- 6.3 Movimientos del mes actual

SELECT
    id_movimiento,
    id_rubro,
    tipo,
    monto,
    fecha,
    descripcion
FROM movimientos
WHERE YEAR(fecha) = YEAR(CURDATE())
  AND MONTH(fecha) = MONTH(CURDATE())
ORDER BY fecha DESC;


-- 6.4 Los cinco gastos mas altos

SELECT
    id_movimiento,
    id_rubro,
    monto,
    fecha,
    descripcion
FROM movimientos
WHERE tipo = 'gasto'
ORDER BY monto DESC
LIMIT 5;


-- 6.5 Rubros vigentes hoy

SELECT
    id_rubro,
    nombre,
    monto_proyectado,
    fecha_inicio,
    fecha_fin
FROM rubros
WHERE CURDATE() BETWEEN fecha_inicio AND fecha_fin;


-- 6.6 Rubros vencidos

SELECT id_rubro, nombre, fecha_fin
FROM rubros
WHERE fecha_fin < CURDATE();


-- ============================================================
-- 7. VISTA DEL BALANCE MENSUAL
-- ============================================================

CREATE VIEW v_balance_mensual AS
SELECT
    c.id_usuario,
    DATE_FORMAT(m.fecha, '%Y-%m') AS mes,

    SUM(
        CASE
            WHEN m.tipo = 'ingreso' THEN m.monto
            ELSE 0
        END
    ) AS ingresos,

    SUM(
        CASE
            WHEN m.tipo = 'gasto' THEN m.monto
            ELSE 0
        END
    ) AS gastos,

    SUM(
        CASE
            WHEN m.tipo = 'ingreso' THEN m.monto
            WHEN m.tipo = 'gasto' THEN -m.monto
            ELSE 0
        END
    ) AS balance

FROM movimientos m
INNER JOIN rubros r
    ON r.id_rubro = m.id_rubro
INNER JOIN categorias c
    ON c.id_categoria = r.id_categoria
GROUP BY
    c.id_usuario,
    DATE_FORMAT(m.fecha, '%Y-%m');


-- ============================================================
-- 8. PROCEDIMIENTOS ALMACENADOS
-- ============================================================

DELIMITER $$


-- 8.1 Registrar usuario

CREATE PROCEDURE sp_registrar_usuario(
    IN p_nombre VARCHAR(50),
    IN p_correo VARCHAR(100),
    IN p_contrasena VARCHAR(255)
)
BEGIN
    INSERT INTO usuarios (
        nombre,
        correo,
        contrasena
    )
    VALUES (
        TRIM(p_nombre),
        LOWER(TRIM(p_correo)),
        p_contrasena
    );

    SELECT LAST_INSERT_ID() AS id_usuario;
END$$


-- 8.2 Crear categoria

CREATE PROCEDURE sp_crear_categoria(
    IN p_id_usuario INT,
    IN p_nombre VARCHAR(50),
    IN p_descripcion VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM usuarios
        WHERE id_usuario = p_id_usuario
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El usuario indicado no existe';
    END IF;

    INSERT INTO categorias (
        id_usuario,
        nombre,
        descripcion
    )
    VALUES (
        p_id_usuario,
        TRIM(p_nombre),
        p_descripcion
    );

    SELECT LAST_INSERT_ID() AS id_categoria;
END$$


-- 8.3 Registrar movimiento dentro de un rubro existente
CREATE  PROCEDURE sp_registrar_movimiento(
    IN p_id_rubro INT,
    IN p_tipo VARCHAR(10),
    IN p_monto DECIMAL(12,2),
    IN p_descripcion VARCHAR(255),
    IN p_fecha DATETIME
)
BEGIN
    DECLARE v_id_movimiento INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    IF p_tipo IS NULL OR p_tipo NOT IN ('ingreso', 'gasto') THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El tipo debe ser ingreso o gasto';
    END IF;

    IF p_monto IS NULL OR p_monto <= 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El monto debe ser mayor que cero';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM rubros
        WHERE id_rubro = p_id_rubro
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El rubro indicado no existe';
    END IF;

    START TRANSACTION;

    INSERT INTO movimientos (
        id_rubro,
        tipo,
        monto,
        descripcion,
        fecha
    )
    VALUES (
        p_id_rubro,
        p_tipo,
        p_monto,
        p_descripcion,
        COALESCE(p_fecha, NOW())
    );

    SET v_id_movimiento = LAST_INSERT_ID();

    COMMIT;

    SELECT v_id_movimiento AS id_movimiento;
END$$


-- 8.4 Actualizar presupuesto mensual

CREATE PROCEDURE sp_actualizar_presupuesto(
    IN p_id_usuario INT,
    IN p_presupuesto DECIMAL(12,2)
)
BEGIN
    IF p_presupuesto IS NULL OR p_presupuesto < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
            'El presupuesto no puede ser negativo ni nulo';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM usuarios
        WHERE id_usuario = p_id_usuario
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El usuario indicado no existe';
    END IF;

    UPDATE usuarios
    SET presupuesto_mensual = p_presupuesto
    WHERE id_usuario = p_id_usuario;
END$$


-- 8.5 Balance mensual de un usuario
-- Utiliza la vista v_balance_mensual.

CREATE PROCEDURE sp_balance_usuario(
    IN p_id_usuario INT
)
BEGIN
    SELECT
        mes,
        ingresos,
        gastos,
        balance
    FROM v_balance_mensual
    WHERE id_usuario = p_id_usuario
    ORDER BY mes;
END$$


-- 8.6 Eliminar rubro si no tiene movimientos asociados

CREATE PROCEDURE sp_eliminar_rubro(
    IN p_id_rubro INT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM rubros
        WHERE id_rubro = p_id_rubro
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El rubro indicado no existe';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM movimientos
        WHERE id_rubro = p_id_rubro
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
            'No se puede eliminar un rubro con movimientos asociados';
    END IF;

    DELETE FROM rubros
    WHERE id_rubro = p_id_rubro;
END$$

-- 8.7 Obtener usuario por id

CREATE PROCEDURE sp_obtener_usuario_por_id(
    IN p_id_usuario INT
)
BEGIN
    SELECT id_usuario, nombre, correo, fecha_registro
    FROM usuarios
    WHERE id_usuario = p_id_usuario
    LIMIT 1;
END$$


-- 8.8 Dashboard: resumen de ingresos y gastos (fecha final exclusiva)

CREATE PROCEDURE sp_dashboard_resumen(
    IN p_id_usuario INT,
    IN p_inicio DATE,
    IN p_fin DATE
)
BEGIN
    SELECT
        COALESCE(SUM(CASE WHEN m.tipo = 'ingreso'
            THEN m.monto ELSE 0 END), 0) AS ingresos,
        COALESCE(SUM(CASE WHEN m.tipo = 'gasto'
            THEN m.monto ELSE 0 END), 0) AS gastos
    FROM movimientos m
    INNER JOIN rubros r ON r.id_rubro = m.id_rubro
    INNER JOIN categorias c ON c.id_categoria = r.id_categoria
    WHERE c.id_usuario = p_id_usuario
      AND m.fecha >= p_inicio
      AND m.fecha < p_fin;
END$$


-- 8.9 Dashboard: ultimos movimientos del usuario

CREATE PROCEDURE sp_dashboard_movimientos_recientes(
    IN p_id_usuario INT,
    IN p_limite INT
)
BEGIN
    SELECT
        DATE_FORMAT(m.fecha, '%d/%m/%Y') AS fecha,
        COALESCE(m.descripcion, r.nombre) AS descripcion,
        c.nombre AS categoria,
        m.tipo,
        m.monto
    FROM movimientos m
    INNER JOIN rubros r ON r.id_rubro = m.id_rubro
    INNER JOIN categorias c ON c.id_categoria = r.id_categoria
    WHERE c.id_usuario = p_id_usuario
    ORDER BY m.fecha DESC, m.id_movimiento DESC
    LIMIT p_limite;
END$$


-- 8.10 Dashboard: ingresos y gastos agrupados por mes

CREATE PROCEDURE sp_dashboard_balance_mensual(
    IN p_id_usuario INT,
    IN p_inicio DATE,
    IN p_fin DATE
)
BEGIN
    SELECT
        YEAR(m.fecha) AS anio,
        MONTH(m.fecha) AS mes,
        SUM(CASE WHEN m.tipo = 'ingreso'
            THEN m.monto ELSE 0 END) AS ingresos,
        SUM(CASE WHEN m.tipo = 'gasto'
            THEN m.monto ELSE 0 END) AS gastos
    FROM movimientos m
    INNER JOIN rubros r ON r.id_rubro = m.id_rubro
    INNER JOIN categorias c ON c.id_categoria = r.id_categoria
    WHERE c.id_usuario = p_id_usuario
      AND m.fecha >= p_inicio
      AND m.fecha < p_fin
    GROUP BY YEAR(m.fecha), MONTH(m.fecha)
    ORDER BY anio, mes;
END$$


-- 8.11 Dashboard: gastos del periodo por categoria

CREATE PROCEDURE sp_dashboard_gastos_categoria(
    IN p_id_usuario INT,
    IN p_inicio DATE,
    IN p_fin DATE
)
BEGIN
    SELECT
        c.nombre AS categoria,
        SUM(m.monto) AS total
    FROM movimientos m
    INNER JOIN rubros r ON r.id_rubro = m.id_rubro
    INNER JOIN categorias c ON c.id_categoria = r.id_categoria
    WHERE c.id_usuario = p_id_usuario
      AND m.tipo = 'gasto'
      AND m.fecha >= p_inicio
      AND m.fecha < p_fin
    GROUP BY c.id_categoria, c.nombre
    ORDER BY total DESC;
END$$


-- 8.12 Dashboard: presupuesto proyectado vs gasto real por categoria

CREATE PROCEDURE sp_dashboard_presupuesto_categoria(
    IN p_id_usuario INT,
    IN p_inicio DATE,
    IN p_fin DATE
)
BEGIN
    SELECT
        c.nombre AS categoria,
        COALESCE(SUM(r.monto_proyectado), 0) AS presupuestado,
        COALESCE(SUM(g.gastado), 0) AS gastado
    FROM categorias c
    INNER JOIN rubros r ON r.id_categoria = c.id_categoria
    LEFT JOIN (
        SELECT m.id_rubro, SUM(m.monto) AS gastado
        FROM movimientos m
        WHERE m.tipo = 'gasto'
          AND m.fecha >= p_inicio
          AND m.fecha < p_fin
        GROUP BY m.id_rubro
    ) g ON g.id_rubro = r.id_rubro
    WHERE c.id_usuario = p_id_usuario
      AND r.fecha_inicio < p_fin
      AND r.fecha_fin >= p_inicio
    GROUP BY c.id_categoria, c.nombre
    ORDER BY c.nombre;
END$$


-- 8.13 Obtener usuario por correo (inicio de sesion y registro)

CREATE PROCEDURE sp_obtener_usuario_por_email(
    IN p_correo VARCHAR(100)
)
BEGIN
    SELECT id_usuario, nombre, correo, contrasena
    FROM usuarios
    WHERE correo = p_correo
    LIMIT 1;
END$$


-- 8.14 Obtener credenciales por id (validar la contrasena actual)

CREATE PROCEDURE sp_obtener_credenciales_usuario(
    IN p_id_usuario INT
)
BEGIN
    SELECT id_usuario, contrasena
    FROM usuarios
    WHERE id_usuario = p_id_usuario
    LIMIT 1;
END$$


-- 8.15 Saber si un correo pertenece a otro usuario

CREATE PROCEDURE sp_correo_en_uso(
    IN p_correo VARCHAR(100),
    IN p_id_usuario INT
)
BEGIN
    SELECT EXISTS (
        SELECT 1
        FROM usuarios
        WHERE correo = LOWER(TRIM(p_correo))
          AND id_usuario <> p_id_usuario
    ) AS en_uso;
END$$


-- 8.16 Actualizar usuario (contrasena NULL = conservar la actual)

CREATE PROCEDURE sp_actualizar_usuario(
    IN p_id_usuario INT,
    IN p_nombre VARCHAR(50),
    IN p_correo VARCHAR(100),
    IN p_contrasena VARCHAR(255)
)
BEGIN
    UPDATE usuarios
    SET nombre = TRIM(p_nombre),
        correo = LOWER(TRIM(p_correo)),
        contrasena = COALESCE(p_contrasena, contrasena)
    WHERE id_usuario = p_id_usuario;
END$$


-- 8.17 Eliminar usuario y todos sus datos
-- Las claves foraneas son ON DELETE RESTRICT: se borra de lo mas
-- interno a lo mas externo y, si algo falla, se deshace todo.

CREATE PROCEDURE sp_eliminar_usuario(
    IN p_id_usuario INT
)
BEGIN
    DECLARE v_eliminados INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    DELETE m
    FROM movimientos m
    INNER JOIN rubros r ON r.id_rubro = m.id_rubro
    INNER JOIN categorias c ON c.id_categoria = r.id_categoria
    WHERE c.id_usuario = p_id_usuario;

    DELETE r
    FROM rubros r
    INNER JOIN categorias c ON c.id_categoria = r.id_categoria
    WHERE c.id_usuario = p_id_usuario;

    DELETE FROM categorias
    WHERE id_usuario = p_id_usuario;

    DELETE FROM historial_presupuesto
    WHERE id_usuario = p_id_usuario;

    DELETE FROM usuarios
    WHERE id_usuario = p_id_usuario;

    SET v_eliminados = ROW_COUNT();

    COMMIT;

    SELECT v_eliminados AS eliminados;
END$$

-- 8.18 Crear token de recuperacion
-- La fecha de expiracion se calcula con la hora de MySQL (NOW()),
-- la misma con la que se valida, para evitar desfases de zona horaria.
-- Maximo 3 solicitudes por usuario cada hora. Devuelve creado = 1 o 0.
DROP PROCEDURE IF EXISTS sp_crear_token_recuperacion$$
CREATE PROCEDURE sp_crear_token_recuperacion(
    IN p_id_usuario INT,
    IN p_token_hash CHAR(64),
    IN p_minutos INT
)
BEGIN
    DECLARE v_recientes INT DEFAULT 0;

    -- Limpieza de tokens viejos.
    DELETE FROM recuperacion_contrasena
    WHERE expira_en < DATE_SUB(NOW(), INTERVAL 1 DAY);

    SELECT COUNT(*) INTO v_recientes
    FROM recuperacion_contrasena
    WHERE id_usuario = p_id_usuario
      AND creado_en > DATE_SUB(NOW(), INTERVAL 1 HOUR);

    IF v_recientes >= 3 THEN
        SELECT 0 AS creado;
    ELSE
        -- Invalida los enlaces anteriores que no se hayan usado.
        UPDATE recuperacion_contrasena
        SET usado_en = NOW()
        WHERE id_usuario = p_id_usuario
          AND usado_en IS NULL;

        INSERT INTO recuperacion_contrasena (id_usuario, token_hash, expira_en)
        VALUES (p_id_usuario, p_token_hash, DATE_ADD(NOW(), INTERVAL p_minutos MINUTE));

        SELECT 1 AS creado;
    END IF;
END$$

-- 8.19 Validar token (sin usarlo)
DROP PROCEDURE IF EXISTS sp_validar_token_recuperacion$$
CREATE PROCEDURE sp_validar_token_recuperacion(
    IN p_token_hash CHAR(64)
)
BEGIN
    SELECT id_usuario
    FROM recuperacion_contrasena
    WHERE token_hash = p_token_hash
      AND usado_en IS NULL
      AND expira_en > NOW()
    LIMIT 1;
END$$

-- 8.20 Restablecer contrasena con el token (un solo uso, transaccional)
DROP PROCEDURE IF EXISTS sp_restablecer_contrasena$$
CREATE PROCEDURE sp_restablecer_contrasena(
    IN p_token_hash CHAR(64),
    IN p_contrasena VARCHAR(255)
)
BEGIN
    DECLARE v_id_usuario INT DEFAULT NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT id_usuario INTO v_id_usuario
    FROM recuperacion_contrasena
    WHERE token_hash = p_token_hash
      AND usado_en IS NULL
      AND expira_en > NOW()
    LIMIT 1
    FOR UPDATE;

    IF v_id_usuario IS NULL THEN
        ROLLBACK;
        SELECT 0 AS restablecido;
    ELSE
        UPDATE usuarios
        SET contrasena = p_contrasena
        WHERE id_usuario = v_id_usuario;

        UPDATE recuperacion_contrasena
        SET usado_en = NOW()
        WHERE id_usuario = v_id_usuario
          AND usado_en IS NULL;

        COMMIT;
        SELECT 1 AS restablecido;
    END IF;
END$$

DELIMITER ;


-- ============================================================
-- 9. TRIGGERS
-- ============================================================

DELIMITER $$


-- 9.1 Normalizar correo y validar presupuesto al insertar usuario

CREATE TRIGGER trg_usuarios_bi
BEFORE INSERT ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.correo IS NOT NULL THEN
        SET NEW.correo = LOWER(TRIM(NEW.correo));
    END IF;

    IF NEW.presupuesto_mensual < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
            'El presupuesto mensual no puede ser negativo';
    END IF;
END$$


-- 9.2 Guardar historial cuando cambia el presupuesto

CREATE TRIGGER trg_usuarios_au
AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF OLD.presupuesto_mensual <> NEW.presupuesto_mensual THEN
        INSERT INTO historial_presupuesto (
            id_usuario,
            presupuesto_anterior,
            presupuesto_nuevo
        )
        VALUES (
            OLD.id_usuario,
            OLD.presupuesto_mensual,
            NEW.presupuesto_mensual
        );
    END IF;
END$$


-- 9.3 Validar monto del movimiento al insertar

CREATE TRIGGER trg_movimientos_bi
BEFORE INSERT ON movimientos
FOR EACH ROW
BEGIN
    IF NEW.monto <= 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
            'El monto del movimiento debe ser mayor que cero';
    END IF;
END$$


-- 9.4 Validar monto del movimiento al actualizar

CREATE TRIGGER trg_movimientos_bu
BEFORE UPDATE ON movimientos
FOR EACH ROW
BEGIN
    IF NEW.monto <= 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
            'El monto del movimiento debe ser mayor que cero';
    END IF;
END$$


-- 9.5 Validar fechas y monto del rubro al insertar

CREATE TRIGGER trg_rubros_bi
BEFORE INSERT ON rubros
FOR EACH ROW
BEGIN
    IF NEW.fecha_fin < NEW.fecha_inicio THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
            'La fecha_fin no puede ser anterior a fecha_inicio';
    END IF;

    IF NEW.monto_proyectado < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
            'El monto proyectado no puede ser negativo';
    END IF;
END$$


-- 9.6 Validar fechas y monto del rubro al actualizar

CREATE TRIGGER trg_rubros_bu
BEFORE UPDATE ON rubros
FOR EACH ROW
BEGIN
    IF NEW.fecha_fin < NEW.fecha_inicio THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
            'La fecha_fin no puede ser anterior a fecha_inicio';
    END IF;

    IF NEW.monto_proyectado < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
            'El monto proyectado no puede ser negativo';
    END IF;
END$$

DELIMITER ;


-- ============================================================
-- 10. VERIFICACION DE LA BASE DE DATOS
-- ============================================================

SELECT * FROM usuarios;
SELECT * FROM categorias;
SELECT * FROM rubros;
SELECT * FROM movimientos;
SELECT * FROM historial_presupuesto;

SELECT * FROM v_balance_mensual;


-- ============================================================
-- 11. PRUEBAS DE PROCEDIMIENTOS
-- No se ejecutan automaticamente para evitar duplicar datos.
-- ============================================================

/*CALL sp_registrar_usuario(
     'Ana Perez',
    'ana@correo.com',
    'hash_de_ejemplo',
    2000000
 );*/

/*CALL sp_crear_categoria(
     1,
     'Alimentacion adicional',
     'Mercado y restaurantes'
 );*/

/* CALL sp_registrar_movimiento(
     1,
     'gasto',
     150000,
     'Compra adicional de mercado',
     '2026-10-21 10:00:00'
 );*/

-- CALL sp_actualizar_presupuesto(1, 2500000);

-- CALL sp_balance_usuario(4);

-- CALL sp_eliminar_rubro(9);