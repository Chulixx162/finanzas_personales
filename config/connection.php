<?php

// Cargar las variables del archivo .env
require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Obtener las credenciales
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = (int) ($_ENV['DB_PORT'] ?? 3306);
$user = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';
$database = $_ENV['DB_NAME'] ?? 'finanzas_personales';

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
} else{
    echo "Conexión exitosa a la base de datos.";
}

// Configurar la codificación
mysqli_set_charset($conn, 'utf8mb4');
?>