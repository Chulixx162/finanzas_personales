<?php
// config/env.php
// Lectura de variables de entorno. Orden de prioridad:
//   1. Variables reales del proceso (Docker: `environment:` / `env_file:`).
//   2. Archivo .env (Laragon / desarrollo local).
//   3. Valor por defecto.
// Así el mismo código corre en Laragon y en un contenedor sin tocar nada.
//
// Nota: con php.ini de producción `variables_order` no incluye "E", y $_ENV llega
// vacío en Docker. Por eso se lee con getenv() primero.

$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (is_file($autoload)) {
    require_once $autoload;

    if (class_exists(\Dotenv\Dotenv::class)) {
        // createImmutable no pisa las variables que ya existen (las de Docker mandan).
        \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
    }
}

/**
 * Valor de una variable de entorno (o $defecto si no existe o está vacía).
 *
 * @param mixed $defecto
 * @return mixed
 */
function app_env(string $clave, $defecto = null)
{
    $valor = getenv($clave);

    if ($valor === false) {
        $valor = $_ENV[$clave] ?? $_SERVER[$clave] ?? false;
    }

    if ($valor === false || $valor === null || $valor === '') {
        return $defecto;
    }

    return $valor;
}