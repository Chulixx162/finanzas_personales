# Plan de Aseguramiento de Calidad (PAC)
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

## Introducción
La calidad de un software no depende solo de escribir código: exige planificar revisiones, aplicar estándares, probar y dejar evidencia de los resultados. Este Plan de Aseguramiento de Calidad (PAC) organiza las actividades, criterios, responsables y procedimientos con los que el equipo promoverá la calidad del **Sistema de Gestión Financiera Personal y Presupuestos**, una aplicación web multiusuario desarrollada con PHP estructurado, MySQL, HTML5 y CSS3 sobre Laragon, que permite registrar ingresos y gastos, clasificarlos por categorías y compararlos con presupuestos por periodo.

El Plan de Aseguramiento de Calidad (PAC) toma como base el SRS del proyecto y todos sus componentes para definir los casos de prueba, cada uno con su procedimiento y su resultado esperado, de modo que desde el inicio quede claro qué significa que una función esté bien implementada y qué evidencia se necesita para demostrarlo. Esta versión del PAC está alineada con la versión vigente del SRS y el esquema de base de datos actual, en la que solo los rubros tienen presupuesto y las categorías únicamente agrupan: seis tablas, 18 historias de usuario y 9 módulos funcionales (RF-01 a RF-09). Los rubros pertenecen a las categorías asignadas a un presupuesto (a través de presupuesto_categoria), y cada movimiento contiene obligatoriamente una categoría y opcionalmente un rubro.

## Tabla de contenido
1\. Generalidades del proyecto

2\. Requisitos de calidad

3\. Estándares y procedimientos

4\. Aplicación del PAC en el ciclo de desarrollo

5\. Revisiones e inspecciones

6\. Plan de pruebas

7\. Gestión de defectos

8\. Roles y responsabilidades

9\. Aseguramiento frente a control de calidad

10\. Indicadores de seguimiento

11\. Registros e informes

12\. Beneficios, limitaciones y riesgos

13\. Licenciamiento y condiciones de uso del proyecto

Conclusiones · Referencias · Anexos

## 1. Generalidades del proyecto
### 1.1 Descripción del proyecto
El Sistema de Gestión Financiera Personal y Presupuestos es una aplicación web pensada para que cualquier persona pueda llevar el control de su dinero de forma sencilla y ordenada. Muchas personas no tienen claro en qué gastan sus ingresos ni cuánto les queda al final del mes, y este sistema busca resolver esa necesidad poniendo la información financiera en un solo lugar y mostrándola de manera clara.

Cada persona crea su propia cuenta y trabaja únicamente con su información, que no es visible para otros usuarios. Dentro de ella puede registrar sus ingresos y sus gastos, clasificarlos en categorías propias, como alimentación, transporte o educación, y consultar su historial filtrándolo por fechas, tipo de movimiento o categoría.

Además la persona define un presupuesto para un periodo y lo reparte entre rubros, que son las divisiones de las categorías en las que planea gastar. Las categorías no tienen presupuesto propio: solo los rubros lo tienen (por ejemplo, de \$1.000.000: en alimentación, \$250.000 para mercado y \$150.000 para restaurantes; en transporte, \$200.000; y en educación, \$200.000). La suma de los presupuestos de los rubros no puede superar el monto total del presupuesto. A medida que se registran los gastos y se asocian a un rubro, el sistema compara automáticamente lo planificado con lo gastado en cada rubro e indica cuánto se ha consumido, cuánto queda disponible y cuándo se superó el presupuesto de un rubro, de modo que el usuario pueda tomar decisiones a tiempo. También le muestra un resumen con sus ingresos, sus gastos y su saldo.

Todos los valores se manejan en pesos colombianos (COP).

**Fuera del alcance del sistema:** conexión con bancos o pasarelas de pago, transacciones reales, asesoría financiera, aplicación móvil nativa, múltiples monedas, reportes exportables, gráficos avanzados y roles administrativos dentro de la aplicación.

### 1.2 Objetivos y alcance del PAC
**Objetivo general.**

Garantizar que el sistema cumpla los requisitos del SRS, funcione de forma confiable y segura, y cuente con evidencias verificables de su calidad antes de la entrega.

**Objetivos específicos**

● Prevenir errores revisando requisitos, diseño y código desde las primeras etapas.

● Verificar con pruebas planificadas que cada historia de usuario y cada requerimiento funcional produzca el resultado esperado.

● Comprobar los requisitos no funcionales: seguridad, rendimiento, usabilidad, portabilidad y mantenibilidad.

● Establecer estándares de programación, documentación y control de versiones comunes para todo el equipo.

● Registrar, priorizar, corregir y volver a verificar los defectos que se detecten.

● Medir la calidad con indicadores sencillos y usar los resultados para mejorar el proceso.

**Alcance.**

El PAC cubre los nueve módulos funcionales de la aplicación (RF-01 a RF-09: usuarios y autenticación, categorías, movimientos, presupuestos, asignación de categorías a presupuestos, rubros, filtros y consultas, cálculos y resumen, y seguridad y validación), los filtros de consulta, la base de datos y la documentación técnica.

### 1.3 Definiciones y siglas
| **Término**            | **Significado**                                                                                                                                                                                                                                                                    |
|------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| PAC                    | Plan de Aseguramiento de Calidad                                                                                                                                                                                                                                                   |
| QA / QC                | Aseguramiento de calidad (proceso) / control de calidad (producto)                                                                                                                                                                                                                 |
| SRS                    | Especificación de Requisitos de Software del proyecto                                                                                                                                                                                                                              |
| HU                     | Historia de usuario (HU-1 a HU-18)                                                                                                                                                                                                                                                 |
| RF / ERF               | Requerimiento funcional por módulo / requerimiento específico (ERF-01.1, etc.)                                                                                                                                                                                                     |
| RNF                    | Requerimiento no funcional (RNF-01 a RNF-21)                                                                                                                                                                                                                                       |
| CP                     | Caso de prueba (CP-01 a CP-69)                                                                                                                                                                                                                                                     |
| DEF                    | Defecto registrado (DEF-001, etc.)                                                                                                                                                                                                                                                 |
| COP                    | Peso colombiano                                                                                                                                                                                                                                                                    |
| FK / PK                | Llave foránea / llave primaria                                                                                                                                                                                                                                                     |
| XSS                    | Cross-Site Scripting                                                                                                                                                                                                                                                               |
| Ejecución presupuestal | Porcentaje del presupuesto de un rubro que ya fue gastado: (gastado ÷ presupuesto del rubro) × 100                                                                                                                                                                                 |
| Rubro                  | Subdivisión de una categoría dentro de un presupuesto (asociado a presupuesto_categoria), con su propio monto. Es el único elemento con presupuesto propio: la categoría no tiene monto, y la suma de los rubros de un presupuesto no puede superar el monto total del presupuesto |

## 2. Requisitos de calidad
### 2.1 Requisitos funcionales
| **Módulo**                                                   | **Requerimientos** | **Historias de usuario** | **Función verificada**                                                                                                                                                                                                  |
|--------------------------------------------------------------|--------------------|--------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| RF-01 Gestión de usuarios y autenticación                    | ERF-01.1 a 01.4    | HU-1, HU-2, HU-3, HU-18  | Registro, inicio y cierre de sesión, control de acceso                                                                                                                                                                  |
| RF-02 Gestión de categorías                                  | ERF-02.1 a 02.4    | HU-10                    | Crear, consultar, editar y eliminar categorías                                                                                                                                                                          |
| RF-03 Gestión de movimientos                                 | ERF-03.1 a 03.4    | HU-4 a HU-8              | Registrar, consultar y modificar (editar) ingresos y gastos, restringiendo su eliminación por seguridad e integridad entre entidades; los movimientos contienen obligatoriamente una categoría y opcionalmente un rubro |
| RF-04 Gestión de presupuestos                                | ERF-04.1 a 04.4    | HU-11, HU-14             | Crear, consultar, editar y eliminar presupuestos por periodo                                                                                                                                                            |
| RF-05 Asignación de categorías a presupuestos (relación N:M) | ERF-05.1 a 05.4    | HU-12                    | Asignar categorías a un presupuesto (sin monto propio), consultarlas y calcular cuánto del presupuesto está distribuido en rubros                                                                                       |
| RF-06 Gestión de rubros                                      | ERF-06.1 a 06.5    | HU-13                    | Crear, consultar, editar y eliminar rubros de una categoría del presupuesto; la suma de los rubros de un presupuesto no puede superar el monto total del presupuesto                                                    |
| RF-07 Filtros y consultas                                    | ERF-07.1 a 07.3    | HU-9                     | Filtrar por rango de fechas, tipo y categoría                                                                                                                                                                           |
| RF-08 Cálculos y resumen financiero                          | ERF-08.1 a 08.5    | HU-15, HU-16, HU-17      | Totales, saldo, comparación presupuesto contra gasto real por rubro, alertas y panel                                                                                                                                    |
| RF-09 Seguridad y validación de datos                        | ERF-09.1 a 09.4    | HU-1, HU-18              | Cifrado, sentencias preparadas, validación y aislamiento por usuario                                                                                                                                                    |

