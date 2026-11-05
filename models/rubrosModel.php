<?php
// models/rubrosModel.php

/**
 * Obtiene los rubros del usuario mediante el procedimiento almacenado.
 */
function obtenerRubrosPorUsuario(mysqli $conn, $id_usuario): array
{
    $rubros = [];

    $sql = "
        SELECT
            r.id_rubro,
            r.nombre AS rubro,
            r.monto_proyectado,
            r.fecha_inicio,
            r.fecha_fin,
            c.id_categoria,
            c.nombre AS categoria,

            COALESCE(SUM(
                CASE
                    WHEN m.tipo = 'ingreso' THEN m.monto
                    ELSE 0
                END
            ), 0) AS total_ingresos,

            COALESCE(SUM(
                CASE
                    WHEN m.tipo = 'gasto' THEN m.monto
                    ELSE 0
                END
            ), 0) AS total_gastos

        FROM rubros r

        INNER JOIN categorias c
            ON c.id_categoria = r.id_categoria

        LEFT JOIN movimientos m
            ON m.id_rubro = r.id_rubro

        WHERE c.id_usuario = ?

        GROUP BY
            r.id_rubro,
            r.nombre,
            r.monto_proyectado,
            r.fecha_inicio,
            r.fecha_fin,
            c.id_categoria,
            c.nombre

        ORDER BY c.nombre, r.nombre
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $resultado = $stmt->get_result();

    while ($fila = $resultado->fetch_assoc()) {
        $rubros[] = $fila;
    }

    $stmt->close();

    return $rubros;
}

/**
 * Obtiene un rubro por su id validando que pertenezca al usuario.
 */
function obtenerRubroPorId(mysqli $conn, $user_id, $rubro_id): ?array {
    $rubro = null;
    $sql = "SELECT r.id_rubro, r.id_categoria, r.nombre, r.monto_proyectado, r.fecha_inicio, r.fecha_fin 
            FROM rubros r 
            INNER JOIN categorias c ON r.id_categoria = c.id_categoria 
            WHERE r.id_rubro = ? AND c.id_usuario = ?";
            
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        $r_id = (int) $rubro_id;
        $u_id = (int) $user_id;
        mysqli_stmt_bind_param($stmt, "ii", $r_id, $u_id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $rubro = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);
    }
    return $rubro;
}

/**
 * Obtiene las categorías del usuario para el select del formulario.
 */
function obtenerCategoriasParaSelect(mysqli $conn, $user_id): array {
    $categorias = [];
    $sql = "SELECT id_categoria, nombre FROM categorias WHERE id_usuario = ? ORDER BY nombre ASC";
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

/**
 * Crea un rubro controlando desbordamientos y excepciones.
 */
function crearRubro(mysqli $conn, $user_id, $id_categoria, $nombre, $monto_proyectado, $fecha_inicio, $fecha_fin): bool {
    $sql = "INSERT INTO rubros (id_categoria, nombre, monto_proyectado, fecha_inicio, fecha_fin)
            SELECT c.id_categoria, ?, ?, ?, ?
            FROM categorias c
            WHERE c.id_categoria = ? AND c.id_usuario = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        try {
            $monto  = (float) $monto_proyectado;
            $cat_id = (int) $id_categoria;
            $u_id   = (int) $user_id;
            mysqli_stmt_bind_param($stmt, "sdssii", $nombre, $monto, $fecha_inicio, $fecha_fin, $cat_id, $u_id);
            $ok = mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) === 1;
            mysqli_stmt_close($stmt);
            return $ok;
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            return false;
        }
    }
    return false;
}

/**
 * Actualiza un rubro existente.
 */
function actualizarRubro(mysqli $conn, $user_id, $rubro_id, $id_categoria, $nombre, $monto_proyectado, $fecha_inicio, $fecha_fin): bool {
    $rubroExistente = obtenerRubroPorId($conn, $user_id, $rubro_id);
    if (!$rubroExistente) {
        return false;
    }

    $sql = "UPDATE rubros 
            SET id_categoria = ?, nombre = ?, monto_proyectado = ?, fecha_inicio = ?, fecha_fin = ? 
            WHERE id_rubro = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        try {
            $cat_id = (int) $id_categoria;
            $monto  = (float) $monto_proyectado;
            $r_id   = (int) $rubro_id;
            mysqli_stmt_bind_param($stmt, "isdssi", $cat_id, $nombre, $monto, $fecha_inicio, $fecha_fin, $r_id);
            $resultado = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $resultado;
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            return false;
        }
    }
    return false;
}

