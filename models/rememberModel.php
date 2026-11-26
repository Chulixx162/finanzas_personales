<?php
// models/rememberModel.php
// "Recordarme" SIN tabla en la base de datos.
//
// La cookie lleva:  id_usuario.expira.firma
// La firma es un HMAC-SHA256 con una clave secreta del servidor que incluye una huella
// de la contraseña actual. Por eso:
//   - nadie puede fabricar ni modificar la cookie sin la clave;
//   - si el usuario cambia su contraseña, todas sus cookies anteriores dejan de valer.

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/usersModel.php';

const RECORDARME_COOKIE = 'recordarme';
const RECORDARME_DIAS   = 30;

/**
 * Clave secreta. Prioridad: variable de entorno APP_KEY (.env o Docker).
 * Si no existe, se genera una vez y se guarda en storage/app.key.
 */
function recordarme_clave(): string
{
    $env = (string) app_env('APP_KEY', '');
    if ($env !== '') {
        return $env;
    }

    $ruta = __DIR__ . '/../storage/app.key';

    if (is_file($ruta)) {
        $clave = trim((string) file_get_contents($ruta));
        if ($clave !== '') {
            return $clave;
        }
    }

    $clave = bin2hex(random_bytes(32));
    @file_put_contents($ruta, $clave, LOCK_EX);
    @chmod($ruta, 0600);

    return $clave;
}

function recordarme_firmar(int $idUsuario, int $expira, string $hashContrasena): string
{
    $huella = hash('sha256', $hashContrasena);

    return hash_hmac('sha256', $idUsuario . '|' . $expira . '|' . $huella, recordarme_clave());
}

function recordarme_poner_cookie(string $valor, int $expira): void
{
    if (headers_sent()) {
        return;
    }

    setcookie(RECORDARME_COOKIE, $valor, [
        'expires'  => $expira,
        'path'     => '/',
        'secure'   => app_es_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/** Envía la cookie "Recordarme" por 30 días. */
function recordarme_crear(int $idUsuario, string $hashContrasena): void
{
    $expira = time() + RECORDARME_DIAS * 86400;
    $firma  = recordarme_firmar($idUsuario, $expira, $hashContrasena);

    recordarme_poner_cookie($idUsuario . '.' . $expira . '.' . $firma, $expira);
}

/**
 * Valida la cookie. Devuelve los datos del usuario (id_usuario, nombre, correo)
 * o null si la cookie no existe, está vencida o fue alterada.
 */
function recordarme_autenticar(): ?array
{
    $cookie = $_COOKIE[RECORDARME_COOKIE] ?? '';

    if (!preg_match('/^(\d{1,10})\.(\d{10})\.([a-f0-9]{64})$/', $cookie, $m)) {
        if ($cookie !== '') {
            recordarme_poner_cookie('', time() - 42000);
        }
        return null;
    }

    $idUsuario = (int) $m[1];
    $expira    = (int) $m[2];
    $firma     = $m[3];

    if ($expira < time()) {
        recordarme_poner_cookie('', time() - 42000);
        return null;
    }

    $credenciales = obtener_credenciales_usuario($idUsuario);
    $usuario      = $credenciales ? obtener_usuario_por_id($idUsuario) : null;

    if (
        !$credenciales || !$usuario ||
        !hash_equals(recordarme_firmar($idUsuario, $expira, (string) $credenciales['contrasena']), $firma)
    ) {
        recordarme_poner_cookie('', time() - 42000);
        return null;
    }

    // Renueva por otros 30 días mientras la siga usando.
    recordarme_crear($idUsuario, (string) $credenciales['contrasena']);

    return $usuario;
}

/** Cierre de sesión (o "Recordarme" sin marcar): borra la cookie de este navegador. */
function recordarme_olvidar(): void
{
    if (isset($_COOKIE[RECORDARME_COOKIE])) {
        recordarme_poner_cookie('', time() - 42000);
    }
}