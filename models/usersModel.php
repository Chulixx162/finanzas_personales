<?php


require_once __DIR__ . '/../config/connection.php';

function obtener_usuario_por_id(int $id): ?array
{
    global $conn;

    $sql = 'SELECT id_usuario, nombre, correo, fecha_registro
            FROM usuarios
            WHERE id_usuario = ?
            LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log('No se pudo preparar la consulta del perfil: ' . mysqli_error($conn));
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (!mysqli_stmt_execute($stmt)) {
        error_log('No se pudo consultar el perfil: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return null;
    }

    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = $resultado ? mysqli_fetch_assoc($resultado) : null;
    mysqli_stmt_close($stmt);

    return $usuario ?: null;
}

function obtener_usuario_por_email($email) {
    global $conn;

    $sql = "SELECT id_usuario, nombre, correo, contrasena FROM usuarios WHERE correo = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);

    mysqli_stmt_close($stmt);

    return $usuario;
}

function crear_usuario($nombre, $email, $password) {
    global $conn;

    // Cifrado seguro de la contraseña
    $password_encriptada = password_hash($password, PASSWORD_BCRYPT);

    // Llamada al Stored Procedure
    $sql = "CALL sp_registrar_usuario(?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    // Vinculamos los parámetros: nombre, email y la contraseña cifrada
    mysqli_stmt_bind_param($stmt, "sss", $nombre, $email, $password_encriptada);
    $exito = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $exito;
}