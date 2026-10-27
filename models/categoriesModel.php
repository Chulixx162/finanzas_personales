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
?>