### 2.2 Requisitos no funcionales
| **Característica**            | **Requisitos**  | **Criterio verificable**                                                                                                                                                                                                |
|-------------------------------|-----------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Seguridad y privacidad        | RNF-01 a RNF-05 | Contraseñas con password_hash; sentencias preparadas; escape de salida con htmlspecialchars; regeneración del identificador de sesión al iniciar; expiración de sesión a los 30 minutos; respeto de la Ley 1581 de 2012 |
| Rendimiento                   | RNF-06 a RNF-08 | Respuesta en menos de 2 segundos; columnas de filtro indexadas; funcionamiento con al menos 10 usuarios concurrentes                                                                                                    |
| Disponibilidad y robustez     | RNF-09 a RNF-12 | Integridad referencial con llaves foráneas; montos en DECIMAL(12,2); errores sin detalles técnicos; respaldo de la base de datos al menos mensual                                                                       |
| Usabilidad                    | RNF-13 a RNF-16 | Un usuario nuevo se registra, crea una categoría y registra su primer movimiento en menos de 5 minutos; diseño adaptable; montos con separador de miles; contraste y tamaño de fuente legibles                          |
| Portabilidad y mantenibilidad | RNF-17 a RNF-21 | Chrome, Firefox y Edge (dos últimas versiones); Laragon con PHP 8.0 o superior; código modular y comentado; codificación utf8mb4                                                                                        |

## 3. Estándares y procedimientos
### 3.1 Convenciones de código
**●** **Archivos:** organizados por responsabilidad (RNF-19): conexión a la base de datos, funciones reutilizables de validación y cálculo, una página por módulo y hojas de estilo CSS separadas.

**●** **Nombres:** tablas y columnas en minúsculas con guión bajo (presupuesto_categoria); funciones PHP en minúsculas con guión bajo y nombres que indiquen la acción (validar_monto, calcular_saldo); clases CSS descriptivas y consistentes.

**●** **Base de datos:** llaves primarias y foráneas definidas, restricciones de integridad, montos en DECIMAL(12,2), codificación utf8mb4 e índices en usuario, categoría, rubro y fecha; la tabla presupuesto_categoria no almacena montos (solo los rubros tienen presupuesto); la tabla rubros se vincula a una categoría asignada a un presupuesto (presupuesto_categoria); y la tabla movimientos contiene obligatoriamente categoria_id y opcionalmente rubro_id.

**●** **Seguridad en el código:** ninguna consulta concatena datos del usuario; toda consulta de lectura, edición o modificación incluye la condición del usuario propietario; los movimientos no disponen de operaciones de eliminación (DELETE) por motivos de seguridad, trazabilidad e integridad referencial entre entidades, permitiéndose únicamente su registro, consulta y modificación por el usuario propietario; toda salida a pantalla se escapa; toda página interna verifica la sesión antes de procesar datos.

**●** **Comentarios:** cada función y sección principal lleva comentario; el script SQL de creación de la base de datos está documentado (RNF-20).

### 3.2 Reglas de documentación
● Los documentos del proyecto (SRS, PAC, informes de pruebas) usan los mismos nombres de módulos, historias y requisitos del SRS para mantener la trazabilidad.

● Todo documento indica versión, fecha y responsable de la última modificación.

● Los cambios en los requisitos se reflejan en el SRS y en los casos de prueba afectados antes de continuar con la programación.

● El alcance del tablero de seguimiento debe coincidir con el del SRS: una tarea que no esté en el alcance del SRS se marca como «versión futura» o se retira del tablero, y una que se agregue al alcance exige primero actualizar el SRS y el PAC.

### 3.3 Gestión de versiones
● El código se guarda en un repositorio Git con una rama de trabajo por integrante (por ejemplo, la rama karoll para el trabajo de interfaz) y una rama principal estable.

● Ningún cambio llega a la rama principal sin revisión de otro integrante y sin que sus pruebas relacionadas hayan sido ejecutadas.

● Los mensajes de commit describen el módulo y el cambio realizado, y cuando es posible nombran la tarjeta del tablero a la que corresponden (por ejemplo, «Categorías: validar nombre duplicado»).

● Antes de cada entrega parcial se guarda una copia de la base de datos (script SQL exportado).

### 3.4 Métodos de revisión
**●** **Revisión por pares:** cada pieza de código la revisa un integrante distinto de quien la escribió, con la lista de verificación de la sección 5.3. Corresponde a la columna «En revisión» del tablero.

**●** **Inspección documental:** el responsable de documentación y calidad revisa el SRS y el PAC contra la lista de verificación de la sección 5.1.

**●** **Revisión de seguimiento:** reunión corta del equipo en cada entrega parcial para revisar indicadores, defectos abiertos y el estado del tablero.

### 3.5 Seguimiento del trabajo en Trello
El equipo usa un tablero de Trello (finanzas_personales) como herramienta de seguimiento. Cada tarjeta corresponde a una funcionalidad o módulo del sistema (por ejemplo, «Administra categorías» o «Proteger sesiones y rutas»), tiene su lista de tareas, sus integrantes asignados y, cuando aplica, una fecha de entrega.

| **Columna**  | **Significado**                                                                                                         | **Quién la mueve**  |
|--------------|-------------------------------------------------------------------------------------------------------------------------|---------------------|
| Backlog      | Funcionalidades identificadas que aún no se han planificado                                                             | Líder del proyecto  |
| Por hacer    | Tareas planificadas para el ciclo actual                                                                                | Líder del proyecto  |
| En proceso   | Tareas que el integrante asignado está desarrollando                                                                    | Integrante asignado |
| Por revisar  | Tarea terminada, con su lista de tareas completa, a la espera de un revisor                                             | Integrante asignado |
| En revisión  | Otro integrante hace la revisión por pares y ejecuta los casos de prueba relacionados                                   | Revisor             |
| Por corregir | La revisión o las pruebas encontraron defectos; la tarjeta vuelve al responsable con el defecto registrado (DEF-\_\_\_) | Revisor             |
| Aceptado     | Revisión y pruebas aprobadas, y código integrado a la rama principal                                                    | Revisor             |

**Flujo de una tarjeta.** El recorrido normal es Por hacer → En proceso → Por revisar → En revisión → Aceptado. Si en la revisión o en las pruebas se detecta un defecto, el revisor lo registra con el formato del Anexo A y mueve la tarjeta a «Por corregir». El responsable lo corrige, deja constancia del cambio y la devuelve a «Por revisar». Antes de pasar a «Aceptado», el revisor repite el caso de prueba que falló y los casos relacionados con el módulo (regresión), como exige la sección 7.3.

**Definición de aceptado.**

Una tarjeta pasa a «Aceptado» sólo si se cumple todo lo siguiente:

1\. Todas las tareas de su lista están marcadas.

2\. El código fue revisado por un integrante distinto de quien lo escribió, con la lista de la sección 5.3.

3\. Los casos de prueba relacionados con la tarjeta fueron ejecutados y aprobados (ver el Anexo C).

4\. Los defectos críticos y altos que se hayan encontrado están cerrados.

5\. Los cambios están integrados a la rama principal.

**Regla de independencia.** Quien está asignado a una tarjeta no puede ser quien la revisa ni quien verifica sus defectos.

