# LOG DE ERRORES Y SUGERENCIAS
## Sistema de Gestión Financiera Personal y Presupuestos
**Instructor:**

José de Jesús Motta Vargas

**Integrantes:**

Julio Andrés Casanova Ramírez

Laura Daniela Isanoa Estrada

Karoll Nikol Coronado Bermeo

Juan Manuel Barreiro Polo

Jaime Andrés Ladino Montealegre

**Programa:**

Análisis y Desarrollo de Software (ADSO)

**Centro de formación:**

Centro de la Industria, la Empresa y los Servicios – SENA

**Ficha:**

3407799

Neiva – Huila

## Introducción y Propósito del Documento
Este documento consolida el **Log de Defectos y Registro de Errores de Software (Bug Log)** identificados durante las revisiones de diseño, inspecciones de código y ciclos de prueba del *Sistema de Gestión Financiera Personal y Presupuestos*. Los casos reportados están alineados con la versión vigente de la Especificación de Requisitos de Software (SRS v2) y el Plan de Aseguramiento de Calidad (PAC).

Cada registro sigue la estructura normalizada del **Anexo A del PAC**, detallando la severidad, el procedimiento de reproducción, la falla obtenida, el comportamiento esperado, la solución técnica implementada en el stack tecnológico (**PHP estructurado, MySQL/PDO-mysqli, HTML5 y CSS3 en Laragon**) y su correspondiente estado de verificación.

##

## 1. Registro Consolidado de Defectos y Errores (Bug Log)
### DEF-001: Inconsistencia documental en la estructura del modelo de datos
- **Fecha / Reportado por:** 2026-11-05 \| Julio Casanova (Líder del Proyecto)

- **Origen / Módulo:** Inspección documental 5.1 / SRS & PAC

- **Requisito afectado:** Estructura del Modelo E-R

- **Severidad:** **Media**

- **Descripción y pasos para reproducir:**

  1.  Comparar las secciones del SRS v1 donde se mencionaban 4 o 5 tablas contra el alcance de presupuestos.

  2.  Notar el uso ambiguo de "rubros" y "presupuestos" para referirse al mismo concepto.

- **Resultado obtenido:** Inconsistencia en la documentación técnica que generaba confusión entre desarrolladores y diseñadores de base de datos.

- **Resultado esperado:** Unificación del modelo en 6 tablas fijas (usuarios, categorias, movimientos, presupuestos, presupuesto_categoria y rubros), aclarando que solo los rubros tienen monto presupuestado.

- **Solución Aplicada:** Se actualizó la especificación al SRS v2 y PAC v2, estableciendo que la tabla intermedia presupuesto_categoria no almacena montos y los gastos se asocian de forma opcional a un rubro_id.

- **Estado:** Corregido / Cerrado

### DEF-002: Exceso de monto total del presupuesto mediante la suma de rubros
- **Fecha / Reportado por:** 2026-11-10 \| Karoll Coronado (Tester)

- **Origen / Módulo:** CP-38 / RF-06 (Gestión de Rubros)

- **Requisito afectado:** ERF-06.5 / HU-13

- **Severidad:** **Alta**

- **Descripción y pasos para reproducir:**

  1.  Autenticarse como usuario registrado y crear un presupuesto de \$1.000.000.

  2.  Asignar la categoría "Alimentación" y crear los rubros "Mercado" ($250.000)y"Restaurantes"($<!-- -->150.000). Total asignado: \$400.000.

  3.  Intentar agregar un nuevo rubro por \$700.000 (suma total deseada = \$1.100.000).

- **Resultado obtenido:** El sistema guardaba el registro en la tabla rubros o provocaba saldos negativos sin control en la interfaz.

- **Resultado esperado:** El servidor debe validar la suma total antes de insertar o editar, rechazando la acción si supera el monto del presupuesto y mostrando el mensaje: *"La suma de los rubros (*$1.100.000)superaelmontototaldelpresupuesto($*1.000.000) por \$100.000"*.

- **Solución Técnica Aplicada (Backend PHP):**

****// Validación en el servidor previa al INSERT / UPDATE en rubros

\$presupuesto_id = (int)\$\_POST\['presupuesto_id'\];

\$monto_nuevo_rubro = (float)\$\_POST\['monto'\];

// 1. Obtener el monto total del presupuesto

\$stmt = \$pdo-\>prepare("SELECT monto_total FROM presupuestos WHERE id = :id AND usuario_id = :user_id");

