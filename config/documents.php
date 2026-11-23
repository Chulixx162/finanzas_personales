<?php
// config/documents.php
// Catálogo de manuales y anexos. Los archivos viven en public/docs/ y se
// buscan por su nombre base con estas extensiones, en este orden.
// La documentación se escribe en Markdown (.md): se VE en el navegador con el
// visor views/docs/viewer.php. También se pueden ver los .pdf y .sql;
// los .docx y .xlsx solo se descargan.

const CARPETA_DOCUMENTOS = __DIR__ . '/../public/docs/';
const EXTENSIONES_DOCUMENTO = ['md', 'pdf', 'docx', 'xlsx', 'sql', 'jpeg', 'mp4'];

// Carpeta de los videos (public/video/). Un documento puede vivir ahí si en el
// catálogo se le indica 'video' como cuarto valor.
const CARPETA_VIDEOS = __DIR__ . '/../public/video/';

/**
 * Grupos de documentos: clave => [título, descripción, nombre base del archivo,
 * carpeta opcional]. Sin carpeta, el archivo está en public/docs/; con 'video',
 * en public/video/.
 */
function catalogo_documentos(): array
{
    return [
        'Manuales' => [
            'user_manual' => [
                'Manual de usuario',
                'Guía paso a paso para usar la página.',
                'user_manual',
            ],
            'technical_manual' => [
                'Manual técnico',
                'Estructura del proyecto, instalación y base de datos.',
                'technical_manual',
            ],
        ],
        'Anexos' => [
            'pac' => [
                'PAC',
                'Plan de Aseguramiento de Calidad del proyecto.',
                'pac',
            ],
            'srs' => [
                'SRS',
                'Especificación de requisitos de software.',
                'srs',
            ],
            'mer' => [
                'MER',
                'Modelo entidad-relación y diccionario de datos.',
                'mer',
            ],
            'error_log' => [
                'Log de errores y sugerencias',
                'Registro de errores encontrados y mejoras propuestas.',
                'error_log',
            ],
            'quality_instruments' => [
                'Instrumentos de calidad',
                'Formatos e instrumentos usados para verificar la calidad.',
                'quality_instruments',
            ],
            'team_presentation' => [
                'Video del equipo',
                'Presentación en video del equipo de desarrollo.',
                'team_presentation',
                'video',
            ],
        ],
    ];
}

/**
 * Busca el archivo de un documento por su clave.
 * Devuelve ['ruta', 'archivo', 'extension'] o null si la clave no existe
 * o el archivo todavía no está en public/docs/.
 */
function buscar_documento(string $clave): ?array
{
    foreach (catalogo_documentos() as $lista) {
        if (!isset($lista[$clave])) {
            continue;
        }

        $base    = $lista[$clave][2];
        $carpeta = (($lista[$clave][3] ?? '') === 'video') ? CARPETA_VIDEOS : CARPETA_DOCUMENTOS;

        foreach (EXTENSIONES_DOCUMENTO as $extension) {
            $ruta = $carpeta . $base . '.' . $extension;

            if (is_file($ruta)) {
                return [
                    'ruta'      => $ruta,
                    'archivo'   => $base . '.' . $extension,
                    'extension' => $extension,
                ];
            }
        }
    }

    return null;
}

/**
 * Devuelve el título de un documento del catálogo por su clave ('' si no existe).
 */
function titulo_documento(string $clave): string
{
    foreach (catalogo_documentos() as $lista) {
        if (isset($lista[$clave])) {
            return $lista[$clave][0];
        }
    }

    return '';
}