**Relación entre las tarjetas y las pruebas.** Cada tarjeta indica los casos de prueba que debe superar, de modo que el avance del tablero alimenta los indicadores de la sección 10: las tarjetas en «Aceptado» cuentan como módulos con pruebas aprobadas. Los defectos se registran en el formato del Anexo A y se les da seguimiento en el tablero, identificados con su código (DEF-001, DEF-002…).

## 4. Aplicación del PAC en el ciclo de desarrollo
| **Etapa**              | **Actividad de calidad**                                                                                                       | **Responsable principal**                          | **Evidencia**                                   |
|------------------------|--------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------|-------------------------------------------------|
| Planificación          | Definir alcance, criterios de calidad, roles y herramientas de seguimiento                                                     | Julio Casanova                                     | Este PAC aprobado                               |
| Análisis de requisitos | Revisar que las historias y requerimientos sean claros, completos, coherentes y verificables                                   | Julio Casanova y Jaime Ladino                      | Lista de verificación 5.1 diligenciada          |
| Diseño                 | Revisar el modelo entidad-relación, las relaciones 1:N y N:M (incluida la de rubros), y los prototipos de interfaz             | Laura Isanoa y Karoll Coronado                     | Lista de verificación 5.2 diligenciada          |
| Programación           | Aplicar convenciones, revisión por pares y pruebas unitarias de las funciones de validación y cálculo                          | Juan Manuel Barreiro y Karoll Coronado             | Lista de verificación 5.3, commits revisados    |
| Pruebas                | Ejecutar los casos de prueba CP-01 a CP-69, registrar resultados y defectos                                                    | Karoll Coronado (tester) con apoyo de Jaime Ladino | Formato de casos de prueba e informe de pruebas |
| Implementación         | Comprobar que la versión se instale y ejecute en Laragon con PHP 8.0 o superior y que el script SQL cree la base correctamente | Laura Isanoa y Juan Manuel Barreiro                | Acta de instalación y copia de la base de datos |
| Mantenimiento          | Revisar cada cambio y repetir las pruebas relacionadas (regresión)                                                             | Todo el equipo                                     | Informe de regresión                            |

## 5. Revisiones e inspecciones
### 5.1 Revisión de requisitos
**Lista de verificación:**

● ¿cada requisito es claro y comprobable?

● ¿Tiene criterio de aceptación?

● ¿Los términos son iguales en todo el documento?

● ¿Cada historia tiene al menos un requerimiento y un caso de prueba?

● ¿se respetan las restricciones (seis tablas, COP, sentencias preparadas, categorías sin monto propio y suma de los rubros dentro del monto total del presupuesto)?

**Hallazgos de la revisión inicial del SRS** (registrados como defectos documentales y resueltos en el SRS v2):

| **Hallazgo**                | **Detalle**                                                                                                                                                                                | **Estado en el SRS v2**                                                                                                                                                                                                    |
|-----------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Número de tablas            | La versión anterior del SRS hablaba de cuatro tablas en un objetivo específico y de cinco en el resumen, la descripción general y las restricciones (presupuestos y presupuesto_categoria) | Resuelto: el SRS v2 unifica el modelo en seis tablas (usuarios, categorias, movimientos, presupuestos, presupuesto_categoria y rubros)                                                                                     |
| Término rubro y presupuesto | Se usaban “rubros” y “presupuestos” para el mismo concepto, y el alcance mencionaba asignar una categoría a varios rubros                                                                  | Resuelto y actualizado: el SRS define «rubro» como subdivisión de una categoría con presupuesto propio y como único elemento con presupuesto dentro del presupuesto del periodo; la categoría solo agrupa y no tiene monto |
| Expiración de sesión        | RNF-05 daba 30 minutos solo como ejemplo                                                                                                                                                   | Resuelto: RNF-05 fija 30 minutos de inactividad; el caso CP-08 es verificable                                                                                                                                              |
| Advertencia del 80 %        | ERF-07.4 de la versión anterior la dejaba como opcional                                                                                                                                    | Resuelto: HU-16 y ERF-08.4 la hacen obligatoria; el caso CP-49 se ejecuta sin condición                                                                                                                                    |

**Revisión del SRS actualizado.**

Al comparar la versión vigente del SRS con la versión anterior de este PAC se actualizó el modelo de presupuestos: las categorías dejaron de tener un monto límite y solo los rubros tienen presupuesto. En consecuencia, presupuesto_categoria ya no almacena montos, los rubros pertenecen a las categorías asignadas al presupuesto (presupuesto_categoria), la suma de los rubros se valida contra el monto total del presupuesto (ERF-06.5), la comparación contra el gasto real y las alertas se calculan por rubro (ERF-08.3 y ERF-08.4), y los movimientos contienen obligatoriamente una categoría y opcionalmente un rubro mediante rubro_id (ERF-03.1). Los criterios de aceptación siguen siendo verificables, con un mensaje de error definido para la suma de rubros. Los casos CP-30 a CP-44, CP-47 a CP-49 y los nuevos CP-65 a CP-69 cubren estos cambios.

### 5.2 Revisión de diseño
● El modelo de datos incluye las seis tablas con llaves primarias, foráneas y la tabla intermedia presupuesto_categoria (sin montos), la tabla rubros vinculada a ella como única tabla con montos presupuestados, la llave foránea opcional rubro_id en movimientos, y restricciones para no repetir la misma categoría dentro de un presupuesto ni el mismo nombre de rubro dentro de una categoría.

● La suma de los presupuestos de todos los rubros de un presupuesto se valida en el servidor y no puede superar el monto total del presupuesto; los rubros pertenecen a categorías del presupuesto (presupuesto_categoria); al eliminar un presupuesto, quitar una categoría o eliminar un rubro, los movimientos no se eliminan (los que estaban asociados a un rubro quedan con rubro_id en NULL) ni tampoco las categorías.

● Cada tabla de datos del usuario incluye la columna de usuario propietario (o se vincula a un registro que la tiene, como rubros a través de presupuestos), necesaria para aislar la información.

● La interfaz tiene menú visible en todas las páginas internas (Inicio, Movimientos, Categorías, Presupuestos, Cerrar sesión) y diseño adaptable.

● Se definió la presentación de ingresos en verde, gastos en rojo, rubros con presupuesto excedido resaltados y advertencia al superar el 80 % del presupuesto del rubro, además de los mensajes de validación junto al campo afectado.

### 5.3 Revisión de código y documentos
● ¿La página verifica la sesión antes de procesar datos?

● ¿Las consultas usan parámetros y se filtran por el usuario propietario?

● ¿Los datos de entrada se validan en el servidor (tipo, formato, longitud, obligatoriedad) y la salida se escapa?

● ¿Los montos se tratan como decimales y se muestran con formato COP?

● ¿La suma de los rubros de un presupuesto se valida en el servidor antes de guardar y no supera el monto total del presupuesto? ¿Se valida que el rubro de un gasto pertenezca a su categoría y que la fecha esté dentro del periodo del presupuesto?

● ¿Hay confirmación antes de eliminar y mensajes de error sin detalles técnicos?

● ¿El código está comentado y organizado por responsabilidad?

● ¿El documento revisado conserva los nombres, versiones y fechas acordados?

## 6. Plan de pruebas
### 6.1 Tipos de pruebas, entorno y estrategia
| **Tipo de prueba** | **Qué se verifica en este proyecto**                                                                                                                          | **Responsable**                                     |
|--------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------|
| Unitarias          | Funciones aisladas de validación (monto, fecha, correo, longitud) y de cálculo (saldo, disponible, porcentaje de ejecución, suma de rubros de un presupuesto) | Juan Manuel Barreiro, revisadas por Karoll Coronado |
| Integración        | Que las páginas PHP, la sesión y las consultas MySQL trabajen juntas; que las relaciones 1:N y N:M (incluidos los rubros) se reflejen en pantalla             | Karoll Coronado                                     |
| Sistema            | Flujos completos de las 18 historias de usuario frente al SRS                                                                                                 | Karoll Coronado y Jaime Ladino                      |
| Aceptación         | Validación con usuarios potenciales y con el instructor frente a los criterios de aceptación                                                                  | Julio Casanova, con usuarios e instructor           |
| Seguridad          | Cifrado de contraseñas, inyección SQL, XSS, control de sesión y aislamiento entre usuarios                                                                    | Karoll Coronado y Juan Manuel Barreiro              |
| Rendimiento        | Tiempo de respuesta con volumen de datos y con 10 usuarios concurrentes                                                                                       | Jaime Ladino                                        |

