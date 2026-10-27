<?php
// models/passwordResetModel.php
// Recuperacion de contrasena. Usa los procedimientos 8.18 a 8.20
// (database/migracion_recuperacion.sql).

require_once __DIR__ . '/../config/connection.php';

/** Libera los resultados pendientes que deja un CALL. */
function recuperacion_liberar_resultados(): void
{
    global $conn;

    while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
        // Solo se consumen los resultados restantes.
    }
}

/** El token llega por URL: solo se aceptan 64 caracteres hexadecimales. */
function token_recuperacion_tiene_formato(string $token): bool
{
    return (bool) preg_match('/^[a-f0-9]{64}$/', $token);
}

/** Guarda el hash del token. Devuelve false si falla o si superó el límite por hora. */
function crear_token_recuperacion(int $idUsuario, string $tokenHash, int $minutos = 60): bool
{
    global $conn;

    $stmt = mysqli_prepare($conn, 'CALL sp_crear_token_recuperacion(?, ?, ?)');

    if (!$stmt) {
        error_log('No se pudo preparar la creación del token: ' . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'isi', $idUsuario, $tokenHash, $minutos);

    if (!mysqli_stmt_execute($stmt)) {
        error_log('No se pudo crear el token: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $resultado = mysqli_stmt_get_result($stmt);
    $fila = $resultado ? mysqli_fetch_assoc($resultado) : null;
    mysqli_stmt_close($stmt);
    recuperacion_liberar_resultados();

    return (int) ($fila['creado'] ?? 0) === 1;
}

/** Indica si el token existe, no está usado y no ha vencido. */
function token_recuperacion_valido(string $token): bool
{
    global $conn;

    if (!token_recuperacion_tiene_formato($token)) {
        return false;
    }

    $tokenHash = hash('sha256', $token);

    $stmt = mysqli_prepare($conn, 'CALL sp_validar_token_recuperacion(?)');

    if (!$stmt) {
        error_log('No se pudo preparar la validación del token: ' . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, 's', $tokenHash);

    if (!mysqli_stmt_execute($stmt)) {
        error_log('No se pudo validar el token: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $resultado = mysqli_stmt_get_result($stmt);
    $fila = $resultado ? mysqli_fetch_assoc($resultado) : null;
    mysqli_stmt_close($stmt);
    recuperacion_liberar_resultados();

    return $fila !== null;
}

/** Cambia la contraseña si el token es válido y lo deja inutilizable. */
function restablecer_contrasena_con_token(string $token, string $nuevaContrasena): bool
{
    global $conn;

    if (!token_recuperacion_tiene_formato($token)) {
        return false;
    }

    $tokenHash = hash('sha256', $token);
    $hash = password_hash($nuevaContrasena, PASSWORD_BCRYPT);

    $stmt = mysqli_prepare($conn, 'CALL sp_restablecer_contrasena(?, ?)');

    if (!$stmt) {
        error_log('No se pudo preparar el restablecimiento: ' . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $tokenHash, $hash);

    if (!mysqli_stmt_execute($stmt)) {
        error_log('No se pudo restablecer la contraseña: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $resultado = mysqli_stmt_get_result($stmt);
    $fila = $resultado ? mysqli_fetch_assoc($resultado) : null;
    mysqli_stmt_close($stmt);
    recuperacion_liberar_resultados();

    return (int) ($fila['restablecido'] ?? 0) === 1;
}