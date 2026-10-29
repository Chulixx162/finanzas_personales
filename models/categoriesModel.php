<?php
// models/categoriesModel.php

function obtenerCategoriasPorUsuario($conn, $user_id) {
    $categorias = [];
    $sql = "SELECT id_categoria AS id, nombre, descripcion FROM categorias WHERE id_usuario = ? ORDER BY id_categoria DESC";
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

function obtenerCategoriaPorId($conn, $user_id, $categoria_id) {
    $categoria = null;
    $sql = "SELECT id_categoria AS id, nombre, descripcion FROM categorias WHERE id_categoria = ? AND id_usuario = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        $cat_id = (int) $categoria_id;
        $u_id   = (int) $user_id;
        mysqli_stmt_bind_param($stmt, "ii", $cat_id, $u_id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $categoria = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);
    }
    return $categoria;
}

function crearCategoria($conn, $user_id, $nombre, $descripcion) {
    $sql = "INSERT INTO categorias (id_usuario, nombre, descripcion) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        $u_id = (int) $user_id;
        mysqli_stmt_bind_param($stmt, "iss", $u_id, $nombre, $descripcion);
        $resultado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $resultado;
    }
    return false;
}

function actualizarCategoria($conn, $user_id, $categoria_id, $nombre, $descripcion) {
    $sql = "UPDATE categorias SET nombre = ?, descripcion = ? WHERE id_categoria = ? AND id_usuario = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        $cat_id = (int) $categoria_id;
        $u_id   = (int) $user_id;
        mysqli_stmt_bind_param($stmt, "ssii", $nombre, $descripcion, $cat_id, $u_id);
        $resultado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $resultado;
    }
    return false;
}

function eliminarCategoria($conn, $user_id, $categoria_id) {
    $sql = "DELETE FROM categorias WHERE id_categoria = ? AND id_usuario = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        $cat_id = (int) $categoria_id;
        $u_id   = (int) $user_id;
        mysqli_stmt_bind_param($stmt, "ii", $cat_id, $u_id);
        $resultado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $resultado;
    }
    return false;
}


/**
 * Libera los resultados pendientes que deja un CALL
 * para poder ejecutar otro procedimiento en la misma conexión.
 */
function categorias_liberar_resultados($conn): void
{
    while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
        // Solo se consumen los resultados restantes.
    }
}

/** Indica si el usuario ya tiene una categoría con ese nombre. */
function categoriaNombreExiste($conn, int $user_id, string $nombre, int $excluir_id = 0): bool
{
    $sql  = 'CALL sp_categoria_nombre_existe(?, ?, ?)';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'isi', $user_id, $nombre, $excluir_id);

    if (!mysqli_stmt_execute($stmt)) {
        error_log(mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        categorias_liberar_resultados($conn);
        return false;
    }

    $resultado = mysqli_stmt_get_result($stmt);
    $fila      = $resultado ? mysqli_fetch_assoc($resultado) : null;
    mysqli_stmt_close($stmt);
    categorias_liberar_resultados($conn);

    return ((int) ($fila['total'] ?? 0)) > 0;
}

/** Cantidad de rubros y movimientos que dependen de la categoría. */
function contarRelacionesCategoria($conn, int $user_id, int $categoria_id): array
{
    $relaciones = ['rubros' => 0, 'movimientos' => 0];

    $sql  = 'CALL sp_contar_relaciones_categoria(?, ?)';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(mysqli_error($conn));
        return $relaciones;
    }

    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $categoria_id);

    if (!mysqli_stmt_execute($stmt)) {
        error_log(mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        categorias_liberar_resultados($conn);
        return $relaciones;
    }

    $resultado = mysqli_stmt_get_result($stmt);
    $fila      = $resultado ? mysqli_fetch_assoc($resultado) : null;
    mysqli_stmt_close($stmt);
    categorias_liberar_resultados($conn);

    if ($fila) {
        $relaciones['rubros']      = (int) $fila['rubros'];
        $relaciones['movimientos'] = (int) $fila['movimientos'];
    }

    return $relaciones;
}

?>