**Entorno de prueba.** Laragon en Windows con Apache, PHP 8.0 o superior y MySQL o MariaDB; navegadores Chrome, Firefox y Edge en sus versiones recientes; un computador, una tablet y un teléfono para las pruebas de diseño adaptable.

**Datos de prueba.** Dos usuarios independientes (usuario A y usuario B), cada uno con sus categorías, movimientos, presupuestos y rubros, para poder verificar el aislamiento de la información. Como presupuesto de referencia se usa «Mes actual» de \$1.000.000 con los rubros Mercado \$250.000 y Restaurantes \$150.000 (Alimentación), Bus \$200.000 (Transporte) y Cursos \$200.000 (Educación), que suman \$800.000. Un tercer conjunto con 5.000 movimientos de un solo usuario para las pruebas de rendimiento.

**Estrategia.** Las pruebas se ejecutan en tres ciclos:

\(1\) por módulo, a medida que cada uno queda programado.

\(2\) de integración y sistema con todo el sistema armado.

\(3\) de aceptación con usuarios potenciales.

Cada defecto corregido obliga a repetir su caso de prueba y los casos relacionados.

### 6.2 Casos de prueba
Todos los casos están **ejecutados y aprobados al 100%**. La columna «Resultado esperado» es el criterio con el que se aprobó cada prueba; al ejecutarla, el tester registró el resultado obtenido y confirmó el estado Aprobado para todos los casos.

#### 6.2.1 Autenticación y sesión (RF-01, HU-1, HU-2, HU-3, HU-18)
| **ID** | **Req.** | **Procedimiento y datos de prueba**                                                                                                                        | **Resultado esperado**                                                                                    | **Estado** |
|--------|----------|------------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------|------------|
| CP-01  | ERF-01.1 | Registrar usuario con nombre, correo nuevo, contraseña de 8 o más caracteres y confirmación igual                                                          | Se crea el registro en usuarios, se muestra mensaje de éxito y se redirige al inicio de sesión            | Aprobado   |
| CP-02  | ERF-01.1 | Registrar un usuario con un correo ya existente                                                                                                            | Se rechaza con un mensaje claro y no se crea un segundo registro                                          | Aprobado   |
| CP-03  | ERF-01.1 | Intentar registrar con campos vacíos, con correo sin formato válido, con contraseña de 7 caracteres y con confirmación distinta (un intento por cada caso) | Cada caso se rechaza con un mensaje ubicado junto al campo afectado y no se crea la cuenta                | Aprobado   |
| CP-04  | ERF-01.2 | Iniciar sesión con correo y contraseña correctos                                                                                                           | Acceso al panel principal y regeneración del identificador de sesión                                      | Aprobado   |
| CP-05  | ERF-01.2 | Iniciar sesión con un correo inexistente y, por separado, con contraseña errónea                                                                           | En ambos casos aparece el mensaje genérico «Correo o contraseña incorrectos», sin indicar cuál dato falló | Aprobado   |
| CP-06  | ERF-01.3 | Cerrar sesión y luego presionar el botón «atrás» del navegador                                                                                             | La sesión queda destruida y el sistema exige autenticarse de nuevo                                        | Aprobado   |
| CP-07  | ERF-01.4 | Sin sesión iniciada, escribir en la URL cada página interna (inicio, movimientos, categorías, presupuestos)                                                | Todas redirigen al inicio de sesión; el visitante solo accede a registro e inicio de sesión               | Aprobado   |
| CP-08  | RNF-05   | Iniciar sesión, dejarla inactiva más de 10 minutos y luego navegar a otra página                                                                           | La sesión expira y el sistema solicita iniciar sesión nuevamente                                          | Aprobado   |

#### 6.2.2 Categorías (RF-02, HU-10)
| **ID** | **Req.**       | **Procedimiento y datos de prueba**                                                                                               | **Resultado esperado**                                                                                   | **Estado** |
|--------|----------------|-----------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------|------------|
| CP-09  | ERF-02.1, 02.2 | Crear la categoría «Alimentación» y abrir el listado de categorías                                                                | La categoría se guarda asociada al usuario y aparece en el listado con las opciones de editar y eliminar | Aprobado   |
| CP-10  | ERF-02.1       | Crear «Alimentación» por segunda vez con el mismo usuario; repetir con el usuario B                                               | Para el mismo usuario se rechaza por duplicada; el usuario B sí puede crear una con el mismo nombre      | Aprobado   |
| CP-11  | ERF-02.3       | Cambiar el nombre de una categoría usada en movimientos y presupuestos; luego intentar ponerle el nombre de otra categoría propia | El cambio se refleja en los movimientos y presupuestos asociados; el nombre duplicado se rechaza         | Aprobado   |
| CP-12  | ERF-02.4       | Eliminar una categoría que tiene movimientos o presupuestos asociados                                                             | El sistema impide la eliminación e informa el motivo; no quedan registros huérfanos                      | Aprobado   |
| CP-13  | ERF-02.4       | Eliminar una categoría sin asociaciones, primero cancelando y luego aceptando la confirmación                                     | Al cancelar no se elimina nada; al aceptar se elimina y se muestra confirmación                          | Aprobado   |

#### 6.2.3 Movimientos (RF-03, HU-4 a HU-8)
| **ID** | **Req.**                 | **Procedimiento y datos de prueba**                                                                                                                                                                                                                                                                                     | **Resultado esperado**                                                                                                                                                                                                                                        | **Estado** |
|--------|--------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------|
| CP-14  | ERF-03.1                 | Registrar un ingreso de \$1.500.000 con fecha, descripción y categoría                                                                                                                                                                                                                                                  | Se guarda con tipo «ingreso» asociado al usuario y aparece en el listado                                                                                                                                                                                      | Aprobado   |
| CP-15  | ERF-03.1                 | Registrar un gasto de \$35.000 con fecha, descripción y categoría                                                                                                                                                                                                                                                       | Se guarda con tipo «gasto» asociado al usuario y aparece en el listado                                                                                                                                                                                        | Aprobado   |
| CP-16  | ERF-03.1                 | Registrar movimientos con monto 0, monto negativo y monto con texto                                                                                                                                                                                                                                                     | Los tres casos se rechazan: el monto debe ser numérico y mayor a cero                                                                                                                                                                                         | Aprobado   |
| CP-17  | ERF-03.1                 | Registrar un movimiento sin fecha, con fecha inválida y un gasto sin categoría                                                                                                                                                                                                                                          | Cada caso se rechaza con mensaje de validación y no se guarda el movimiento                                                                                                                                                                                   | Aprobado   |
| CP-18  | ERF-03.1, ERF-09.4       | Registrar un movimiento alterando el identificador de categoría del formulario por uno del usuario B                                                                                                                                                                                                                    | Se rechaza porque la categoría no pertenece al usuario                                                                                                                                                                                                        | Aprobado   |
| CP-19  | ERF-03.2                 | Registrar varios movimientos con fechas distintas y abrir el listado                                                                                                                                                                                                                                                    | Se listan solo los del usuario autenticado, del más reciente al más antiguo, con fecha, tipo, categoría, descripción y monto; ingresos y gastos se distinguen visualmente                                                                                     | Aprobado   |
| CP-20  | ERF-03.3                 | Editar el monto de un movimiento existente                                                                                                                                                                                                                                                                              | La edición es la única vía permitida para corregir un movimiento registrado; el formulario carga los datos actuales, se actualizan los datos al guardar y los totales y presupuestos se recalculan                                                            | Aprobado   |
| CP-21  | ERF-03.4                 | Verificar la interfaz del listado de movimientos e intentar enviar una petición directa de eliminación de un movimiento propio                                                                                                                                                                                          | No existe opción de eliminación en la interfaz y cualquier intento de borrado es rechazado por el sistema para preservar la seguridad, trazabilidad e integridad de las relaciones entre entidades; se indica el uso de la edición como único medio de ajuste | Aprobado   |
| CP-22  | ERF-03.3, 03.4, ERF-09.4 | Con el usuario A, cambiar en la URL el identificador de un movimiento por uno del usuario B e intentar editarlo o forzar su borrado                                                                                                                                                                                     | El acceso es denegado y el registro del usuario B no sufre cambios                                                                                                                                                                                            | Aprobado   |
| CP-65  | ERF-03.1                 | Registrar un gasto de \$60.000 en la categoría Alimentación, asociado al rubro «Mercado» (presupuesto vigente cuyo periodo incluye la fecha del gasto)                                                                                                                                                                  | Se guarda con tipo «gasto» asociado al usuario y al rubro; aparece en el listado indicando el rubro y suma al gastado de «Mercado»                                                                                                                            | Aprobado   |
| CP-66  | ERF-03.1, ERF-09.4       | Intentar guardar, un intento por cada caso: un ingreso asociado a un rubro; un gasto de la categoría Transporte asociado al rubro «Mercado» (de Alimentación); un gasto con fecha fuera del periodo del presupuesto del rubro; y, alterando el identificador del formulario, un gasto asociado a un rubro del usuario B | Los cuatro casos se rechazan con mensaje de validación y no se guarda el movimiento: el rubro solo aplica a gastos, debe ser de la misma categoría del gasto, de un presupuesto cuyo periodo incluya la fecha y pertenecer al usuario                         | Aprobado   |
| CP-67  | ERF-03.1, ERF-08.3       | Registrar un gasto de \$50.000 en la categoría Alimentación sin seleccionar ningún rubro                                                                                                                                                                                                                                | Se guarda, porque el rubro es opcional; no suma al gastado de ningún rubro y se muestra como «gasto sin rubro» en la comparación del presupuesto                                                                                                              | Aprobado   |
| CP-68  | ERF-03.3                 | Editar un gasto asociado a «Mercado»: cambiar el rubro a «Restaurantes»; quitar el rubro; y, por separado, cambiar la categoría del gasto a Transporte dejando el rubro «Mercado»                                                                                                                                       | Los dos primeros cambios se guardaron y el gastado de cada rubro se recalculó; el tercero se rechazó porque el rubro no pertenece a la categoría del gasto                                                                                                    | Aprobado   |

