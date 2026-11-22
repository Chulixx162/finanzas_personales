package com.finanzas.errors;

import java.util.concurrent.Callable;
import java.util.function.Consumer;

/**
 * Manejador global de errores para la parte Java.
 *
 * Qué hace:
 *   1. Captura cualquier excepción que escape de un hilo (Thread.setDefaultUncaughtExceptionHandler).
 *   2. La anota en storage/logs/error_log.md (mismo archivo y formato que PHP).
 *   3. Le muestra a la persona un mensaje amable con la referencia ERR-..., nunca la traza.
 *
 * Instalación: una sola línea al arrancar la aplicación (main, ServletContextListener, etc.):
 *
 *     GlobalErrorHandler.instalar();
 *
 * Uso en bloques try/catch que quieren dejar constancia y seguir:
 *
 *     try { ... } catch (Exception e) {
 *         String mensaje = GlobalErrorHandler.manejar(e, "Generar reporte");
 *         mostrarAlUsuario(mensaje);
 *     }
 *
 * O envolviendo la tarea completa:
 *
 *     Integer total = GlobalErrorHandler.ejecutarSeguro(() -> calcularTotal(), 0, "Calcular total");
 *
 * Requiere Java 11 o superior. No depende de librerías externas.
 */
public final class GlobalErrorHandler implements Thread.UncaughtExceptionHandler {

    /** Mensaje para la persona. %s = referencia del error. */
    public static final String MENSAJE_AMIGABLE =
            "Ocurrió un problema inesperado. Ya quedó registrado y lo vamos a revisar. "
          + "Si continúa, comunícate con soporte e indica la referencia %s.";

    private static volatile GlobalErrorHandler actual;

    private final ErrorLogWriter log;
    private final Consumer<String> notificador;
    private final Thread.UncaughtExceptionHandler anterior;

    private GlobalErrorHandler(ErrorLogWriter log, Consumer<String> notificador,
                               Thread.UncaughtExceptionHandler anterior) {
        this.log = log;
        this.notificador = notificador;
        this.anterior = anterior;
    }

    /** Instala el manejador con la ruta por defecto y el mensaje por consola (System.err). */
    public static void instalar() {
        instalar(ErrorLogWriter.porDefecto(), System.err::println);
    }

    /**
     * Instala el manejador.
     *
     * @param log         dónde se anotan los errores
     * @param notificador cómo se le avisa a la persona (consola, JOptionPane, etc.);
     *                    recibe el mensaje amable ya con la referencia
     */
    public static void instalar(ErrorLogWriter log, Consumer<String> notificador) {
        GlobalErrorHandler manejador = new GlobalErrorHandler(
                log, notificador, Thread.getDefaultUncaughtExceptionHandler());
        actual = manejador;
        Thread.setDefaultUncaughtExceptionHandler(manejador);
    }

    /** Excepción que escapó de un hilo: se anota y se avisa; la aplicación no se cae por esto. */
    @Override
    public void uncaughtException(Thread hilo, Throwable error) {
        try {
            String ref = log.registrar("Excepción no capturada", error, "Hilo " + hilo.getName());
            notificador.accept(String.format(MENSAJE_AMIGABLE, ref));
        } catch (Throwable fallo) {
            // El manejador nunca debe lanzar: si falla, delega en el anterior (si existe).
            if (anterior != null) {
                anterior.uncaughtException(hilo, error);
            }
        }
    }

    /**
     * Para catch explícitos: anota el error y devuelve el mensaje amable listo para mostrar.
     * Si aún no se llamó a instalar(), usa la configuración por defecto.
     */
    public static String manejar(Throwable error, String contexto) {
        ErrorLogWriter escritor = actual != null ? actual.log : ErrorLogWriter.porDefecto();
        String ref = escritor.registrar("Excepción capturada", error, contexto);
        return String.format(MENSAJE_AMIGABLE, ref);
    }

    /**
     * Ejecuta la tarea; si falla, la anota y devuelve el valor por defecto en lugar de propagar el error.
     */
    public static <T> T ejecutarSeguro(Callable<T> tarea, T valorPorDefecto, String contexto) {
        try {
            return tarea.call();
        } catch (Exception error) { // los Error graves (OutOfMemoryError...) sí se propagan
            String mensaje = manejar(error, contexto);
            if (actual != null) {
                actual.notificador.accept(mensaje);
            }
            return valorPorDefecto;
        }
    }
}