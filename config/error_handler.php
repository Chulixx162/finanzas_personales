<?php
// config/error_handler.php
// Manejador global de errores y excepciones.
//
// Qué hace:
//   1. Captura errores (warnings, notices...), excepciones no capturadas y errores fatales
//      del servidor, y los errores del navegador que envía public/js/error_logger.js
//      (a través de controllers/clientErrorController.php).
//   2. Los anota en storage/logs/error_log.md (Markdown), con una referencia ERR-AAAAMMDD-XXXXXX.
//   3. Le muestra a la persona una página amigable (controllers/errorController.php +
//      views/errors/error.php) en lugar de una pantalla en blanco o un volcado técnico.
//
// Se activa solo: config/app.php lo carga, y app.php lo cargan config/session.php y
// config/connection.php, o sea todos los controladores y vistas con lógica.
//
// Variables de entorno opcionales (.env):
//   ERROR_LOG_PATH   ruta del archivo de log (por defecto storage/logs/error_log.md)

final class ManejadorErrores
{
    /** Máximo de entradas que una sola petición puede escribir (evita llenar el disco en un bucle). */
    private const MAX_ENTRADAS_POR_PETICION = 25;

    /** Al superar este tamaño el log se archiva y se empieza uno nuevo. */
    private const TAMANO_MAX_LOG = 2097152; // 2 MB

    /** Cuántos logs archivados se conservan. */
    private const ARCHIVOS_ROTADOS = 5;