#### 6.2.4 Filtros de consulta (RF-07, HU-9)
| **ID** | **Req.** | **Procedimiento y datos de prueba**                                                  | **Resultado esperado**                                                                                  | **Estado** |
|--------|----------|--------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------|------------|
| CP-23  | ERF-07.1 | Filtrar por un rango de fechas que incluya movimientos dentro y fuera del rango      | Solo se listan los movimientos del rango (extremos incluidos) y los totales corresponden al filtro      | Aprobado   |
| CP-24  | ERF-07.1 | Poner la fecha final anterior a la inicial; luego indicar solo una de las dos fechas | Con fechas invertidas aparece un mensaje de error; con una sola fecha se filtra desde o hasta esa fecha | Aprobado   |
| CP-25  | ERF-07.2 | Filtrar por tipo: todos, ingreso y gasto                                             | Por defecto se muestran todos; cada opción muestra solo su tipo, con sus totales                        | Aprobado   |
| CP-26  | ERF-07.3 | Filtrar por una categoría específica                                                 | Solo se listan los movimientos de esa categoría y los totales se actualizan                             | Aprobado   |
| CP-27  | ERF-07.3 | Combinar fecha, tipo y categoría; luego usar la opción de limpiar filtros            | Los filtros se aplican en conjunto (intersección); al limpiar vuelve el listado completo                | Aprobado   |

#### 6.2.5 Presupuestos y asignación por categorías (RF-04, RF-05, HU-11, HU-12, HU-14)
| **ID** | **Req.**       | **Procedimiento y datos de prueba**                                                                                                                                                                                                                         | **Resultado esperado**                                                                                                                                                                           | **Estado** |
|--------|----------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------|
| CP-28  | ERF-04.1, 04.2 | Crear el presupuesto «Mes actual» de \$1.000.000 con fecha de inicio el día 1 y fecha de fin el último día del mes                                                                                                                                          | Se guarda asociado al usuario y aparece en el listado con su periodo y monto total                                                                                                               | Aprobado   |
| CP-29  | ERF-04.1       | Crear presupuestos con monto 0 y con fecha de fin anterior a la de inicio                                                                                                                                                                                   | Ambos casos se rechazan con mensaje de validación                                                                                                                                                | Aprobado   |
| CP-30  | ERF-05.1, 05.4 | Asignar al presupuesto «Mes actual» de \$1.000.000 las categorías Alimentación, Transporte y Educación, sin indicar ningún monto por categoría                                                                                                              | Se crean tres registros en presupuesto_categoria sin ningún monto; el formulario no solicita monto por categoría y la tabla no almacena montos                                                   | Aprobado   |
| CP-31  | ERF-05.1       | Asignar la misma categoría dos veces al mismo presupuesto, y asignar una categoría de otro usuario                                                                                                                                                          | Ambos casos se rechazan; la misma categoría sí puede estar en otro presupuesto del mismo usuario                                                                                                 | Aprobado   |
| CP-32  | ERF-05.4       | Con rubros ya creados: Mercado \$250.000 y Restaurantes \$150.000 (Alimentación), Bus \$200.000 (Transporte) y Cursos \$200.000 (Educación), abrir la distribución del presupuesto de \$1.000.000; luego crear en Educación el rubro «Libros» por \$200.000 | Con rubros por \$800.000 se muestran \$800.000 asignados a rubros y \$200.000 aún sin distribuir; tras crear «Libros» se muestran \$1.000.000 asignados y \$0 sin distribuir                     | Aprobado   |
| CP-33  | ERF-05.2       | Abrir el detalle del presupuesto con las categorías Alimentación, Transporte y Educación asignadas y sus rubros                                                                                                                                             | Se listan las categorías asignadas con sus rubros y su presupuesto; cada categoría muestra la suma de sus rubros solo como dato informativo, sin monto límite ni porcentaje de ejecución propios | Aprobado   |
| CP-34  | ERF-05.3       | Quitar la categoría Educación del presupuesto, habiendo definido antes rubros dentro de ella y registrado gastos asociados a esos rubros                                                                                                                    | Se elimina la asignación junto con sus rubros; la categoría y sus movimientos permanecen intactos (los gastos que estaban asociados a esos rubros quedan sin rubro)                              | Aprobado   |
| CP-35  | ERF-04.3       | Editar el monto total y el periodo de un presupuesto con movimientos registrados, sin que el nuevo monto total quede por debajo de la suma de sus rubros                                                                                                    | Se aplican las mismas validaciones de la creación y las comparaciones contra el gasto real se recalculan                                                                                         | Aprobado   |
| CP-36  | ERF-04.4       | Eliminar un presupuesto con categorías asignadas, rubros definidos y gastos asociados a esos rubros, aceptando la confirmación                                                                                                                              | Se eliminan el presupuesto y sus registros en presupuesto_categoria y rubros; los movimientos y las categorías no cambian (los gastos asociados a los rubros eliminados quedan sin rubro)        | Aprobado   |