\$stmt-\>execute(\[':id' =\> \$presupuesto_id, ':user_id' =\> \$\_SESSION\['usuario_id'\]\]);

\$presupuesto = \$stmt-\>fetch();

// 2. Sumar el valor de los rubros existentes

\$stmtSum = \$pdo-\>prepare("SELECT COALESCE(SUM(r.monto), 0) AS total_rubros

FROM rubros r

JOIN presupuesto_categoria pc ON r.presupuesto_categoria_id = pc.id

WHERE pc.presupuesto_id = :p_id");

\$stmtSum-\>execute(\[':p_id' =\> \$presupuesto_id\]);

\$actual = \$stmtSum-\>fetch()\['total_rubros'\];

if ((\$actual + \$monto_nuevo_rubro) \> \$presupuesto\['monto_total'\]) {

\$excedido = (\$actual + \$monto_nuevo_rubro) - \$presupuesto\['monto_total'\];

\$error = "La suma de los rubros (\$" . number_format(\$actual + \$monto_nuevo_rubro, 2) . ") supera el monto total del presupuesto (\$" . number_format(\$presupuesto\['monto_total'\], 2) . ") por \$" . number_format(\$excedido, 2);

// Cancelar la transacción y retornar alerta a la vista

}

- 
  > **Estado:** Corregido / Verificado

### DEF-003: Vulnerabilidad IDOR (Insecure Direct Object Reference) en la manipulación de registros de otros usuarios
- **Fecha / Reportado por:** 2026-11-10 \| Karoll Coronado (Tester)

- **Origen / Módulo:** CP-22, CP-42, CP-54 / RF-09 (Seguridad y Aislamiento)

- **Requisito afectado:** ERF-09.4 / RNF-03 / HU-18

- **Severidad:** **Crítica**

- **Descripción y pasos para reproducir:**

  1.  Iniciar sesión como Usuario A.

  2.  Alterar manualmente el parámetro id en la URL o en peticiones POST (ejemplo: eliminar_movimiento.php?id=85, correspondiente a un registro del Usuario B).

- **Resultado obtenido:** El backend procesaba la solicitud y eliminaba/modificaba el registro del Usuario B sin validar la propiedad.

- **Resultado esperado:** El sistema debe restringir el acceso con una respuesta HTTP 403 o la redirección adecuada, impidiendo la lectura o alteración de datos ajenos.

- **Solución Técnica Aplicada (Backend SQL):**

****// Forzar la validación de la sesión del usuario en todas las consultas de modificación/eliminación

\$movimiento_id = (int)\$\_GET\['id'\];

\$usuario_id = (int)\$\_SESSION\['usuario_id'\];

\$stmt = \$pdo-\>prepare("DELETE FROM movimientos WHERE id = :id AND usuario_id = :usuario_id");

\$stmt-\>execute(\[':id' =\> \$movimiento_id, ':usuario_id' =\> \$usuario_id\]);

if (\$stmt-\>rowCount() === 0) {

http_response_code(403);

exit("Acceso denegado o el registro no existe.");

}

- 
  > **Estado:** Corregido / Cerrado

### DEF-004: Fallo de Integridad Referencial al eliminar un rubro asociado a movimientos
- **Fecha / Reportado por:** 2026-11-10 \| Jaime Ladino (QA / Soporte)

- **Origen / Módulo:** CP-69 / RF-03, RF-06 (Base de Datos)

- **Requisito afectado:** ERF-06.4 / ERF-03.1 / RNF-09

- **Severidad:** **Alta**

- **Descripción y pasos para reproducir:**

  1.  Registrar un gasto de \$50.000 vinculado al rubro "Restaurantes".

  2.  Eliminar el rubro "Restaurantes" desde el módulo de presupuestos.

- **Resultado obtenido:** Ocurrencia de error SQL (FOREIGN KEY constraint fails) o eliminación en cascada de los movimientos de gasto del usuario.

- **Resultado esperado:** El rubro debe eliminarse, pero los movimientos de gasto deben ser preservados. El campo rubro_id en movimientos debe actualizarse a NULL.

- **Solución Técnica Aplicada (DDL SQL):**

****-- Configurar adecuadamente la regla de llave foránea en MySQL

ALTER TABLE movimientos

DROP FOREIGN KEY fk_movimientos_rubro;

ALTER TABLE movimientos

ADD CONSTRAINT fk_movimientos_rubro

FOREIGN KEY (rubro_id) REFERENCES rubros(id)

ON DELETE SET NULL

ON UPDATE CASCADE;

- 
  > **Estado:** Corregido / Verificado

### DEF-005: Control ineficiente del tiempo de expiración de sesión
- **Fecha / Reportado por:** 2026-11-10 \| Karoll Coronado (Tester)

- **Origen / Módulo:** CP-08 / RF-01 (Autenticación y Sesión)

- **Requisito afectado:** RNF-05 / ERF-01.4

- **Severidad:** **Media**

- **Descripción y pasos para reproducir:**

  1.  Iniciar sesión en el sistema.

  2.  Dejar la ventana inactiva por más de 30 minutos.

  3.  Realizar una acción dentro del sistema.

- **Resultado obtenido:** La sesión continuaba activa debido a la falta de destrucción explícita por marcas de tiempo en servidor.

- **Resultado esperado:** Destruir la sesión y redirigir al formulario de autenticación tras 30 minutos de inactividad.

- **Solución Técnica Aplicada (Controlador PHP):**

****session_start();

\$inactividad = 1800; // 30 minutos expresados en segundos

if (isset(\$\_SESSION\['ultimo_acceso'\])) {

\$vida_sesion = time() - \$\_SESSION\['ultimo_acceso'\];

if (\$vida_sesion \> \$inactividad) {

session_unset();

session_destroy();

header("Location: login.php?mensaje=sesion_expirada");

exit();

}

}

\$\_SESSION\['ultimo_acceso'\] = time();

- 
  > **Estado:** Corregido / Cerrado

### DEF-006: Exposición de detalles técnicos y credenciales por fallo de conexión a BD
- **Fecha / Reportado por:** 2026-11-10 \| Laura Isanoa (Base de Datos)

- **Origen / Módulo:** CP-55 / RNF-11 (Manejo de Errores)

- **Requisito afectado:** RNF-11 / ERF-09.2

- **Severidad:** **Alta**

- **Descripción y pasos para reproducir:**

  1.  Detener el servicio de MySQL o MariaDB en Laragon.

  2.  Intentar ingresar a cualquier ruta del sistema web.

- **Resultado obtenido:** PHP lanzaba un error nativo no capturado (PDOException), revelando contraseñas, usuario local (root), puerto y rutas de carpetas.

- **Resultado esperado:** Ocultar detalles técnicos, registrar el fallo en logs del servidor y presentar un mensaje amigable al usuario.

- **Solución Técnica Aplicada (PHP / PDO):**

****// conexion.php

\$host = 'localhost';

\$db = 'finanzas_personales';

\$user = 'root';

\$pass = '';

\$charset = 'utf8mb4';

\$dsn = "mysql:host=\$host;dbname=\$db;charset=\$charset";

\$options = \[

PDO::ATTR_ERRMODE =\> PDO::ERRMODE_EXCEPTION,

PDO::ATTR_DEFAULT_FETCH_MODE =\> PDO::FETCH_ASSOC,

PDO::ATTR_EMULATE_PREPARES =\> false,

\];

try {

\$pdo = new PDO(\$dsn, \$user, \$pass, \$options);

} catch (\PDOException \$e) {

error_log("Error de conexión a BD: " . \$e-\>getMessage());

http_response_code(500);

include 'vistas/error_conexion.php'; // Pantalla limpia sin datos sensibles

exit();

}

- 
  > **Estado:** Corregido / Verificado

### DEF-007: Bucles de redirección y rutas rotas tras la pérdida de sesión
- **Fecha / Reportado por:** 2026-11-10 \| Julio Casanova (Líder del Proyecto)

- **Origen / Módulo:** CP-07, CP-08 / RF-01 (Control de Acceso)

- **Requisito afectado:** ERF-01.3 / ERF-01.4

- **Severidad:** **Media**

- **Descripción y pasos para reproducir:**

  1.  Intentar acceder a una subcarpeta interna sin haber iniciado sesión.

  2.  O presionar el botón "Atrás" del navegador tras haber finalizado la sesión.

- **Resultado obtenido:** Ocurrencia de redirecciones infinitas (ERR_TOO_MANY_REDIRECTS) o fallos 404 por el uso de rutas relativas inconsistentes (header("Location: login.php")).

- **Resultado esperado:** Redirección consistente hacia login.php mediante el uso de constantes globales de ruta base.

- **Solución Técnica Aplicada (PHP):**

****// Definición de ruta base del sistema

define('BASE_URL', '/sistema_finanzas/');

// Middleware de verificación de sesión

if (!isset(\$\_SESSION\['usuario_id'\])) {

header("Location: " . BASE_URL . "login.php?error=acceso_no_autorizado");

exit();

}

- 
  > **Estado:** Corregido / Cerrado

### DEF-008: Ausencia de parámetros GET en enlaces de edición y eliminación (Direccionamiento)
- **Fecha / Reportado por:** 2026-11-10 \| Karoll Coronado (Front-end)

- **Origen / Módulo:** RF-03, RF-06 / Vistas HTML

- **Requisito afectado:** ERF-03.3 / ERF-06.3 / HU-07

- **Severidad:** **Media**

- **Descripción y pasos para reproducir:**

  1.  Abrir la lista de movimientos o rubros.

  2.  Hacer clic en los botones de acción "Editar" o "Eliminar".

- **Resultado obtenido:** Redirección a la vista destino sin enviar el ID en la querystring, provocando avisos de PHP (Notice: Undefined index: id) e imposibilitando la carga del registro.

- **Resultado esperado:** Todas las acciones deben concatenar de forma segura el identificador numérico validado.

- **Solución Técnica Aplicada (HTML / PHP):**

****\<!-- Enlace de edición en la vista de listado --\>

\<a href="editar_movimiento.php?id=\<?php echo (int)\$movimiento\['id'\]; ?\>" class="btn-editar"\>Editar\</a\>



// Validación en el archivo receptor editar_movimiento.php

if (!isset(\$\_GET\['id'\]) \|\| !is_numeric(\$\_GET\['id'\])) {

header("Location: movimientos.php?error=id_invalido");

exit();

}

\$id_movimiento = (int)\$\_GET\['id'\];

- 
  > **Estado:** Corregido / Verificado

### DEF-009: Conflictos de integración de código al ejecutar Git Pull entre integrantes
- **Fecha / Reportado por:** 2026-11-10 \| Karoll Coronado (Front-end y Tester)

- **Origen / Módulo:** Sección 3.3 del PAC / Control de Versiones (Git)

- **Requisito afectado:** RNF-19 / Estándares de Control de Versiones

- **Severidad:** **Media**

- **Descripción y pasos para reproducir:**

  1.  Dos integrantes modifican simultáneamente el mismo archivo (ejemplo: index.php o conexion.php) en sus respectivas ramas locales.

  2.  Ejecutar git pull origin main o intentar fusionar (git merge).

- **Resultado obtenido:** El repositorio bloquea la fusión e inserta marcadores de conflicto (\<\<\<\<\<\<\< HEAD, =======, \>\>\>\>\>\>\> branch_name), desconfigurando la ejecución del código PHP.

- **Resultado esperado:** Integración limpia respetando la rama principal estable, previa resolución manual por los involucrados.

- **Solución Aplicada (Procedimiento Git):**

****\# 1. Identificar archivos en conflicto

git status

\# 2. Abrir el archivo, revisar y seleccionar la versión válida limpiando las marcas de Git

\# 3. Validar localmente en Laragon y confirmar los cambios

git add \<archivo_solucionado.php\>

git commit -m "Fix: Resolución de conflictos de merge en el módulo correspondiente"

git push origin \<rama_trabajo\>

- 
  > **Estado:** Corregido / Verificado

### DEF-010: Conflictos de puertos en Laragon (Apache 80/443 y MySQL 3306) impiden iniciar el servidor
- **Fecha / Reportado por:** 2026-11-10 \| Laura Isanoa (Base de Datos) / Juan Manuel Barreiro (Back-end)

- **Origen / Módulo:** Sección 12 del PAC (Riesgos del Entorno Local) / Entorno Laragon (Windows)

- **Requisito afectado:** RNF-18 / Entorno de Ejecución Laragon

- **Severidad:** **Alta**

- **Descripción y pasos para reproducir:**

  1.  Abrir Laragon y presionar el botón "Start All".

  2.  Contar con servicios externos en ejecución en el equipo (como Skype, VMware, IIS en el puerto 80/443 o una instancia independiente de MySQL/MariaDB en el puerto 3306).

- **Resultado obtenido:** Laragon arroja un error (Port 3306 is used by another application o Apache cannot start: Port 80 blocked), bloqueando la ejecución del servidor web y la conexión a la base de datos MySQL.

- **Resultado esperado:** Identificar y detener los servicios en conflicto, o reconfigurar los puertos de escucha en Laragon para permitir el correcto funcionamiento de Apache y MySQL.

- **Solución Técnica Aplicada (Diagnóstico y Reconfiguración de Entorno):**

****\# Opcion A: Diagnóstico mediante Consola/CMD en Windows para identificar el PID del proceso en conflicto

netstat -ano \| findstr :3306

netstat -ano \| findstr :80

\# Finalizar el proceso que ocupa el puerto (remplazando PID por el número hallado)

taskkill /PID \<PID_NUMERO\> /F



// Opción B: Si se cambia el puerto de MySQL en Laragon a 3307, actualizar la cadena DSN en conexion.php:

\$host = '127.0.0.1';

\$port = '3307'; // Puerto alternativo configurado en Laragon

\$db = 'finanzas_personales';

\$user = 'root';

\$pass = '';

\$dsn = "mysql:host=\$host;port=\$port;dbname=\$db;charset=utf8mb4";

\$pdo = new PDO(\$dsn, \$user, \$pass, \$options);

- 
  > **Estado:** Corregido / Verificado

##

## 2. Tabla Resumen Ejecutiva de Defectos (Bug Log)
| **ID Defecto** | **Módulo / Requisito**     | **Severidad** | **Descripción Resumida**                                                           | **Estado** |
|----------------|----------------------------|---------------|------------------------------------------------------------------------------------|------------|
| **DEF-001**    | Inspección / Documentación | Media         | Inconsistencia en el conteo de tablas y término rubro/presupuesto en la versión 1. | Cerrado    |
| **DEF-002**    | RF-06 / ERF-06.5           | Alta          | Exceso de presupuesto al insertar/editar rubros sin validación total.              | Verificado |
| **DEF-003**    | RF-09 / ERF-09.4           | Crítica       | Vulnerabilidad IDOR en URL y formularios al manipular ID de registros ajenos.      | Cerrado    |
| **DEF-004**    | RF-03, RF-06 / RNF-09      | Alta          | Error de Foreign Key / Borrado en cascada no deseado al eliminar un rubro.         | Verificado |
| **DEF-005**    | RF-01 / RNF-05             | Media         | Inactividad mayor a 30 minutos no finalizaba la sesión del usuario.                | Cerrado    |
| **DEF-006**    | Capa de Datos / RNF-11     | Alta          | Exposición de credenciales PDO/MySQL en pantalla ante caída del servicio.          | Verificado |
| **DEF-007**    | RF-01 / ERF-01.4           | Media         | Bucles de redirección por el uso de rutas relativas al perder la sesión.           | Cerrado    |
| **DEF-008**    | RF-03, RF-06 / Vistas      | Media         | Enlaces de edición/eliminación sin parámetro GET id en la querystring.             | Verificado |
| **DEF-009**    | Git / PAC Sección 3.3      | Media         | Marcadores de conflicto en archivos PHP tras ejecuciones de Git Pull/Merge.        | Verificado |
| **DEF-010**    | Entorno / RNF-18           | Alta          | Bloqueo de puertos (80 y 3306) en Laragon por servicios externos.                  | Verificado |

##

## 3. Sugerencias Técnicas y Recomendaciones para el Equipo
Para garantizar que el sistema cumpla con las metas de calidad estipuladas en el PAC (cobertura del 100% de los 69 casos de prueba y 0 defectos críticos/altos abiertos para la entrega), se establecen los siguientes lineamientos:

1.  **Centralización del Middleware de Seguridad:** Implementar un script maestro (ej. seguridad.php) que se incluya en el encabezado de cada página. Este script debe unificar la verificación de sesión, el control de expiración de 30 minutos (RNF-05) y el saneamiento básico de datos.

2.  **Transacciones SQL en Operaciones Compuestas:** En operaciones que afecten múltiples tablas (como eliminar un presupuesto que desvincula rubros y actualiza gastos), utilizar transacciones PDO (\$pdo-\>beginTransaction(), \$pdo-\>commit(), \$pdo-\>rollBack()) para prevenir inconsistencias en la base de datos ante eventuales fallos de ejecución.

3.  **Estandarización del Entorno Laragon:** Verificar al inicio de cada jornada que los puertos 80 y 3306 estén libres antes de iniciar Laragon. Si se cambia el puerto de MySQL (ejemplo: a 3307), todo el equipo debe actualizar su constante en conexion.php sin subir credenciales personales al repositorio Git.

4.  **Independencia en las Revisiones del Tablero Trello:** Asegurar el cumplimiento estricto del PAC en Trello: el desarrollador asignado a una tarea no puede realizar la revisión ni aprobar los casos de prueba en la columna "En revisión". La comprobación debe ser efectuada por el rol de Tester o Control de Calidad.
