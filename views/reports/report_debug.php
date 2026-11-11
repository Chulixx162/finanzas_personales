<?php
// views/reports/report_debug.php
// Página TEMPORAL de diagnóstico del reporte. Solo lee datos; bórrala cuando termines.

require_once __DIR__ . '/../../config/session.php';
$idUsuario = requerir_login();

require_once __DIR__ . '/../../config/connection.php';

header('Content-Type: text/plain; charset=UTF-8');

/** Ejecuta un SELECT simple (sin parámetros del usuario) y devuelve las filas. */
function dbg_select(string $sql): array
{
    global $conn;
    $res = mysqli_query($conn, $sql);
    $filas = [];
    while ($res && ($f = mysqli_fetch_assoc($res))) {
        $filas[] = $f;
    }
    return $filas;
}

/** Llama un procedimiento del reporte y devuelve filas o el mensaje de error. */
function dbg_sp(string $nombre, int $id, string $desde, string $hasta)
{
    global $conn;
    try {
        $stmt = mysqli_prepare($conn, "CALL {$nombre}(?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iss', $id, $desde, $hasta);
        mysqli_stmt_execute($stmt);
        $res   = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($res && ($f = mysqli_fetch_assoc($res))) {
            $filas[] = $f;
        }
        mysqli_stmt_close($stmt);
        while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
        }
        return $filas;
    } catch (Throwable $e) {
        return 'ERROR: ' . $e->getMessage();
    }
}

echo "=== 1. Sesión ===\n";
echo "id_usuario de la sesión: {$idUsuario}\n";
echo "nombre en sesión: " . ($_SESSION['user_nombre'] ?? '(vacío)') . "\n";
echo "fecha del servidor PHP: " . date('Y-m-d H:i:s') . "\n";
echo "base de datos conectada: " . print_r(dbg_select('SELECT DATABASE() AS bd'), true) . "\n";

echo "=== 2. Movimientos por dueño (JOIN, sin filtro de fechas) ===\n";
echo "(si tu id_usuario no aparece aquí, tu cuenta no tiene movimientos)\n";
print_r(dbg_select(
    "SELECT c.id_usuario, COUNT(*) AS movimientos, MIN(m.fecha) AS primera, MAX(m.fecha) AS ultima
     FROM movimientos m
     INNER JOIN rubros r ON r.id_rubro = m.id_rubro
     INNER JOIN categorias c ON c.id_categoria = r.id_categoria
     GROUP BY c.id_usuario"
));

echo "\n=== 3. Procedimientos que existen ===\n";
print_r(dbg_select(
    "SELECT ROUTINE_NAME FROM information_schema.ROUTINES
     WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_NAME LIKE 'sp_reporte%'"
));

echo "\n=== 4. sp_reporte_por_categoria (tu usuario, 2000-01-01 a 2100-12-31) ===\n";
print_r(dbg_sp('sp_reporte_por_categoria', $idUsuario, '2000-01-01', '2100-12-31'));

echo "\n=== 5. sp_reporte_totales_periodo (tu usuario, 2000-01-01 a 2100-12-31) ===\n";
print_r(dbg_sp('sp_reporte_totales_periodo', $idUsuario, '2000-01-01', '2100-12-31'));