<?php
// controllers/rubrosController.php

// Iniciar y verificar sesión
require_once __DIR__ . '/../config/session.php';

$idUsuario = requerir_login();

// Conexión a la base de datos
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../models/rubrosModel.php';

$user_id   = $_SESSION['user_id'];
$action    = $_GET['action'] ?? 'index';
$MAX_MONTO = 9999999999.99; // Límite estricto de la columna DECIMAL(12,2)

switch ($action) {

    // ==========================================
    // CREAR RUBRO
    // ==========================================
    case 'store':

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Límite alcanzado: no se crean más rubros (aunque manden el formulario a mano).
            $estadoCupo = obtenerEstadoCupoRubros($conn, (int) $idUsuario);
            if ($estadoCupo['alcanzado']) {
                redirigir('views/rubros/index.php', ['error' => rubro_mensaje_limite($estadoCupo)]);
            }

            $id_categoria = $_POST['id_categoria'] ?? null;
            $nombre = trim($_POST['nombre'] ?? '');
            $monto_proyectado = (float) ($_POST['monto_proyectado'] ?? 0);
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $fecha_fin = $_POST['fecha_fin'] ?? '';
            $creado = false;
            $error  = null;

            if (
                $id_categoria &&
                !empty($nombre) &&
                $monto_proyectado >= 0 &&
                $monto_proyectado <= $MAX_MONTO &&
                !empty($fecha_inicio) &&
                !empty($fecha_fin) &&
                $fecha_fin >= $fecha_inicio
            ) {
                // Los rubros no pueden pasar del presupuesto mensual.
                $error = validarRubroContraPresupuesto(
                    $conn,
                    $idUsuario,
                    $monto_proyectado,
                    $fecha_inicio,
                    $fecha_fin
                );

                if ($error === null) {
                    $creado = crearRubro(
                        $conn,
                        $idUsuario,
                        $id_categoria,
                        $nombre,
                        $monto_proyectado,
                        $fecha_inicio,
                        $fecha_fin
                    );
                }
            }

            $destino = $creado
                ? 'success=created'
                : 'error=' . urlencode($error ?? 'No se pudo crear el rubro. Revisa los datos.');

            redirigir('views/rubros/index.php?' . $destino);
        }

        break;


    // ==========================================
    // ACTUALIZAR RUBRO
    // ==========================================
    case 'update':

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $id_rubro = $_POST['id_rubro'] ?? null;
            $id_categoria = $_POST['id_categoria'] ?? null;
            $nombre = trim($_POST['nombre'] ?? '');
            $monto_proyectado = (float) ($_POST['monto_proyectado'] ?? 0);
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $fecha_fin = $_POST['fecha_fin'] ?? '';
            $actualizado = false;
            $error       = null;

            if (
                $id_rubro &&
                $id_categoria &&
                !empty($nombre) &&
                $monto_proyectado >= 0 &&
                $monto_proyectado <= $MAX_MONTO &&
                !empty($fecha_inicio) &&
                !empty($fecha_fin) &&
                $fecha_fin >= $fecha_inicio
            ) {
                $rubroActual = obtenerRubroPorId($conn, $idUsuario, $id_rubro);

                if (!$rubroActual) {
                    $error = 'El rubro no existe o no tienes permisos.';
                } else {
                    // Los rubros no pueden pasar del presupuesto mensual.
                    $error = validarRubroContraPresupuesto(
                        $conn,
                        $idUsuario,
                        $monto_proyectado,
                        $fecha_inicio,
                        $fecha_fin,
                        $rubroActual
                    );

                    if ($error === null) {
                        $actualizado = actualizarRubro(
                            $conn,
                            $idUsuario,
                            $id_rubro,
                            $id_categoria,
                            $nombre,
                            $monto_proyectado,
                            $fecha_inicio,
                            $fecha_fin
                        );
                    }
                }
            }

            $destino = $actualizado
                ? 'success=updated'
                : 'error=' . urlencode($error ?? 'No se pudo actualizar el rubro. Revisa los datos.');

            redirigir('views/rubros/index.php?' . $destino);
        }

        break;


    // ==========================================
    // ELIMINAR RUBRO
    // ==========================================
    case 'delete':

        $id = $_GET['id'] ?? null;

        if ($id) {

            $resultado = eliminarRubro($conn, $idUsuario, $id);

            if (!$resultado['success']) {

                $mensaje = urlencode($resultado['message']);
                redirigir('views/rubros/index.php?error=' . $mensaje);
                exit();
            }
            redirigir('views/rubros/index.php?success=deleted');
            exit();
        }
        redirigir('views/rubros/index.php');
        exit();

        break;


    // ==========================================
    // FORMULARIO DE CREACIÓN
    // ==========================================
    case 'create':

        // Límite alcanzado: el formulario ni se abre, se vuelve al listado con el aviso.
        $estadoCupo = obtenerEstadoCupoRubros($conn, (int) $idUsuario);
        if ($estadoCupo['alcanzado']) {
            redirigir('views/rubros/index.php', ['error' => rubro_mensaje_limite($estadoCupo)]);
        }

        $categorias = obtenerCategoriasParaSelect(
            $conn,
            $idUsuario
        );

        break;


    // ==========================================
    // FORMULARIO DE EDICIÓN
    // ==========================================
    case 'edit':

        $id = $_GET['id'] ?? null;

        $rubroEdit = null;

        $categorias = obtenerCategoriasParaSelect(
            $conn,
            $idUsuario
        );

        if ($id) {

            $rubroEdit = obtenerRubroPorId(
                $conn,
                $idUsuario,
                $id
            );
        }

        break;


    // ==========================================
    // LISTAR RUBROS
    // ==========================================
    default:

        $rubros = obtenerRubrosPorUsuario(
            $conn,
            $idUsuario
        );

        // Para la alerta y el botón "Nuevo Rubro" bloqueado de la vista.
        $estadoCupo = obtenerEstadoCupoRubros($conn, (int) $idUsuario);

        break;
}