/**
 * Elimina un rubro validando previamente si tiene movimientos asociados 
 * para respetar la restricción ON DELETE RESTRICT de la base de datos.
 */
function eliminarRubro(mysqli $conn, $user_id, $rubro_id): array {
    $rubroExistente = obtenerRubroPorId($conn, $user_id, $rubro_id);
    if (!$rubroExistente) {
        return ['success' => false, 'message' => 'El rubro no existe o no tienes permisos.'];
    }

    $r_id = (int) $rubro_id;

    // 1. Verificación previa de movimientos en PHP (Evita que salte el error 1451 de MySQL)
    $sqlCheck = "SELECT COUNT(*) AS total FROM movimientos WHERE id_rubro = ?";
    $stmtCheck = mysqli_prepare($conn, $sqlCheck);
    if ($stmtCheck) {
        mysqli_stmt_bind_param($stmtCheck, "i", $r_id);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        $rowCheck = mysqli_fetch_assoc($resCheck);
        mysqli_stmt_close($stmtCheck);
        
        if ($rowCheck['total'] > 0) {
            return [
                'success' => false, 
                'message' => 'No se puede eliminar un rubro con movimientos asociados.'
            ];
        }
    }

    // 2. Si pasa la validación, procedemos a borrar con seguridad
    $sqlDelete = "DELETE FROM rubros WHERE id_rubro = ?";
    $stmtDelete = mysqli_prepare($conn, $sqlDelete);
    
    if ($stmtDelete) {
        mysqli_stmt_bind_param($stmtDelete, "i", $r_id);
        $resultado = mysqli_stmt_execute($stmtDelete);
        mysqli_stmt_close($stmtDelete);
        
        if ($resultado) {
            return ['success' => true];
        }
    }
    
    return ['success' => false, 'message' => 'Error al procesar la eliminación en la base de datos.'];
}

/* ==========================================================
   LÍMITE DEL PRESUPUESTO MENSUAL
   Los RUBROS de un mes nunca pueden sumar más que el presupuesto mensual
   del usuario. Un rubro cuenta en cada mes que toca su periodo (mismo
   criterio que "Uso de tu presupuesto"). Los GASTOS no tienen este límite:
   pueden superarlo y la app solo avisa.
   ========================================================== */

/**
 * Número de mes (año * 12 + mes) de una fecha 'YYYY-MM-DD'. null si no es válida.
 */
function rubro_indice_mes(string $fecha): ?int {
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $fecha, $m)) {
        return null;
    }
    if (!checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
        return null;
    }
    return (int) $m[1] * 12 + (int) $m[2];
}

function rubro_mes_actual(): int {
    return (int) date('Y') * 12 + (int) date('n');
}

function rubro_pesos($valor): string {
    return '$' . number_format((float) $valor, 0, ',', '.');
}

/**
 * Presupuesto mensual del usuario y sus rubros (como rangos de meses).
 * $excluir_id deja fuera un rubro (el que se está editando).
 */
function obtenerCupoRubros(mysqli $conn, int $user_id, int $excluir_id = 0): array {
    $cupo = ['presupuesto' => null, 'rubros' => []];

    try {
        $stmt = mysqli_prepare($conn, 'SELECT presupuesto_mensual FROM usuarios WHERE id_usuario = ?');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
            $res  = mysqli_stmt_get_result($stmt);
            $fila = $res ? mysqli_fetch_assoc($res) : null;
            mysqli_stmt_close($stmt);

            if ($fila && $fila['presupuesto_mensual'] !== null) {
                $cupo['presupuesto'] = (float) $fila['presupuesto_mensual'];
            }
        }

        $sql = 'SELECT r.monto_proyectado, r.fecha_inicio, r.fecha_fin
                FROM rubros r
                INNER JOIN categorias c ON c.id_categoria = r.id_categoria
                WHERE c.id_usuario = ? AND r.id_rubro <> ?';
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ii', $user_id, $excluir_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);

            while ($res && ($fila = mysqli_fetch_assoc($res))) {
                $ini = rubro_indice_mes((string) $fila['fecha_inicio']);
                $fin = rubro_indice_mes((string) $fila['fecha_fin']);
                if ($ini === null || $fin === null) {
                    continue;
                }
                $cupo['rubros'][] = [
                    'ini'   => $ini,
                    'fin'   => $fin,
                    'monto' => (float) $fila['monto_proyectado'],
                ];
            }
            mysqli_stmt_close($stmt);
        }
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
    }

    return $cupo;
}

