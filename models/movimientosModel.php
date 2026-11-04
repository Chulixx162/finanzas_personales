<?php
// models/movimientosModel.php

/**
 * Obtiene el historial de movimientos de un usuario con los nombres de su rubro y categoría.
 */
function obtenerMovimientosPorUsuario(mysqli $conn, $user_id): array {
    $movimientos = [];
    $sql = "SELECT m.id_movimiento, m.id_rubro, m.tipo, m.monto, m.fecha, m.descripcion,
                   r.nombre AS rubro_nombre, c.nombre AS categoria_nombre
            FROM movimientos m
            INNER JOIN rubros r ON m.id_rubro = r.id_rubro
            INNER JOIN categorias c ON r.id_categoria = c.id_categoria
            WHERE c.id_usuario = ?
            ORDER BY m.fecha DESC, m.id_movimiento DESC";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        $u_id = (int) $user_id;
        mysqli_stmt_bind_param($stmt, "i", $u_id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        while ($fila = mysqli_fetch_assoc($resultado)) {
            $movimientos[] = $fila;
        }
        mysqli_stmt_close($stmt);
    }
    return $movimientos;
}

/**
 * Obtiene los rubros del usuario para poblar el select en el formulario de movimientos.
 */
function obtenerRubrosParaSelect(mysqli $conn, $user_id): array {
    $rubros = [];
    $sql = "SELECT r.id_rubro, r.nombre AS rubro_nombre, c.nombre AS categoria_nombre 
            FROM rubros r
            INNER JOIN categorias c ON r.id_categoria = c.id_categoria
            WHERE c.id_usuario = ? 
            ORDER BY c.nombre ASC, r.nombre ASC";
            
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        $u_id = (int) $user_id;
        mysqli_stmt_bind_param($stmt, "i", $u_id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $rubros[] = $fila;
        }
        mysqli_stmt_close($stmt);
    }
    return $rubros;
}

/**
 * Inserta un nuevo movimiento financiero validando que el rubro pertenezca al usuario.
 */
function crearMovimiento(mysqli $conn, $user_id, $id_rubro, $tipo, $monto, $fecha, $descripcion): bool {
    // 1. Validar que el rubro pertenezca a una categoría del usuario
    $sqlCheck = "SELECT r.id_rubro FROM rubros r 
                 INNER JOIN categorias c ON r.id_categoria = c.id_categoria 
                 WHERE r.id_rubro = ? AND c.id_usuario = ?";
    $stmtCheck = mysqli_prepare($conn, $sqlCheck);
    if ($stmtCheck) {
        $r_id = (int) $id_rubro;
        $u_id = (int) $user_id;
        mysqli_stmt_bind_param($stmtCheck, "ii", $r_id, $u_id);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        if (mysqli_num_rows($resCheck) === 0) {
            mysqli_stmt_close($stmtCheck);
            return false; // El rubro no es del usuario
        }
        mysqli_stmt_close($stmtCheck);
    }

    // 2. Insertar el movimiento
    $sql = "INSERT INTO movimientos (id_rubro, tipo, monto, fecha, descripcion) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        try {
            $r_id  = (int) $id_rubro;
            $t     = $tipo; // 'ingreso' o 'gasto'
            $m     = (float) $monto;
            $f     = empty($fecha) ? date('Y-m-d H:i:s') : $fecha;
            $d     = trim($descripcion);

            mysqli_stmt_bind_param($stmt, "isdss", $r_id, $t, $m, $f, $d);
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
?>