#### 6.2.6 Rubros (RF-06, HU-13)
| **ID** | **Req.**                             | **Procedimiento y datos de prueba**                                                                                                                                                                                                                                                 | **Resultado esperado**                                                                                                                                                                                                                                                            | **Estado** |
|--------|--------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------|
| CP-37  | ERF-06.1, 06.2                       | En el presupuesto de \$1.000.000 con la categoría Alimentación asignada (sin monto), crear los rubros «Mercado» por \$250.000 y «Restaurantes» por \$150.000 y abrir el detalle del presupuesto                                                                                     | Se guardan dos registros en rubros vinculados a esa categoría del presupuesto; el detalle muestra cada rubro con su monto, la suma de los rubros de Alimentación ($400.000,soloinformativa),eltotalasignadoarubrosdelpresupuesto($<!-- -->400.000) y \$600.000 aún sin distribuir | Aprobado   |
| CP-38  | ERF-06.1, 06.5                       | Con rubros ya creados que suman \$800.000 en un presupuesto de \$1.000.000 (Mercado \$250.000, Restaurantes \$150.000, Bus \$200.000 y Cursos \$200.000), crear un rubro nuevo por \$300.000 (suma \$1.100.000) y, por separado, por \$200.000 (suma \$1.000.000)                   | Con $300.000noseguardaningúncambioysemuestraelmensaje«Lasumadelosrubros($<!-- -->1.100.000) supera el monto total del presupuesto (\$1.000.000) por \$100.000»; con \$200.000 se guarda, porque una suma igual al monto total es válida                                           | Aprobado   |
| CP-39  | ERF-06.1                             | Intentar crear rubros con nombre vacío, con monto 0, con monto negativo o con texto, y con un nombre ya usado en la misma categoría del mismo presupuesto (un intento por cada caso)                                                                                                | Cada caso se rechaza con un mensaje de validación junto al campo afectado y no se crea el rubro                                                                                                                                                                                   | Aprobado   |
| CP-40  | ERF-06.1, ERF-09.4                   | Con el usuario A, alterando los identificadores del formulario o de la URL, intentar crear un rubro en una categoría que no está asignada al presupuesto y otro en un presupuesto del usuario B                                                                                     | Ambos intentos se rechazan: la categoría debe estar asignada al presupuesto y ambos deben pertenecer al usuario; no se crea ningún rubro                                                                                                                                          | Aprobado   |
| CP-41  | ERF-06.3, 06.5                       | Con rubros que suman \$800.000 en un presupuesto de \$1.000.000 (Mercado \$250.000, Restaurantes \$150.000, Bus \$200.000 y Cursos \$200.000), editar «Mercado» a \$500.000 (suma \$1.050.000) y, por separado, a \$450.000 (suma \$1.000.000); luego cambiar el nombre de un rubro | Con \$500.000 el cambio no se guarda, se conserva el valor anterior y se muestra el mensaje de error; con \$450.000 se actualiza y quedan \$0 sin distribuir; el cambio de nombre aplica las validaciones de la creación                                                          | Aprobado   |
| CP-42  | ERF-06.3, 06.4, ERF-09.4             | Con el usuario A, cambiar en la URL el identificador de un rubro por uno del usuario B e intentar editarlo y eliminarlo                                                                                                                                                             | Acceso denegado en ambos casos y el rubro del usuario B no sufre cambios                                                                                                                                                                                                          | Aprobado   |
| CP-43  | ERF-06.4                             | Eliminar el rubro «Restaurantes», primero cancelando la confirmación y luego aceptándola                                                                                                                                                                                            | Al cancelar no se elimina nada; al aceptar se elimina solo el rubro, el monto del presupuesto sin distribuir aumenta en \$150.000 y no cambian la categoría, su asignación al presupuesto ni los movimientos (los gastos asociados a «Restaurantes» quedan sin rubro)             | Aprobado   |
| CP-44  | ERF-04.3, 06.5                       | Con un presupuesto de \$1.000.000 y rubros que suman \$800.000, intentar bajar el monto total del presupuesto a \$700.000 y, por separado, a \$800.000                                                                                                                              | Con \$700.000 se rechaza porque el nuevo monto total es menor que la suma de los rubros, se conserva el valor anterior y se muestra un mensaje de error; con \$800.000 se acepta                                                                                                  | Aprobado   |
| CP-69  | ERF-06.4, ERF-05.3, ERF-04.4, RNF-09 | Con dos gastos asociados al rubro «Restaurantes», eliminar el rubro y luego consultar la tabla movimientos directamente en la base de datos                                                                                                                                         | Los dos gastos permanecen en movimientos con el rubro en NULL; no quedan registros huérfanos ni errores de llave foránea y los totales de ingresos, gastos y saldo no cambian                                                                                                     | Aprobado   |

#### 6.2.7 Cálculos y resumen financiero (RF-08, HU-15, HU-16, HU-17)
| **ID** | **Req.**       | **Procedimiento y datos de prueba**                                                                                                                                                                                                                                                                  | **Resultado esperado**                                                                                                                                                                                                                                     | **Estado** |
|--------|----------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------|
| CP-45  | ERF-08.1, 08.2 | Registrar un ingreso de \$2.000.000 y tres gastos de \$50.000, \$70.000 y \$80.000                                                                                                                                                                                                                   | Total de ingresos \$2.000.000, total de gastos \$200.000 y saldo \$1.800.000                                                                                                                                                                               | Aprobado   |
| CP-46  | ERF-08.2       | Registrar gastos mayores que los ingresos del periodo                                                                                                                                                                                                                                                | El saldo se muestra negativo y resaltado                                                                                                                                                                                                                   | Aprobado   |
| CP-47  | ERF-08.3       | Con el rubro «Mercado» de \$300.000 en un presupuesto, registrar gastos de \$120.000 y \$60.000 asociados al rubro dentro del periodo, un gasto de \$50.000 de la misma categoría sin asociar a ningún rubro, un gasto de \$40.000 fuera del periodo y un ingreso de \$100.000 en la misma categoría | Mercado: gastado \$180.000 (no cuentan el gasto sin rubro, el gasto fuera del periodo ni el ingreso), disponible \$120.000 y ejecución 60 %; los \$50.000 sin rubro aparecen como «gasto sin rubro» y la categoría no muestra límite ni porcentaje propios | Aprobado   |
| CP-48  | ERF-08.4       | Rubro con presupuesto \$200.000 y gastos del periodo asociados a él por \$250.000                                                                                                                                                                                                                    | El rubro se resalta (por ejemplo, en rojo), se muestra el valor excedido de \$50.000 y la ejecución es 125 %                                                                                                                                               | Aprobado   |
| CP-49  | ERF-08.4       | Rubro con presupuesto \$100.000 y gastos asociados a él por \$85.000                                                                                                                                                                                                                                 | Se muestra un indicador de advertencia por superar el 80 % del presupuesto del rubro, sin marcarlo como excedido                                                                                                                                           | Aprobado   |
| CP-50  | ERF-08.5       | Abrir el panel principal con datos de los usuarios A y B                                                                                                                                                                                                                                             | Se muestran, solo del usuario autenticado, ingresos, gastos, saldo del mes actual, últimos movimientos y estado de los presupuestos vigentes, y se actualizan al recargar                                                                                  | Aprobado   |

#### 6.2.8 Seguridad (RF-09, HU-18, RNF-01 a RNF-05)
| **ID** | **Req.**         | **Procedimiento y datos de prueba**                                                                             | **Resultado esperado**                                                                                                                      | **Estado** |
|--------|------------------|-----------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------|------------|
| CP-51  | ERF-09.1, RNF-02 | Registrar un usuario y consultar la tabla usuarios directamente en la base de datos                             | La contraseña está almacenada como hash generado con password_hash y nunca en texto plano; el inicio de sesión funciona con password_verify | Aprobado   |
| CP-52  | ERF-09.2, RNF-03 | Escribir ' OR '1'='1 en el correo del inicio de sesión y en los campos de descripción y filtros                 | El texto se trata como dato: no hay acceso indebido, no se altera la consulta y no aparece error SQL                                        | Aprobado   |
| CP-53  | ERF-09.3, RNF-03 | Registrar un movimiento con la descripción \<script\>alert(1)\</script\> y abrir el listado                     | El texto se muestra escapado como texto plano y el script no se ejecuta                                                                     | Aprobado   |
| CP-54  | ERF-09.4, HU-18  | Con el usuario A, cambiar en la URL los identificadores de categoría, presupuesto y rubro por los del usuario B | Acceso denegado en todos los casos y ningún dato ajeno se muestra ni se modifica                                                            | Aprobado   |
| CP-55  | RNF-11           | Detener el servicio de MySQL o forzar un error y abrir una página                                               | Se muestra un mensaje claro sin exponer detalles técnicos ni consultas SQL                                                                  | Aprobado   |
| CP-56  | ERF-09.2         | Revisión de código: inspeccionar todas las consultas de los archivos PHP                                        | Ninguna consulta concatena datos del usuario; todas usan parámetros (mysqli o PDO)                                                          | Aprobado   |

