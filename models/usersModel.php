<?php
// models/usersModel.php
// Todas las consultas de usuarios usan procedimientos almacenados
// (database/schema.sql, sección 8).

require_once __DIR__ . '/../config/connection.php';

/**
 * Libera los resultados pendientes que deja un CALL
 * para poder ejecutar otro procedimiento en la misma conexión.
 */
function usuarios_liberar_resultados(): void
{
    global $conn;

    while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
        // Solo se consumen los resultados restantes.
    }
}

/** Datos del usuario para mostrar (sin la contraseña). */
function obtener_usuario_por_id(int $id): ?array
{
    global $conn;

    $sql = 'CALL sp_obtener_usuario_por_id(?)';
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
    usuarios_liberar_resultados();

    return $usuario ?: null;
}

/** Usuario con su contraseña cifrada, para el inicio de sesión. */
function obtener_usuario_por_email($email)
{
    global $conn;

    $sql = 'CALL sp_obtener_usuario_por_email(?)';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log('No se pudo preparar la consulta por correo: ' . mysqli_error($conn));
        return null;
    }

    mysqli_stmt_bind_param($stmt, 's', $email);

    if (!mysqli_stmt_execute($stmt)) {
        error_log('No se pudo consultar por correo: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return null;
    }

    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = $resultado ? mysqli_fetch_assoc($resultado) : null;
    mysqli_stmt_close($stmt);
    usuarios_liberar_resultados();

    return $usuario ?: null;
}

/** Id y contraseña cifrada del usuario, para confirmar su contraseña actual. */
function obtener_credenciales_usuario(int $id): ?array
{
    global $conn;

    $sql = 'CALL sp_obtener_credenciales_usuario(?)';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log('No se pudo preparar la consulta de credenciales: ' . mysqli_error($conn));
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (!mysqli_stmt_execute($stmt)) {
        error_log('No se pudo consultar las credenciales: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return null;
    }

    $resultado = mysqli_stmt_get_result($stmt);
    $credenciales = $resultado ? mysqli_fetch_assoc($resultado) : null;
    mysqli_stmt_close($stmt);
    usuarios_liberar_resultados();

    return $credenciales ?: null;
}

/** Indica si el correo pertenece a otro usuario distinto de $idExcluido. */
function correo_en_uso(string $correo, int $idExcluido): bool
{
    global $conn;

    $sql = 'CALL sp_correo_en_uso(?, ?)';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log('No se pudo preparar la verificación del correo: ' . mysqli_error($conn));
        return true; // Ante la duda no se permite el cambio.
    }

    mysqli_stmt_bind_param($stmt, 'si', $correo, $idExcluido);

    if (!mysqli_stmt_execute($stmt)) {
        error_log('No se pudo verificar el correo: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return true;
    }

    $resultado = mysqli_stmt_get_result($stmt);
    $fila = $resultado ? mysqli_fetch_assoc($resultado) : null;
    mysqli_stmt_close($stmt);
    usuarios_liberar_resultados();

    return (bool) ($fila['en_uso'] ?? 1);
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

/**
 * Actualiza nombre y correo. Si $nuevaContrasena no está vacía, también
 * cambia la contraseña (se cifra aquí, igual que en crear_usuario).
 */
function actualizar_usuario(
    int $id,
    string $nombre,
    string $correo,
    string $nuevaContrasena = ''
): bool {
    global $conn;

    // NULL le indica al procedimiento que conserve la contraseña actual.
    $hash = $nuevaContrasena !== ''
        ? password_hash($nuevaContrasena, PASSWORD_BCRYPT)
        : null;

    $sql = 'CALL sp_actualizar_usuario(?, ?, ?, ?)';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log('No se pudo preparar la actualización del usuario: ' . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'isss', $id, $nombre, $correo, $hash);
    $exito = mysqli_stmt_execute($stmt);

    if (!$exito) {
        error_log('No se pudo actualizar el usuario: ' . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);

    return $exito;
}

/** Elimina al usuario y todos sus datos. Devuelve true si se borró la cuenta. */
function eliminar_usuario(int $id): bool
{
    global $conn;

    $sql = 'CALL sp_eliminar_usuario(?)';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log('No se pudo preparar la eliminación del usuario: ' . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (!mysqli_stmt_execute($stmt)) {
        error_log('No se pudo eliminar el usuario: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $resultado = mysqli_stmt_get_result($stmt);
    $fila = $resultado ? mysqli_fetch_assoc($resultado) : null;
    mysqli_stmt_close($stmt);
    usuarios_liberar_resultados();

    return (int) ($fila['eliminados'] ?? 0) === 1;
}