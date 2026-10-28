<?php
// models/rubrosModel.php

require_once __DIR__ . '/../config/connection.php';

/**
 * Libera los resultados pendientes que deja un CALL
 * para poder ejecutar otro procedimiento en la misma conexión.
 */
function rubros_liberar_resultados(): void
{
    global $conn;

    while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
        // Solo se consumen los resultados restantes.
    }
}

/**
 * Lista los rubros del usuario junto con la categoría a la que pertenecen.
 * Solo devuelve rubros de categorías que son del usuario.
 */
function obtener_rubros_por_usuario(int $idUsuario): array
{
    global $conn;

    $rubros = [];

    $sql  = 'CALL sp_listar_rubros_usuario(?)';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(mysqli_error($conn));
        return $rubros;
    }

    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);

    if (!mysqli_stmt_execute($stmt)) {
        error_log(mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        rubros_liberar_resultados();
        return $rubros;
    }

    $resultado = mysqli_stmt_get_result($stmt);

    while ($resultado && ($fila = mysqli_fetch_assoc($resultado))) {
        $rubros[] = $fila;
    }

    mysqli_stmt_close($stmt);
    rubros_liberar_resultados();

    return $rubros;
}