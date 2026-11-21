<?php
// config/connection.php
// Conexión a MySQL. Las credenciales salen de las variables de entorno:
//   - Docker:  DB_HOST=db  (nombre del servicio en docker-compose)
//   - Laragon: archivo .env con DB_HOST=127.0.0.1

require_once __DIR__ . '/app.php';

$host     = (string) app_env('DB_HOST', '127.0.0.1');
$port     = (int) app_env('DB_PORT', 3306);
$user     = (string) app_env('DB_USER', 'root');
$password = (string) app_env('DB_PASS', '');
$database = (string) app_env('DB_NAME', 'finanzas_personales');

// Conectar a MySQL
$conn = mysqli_connect(
    $host,
    $user,
    $password,
    $database,
    $port
);

// Verificar la conexión
if (!$conn) {
    error_log(mysqli_connect_error());
    die('Error al conectar con la base de datos.');
}
// Configurar la codificación
mysqli_set_charset($conn, 'utf8mb4');