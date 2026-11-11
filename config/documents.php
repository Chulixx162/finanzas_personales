<?php
// config/documents.php
// Catálogo de manuales y anexos. Los archivos viven en public/docs/ y se
// buscan por su nombre base con estas extensiones, en este orden.
// Para que un documento se pueda VER en el navegador, súbelo en PDF
// (el .sql también se ve como texto). Los .docx y .xlsx solo se descargan.

const CARPETA_DOCUMENTOS = __DIR__ . '/../public/docs/';
const EXTENSIONES_DOCUMENTO = ['pdf', 'docx', 'xlsx', 'sql'];

/**
 * Grupos de documentos: clave => [título, descripción, nombre base del archivo].
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
                'PAC',
            ],
            'srs' => [
                'SRS',
                'Especificación de requisitos de software.',
                'SRS',
            ],
            'mer' => [
                'MER',
                'Modelo entidad-relación y diccionario de datos.',
                'mer',
            ],
            'sql' => [
                'Script SQL',
                'Tablas y procedimientos almacenados de la base de datos.',
                'schema',
            ],
            'error_log' => [
                'Log de errores y sugerencias',
                'Registro de errores encontrados y mejoras propuestas.',
                'error_suggestions_log',
            ],
            'quality_instruments' => [
                'Instrumentos de calidad',
                'Formatos e instrumentos usados para verificar la calidad.',
                'quality_instruments',
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

        $base = $lista[$clave][2];

        foreach (EXTENSIONES_DOCUMENTO as $extension) {
            $ruta = CARPETA_DOCUMENTOS . $base . '.' . $extension;

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