    /** Niveles que terminan el script: se atienden en el cierre (shutdown). */
    private const NIVELES_FATALES = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];

    private static bool $instalado = false;
    private static bool $respondido = false;
    private static bool $dentro = false;      // evita recursión si el propio manejador falla
    private static int $escritas = 0;
    private static bool $avisoLimite = false;
    private static array $vistos = [];         // huella => referencia (evita duplicados)
    private static ?string $reserva = null;    // memoria de emergencia para errores "memory exhausted"

    /* ==========================================================
       Instalación
       ========================================================== */

    public static function instalar(): void
    {
        if (self::$instalado) {
            return;
        }
        self::$instalado = true;

        // Memoria de reserva: se libera en el cierre para poder dibujar la página de error.
        self::$reserva = str_repeat('x', 65536);

        // Con el buffer activo, si algo falla a mitad de página se descarta lo que ya se
        // había escrito y la persona no ve media página rota. Los controladores que
        // hacen streaming (documentsController) ya vacían los buffers por su cuenta.
        if (PHP_SAPI !== 'cli' && ob_get_level() === 0) {
            ob_start();
        }

        set_error_handler([self::class, 'manejarError']);
        set_exception_handler([self::class, 'manejarExcepcion']);
        register_shutdown_function([self::class, 'manejarCierre']);
    }

    /* ==========================================================
       Manejadores registrados en PHP
       ========================================================== */

    /**
     * Warnings, notices y deprecations: se REGISTRAN pero no detienen la página
     * (así el proyecto se comporta igual que antes). Solo E_USER_ERROR y
     * E_RECOVERABLE_ERROR se convierten en excepción.
     */
    public static function manejarError(int $nivel, string $mensaje, string $archivo = '', int $linea = 0): bool
    {
        // Respeta error_reporting() y el operador @.
        if (!(error_reporting() & $nivel)) {
            return false;
        }

        if (in_array($nivel, [E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
            throw new ErrorException($mensaje, 0, $nivel, $archivo, $linea);
        }

        if (self::$dentro) {
            return false;
        }

        self::$dentro = true;
        try {
            $traza = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
            array_shift($traza); // quita el marco del propio manejador

            self::registrar(
                'Aviso de PHP (' . self::nombreNivel($nivel) . ')',
                self::nombreNivel($nivel),
                $mensaje,
                $archivo,
                $linea,
                $traza
            );
        } catch (Throwable $e) {
            // Registrar nunca debe romper la página.
        } finally {
            self::$dentro = false;
        }

        // Desarrollo: PHP además lo muestra en pantalla como siempre.
        // Producción: se queda solo en el log, la persona no ve nada.
        return !app_debug();
    }

    /** Excepciones que nadie capturó. */
    public static function manejarExcepcion(Throwable $e): void
    {
        $ref = self::registrarExcepcion($e, 'Excepción no capturada');
        self::responder(500, $ref, self::detalleDepuracion($e));
    }

    /** Errores fatales (E_ERROR, parse error en un include, memoria agotada...). */
    public static function manejarCierre(): void
    {
        $error = error_get_last();

        if ($error === null || !in_array($error['type'], self::NIVELES_FATALES, true) || self::$respondido) {
            return;
        }

        self::$reserva = null; // libera memoria para poder registrar y dibujar la página

        $ref = self::registrar(
            'Error fatal de PHP',
            self::nombreNivel((int) $error['type']),
            (string) $error['message'],
            (string) $error['file'],
            (int) $error['line'],
            []
        );

        $detalle = app_debug()
            ? $error['message'] . "\n" . self::rutaRelativa((string) $error['file']) . ':' . $error['line']
            : null;

        self::responder(500, $ref, $detalle);
    }

    /* ==========================================================
       API pública
       ========================================================== */

    /**
     * Para usar en bloques catch que quieren dejar constancia y seguir:
     *
     *   try { ... } catch (Throwable $e) {
     *       $ref = ManejadorErrores::registrarExcepcion($e);
     *       redirigir('views/rubros/index.php', ['error' => 'No se pudo guardar. Ref: ' . $ref]);
     *   }
     *
     * Devuelve la referencia ERR-... para mostrársela a la persona.
     */
    public static function registrarExcepcion(Throwable $e, string $titulo = 'Excepción capturada'): string
    {
        try {
            $mensaje = $e->getMessage();
            $previa  = $e->getPrevious();

            if ($previa !== null) {
                $mensaje .= ' | Causa: ' . get_class($previa) . ': ' . $previa->getMessage();
            }

            return self::registrar($titulo, get_class($e), $mensaje, $e->getFile(), $e->getLine(), $e->getTrace());
        } catch (Throwable $falloAlRegistrar) {
            return self::nuevaReferencia();
        }
    }

    /**
     * Anota un error que ocurrió en el NAVEGADOR (lo envía public/js/error_logger.js).
     * Todo lo que llega del cliente se trata como texto no confiable: se recorta y se
     * limpia en registrar() (una línea, sin acentos graves, largo acotado).
     * Devuelve la referencia ERR-... para mostrársela a la persona.
     */
    public static function registrarCliente(
        string $tipo,
        string $mensaje,
        string $archivo,
        int $linea,
        int $columna,
        string $traza,
        string $pagina
    ): string {
        try {
            $ubicacion = $archivo !== '' ? $archivo . ':' . $linea . ':' . $columna : 'desconocida';

            return self::registrar(
                'Error de JavaScript',
                $tipo,
                $mensaje,
                $ubicacion,
                0,
                [],
                'JavaScript',
                'GET ' . $pagina,
                $traza
            );
        } catch (Throwable $falloAlRegistrar) {
            return self::nuevaReferencia();
        }
    }

    /** Ruta absoluta del archivo de log. */
    public static function rutaLog(): string
    {
        $ruta = (string) app_env('ERROR_LOG_PATH', '');

        if ($ruta === '') {
            return dirname(__DIR__) . '/storage/logs/error_log.md';
        }

        // Ruta relativa: se interpreta desde la raíz del proyecto.
        $esAbsoluta = $ruta[0] === '/' || $ruta[0] === '\\' || preg_match('/^[A-Za-z]:[\\\\\/]/', $ruta) === 1;

        return $esAbsoluta ? $ruta : dirname(__DIR__) . '/' . $ruta;
    }

    /* ==========================================================
       Registro en Markdown
       ========================================================== */

    /**
     * Escribe una entrada en el log y devuelve su referencia.
     * Si el mismo error ya se anotó en esta petición, devuelve la referencia anterior.
     */
    private static function registrar(
        string $titulo,
        string $tipo,
        string $mensaje,
        string $archivo,
        int $linea,
        array $traza,
        string $origen = 'PHP',
        ?string $peticion = null,
        string $trazaTexto = ''
    ): string {
        $huella = md5($origen . '|' . $tipo . '|' . $archivo . '|' . $linea . '|' . $mensaje);

        if (isset(self::$vistos[$huella])) {
            return self::$vistos[$huella];
        }

        $ref = self::nuevaReferencia();
        self::$vistos[$huella] = $ref;

        if (self::$escritas >= self::MAX_ENTRADAS_POR_PETICION) {
            if (!self::$avisoLimite) {
                self::$avisoLimite = true;
                self::escribir("### Aviso · límite de entradas por petición\n"
                    . '- **Fecha:** ' . date('Y-m-d H:i:s') . "\n"
                    . '- **Petición:** ' . self::peticion() . "\n"
                    . '- Se alcanzó el máximo de ' . self::MAX_ENTRADAS_POR_PETICION
                    . " entradas; el resto de errores de esta petición no se anotó.\n\n---\n\n");
            }

            return $ref;
        }

        self::$escritas++;

        $usuario = (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id']))
            ? 'id ' . (int) $_SESSION['user_id']
            : 'sin sesión';

        $entrada = '### ' . $ref . ' · ' . self::linea($titulo) . "\n"
            . '- **Fecha:** ' . date('Y-m-d H:i:s') . ' (' . date_default_timezone_get() . ")\n"
            . '- **Origen:** ' . self::linea($origen) . "\n"
            . '- **Tipo:** `' . self::linea($tipo) . "`\n"
            . '- **Mensaje:** `' . self::linea($mensaje) . "`\n"
            . '- **Ubicación:** `' . self::linea(self::rutaRelativa($archivo)) . ($linea > 0 ? ':' . $linea : '') . "`\n"
            . '- **Petición:** `' . ($peticion !== null ? self::linea($peticion) : self::peticion()) . "`\n"
            . '- **Usuario:** ' . $usuario . "\n"
            . '- **IP:** `' . self::linea((string) ($_SERVER['REMOTE_ADDR'] ?? 'cli')) . "`\n"
            . "- **Estado:** Pendiente de revisión\n";

        $textoTraza = $trazaTexto !== '' ? self::limitar($trazaTexto) : self::formatearTraza($traza);

        if ($textoTraza !== '') {
            $entrada .= "- **Traza:**\n\n" . self::bloque($textoTraza) . "\n";
        }

        $entrada .= "\n---\n\n";

        if (!self::escribir($entrada)) {
            // Sin acceso al archivo: al menos que quede en el log del servidor / contenedor.
            error_log('[' . $ref . '] ' . $tipo . ': ' . $mensaje . ' en ' . $archivo . ':' . $linea);
        }

        return $ref;
    }

    private static function escribir(string $entrada): bool
    {
        $ruta = self::rutaLog();
        $dir  = dirname($ruta);

        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return false;
        }

        self::rotarSiHaceFalta($ruta);

        $h = @fopen($ruta, 'ab');

        if ($h === false) {
            return false;
        }

        try {
            if (!flock($h, LOCK_EX)) {
                return false;
            }

            $estado = fstat($h);

            if (($estado['size'] ?? 0) === 0) {
                fwrite($h, self::cabecera());
            }

            fwrite($h, $entrada);
            fflush($h);
            flock($h, LOCK_UN);

            return true;
        } finally {
            fclose($h);
        }
    }

    private static function cabecera(): string
    {
        return "# LOG AUTOMÁTICO DE ERRORES\n\n"
            . "> Generado por `config/error_handler.php` (servidor) y `public/js/error_logger.js` (navegador).\n"
            . "> No lo edites a mano: cada entrada tiene una referencia `ERR-AAAAMMDD-XXXXXX`\n"
            . "> que la persona ve en pantalla y que sirve para encontrar el error aquí.\n\n---\n\n";
    }

    /** Archiva el log cuando pesa demasiado y borra los archivados más viejos. */
    private static function rotarSiHaceFalta(string $ruta): void
    {
        clearstatcache(true, $ruta);

        if (!is_file($ruta) || (int) @filesize($ruta) < self::TAMANO_MAX_LOG) {
            return;
        }

        $archivo = dirname($ruta) . '/' . pathinfo($ruta, PATHINFO_FILENAME) . '-' . date('Ymd-His') . '.md';
        @rename($ruta, $archivo);

        $viejos = glob(dirname($ruta) . '/' . pathinfo($ruta, PATHINFO_FILENAME) . '-*.md') ?: [];
        sort($viejos);

        foreach (array_slice($viejos, 0, max(0, count($viejos) - self::ARCHIVOS_ROTADOS)) as $viejo) {
            @unlink($viejo);
        }
    }

    /* ==========================================================
       Respuesta a la persona
       ========================================================== */

    private static function responder(int $codigo, string $ref, ?string $detalle): void
    {
        if (self::$respondido) {
            return;
        }
        self::$respondido = true;

        try {
            require_once dirname(__DIR__) . '/controllers/errorController.php';
            mostrar_error($codigo, $ref, $detalle);
        } catch (Throwable $e) {
            self::respuestaMinima($codigo, $ref);
        }
    }

    /** Último recurso: HTML mínimo sin dependencias, por si falla el controlador o la vista. */
    private static function respuestaMinima(int $codigo, string $ref): void
    {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        if (!headers_sent()) {
            http_response_code($codigo);
            header('Content-Type: text/html; charset=utf-8');
        }

        echo '<!DOCTYPE html><html lang="es"><meta charset="utf-8"><title>Algo salió mal</title>'
            . '<body style="font-family:system-ui,sans-serif;max-width:560px;margin:15vh auto;padding:0 20px">'
            . '<h1>Algo salió mal</h1>'
            . '<p>Tuvimos un problema y ya quedó registrado. Inténtalo de nuevo en unos minutos.</p>'
            . '<p>Referencia: <strong>' . htmlspecialchars($ref, ENT_QUOTES, 'UTF-8') . '</strong></p>'
            . '</body></html>';
    }

    /* ==========================================================
       Utilidades
       ========================================================== */

    private static function detalleDepuracion(Throwable $e): ?string
    {
        if (!app_debug()) {
            return null; // en producción nunca se muestran detalles técnicos
        }

        return get_class($e) . ': ' . $e->getMessage() . "\n"
            . self::rutaRelativa($e->getFile()) . ':' . $e->getLine() . "\n\n"
            . self::formatearTraza($e->getTrace());
    }

    private static function nuevaReferencia(): string
    {
        return 'ERR-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    /**
     * Petición resumida. Solo método y ruta: la query string se omite a propósito
     * porque puede llevar tokens (recuperar contraseña) u otros datos privados.
     */
    private static function peticion(): string
    {
        if (PHP_SAPI === 'cli') {
            return 'CLI ' . self::linea((string) ($_SERVER['argv'][0] ?? ''));
        }

        $metodo = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $ruta   = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);

        return self::linea($metodo . ' ' . $ruta);
    }

    /** Traza sin argumentos (los argumentos pueden contener contraseñas). */
    private static function formatearTraza(array $traza): string
    {
        $lineas = [];

        foreach (array_slice($traza, 0, 15) as $i => $marco) {
            $donde = isset($marco['file'])
                ? self::rutaRelativa((string) $marco['file']) . ':' . ($marco['line'] ?? '?')
                : '[código interno]';

            $funcion = ($marco['class'] ?? '') . ($marco['type'] ?? '') . ($marco['function'] ?? '');

            $lineas[] = '#' . $i . ' ' . $donde . ($funcion !== '' ? ' → ' . $funcion . '()' : '');
        }

        return implode("\n", $lineas);
    }

    private static function rutaRelativa(string $ruta): string
    {
        $raiz = str_replace('\\', '/', (string) realpath(dirname(__DIR__)));
        $ruta = str_replace('\\', '/', $ruta);

        return ($raiz !== '' && strpos($ruta, $raiz . '/') === 0)
            ? substr($ruta, strlen($raiz) + 1)
            : $ruta;
    }

    /** Una sola línea, sin acentos graves que rompan el Markdown y con largo acotado. */
    private static function linea(string $texto, int $max = 600): string
    {
        $texto = trim((string) preg_replace('/\s+/u', ' ', $texto));
        $texto = str_replace('`', "'", $texto);

        return mb_strlen($texto) > $max ? mb_substr($texto, 0, $max) . '…' : $texto;
    }

    /** Texto multilínea acotado (traza que llega del navegador). */
    private static function limitar(string $texto, int $max = 3000): string
    {
        $texto = trim($texto);

        return mb_strlen($texto) > $max ? mb_substr($texto, 0, $max) . '…' : $texto;
    }

    /** Bloque de código Markdown; neutraliza ``` dentro del contenido. */
    private static function bloque(string $texto): string
    {
        return "```text\n" . str_replace('```', "'''", $texto) . "\n```";
    }

    private static function nombreNivel(int $nivel): string
    {
        $nombres = [
            E_ERROR             => 'E_ERROR',
            E_WARNING           => 'E_WARNING',
            E_PARSE             => 'E_PARSE',
            E_NOTICE            => 'E_NOTICE',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_USER_ERROR        => 'E_USER_ERROR',
            E_USER_WARNING      => 'E_USER_WARNING',
            E_USER_NOTICE       => 'E_USER_NOTICE',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED        => 'E_DEPRECATED',
            E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
        ];

        return $nombres[$nivel] ?? 'E_' . $nivel;
    }
}

ManejadorErrores::instalar();