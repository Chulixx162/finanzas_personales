<?php
// models/rubrosModel.php

/**
 * Obtiene los rubros del usuario mediante el procedimiento almacenado.
 */
function obtenerRubrosPorUsuario(mysqli $conn, $id_usuario): array
{
    $rubros = [];

    $sql = "
        SELECT
            r.id_rubro,
            r.nombre AS rubro,
            r.monto_proyectado,
            r.fecha_inicio,
            r.fecha_fin,
            c.id_categoria,
            c.nombre AS categoria,

            COALESCE(SUM(
                CASE
                    WHEN m.tipo = 'ingreso' THEN m.monto
                    ELSE 0
                END
            ), 0) AS total_ingresos,

            COALESCE(SUM(
                CASE
                    WHEN m.tipo = 'gasto' THEN m.monto
                    ELSE 0
                END
            ), 0) AS total_gastos

        FROM rubros r

        INNER JOIN categorias c
            ON c.id_categoria = r.id_categoria

        LEFT JOIN movimientos m
            ON m.id_rubro = r.id_rubro

        WHERE c.id_usuario = ?

        GROUP BY
            r.id_rubro,
            r.nombre,
            r.monto_proyectado,
            r.fecha_inicio,
            r.fecha_fin,
            c.id_categoria,
            c.nombre

        ORDER BY c.nombre, r.nombre
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $resultado = $stmt->get_result();

    while ($fila = $resultado->fetch_assoc()) {
        $rubros[] = $fila;
    }

    $stmt->close();

    return $rubros;
}

/**
 * Obtiene un rubro por su id validando que pertenezca al usuario.
 */
function obtenerRubroPorId(mysqli $conn, $user_id, $rubro_id): ?array {
    $rubro = null;
    $sql = "SELECT r.id_rubro, r.id_categoria, r.nombre, r.monto_proyectado, r.fecha_inicio, r.fecha_fin 
            FROM rubros r 
            INNER JOIN categorias c ON r.id_categoria = c.id_categoria 
            WHERE r.id_rubro = ? AND c.id_usuario = ?";
            
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        $r_id = (int) $rubro_id;
        $u_id = (int) $user_id;
        mysqli_stmt_bind_param($stmt, "ii", $r_id, $u_id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $rubro = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);
    }
    return $rubro;
}

/**
 * Obtiene las categorías del usuario para el select del formulario.
 */
function obtenerCategoriasParaSelect(mysqli $conn, $user_id): array {
    $categorias = [];
    $sql = "SELECT id_categoria, nombre FROM categorias WHERE id_usuario = ? ORDER BY nombre ASC";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        $u_id = (int) $user_id;
        mysqli_stmt_bind_param($stmt, "i", $u_id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $categorias[] = $fila;
        }
        mysqli_stmt_close($stmt);
    }
    return $categorias;
}

/**
 * Crea un rubro controlando desbordamientos y excepciones.
 */
function crearRubro(mysqli $conn, $id_categoria, $nombre, $monto_proyectado, $fecha_inicio, $fecha_fin): bool {
    $sql = "INSERT INTO rubros (id_categoria, nombre, monto_proyectado, fecha_inicio, fecha_fin) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        try {
            $cat_id = (int) $id_categoria;
            $monto  = (float) $monto_proyectado;
            mysqli_stmt_bind_param($stmt, "isdss", $cat_id, $nombre, $monto, $fecha_inicio, $fecha_fin);
            $resultado = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $resultado;
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            return false;
        }
    }
    return false;
}

/**
 * Actualiza un rubro existente.
 */
function actualizarRubro(mysqli $conn, $user_id, $rubro_id, $id_categoria, $nombre, $monto_proyectado, $fecha_inicio, $fecha_fin): bool {
    $rubroExistente = obtenerRubroPorId($conn, $user_id, $rubro_id);
    if (!$rubroExistente) {
        return false;
    }

    $sql = "UPDATE rubros 
            SET id_categoria = ?, nombre = ?, monto_proyectado = ?, fecha_inicio = ?, fecha_fin = ? 
            WHERE id_rubro = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        try {
            $cat_id = (int) $id_categoria;
            $monto  = (float) $monto_proyectado;
            $r_id   = (int) $rubro_id;
            mysqli_stmt_bind_param($stmt, "isdssi", $cat_id, $nombre, $monto, $fecha_inicio, $fecha_fin, $r_id);
            $resultado = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $resultado;
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            return false;
        }
    }
    return false;
}

/**
 * Elimina un rubro validando previamente si tiene movimientos asociados 
 * para respetar la restricción ON DELETE RESTRICT de la base de datos.
 */
function eliminarRubro(mysqli $conn, $user_id, $rubro_id): array {
    $rubroExistente = obtenerRubroPorId($conn, $user_id, $rubro_id);
    if (!$rubroExistente) {
        return ['success' => false, 'message' => 'El rubro no existe o no tienes permisos.'];
    }

    $r_id = (int) $rubro_id;

    // 1. Verificación previa de movimientos en PHP (Evita que salte el error 1451 de MySQL)
    $sqlCheck = "SELECT COUNT(*) AS total FROM movimientos WHERE id_rubro = ?";
    $stmtCheck = mysqli_prepare($conn, $sqlCheck);
    if ($stmtCheck) {
        mysqli_stmt_bind_param($stmtCheck, "i", $r_id);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        $rowCheck = mysqli_fetch_assoc($resCheck);
        mysqli_stmt_close($stmtCheck);
        
        if ($rowCheck['total'] > 0) {
            return [
                'success' => false, 
                'message' => 'No se puede eliminar un rubro con movimientos asociados.'
            ];
        }
    }

    // 2. Si pasa la validación, procedemos a borrar con seguridad
    $sqlDelete = "DELETE FROM rubros WHERE id_rubro = ?";
    $stmtDelete = mysqli_prepare($conn, $sqlDelete);
    
    if ($stmtDelete) {
        mysqli_stmt_bind_param($stmtDelete, "i", $r_id);
        $resultado = mysqli_stmt_execute($stmtDelete);
        mysqli_stmt_close($stmtDelete);
        
        if ($resultado) {
            return ['success' => true];
        }
    }
    
    return ['success' => false, 'message' => 'Error al procesar la eliminación en la base de datos.'];
}
?>