#### 6.2.9 Requisitos no funcionales (rendimiento, usabilidad, portabilidad y robustez)
| **ID** | **Req.**               | **Procedimiento y datos de prueba**                                                                                                                                              | **Resultado esperado**                                                                                                                                                      | **Estado** |
|--------|------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------|
| CP-57  | RNF-06, RNF-07         | Cargar 5.000 movimientos de un usuario y abrir el listado, aplicar filtros y abrir el panel resumen                                                                              | Cada operación responde en menos de 2 segundos                                                                                                                              | Aprobado   |
| CP-58  | RNF-08                 | Usar el sistema con 10 usuarios al mismo tiempo registrando y consultando movimientos                                                                                            | Sin errores, sin mezcla de datos entre usuarios y con tiempos de respuesta dentro de lo definido                                                                            | Aprobado   |
| CP-59  | RNF-14                 | Abrir todas las páginas en computador, tablet y teléfono                                                                                                                         | El contenido se adapta sin desbordes, el menú es visible y todo es legible y utilizable                                                                                     | Aprobado   |
| CP-60  | RNF-17                 | Repetir los flujos principales en Chrome, Firefox y Edge                                                                                                                         | El comportamiento y la presentación son equivalentes en los tres navegadores                                                                                                | Aprobado   |
| CP-61  | RNF-15, RNF-16         | Revisar la presentación de montos, colores y confirmaciones en todas las páginas                                                                                                 | Los montos aparecen en COP con separador de miles (por ejemplo, \$1.000.000); ingresos en verde y gastos en rojo; contraste legible; confirmación antes de cada eliminación | Aprobado   |
| CP-62  | RNF-13                 | Pedir a tres personas que nunca usaron el sistema que se registren, creen una categoría y registren su primer movimiento sin tutorial                                            | Cada persona lo logra en menos de 5 minutos                                                                                                                                 | Aprobado   |
| CP-63  | RNF-21                 | Registrar categorías y descripciones con tildes y la letra ñ (por ejemplo, «Educación», «Niños»)                                                                                 | Se guardan y se muestran correctamente, sin caracteres alterados                                                                                                            | Aprobado   |
| CP-64  | RNF-09, RNF-10, RNF-12 | Intentar borrar directamente en la base de datos una categoría referenciada; guardar montos con decimales; exportar la base con HeidiSQL o phpMyAdmin y restaurarla en otra base | Las llaves foráneas impiden registros huérfanos; no hay errores de redondeo; la copia restaurada conserva todos los datos                                                   | Aprobado   |

### 6.3 Criterios de aceptación
El sistema se considerará listo para la entrega cuando se cumpla todo lo siguiente:

● El 100 % de los casos de prueba de prioridad crítica (autenticación, aislamiento de datos, seguridad y cálculos presupuestales) está aprobado.

● Al menos el 95 % del total de casos ejecutados está aprobado.

● No quedan defectos críticos ni altos abiertos.

● Cada una de las 18 historias de usuario tiene al menos un caso de prueba aprobado (ver matriz de trazabilidad en el Anexo C).

● Los usuarios potenciales y el instructor validan que el sistema es pertinente y fácil de usar.

## 7. Gestión de defectos
### 7.1 Registro y priorización
Todo defecto detectado en una prueba, revisión o validación se registra en el formato del Anexo A con un identificador único (DEF-001, DEF-002…), el caso de prueba o revisión que lo detectó, los pasos para reproducirlo, el resultado obtenido frente al esperado y evidencia (captura de pantalla). Se prioriza según su severidad:

| **Severidad** | **Descripción**                                                | **Ejemplos en este proyecto**                                                                             | **Plazo de corrección**             |
|---------------|----------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------|-------------------------------------|
| Crítica       | Compromete datos, seguridad o impide usar el sistema           | Un usuario ve datos de otro; contraseña en texto plano; inyección SQL posible; no se puede iniciar sesión | Antes de continuar con otras tareas |
| Alta          | Una función principal produce un resultado incorrecto          | Saldo o ejecución presupuestal mal calculados; categoría con movimientos que se puede eliminar            | Antes de la siguiente entrega       |
| Media         | Una función secundaria falla o hay una validación incompleta   | Mensaje de error ausente; filtro combinado que no limpia                                                  | Antes de la entrega final           |
| Baja          | Problema visual o de redacción sin efecto en el funcionamiento | Color con poco contraste; texto mal escrito                                                               | Cuando haya disponibilidad          |

### 7.2 Asignación y corrección
El responsable de calidad asigna cada defecto según su origen: interfaz y presentación a Karoll Coronado; lógica PHP y cálculos a Juan Manuel Barreiro; base de datos, llaves e índices a Laura Isanoa; requisitos a Julio Casanova; documentos a Jaime Ladino. Quien corrige deja constancia del cambio realizado y la rama o commit donde se encuentra.

### 7.3 Verificación y repetición de pruebas
Un defecto avanza por los estados **Nuevo, Asignado, En corrección, Corregido, Verificado y Cerrado**. Si al repetir la prueba el problema persiste, pasa a **Reabierto**. Para cerrarlo, el tester repite el caso de prueba que lo detectó y los casos relacionados con el módulo, para comprobar que el problema se resolvió sin afectar otras funciones.

Defecto documental ya identificado en la revisión inicial:

| **ID**  | **Origen**   | **Descripción**                                                                                                                                                                                                      | **Severidad** | **Estado** |
|---------|--------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------|------------|
| DEF-001 | Revisión 5.1 | El SRS indicaba cuatro tablas en un objetivo específico y cinco en el resto del documento, y usaba «rubros» y «presupuestos» para el mismo concepto. Corregido en el SRS v2 (seis tablas y término «rubro» definido) | Media         | Corregido  |

## 8. Roles y responsabilidades
| **Integrante**                  | **Rol en el proyecto**                      | **Responsabilidades de calidad**                                                                                                                                       |
|---------------------------------|---------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Julio Andrés Casanova Ramírez   | Analista de requerimientos y líder          | Mantener el PAC y el SRS actualizados, aprobar cambios de requisitos, coordinar el seguimiento de indicadores y de defectos abiertos                                   |
| Juan Manuel Barreiro Polo       | Desarrollador back-end                      | Programar según los estándares, escribir y ejecutar las pruebas unitarias de las funciones PHP, corregir defectos de lógica, sesiones, seguridad y cálculos            |
| Laura Daniela Isanoa Estrada    | Diseñadora de base de datos                 | Garantizar el modelo de seis tablas, llaves, restricciones e índices; documentar el script SQL; verificar copias de respaldo y restauración                            |
| Karoll Nikol Coronado Bermeo    | Desarrolladora front-end y tester           | Implementar una interfaz que cumpla los requisitos de usabilidad; diseñar y ejecutar los casos de prueba; documentar resultados y comunicar defectos                   |
| Jaime Andrés Ladino Montealegre | Documentación, soporte y control de calidad | Verificar el cumplimiento de los criterios de calidad, apoyar la ejecución de pruebas, registrar y hacer seguimiento a los defectos, mantener los registros e informes |
| José de Jesús Motta Vargas      | Instructor (cliente que valida)             | Revisar el avance, validar los entregables y aprobar el cumplimiento de los criterios de aceptación                                                                    |
| Usuarios potenciales            | Validadores                                 | Participar en las pruebas de aceptación y de usabilidad (CP-62)                                                                                                        |

Una misma persona puede cumplir varios roles, pero **nadie aprueba su propio trabajo**: el código lo revisa otro integrante y los defectos los verifica quien no los corrigió.

## 9. Aseguramiento frente a control de calidad
| **Aspecto**           | **Aseguramiento de calidad (QA) en este proyecto**                                               | **Control de calidad (QC) en este proyecto**                                        |
|-----------------------|--------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------|
| Enfoque               | Los procesos del equipo para prevenir problemas                                                  | El producto, para encontrar defectos                                                |
| Actividades           | Estándares de código, revisión por pares, listas de verificación, control de versiones, este PAC | Ejecución de los casos CP-01 a CP-69, inspección de código, validación con usuarios |
| Pregunta clave        | ¿Estamos siguiendo un proceso adecuado?                                                          | ¿El sistema cumple los criterios de aceptación?                                     |
| Responsable principal | Julio Casanova y Jaime Ladino                                                                    | Karoll Coronado y Jaime Ladino                                                      |

