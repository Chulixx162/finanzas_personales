<?php
// models/rubrosModel.php

/**
 * Obtiene los rubros del usuario invocando el procedimiento almacenado sp_listar_rubros_usuario.
 */
function obtenerRubrosPorUsuario($conn, $user_id) {
    $rubros = [];
    $sql = "CALL sp_listar_rubros_usuario(?)";
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
        while (mysqli_more_results($conn) && mysqli_next_result($conn));
    }
    return $rubros;
}

/**
 * Obtiene un rubro por su id_rubro validando la propiedad del usuario.
 */
function obtenerRubroPorId($conn, $user_id, $rubro_id) {
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
 * Obtiene las categorías del usuario para poblar los select.
 */
function obtenerCategoriasParaSelect($conn, $user_id) {
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
 * Inserta un nuevo rubro en la base de datos controlando excepciones de desbordamiento.
 */
function crearRubro($conn, $id_categoria, $nombre, $monto_proyectado, $fecha_inicio, $fecha_fin) {
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
 * Actualiza los datos de un rubro existente controlando excepciones de desbordamiento.
 */
function actualizarRubro($conn, $user_id, $rubro_id, $id_categoria, $nombre, $monto_proyectado, $fecha_inicio, $fecha_fin) {
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
 * Elimina un rubro mediante el procedimiento almacenado sp_eliminar_rubro.
 */
function eliminarRubro($conn, $user_id, $rubro_id) {
    $rubroExistente = obtenerRubroPorId($conn, $user_id, $rubro_id);
    if (!$rubroExistente) {
        return false;
    }

    try {
        $sql = "CALL sp_eliminar_rubro(?)";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            $r_id = (int) $rubro_id;
            mysqli_stmt_bind_param($stmt, "i", $r_id);
            $resultado = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            while (mysqli_more_results($conn) && mysqli_next_result($conn));
            return $resultado;
        }
    } catch (Exception $e) {
        return false;
    }
    return false;
}
?>