package com.finanzas.errors;

import java.io.IOException;
import java.nio.ByteBuffer;
import java.nio.channels.FileChannel;
import java.nio.channels.FileLock;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardOpenOption;
import java.security.SecureRandom;
import java.time.ZoneId;
import java.time.ZonedDateTime;
import java.time.format.DateTimeFormatter;

/**
 * Escribe errores de Java en el MISMO archivo Markdown que usa PHP
 * (storage/logs/error_log.md) y con el mismo formato de entrada.
 *
 * Ruta del archivo, en orden de prioridad:
 *   1. propiedad del sistema  -Dfinanzas.errorlog=/ruta/error_log.md
 *   2. variable de entorno    ERROR_LOG_PATH
 *   3. storage/logs/error_log.md relativo al directorio de trabajo
 *
 * Usa un bloqueo de archivo (FileLock) para no mezclar entradas con las que
 * escribe PHP al mismo tiempo. La rotación por tamaño la hace PHP.
 */
public final class ErrorLogWriter {

    private static final DateTimeFormatter FECHA = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss");
    private static final DateTimeFormatter DIA = DateTimeFormatter.ofPattern("yyyyMMdd");
    private static final SecureRandom AZAR = new SecureRandom();
    private static final Object CERROJO = new Object();
    private static final int MAX_MARCOS = 15;

    private final Path ruta;
    private final ZoneId zona;

    public ErrorLogWriter(Path ruta) {
        this.ruta = ruta;
        String tz = System.getenv("APP_TIMEZONE");
        ZoneId z;
        try {
            z = ZoneId.of(tz == null || tz.isBlank() ? "America/Bogota" : tz);
        } catch (Exception e) {
            z = ZoneId.of("America/Bogota");
        }
        this.zona = z;
    }

    /** Resuelve la ruta con las reglas de la documentación de la clase. */
    public static ErrorLogWriter porDefecto() {
        String ruta = System.getProperty("finanzas.errorlog");
        if (ruta == null || ruta.isBlank()) {
            ruta = System.getenv("ERROR_LOG_PATH");
        }
        if (ruta == null || ruta.isBlank()) {
            ruta = "storage/logs/error_log.md";
        }
        return new ErrorLogWriter(Paths.get(ruta));
    }

    /**
     * Anota el error y devuelve su referencia (ERR-AAAAMMDD-XXXXXX).
     * Nunca lanza excepciones: si no puede escribir el archivo, lo deja en System.err.
     */
    public String registrar(String titulo, Throwable error, String contexto) {
        String ref = nuevaReferencia();

        try {
            String entrada = construirEntrada(ref, titulo, error, contexto);
            escribir(entrada);
        } catch (Throwable fallo) {
            System.err.println("[" + ref + "] " + error + " (no se pudo escribir " + ruta + ": " + fallo + ")");
        }

        return ref;
    }

    /* ---------------- Construcción de la entrada ---------------- */

    private String construirEntrada(String ref, String titulo, Throwable error, String contexto) {
        StackTraceElement[] marcos = error.getStackTrace();
        String ubicacion = marcos.length > 0 ? marcos[0].toString() : "desconocida";

        StringBuilder sb = new StringBuilder();
        sb.append("### ").append(ref).append(" · ").append(linea(titulo)).append('\n');
        sb.append("- **Fecha:** ").append(FECHA.format(ZonedDateTime.now(zona)))
          .append(" (").append(zona).append(")\n");
        sb.append("- **Origen:** Java\n");
        sb.append("- **Tipo:** `").append(linea(error.getClass().getName())).append("`\n");
        sb.append("- **Mensaje:** `").append(linea(String.valueOf(error.getMessage()))).append("`\n");
        sb.append("- **Ubicación:** `").append(linea(ubicacion)).append("`\n");
        sb.append("- **Petición:** `").append(linea(contexto == null || contexto.isBlank()
                ? "Hilo " + Thread.currentThread().getName() : contexto)).append("`\n");
        sb.append("- **Usuario:** sin sesión\n");
        sb.append("- **IP:** `local`\n");
        sb.append("- **Estado:** Pendiente de revisión\n");
        sb.append("- **Traza:**\n\n");
        sb.append(bloque(traza(error))).append("\n\n---\n\n");
        return sb.toString();
    }

    /** Traza con la cadena de causas, sin argumentos ni valores de variables. */
    private static String traza(Throwable error) {
        StringBuilder sb = new StringBuilder();
        Throwable actual = error;
        int profundidad = 0;

        while (actual != null && profundidad < 5) {
            if (profundidad > 0) {
                sb.append("Causado por: ").append(actual.getClass().getName())
                  .append(": ").append(actual.getMessage()).append('\n');
            }
            StackTraceElement[] marcos = actual.getStackTrace();
            for (int i = 0; i < Math.min(marcos.length, MAX_MARCOS); i++) {
                sb.append('#').append(i).append(' ').append(marcos[i]).append('\n');
            }
            actual = actual.getCause() == actual ? null : actual.getCause();
            profundidad++;
        }

        return sb.toString().stripTrailing();
    }

    /* ---------------- Escritura con bloqueo ---------------- */

    @SuppressWarnings("try") // el FileLock solo se usa por su efecto (bloqueo), no se referencia
    private void escribir(String entrada) throws IOException {
        synchronized (CERROJO) {
            Path dir = ruta.toAbsolutePath().getParent();
            if (dir != null) {
                Files.createDirectories(dir);
            }

            try (FileChannel canal = FileChannel.open(ruta,
                    StandardOpenOption.CREATE, StandardOpenOption.WRITE, StandardOpenOption.APPEND);
                 FileLock candado = canal.lock()) {

                if (canal.size() == 0) {
                    canal.write(ByteBuffer.wrap(cabecera().getBytes(StandardCharsets.UTF_8)));
                }
                canal.write(ByteBuffer.wrap(entrada.getBytes(StandardCharsets.UTF_8)));
                canal.force(false);
            }
        }
    }

    private static String cabecera() {
        return "# LOG AUTOMÁTICO DE ERRORES\n\n"
             + "> Generado por `config/error_handler.php` (PHP) y `GlobalErrorHandler` (Java).\n"
             + "> No lo edites a mano: cada entrada tiene una referencia `ERR-AAAAMMDD-XXXXXX`\n"
             + "> que la persona ve en pantalla y que sirve para encontrar el error aquí.\n\n---\n\n";
    }

    /* ---------------- Utilidades ---------------- */

    private String nuevaReferencia() {
        byte[] b = new byte[3];
        AZAR.nextBytes(b);
        StringBuilder hex = new StringBuilder();
        for (byte x : b) {
            hex.append(String.format("%02X", x));
        }
        return "ERR-" + DIA.format(ZonedDateTime.now(zona)) + "-" + hex;
    }

    /** Una sola línea, sin acentos graves que rompan el Markdown y con largo acotado. */
    private static String linea(String texto) {
        String limpio = texto == null ? "" : texto.replaceAll("\\s+", " ").trim().replace('`', '\'');
        return limpio.length() > 600 ? limpio.substring(0, 600) + "…" : limpio;
    }

    /** Bloque de código Markdown; neutraliza ``` dentro del contenido. */
    private static String bloque(String texto) {
        return "```text\n" + texto.replace("```", "'''") + "\n```";
    }
}