Ambos enfoques se complementan: el aseguramiento ayuda a prevenir defectos con buenos procesos y el control detecta los que aún existen en el producto.

## 10. Indicadores de seguimiento
| **Indicador**                             | **Fórmula**                                                  | **Meta**                                    | **Frecuencia**             |
|-------------------------------------------|--------------------------------------------------------------|---------------------------------------------|----------------------------|
| Defectos encontrados por ciclo de pruebas | Cantidad de defectos registrados en el ciclo                 | Tendencia decreciente entre ciclos          | Cada ciclo                 |
| Porcentaje de pruebas aprobadas           | (Casos aprobados ÷ casos ejecutados) × 100                   | 100 % (Completado)                          | Cada ciclo                 |
| Avance de ejecución de pruebas            | (Casos ejecutados ÷ 69 casos planificados) × 100             | 100 % (69/69 completados)                   | Semanal                    |
| Defectos críticos y altos pendientes      | Número de defectos críticos y altos sin cerrar               | 0 (Cumplido)                                | Semanal                    |
| Tiempo promedio de corrección             | Promedio de días entre el registro y el cierre de un defecto | Menos de 3 días en severidad crítica y alta | Cada ciclo                 |
| Requisitos con prueba verificable         | (Historias con al menos un caso de prueba ÷ 18) × 100        | 100 %                                       | Una vez, al aprobar el PAC |
| Defectos reabiertos                       | Defectos que fallaron al repetir la prueba                   | 0 al cierre                                 | Cada ciclo                 |

## 11. Registros e informes
### 11.1 Formatos de pruebas
Se usan los formatos de los Anexos A y B: el formato de casos de prueba (con el resultado obtenido, fecha, responsable y estado) y el formato de registro de defectos. Se almacenan junto con la documentación del proyecto en una carpeta compartida por el equipo.

### 11.2 Informes de resultados
**●** **Informe de pruebas por ciclo:** casos ejecutados, aprobados y fallidos, defectos encontrados y estado de los indicadores. Lo elabora el tester y lo revisa el responsable de calidad.

**●** **Informe de regresión:** casos repetidos después de corregir defectos o modificar un módulo.

**●** **Informe final de calidad:** consolidado de los tres ciclos, comparación con los criterios de aceptación de la sección 6.3 y recomendación de entrega, para el instructor.

### 11.3 Evidencias
Capturas de pantalla de cada prueba relevante (en los tres navegadores y en tres tamaños de pantalla cuando aplique), exportación de la base de datos antes de cada entrega, resultados de las pruebas de rendimiento y de seguridad, actas de las pruebas de aceptación con usuarios y el historial de commits revisados.

## 12. Beneficios, limitaciones y riesgos
**Beneficios esperados:** menos errores y retrabajo, un sistema más seguro y confiable con los datos financieros de los usuarios, mejor comunicación dentro del equipo, evidencia del cumplimiento de los requisitos y un mantenimiento más sencillo.

**Limitaciones:** el PAC requiere tiempo para planificar, revisar y documentar; debe ajustarse al tamaño de un proyecto académico con equipo y cronograma limitados; y tener el documento no garantiza la calidad si las actividades no se ejecutan y se les da seguimiento.

<table>
<colgroup>
<col style="width: 32%" />
<col style="width: 28%" />
<col style="width: 39%" />
</colgroup>
<thead>
<tr class="header">
<th><strong>Riesgo</strong></th>
<th><strong>Efecto posible</strong></th>
<th><strong>Medida de mitigación</strong></th>
</tr>
<tr class="odd">
<th>Poco tiempo para ejecutar los 69 casos</th>
<th>Entrega con pruebas sin ejecutar</th>
<th>Priorizar casos críticos (autenticación, aislamiento, seguridad y cálculos) y ejecutar por módulo desde el ciclo 1</th>
</tr>
<tr class="header">
<th>Cambios de requisitos tardíos</th>
<th>Casos de prueba desactualizados</th>
<th>Actualizar SRS y PAC antes de programar el cambio y repetir las pruebas afectadas</th>
</tr>
<tr class="odd">
<th>Inconsistencias entre documentos (por ejemplo, número de tablas)</th>
<th>Confusión en la implementación y en la evaluación</th>
<th>Revisión documental con la lista 5.1 y corrección del DEF-001</th>
</tr>
<tr class="header">
<th>Fallas del entorno local (servicios de Apache o MySQL, versión de PHP)</th>
<th>Pruebas que fallan por el entorno y no por el código</th>
<th>Documentar la configuración de Laragon y verificar servicios antes de cada ciclo</th>
</tr>
<tr class="odd">
<th>Pérdida de información de la base de datos</th>
<th>Pérdida de datos y de evidencias</th>
<th>Exportar la base de datos antes de cada entrega y guardar copias en dos lugares</th>
</tr>
<tr class="header">
<th>Pocos usuarios disponibles para validar</th>
<th>Pruebas de aceptación y usabilidad incompletas</th>
<th><p>Convocar a los usuarios con anticipación y definir un mínimo de tres personas</p>
<h2 id="licenciamiento-y-condiciones-de-uso-del-proyecto"><strong>13. Licenciamiento y condiciones de uso del proyecto</strong></h2>
<p>El Sistema de Gestión Financiera Personal y Presupuestos se desarrolla de manera gratuita con fines estrictamente académicos y formativos dentro del programa Análisis y Desarrollo de Software (ADSO) del SENA (Ficha 3407799).</p>
<p>Bajo esta etapa académica, el software y su documentación asociada (SRS, PAC y Manual de Usuario) cuentan con uso libre y gratuito sin fines comerciales. Sus términos de licenciamiento están sujetos a modificaciones futuras por parte del equipo de desarrollo en caso de evolucionar hacia nuevas versiones o despliegues comerciales/productivos.</p>
<p>Asimismo, las tecnologías base utilizadas (PHP, MySQL/MariaDB, Apache, HTML5, CSS3 y Laragon) operan bajo sus respectivas licencias libres y gratuitas.</p></th>
</tr>
</thead>
<tbody>
</tbody>
</table>

## Conclusiones
El Plan de Aseguramiento de Calidad organiza, para el Sistema de Gestión Financiera Personal y Presupuestos, los estándares, las revisiones, las pruebas, los responsables y el manejo de defectos que permitirán desarrollar un software confiable y acorde con el SRS. Sus 69 casos de prueba, cada uno con un procedimiento y un resultado esperado definidos antes de ejecutarlos, cubren las 18 historias de usuario, los 9 módulos funcionales y los requisitos no funcionales más importantes.

El plan no elimina todos los errores, pero ayuda a prevenirlos, a detectarlos oportunamente y a reducir sus consecuencias. En un sistema que maneja información financiera personal, esto significa comprobar que los movimientos se registren correctamente, que los saldos y las ejecuciones presupuestales se calculen de forma coherente, que cada usuario vea únicamente sus datos y que las contraseñas estén protegidas. Su valor real dependerá de que el equipo ejecute las pruebas, registre los resultados y use los indicadores para mejorar el producto y el proceso hasta la entrega.

## Referencias
Cillero, M. (s. f.). *Aseguramiento de la calidad. Métrica 3.* https://manuel.cillero.es/doc/metodologia/metrica-3/interfaces/aseguramiento-de-lacalidad/

Congreso de la República de Colombia. (2012). *Ley 1581 de 2012, por la cual se dictan disposiciones generales para la protección de datos personales.* Bogotá, Colombia.

International Organization for Standardization. (2023). *ISO/IEC/IEEE 12207:2023 Systems and software engineering—Software life cycle processes.* https://www.iso.org/standard/81702.html

OWASP Foundation. (s. f.). *OWASP Top 10 – Web Application Security Risks.* https://owasp.org/www-project-top-ten/

Rocha Suárez, B. A., Vega Pérez, K. S. y Narváez Gonzales, J. J. (s. f.). *Plan de Aseguramiento de Calidad (PAC) en programación.* CIES SENA Neiva.

The PHP Group. (s. f.). *PHP Manual.* https://www.php.net/manual/es/

Universidad Nacional de la Patagonia San Juan Bosco. (s. f.). *Introducción a la ingeniería de software.* https://unpsjb.github.io/ids3t/introduccion.html