/**
 * Cuánto se puede asignar todavía a un rubro nuevo con ese periodo:
 * presupuesto menos lo que ya suman los otros rubros en el mes más cargado
 * del periodo. Solo cuentan los meses desde el actual (el pasado no cambia).
 * Puede dar negativo (ya estaba excedido) o INF (periodo ya terminado).
 */
function rubro_disponible(array $cupo, int $ini, int $fin, ?int $desde = null): float {
    $desde = $desde ?? rubro_mes_actual();
    $ini   = max($ini, $desde);

    if ($fin < $ini) {
        return INF;
    }

    // La suma solo sube cuando empieza otro rubro: basta revisar esos meses.
    $puntos = [$ini];
    foreach ($cupo['rubros'] as $r) {
        if ($r['ini'] > $ini && $r['ini'] <= $fin) {
            $puntos[] = $r['ini'];
        }
    }

    $maxOtros = 0.0;
    foreach ($puntos as $mes) {
        $suma = 0.0;
        foreach ($cupo['rubros'] as $r) {
            if ($r['ini'] <= $mes && $mes <= $r['fin']) {
                $suma += $r['monto'];
            }
        }
        $maxOtros = max($maxOtros, $suma);
    }

    return (float) $cupo['presupuesto'] - $maxOtros;
}

/**
 * Lo máximo que suman los rubros en un solo mes, desde el mes actual en adelante.
 * Es el piso al que se puede bajar el presupuesto mensual.
 */
function rubro_max_asignado(array $cupo, ?int $desde = null): float {
    $desde = $desde ?? rubro_mes_actual();
    $max   = 0.0;

    foreach ($cupo['rubros'] as $a) {
        if ($a['fin'] < $desde) {
            continue;
        }
        $mes  = max($a['ini'], $desde);
        $suma = 0.0;
        foreach ($cupo['rubros'] as $r) {
            if ($r['ini'] <= $mes && $mes <= $r['fin']) {
                $suma += $r['monto'];
            }
        }
        $max = max($max, $suma);
    }

    return $max;
}

/**
 * Revisa que crear o editar un rubro no haga pasar los rubros del presupuesto.
 * Devuelve null si cabe, o el texto del error si no.
 * $rubroActual: el rubro tal como está guardado (solo al editar).
 */
function validarRubroContraPresupuesto(
    mysqli $conn,
    int $user_id,
    float $monto,
    string $fecha_inicio,
    string $fecha_fin,
    ?array $rubroActual = null
): ?string {
    $ini = rubro_indice_mes($fecha_inicio);
    $fin = rubro_indice_mes($fecha_fin);

    if ($ini === null || $fin === null || $fin < $ini) {
        return 'Las fechas del rubro no son válidas.';
    }

    // Dejar igual o bajar un rubro nunca empeora las cosas: se permite, así se
    // pueden arreglar rubros viejos que ya se pasaban del presupuesto.
    if ($rubroActual) {
        $oIni = rubro_indice_mes((string) $rubroActual['fecha_inicio']);
        $oFin = rubro_indice_mes((string) $rubroActual['fecha_fin']);

        if (
            $oIni !== null && $oFin !== null &&
            $monto <= (float) $rubroActual['monto_proyectado'] + 0.005 &&
            $ini >= $oIni && $fin <= $oFin
        ) {
            return null;
        }
    }

    $excluir = $rubroActual ? (int) $rubroActual['id_rubro'] : 0;
    $cupo    = obtenerCupoRubros($conn, $user_id, $excluir);

    if ($cupo['presupuesto'] === null) {
        return 'Primero define tu presupuesto mensual en «Historial de Presupuesto» para poder asignar rubros.';
    }

    $disponible = rubro_disponible($cupo, $ini, $fin);

    if ($monto > $disponible + 0.005) {
        return 'Este rubro supera tu presupuesto mensual de ' . rubro_pesos($cupo['presupuesto'])
            . '. Para ese periodo solo te quedan ' . rubro_pesos(max(0, $disponible)) . ' por asignar.';
    }

    return null;
}
?>