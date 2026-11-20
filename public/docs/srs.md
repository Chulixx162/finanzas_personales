**SRS**

**Sistema de Gestión Financiera Personal y Presupuestos**

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

**Neiva – Huila**

**1. INTRODUCCIÓN**

La administración del dinero es una de las competencias más importantes en la vida cotidiana, pero también una de las menos practicadas de forma ordenada. Muchas personas reciben ingresos de manera periódica y los gastan sin un registro formal, por lo que al final del mes no saben con certeza en qué se fue su dinero ni cuánto les queda disponible. En un entorno donde el acceso a medios de pago, compras en línea y créditos es cada vez más fácil, la falta de control financiero personal se traduce en endeudamiento, ausencia de ahorro y decisiones económicas improvisadas.

Aunque existen aplicaciones de finanzas personales, muchas son de pago, tienen un nivel de complejidad que desmotiva su uso diario o no permiten distribuir un presupuesto de forma flexible entre varias categorías de gasto. Esto deja a un gran número de personas sin una herramienta sencilla que les permita registrar sus movimientos, planificar su dinero y verificar si están cumpliendo lo que se propusieron.

En respuesta a esta necesidad, el proyecto "Sistema de Gestión Financiera Personal y Presupuestos" propone una aplicación web multiusuario en la que cada persona puede crear su cuenta, registrar gastos, clasificarlos por categorías (alimentación, transporte, educación, entretenimiento, entre otras) y definir presupuestos para períodos determinados. Las categorías no tienen presupuesto propio: cada categoría puede dividirse en rubros y es cada rubro el que cuenta con su propio presupuesto (por ejemplo, dentro de alimentación, los rubros mercado y restaurantes), y el sistema compara automáticamente lo planificado en cada rubro con lo realmente gastado, indicando cuánto se ha consumido y cuánto dinero queda disponible.

#

# 1.1 PLANTEAMIENTO DEL PROBLEMA
Una gran parte de las personas no lleva un control formal de su dinero. Los ingresos y gastos se registran de manera informal (en la memoria, en cuadernos o en hojas de cálculo dispersas), lo que genera problemas como:

● Falta de visibilidad sobre en qué se gasta realmente el dinero cada mes.

● Dificultad para identificar los gastos hormiga y las categorías donde se concentra el mayor consumo.

● Ausencia de límites claros de gasto, lo que lleva a agotar el dinero disponible antes de terminar el periodo.

● Imposibilidad de comparar de forma objetiva lo planificado frente a lo realmente gastado.

● Información financiera desorganizada, sin historial consultable ni filtros por fecha o categoría.

● Dependencia de cálculos manuales propensos a errores para conocer el saldo disponible.

Esta situación evidencia una brecha entre la necesidad de las personas de controlar su economía y la disponibilidad de herramientas simples, accesibles y adaptables a su forma de planificar el gasto.

¿De qué forma se podría implementar una solución web sencilla y multiusuario que permita registrar ingresos y gastos, organizarlos por categorías y compararlos con presupuestos definidos por el propio usuario, para facilitar el control de su economía personal?

# 1.2 PROPÓSITO
Desarrollar una aplicación web de gestión financiera personal que permita a cada usuario registrar y clasificar sus ingresos y gastos, definir presupuestos por periodo distribuidos entre varios rubros y conocer en todo momento su saldo, su nivel de gasto y el dinero que tiene disponible, con el fin de promover hábitos de ahorro, mejorar la planificación económica y apoyar la toma de decisiones financieras responsables.

# 1.3 JUSTIFICACIÓN DEL PROYECTO
Muchas personas reciben su dinero cada mes y, al llegar el final del periodo, no saben con claridad en qué se fue ni cuánto les queda disponible. No se trata de ganar poco o mucho: aun con ingresos estables, la falta de un registro ordenado hace que los gastos pequeños y frecuentes pasen desapercibidos, que el dinero se agote antes de tiempo y que se recurra al endeudamiento para cubrir necesidades que pudieron planificarse. Esta situación genera estrés, dificulta el ahorro y deja a las personas tomando decisiones económicas sin información.

Las alternativas que hoy se usan para resolver este problema no siempre funcionan. Los cuadernos y las hojas de cálculo dependen de la constancia y de cálculos manuales que se prestan a errores, y muchas aplicaciones existentes son demasiado complejas para el uso diario o no permiten planificar el dinero de la forma en que las personas realmente lo piensan. Cuando una herramienta resulta incómoda, se abandona a las pocas semanas y el problema continúa.

El Sistema de Gestión Financiera Personal y Presupuestos responde a esta necesidad con una solución sencilla y directa. Permite registrar ingresos y gastos en pocos pasos, clasificarlos por categorías como alimentación, transporte, educación o entretenimiento, y conocer en cualquier momento cuánto se ha recibido, cuánto se ha gastado y cuánto dinero queda disponible.

Su principal aporte es la planificación por presupuestos. El usuario puede definir cuánto dinero destinará en un periodo, por ejemplo \$1.000.000 en un mes, y repartirlo entre los rubros que le interesen. A medida que registra sus gastos, el sistema los compara automáticamente con lo planificado e indica cuánto lleva gastado y cuándo ha superado el límite que se propuso. De esta forma, el usuario puede corregir sus hábitos a tiempo y no al final del mes, cuando ya no hay margen de reacción.

Además, el sistema mantiene la información de cada persona organizada, histórica y privada: cada usuario accede únicamente a sus propios datos y puede consultarlos por tipo de movimiento o categoría, sin necesidad de hacer cuentas manuales.

En conjunto, el proyecto se justifica porque ofrece a las personas una forma práctica de conocer su situación económica real, planificar sus gastos y desarrollar hábitos de ahorro y consumo más responsables, con una herramienta accesible desde el navegador y fácil de usar sin necesidad de conocimientos financieros ni técnicos.

# 1.4 OBJETIVO GENERAL
Diseñar y desarrollar un sistema web de gestión financiera personal y presupuestos que permita a múltiples usuarios registrar y consultar sus ingresos y gastos, clasificarlos por categorías y rubros, comparando lo planificado con lo realmente gastado para facilitar el control de su economía.

## 1.4.1 OBJETIVOS ESPECÍFICOS
1\. Identificar y analizar las necesidades de las personas en cuanto al registro, clasificación y control de sus ingresos y gastos.

2\. Diseñar una base de datos relacional en MySQL compuesta por seis tablas (usuarios, categorias, movimientos, presupuestos, presupuesto_categoria y rubros), con sus llaves primarias, foráneas y restricciones de integridad.

3\. Implementar un módulo de autenticación que permita el registro, inicio y cierre de sesión, garantizando que cada usuario acceda únicamente a su propia información.

4\. Desarrollar el registro, consulta y modificación (edición) de movimientos de tipo ingreso y gasto, restringiendo su eliminación por motivos de seguridad, trazabilidad e integridad de las relaciones entre entidades.

5\. Permitir la gestión de categorías para clasificar los movimientos según su naturaleza.

6\. Implementar la creación y administración de presupuestos por periodo, asignando a cada uno las categorías que el usuario necesite mediante la relación N:M (tabla intermedia presupuesto_categoria), sin monto propio, y definiendo dentro de cada categoría rubros con presupuesto propio, cuya suma total no supere el monto total del presupuesto.

7\. Calcular automáticamente el total de ingresos, el total de gastos, el saldo disponible y el porcentaje de ejecución del presupuesto de cada rubro.

8\. Ofrecer filtros de consulta por fecha, tipo de movimiento y categoría.

9\. Construir una interfaz web clara y sencilla con HTML y CSS, compatible con los navegadores modernos.

10\. Aplicar medidas básicas de seguridad: cifrado de contraseñas, sentencias preparadas y validación de datos de entrada.

11\. Evaluar el funcionamiento del sistema mediante pruebas funcionales y validaciones con usuarios potenciales.

# 1.5 ALCANCE DEL PROYECTO
El proyecto contempla el diseño y desarrollo de una aplicación web de gestión financiera personal y presupuestos, orientada a personas naturales que desean controlar sus ingresos y gastos.

Incluye:

1\. Diseño de la base de datos relacional de seis tablas.

2\. Registro de usuarios, inicio y cierre de sesión.

3\. Registro, consulta y modificación de movimientos de tipo ingreso y gasto (sin opción de eliminación por seguridad e integridad referencial entre entidades), con fecha, monto, descripción, categoría y, en los gastos, rubro opcional.

4\. Gestión de categorías (crear, consultar, editar y eliminar).

5\. Gestión de presupuestos asociados a un periodo (fecha de inicio y fecha de fin).

6\. Asignación de varias categorías a un presupuesto (sin monto propio) y definición de rubros dentro de cada categoría, cada uno con su propio presupuesto, sin que la suma de todos los rubros supere el monto total del presupuesto.

7\. Cálculo y visualización de totales de ingresos, gastos y saldo por periodo.

8\. Comparación entre el monto presupuestado y el gasto real por rubro, indicando valor gastado, valor disponible y si se excedió el presupuesto del rubro.

9\. Filtros de movimientos por rango de fechas, tipo y categoría.

10\. Separación de la información de cada usuario.

11\. Pruebas funcionales y validación del sistema.

No incluye:

1\. Conexión con bancos, billeteras digitales ni pasarelas de pago.

2\. Procesamiento de transacciones o transferencias reales de dinero.

3\. Asesoría financiera o de inversiones.

4\. Aplicación móvil nativa (únicamente interfaz web adaptable).

5\. Manejo de múltiples monedas o conversión de divisas; se trabaja con pesos colombianos (COP).

6\. Roles administrativos dentro de la aplicación ni gestión de usuarios por terceros.

Beneficios esperados:

● Mayor control y claridad sobre los ingresos y gastos personales.

● Mejor planificación financiera mediante presupuestos por periodo.

● Detección temprana de excesos de gasto por rubro.

● Información organizada, histórica y de fácil consulta.

Tecnologías: PHP estructurado (sin frameworks), MySQL, HTML5, CSS3 y entorno de desarrollo local Laragon (Apache, MySQL y PHP).

# 1.6 PERSONAL INVOLUCRADO
El proyecto se desarrollará bajo un esquema colaborativo entre el equipo de desarrollo y el Instructor o cliente que valida los entregables.

Instructor o director del proyecto:
JOSÉ DE JESÚS MOTTA VARGAS. Orienta el desarrollo, valida el cumplimiento de los criterios técnicos, supervisa los avances y evalúa los entregables parciales y finales. Contacto: directo.

Equipo de desarrollo:
Conformado por

● Julio Andrés Casanova Ramírez

● Laura Daniela Isanoa Estrada

● Karoll Nikol Coronado Bermeo

● Juan Manuel Barreiro Polo

● Jaime Andrés Ladino Montealegre

distribuidos en los siguientes roles:

● Julio Andrés Casanova Ramírez – Analista de requerimientos / Líder del proyecto: levantamiento de información y documentación técnica, coordinación del equipo.

● Juan Manuel Barreiro Polo – Desarrollador back-end: programación en PHP, lógica de negocio y conexión con la base de datos.

● Laura Daniela Isanoa Estrada – Diseñador de base de datos: modelo entidad-relación, scripts SQL y consultas.

● Karoll Nikol Coronado Bermeo– Desarrollador front-end y tester: interfaz en HTML y CSS, pruebas funcionales y validación del sistema.

● Jaime Andrés Ladino Montealegre – Responsable de documentación, soporte y control de calidad: elaboración y actualización de la documentación del sistema, seguimiento de los avances y entregables, apoyo en la ejecución de pruebas, identificación y reporte de errores, y verificación del cumplimiento de los criterios de calidad establecidos para el proyecto.

Usuarios finales:
Personas naturales que desean controlar sus finanzas personales y que participarán en pruebas y validaciones para garantizar que el sistema sea pertinente y fácil de usar.

# 1.7 DEFINICIONES, ACRÓNIMOS Y ABREVIATURAS
## 1. Definiciones

Usuario: persona que se registra en el sistema para administrar sus finanzas.
Movimiento: registro de una operación financiera, que puede ser un ingreso o un gasto.
Ingreso: dinero que recibe el usuario (salario, ventas, regalos, etc.).
Gasto: dinero que el usuario destina a un pago o compra.
Categoría: clasificación que agrupa movimientos de naturaleza similar (alimentación, transporte, educación, entretenimiento, etc.). La categoría no tiene presupuesto propio.
Presupuesto: plan de gasto definido por el usuario para un periodo, con un monto total establecido.
Rubro: subdivisión de una categoría dentro de un presupuesto (por ejemplo, mercado y restaurantes dentro de alimentación). Es el único elemento con presupuesto propio dentro de un presupuesto: la categoría no tiene monto y el presupuesto del periodo se reparte entre los rubros.
Presupuesto por rubro: monto asignado a un rubro. La suma de los presupuestos de todos los rubros de un presupuesto no puede superar el monto total del presupuesto.
Periodo: intervalo de tiempo delimitado por una fecha de inicio y una fecha de fin (por ejemplo, un mes).
Saldo: diferencia entre el total de ingresos y el total de gastos de un periodo.
Saldo disponible del presupuesto: diferencia entre el monto presupuestado y el monto gastado.
Ejecución presupuestal: porcentaje del presupuesto de un rubro que ya ha sido consumido.
Gasto hormiga: gasto pequeño y frecuente que pasa desapercibido pero que, acumulado, representa un valor importante.
Sesión: mecanismo por el cual el sistema identifica a un usuario autenticado durante su navegación.
Relación muchos a muchos (N:M): relación en la que un registro de una tabla puede asociarse con varios registros de otra y viceversa; se implementa mediante una tabla intermedia.
Tabla intermedia: tabla que resuelve una relación N:M; en este proyecto, presupuesto_categoria.
Educación financiera: conocimientos y hábitos que permiten administrar el dinero de forma responsable.

Laragon: entorno de desarrollo local para Windows que integra un servidor web (Apache o Nginx), un gestor de base de datos (MySQL o MariaDB) y PHP, permitiendo ejecutar aplicaciones web en el equipo del desarrollador.

## 2. Acrónimos

PHP: Hypertext Preprocessor.
SQL: Structured Query Language (Lenguaje de Consulta Estructurada).
BD: Base de Datos.
HTML: HyperText Markup Language.
CSS: Cascading Style Sheets.
HTTP / HTTPS: HyperText Transfer Protocol / Protocol Secure.
CRUD: Create, Read, Update, Delete (crear, consultar, actualizar y eliminar).
PK: Primary Key (llave primaria).
FK: Foreign Key (llave foránea).
XSS: Cross-Site Scripting.
COP: Peso colombiano.
UI: User Interface (Interfaz de Usuario).
UX: User Experience (Experiencia de Usuario).
APACHE: servidor web de código abierto utilizado para ejecutar la aplicación.

MariaDB: sistema gestor de bases de datos compatible con MySQL, incluido en Laragon.

## 3. Abreviaturas

Art.: Artículo.
No.: Número.
App.: Aplicación.
Dpto.: Departamento.

#

# 1.8 REFERENCIAS
Congreso de la República de Colombia. (2012). Ley 1581 de 2012, por la cual se dictan disposiciones generales para la protección de datos personales. Bogotá, Colombia.

Elmasri, R., & Navathe, S. B. (2016). Fundamentos de Sistemas de Bases de Datos (7.ª ed.). Pearson Educación.

Laragon. Laragon Documentation. [<u>https://laragon.org/docs/</u>](https://laragon.org/docs/)

The PHP Group. PHP Manual. https://www.php.net/manual/es/

Oracle Corporation. MySQL Reference Manual. https://dev.mysql.com/doc/

Mozilla Developer Network (MDN). Documentación de HTML y CSS. https://developer.mozilla.org/es/

OWASP Foundation. OWASP Top 10 – Web Application Security Risks. https://owasp.org/www-project-top-ten/

(Verifique que el formato de citación coincida con el exigido por su institución. Si desea respaldar la justificación con datos de educación financiera o endeudamiento en Colombia, puede agregar fuentes como la Superintendencia Financiera de Colombia o el Banco de la República, citando el informe y el año exactos que consulte.)

#

# 1.9 RESUMEN
El proyecto "Sistema de Gestión Financiera Personal y Presupuestos" consiste en el desarrollo de una aplicación web orientada a que cada persona pueda controlar sus ingresos y gastos de forma organizada. Surge ante la falta de control financiero formal de muchas personas, que registran su dinero de manera informal, desconocen en qué lo gastan y no cuentan con límites claros ni con una forma objetiva de comparar lo planificado con lo realmente gastado.

El sistema permite a los usuarios crear una cuenta, iniciar sesión y registrar sus movimientos clasificándolos por categorías como alimentación, transporte, educación o entretenimiento. Además, permite crear presupuestos para períodos determinados y asignarles categorías, que se dividen en rubros; solo los rubros tienen presupuesto propio. Para ello, la base de datos se compone de seis tablas (usuarios, movimientos, categorias, presupuestos, presupuesto_categoria y rubros), donde presupuesto_categoria resuelve la relación muchos a muchos entre presupuestos y categorías (sin almacenar montos), y rubros contiene el presupuesto de cada división de una categoría asignada.

A partir de la información registrada, el sistema calcula ingresos, gastos, saldos y la ejecución del presupuesto de cada rubro, y permite filtrar los movimientos por fecha, tipo y categoría. Con ello se espera que los usuarios mejoren su planificación económica, detecten oportunamente los excesos de gasto y desarrollen hábitos de ahorro, mediante una herramienta sencilla, de bajo costo y accesible desde el navegador.

**2. DESCRIPCIÓN GENERAL**

# 2.1 PERSPECTIVAS DEL PRODUCTO
El Sistema de Gestión Financiera Personal y Presupuestos es un producto de software independiente (standalone), diseñado para que personas naturales administren sus finanzas desde el navegador. No reemplaza ni extiende ningún sistema existente y no depende de servicios externos para su operación principal. Toda la información es ingresada manualmente por el usuario.

El sistema funciona como una aplicación web de arquitectura cliente-servidor y se organiza en tres capas:

● Capa de presentación: páginas HTML con estilos CSS (formularios, tablas, listados y paneles de resumen).

● Capa de lógica de negocio: scripts PHP que gestionan autenticación, sesiones, validaciones y cálculos de ingresos, gastos, saldos y ejecución presupuestal.

● Capa de datos: base de datos MySQL con seis tablas relacionadas.

El sistema interactúa con el entorno mediante las siguientes interfaces:

● Interfaz de usuario: aplicación web accesible desde navegadores en computadores, tablets y teléfonos inteligentes, con formularios, listados y paneles de resumen de fácil lectura.

● Interfaz de hardware: funciona en dispositivos convencionales, sin requerir hardware especializado.

● Interfaz de software: el servidor utiliza Apache y PHP provistos por Laragon, y se comunica con MySQL mediante sentencias preparadas (mysqli o PDO). No se integra con APIs de terceros, bancos ni pasarelas de pago.

● Interfaz de comunicaciones: utiliza el protocolo HTTP/HTTPS para la comunicación entre el navegador, el servidor y la base de datos.

Modelo de datos general. La base de datos está compuesta por seis tablas:

● usuarios: datos de acceso de cada persona registrada (identificador, nombre, correo y contraseña cifrada).

● categorias: clasificaciones de los movimientos (identificador, nombre y usuario propietario).

● movimientos: cada ingreso o gasto (identificador, usuario, categoría, rubro (opcional, solo para gastos), tipo, monto, fecha y descripción).

● presupuestos: presupuestos por periodo (identificador, usuario, nombre, monto total, fecha de inicio y fecha de fin).

● presupuesto_categoria: tabla intermedia que resuelve la relación N:M entre presupuestos y categorías (identificador del presupuesto e identificador de la categoría). No almacena montos: las categorías no tienen presupuesto propio.

● rubros: subdivisiones de una categoría dentro de un presupuesto, cada una con su propio presupuesto; es la única tabla que almacena montos presupuestados (identificador, presupuesto, categoría, nombre y monto del rubro).

Relaciones principales:

● Un usuario tiene muchos movimientos, muchas categorías y muchos presupuestos (1:N).

● Una categoría puede clasificar muchos movimientos (1:N).

● Un presupuesto puede incluir muchas categorías y una categoría puede estar en muchos presupuestos (N:M), resuelto mediante presupuesto_categoria.

● Una categoría asignada a un presupuesto (registro de presupuesto_categoria) puede dividirse en muchos rubros (1:N). Solo los rubros tienen presupuesto: la suma de los montos de todos los rubros de un presupuesto no puede superar el monto total de ese presupuesto.

● Un rubro puede asociarse opcionalmente a muchos movimientos de tipo gasto (1:N). El gasto real de un rubro se calcula con los movimientos de tipo gasto asociados a él.

Funciones principales del producto:

● Gestión de cuentas de usuario (registro, inicio y cierre de sesión).

● Gestión de movimientos (ingresos y gastos).

● Gestión de categorías.

● Gestión de presupuestos, asignación de categorías y asignación de montos por rubro.

● Consultas con filtros por fecha, tipo y categoría.

● Cálculo de totales, saldos y comparación entre lo presupuestado y lo gastado.

#

# 2.2 CARACTERÍSTICAS DE LOS USUARIOS
Para el desarrollo del sistema se identifican los siguientes perfiles, considerando su nivel educativo, experiencia técnica y habilidades, con el fin de diseñar una interfaz accesible y funcional.

## 1. Usuario registrado

Persona natural que utiliza la plataforma para controlar sus finanzas personales. Debe registrarse e iniciar sesión para acceder a sus funciones.

Educación: nivel educativo variado (básico en adelante); no se exige formación financiera ni técnica.
Experiencia técnica: nivel básico en el uso de internet y diligenciamiento de formularios.
Habilidades específicas: comprender conceptos básicos como ingreso, gasto y presupuesto; registrar información de forma oportuna y consultar sus resúmenes.
Frecuencia de uso: diaria o semanal.
Permisos: gestionar únicamente su propia información (movimientos, categorías y presupuestos).

## 2. Visitante

Persona que accede al sistema sin haber iniciado sesión. No puede utilizar las funciones de gestión financiera.

Educación: nivel educativo variado.
Experiencia técnica: nivel básico en el uso de internet.
Habilidades específicas: navegar y diligenciar formularios de registro o inicio de sesión.
Permisos: solo puede acceder a las páginas de registro e inicio de sesión.

## 3. Administrador técnico de la plataforma

Persona encargada del despliegue y mantenimiento del servidor y la base de datos. No es un rol dentro de la aplicación.

Educación: formación técnica o tecnológica en sistemas o software.
Experiencia técnica: nivel medio o alto en PHP, MySQL y administración de servidores.
Habilidades específicas: instalación, respaldos y mantenimiento de la base de datos.
Permisos: acceso directo al servidor y a la base de datos, fuera de la interfaz de la aplicación.

Observaciones:

● El sistema no incluye roles administrativos dentro de la aplicación; todos los usuarios registrados tienen los mismos permisos sobre sus propios datos.

● La interfaz debe ser lo suficientemente intuitiva para que no se requiera capacitación previa.

#

# 2.3 RESTRICCIONES
El desarrollo y despliegue del sistema están sujetos a las siguientes limitaciones:

● Restricciones tecnológicas: se desarrolla con PHP estructurado (sin frameworks), MySQL, HTML y CSS. El acceso a la base de datos se realiza mediante sentencias preparadas (mysqli o PDO).

● Restricciones de diseño: la base de datos se compone exactamente de seis tablas, con llaves primarias y foráneas e integridad referencial. La relación entre presupuestos y categorías se implementa obligatoriamente mediante la tabla intermedia presupuesto_categoria, y los rubros se almacenan en la tabla rubros, vinculados a una categoría asignada a un presupuesto. La tabla presupuesto_categoria no almacena montos, porque solo los rubros tienen presupuesto. Los movimientos de tipo gasto pueden referenciar opcionalmente un rubro mediante una llave foránea (rubro_id) en la tabla movimientos, sin agregar tablas.

● Manejo de montos: todos los valores monetarios se manejan en una única moneda (COP) y con tipo de dato decimal para evitar errores de redondeo.

● Seguridad: las contraseñas deben almacenarse cifradas (por ejemplo, con password_hash de PHP) y nunca en texto plano; todas las páginas internas deben verificar una sesión activa; cada usuario solo puede consultar, modificar o eliminar sus propios registros; los datos de entrada deben validarse y sanearse en el servidor para prevenir inyección SQL y XSS. Por motivos de seguridad, auditoría e integridad de las relaciones entre entidades (usuarios, categorías, presupuestos y rubros), el sistema permite modificar los movimientos registrados pero no permite su eliminación.

● Normativa legal: el sistema debe respetar la Ley 1581 de 2012 sobre protección de datos personales, tratando de forma responsable la información de los usuarios registrados.

● Compatibilidad de navegadores: debe funcionar correctamente en Google Chrome, Mozilla Firefox y Microsoft Edge en sus versiones recientes.

● Entorno de ejecución: el sistema se desarrolla y se ejecuta en un entorno local con Laragon, que provee Apache, MySQL (o MariaDB) y PHP. No se contempla en esta versión su despliegue en servidores de producción.

● Alcance y proyecto: se desarrolla como proyecto académico, con un cronograma y un equipo limitado; no incluye reportes exportables, gráficos avanzados, aplicación móvil ni integración con entidades financieras.

# 2.4 SUPOSICIONES Y DEPENDENCIAS
A diferencia de las restricciones, estas son condiciones que se espera que se cumplan para que el sistema funcione correctamente, pero que no dependen directamente del equipo de desarrollo.

## 2.4.1 Suposiciones (Assumptions)

● Se asume que los usuarios cuentan con un dispositivo con navegador web actualizado y con conexión al servidor donde se aloje el sistema.

● Se asume que los usuarios poseen conocimientos básicos para navegar en aplicaciones web.

● Se asume que los usuarios registrarán sus movimientos de forma honesta y oportuna; la calidad de los cálculos depende de la calidad de los datos ingresados.

● Se asume que cada usuario se registra con un correo electrónico único y no comparte su cuenta con otras personas.

● Se asume que todos los montos se expresan en pesos colombianos (COP).

● Se asume que un presupuesto corresponde a un periodo definido por una fecha de inicio y una de fin, y que la suma de los presupuestos de todos sus rubros no puede superar el monto total del presupuesto.

● Se asume que cada rubro pertenece a una categoría asignada a un presupuesto y que es el único nivel con presupuesto propio: las categorías solo agrupan rubros y no tienen monto.

● Se asume que el gasto real de un rubro dentro de un presupuesto se calcula con los movimientos de tipo gasto asociados a ese rubro cuya fecha esté dentro del periodo del presupuesto.

● Se asume que el equipo donde se ejecuta el sistema tiene instalado Laragon con PHP y las extensiones necesarias para conectarse a MySQL (mysqli o PDO) habilitadas.

## 2.4.2 Dependencias (Dependencies)

● Dependencia de Laragon como entorno de desarrollo local, que provee el servidor web Apache, PHP (se recomienda la versión 8.0 o superior) y el gestor de base de datos MySQL o MariaDB.

● Dependencia de que los servicios de Apache y MySQL estén en ejecución en Laragon para poder usar el sistema.

● Dependencia de un navegador web moderno con soporte para HTML5 y CSS3.

● Dependencia de un mecanismo de respaldo de la base de datos (por ejemplo, exportación periódica con HeidiSQL o phpMyAdmin, ambos disponibles en Laragon) para evitar la pérdida de información.

● Dependencia de la disponibilidad del equipo de desarrollo y del docente o cliente para validar el sistema en las fechas establecidas.

**3. REQUISITOS ESPECÍFICOS**

Las categorías son propias de cada usuario y existe un solo rol dentro de la aplicación (usuario registrado), además del visitante. Las categorías solo agrupan: no tienen presupuesto; únicamente los rubros lo tienen.

# 3.1 REQUISITOS COMUNES DE LAS INTERFACES
## 3.1.1 Interfaz de Usuario (UI)

● Diseño intuitivo, limpio y de fácil navegación, que no requiera capacitación previa.

● Construida únicamente con HTML5 y CSS3, con menú de navegación visible en todas las páginas internas (Inicio, Movimientos, Categorías, Presupuestos, Cerrar sesión).

● Diseño adaptable (responsive) para computadores, tablets y teléfonos inteligentes.

● Uso de botones visibles, tipografía legible y contraste adecuado entre texto y fondo.

● Los montos se muestran en pesos colombianos (COP) con separador de miles (ejemplo: \$1.000.000).

● Los ingresos y gastos se diferencian visualmente (por ejemplo, ingresos en verde y gastos en rojo).

● Los rubros cuyo gasto supere su presupuesto se resaltan visualmente, y los que superen el 80 % de su presupuesto muestran una advertencia.

● Mensajes de validación claros, comprensibles y ubicados cerca del campo afectado.

● Confirmación previa antes de eliminar cualquier registro.

● Idioma de la interfaz: español.

## 3.1.2 Interfaz de Hardware

● Compatibilidad con dispositivos estándar (computador, tablet y teléfono inteligente).

● Soporte para interacción con teclado, mouse y pantalla táctil.

● No requiere hardware especializado.

## 3.1.3 Interfaz de Software

● Servidor web Apache y PHP estructurado, provistos por Laragon.

● Base de datos MySQL (o MariaDB) accedida mediante mysqli o PDO con sentencias preparadas.

● Navegadores compatibles: Google Chrome, Mozilla Firefox y Microsoft Edge en sus versiones recientes.

● Manejo de sesiones mediante las sesiones nativas de PHP.

● No se integra con APIs, servicios en la nube, bancos ni pasarelas de pago.

## 3.1.4 Interfaz de Comunicación

● Comunicación entre el navegador y el servidor mediante el protocolo HTTP en el entorno local de Laragon; se recomienda HTTPS en un eventual despliegue en producción.

● Envío de datos mediante formularios HTML con los métodos GET (consultas y filtros) y POST (registro, edición y eliminación).

● Identificación del usuario autenticado mediante cookie de sesión de PHP.

# 3.2 HISTORIAS DE USUARIO
HU-1: Registro de usuario
Historia: Como persona que quiere controlar sus finanzas, quiero crear una cuenta con mi nombre, correo y contraseña, para tener un espacio personal donde guardar mi información.

Criterios de Aceptación: El formulario valida campos obligatorios, formato de correo y contraseña mínima de 8 caracteres. No permite registrar un correo ya existente. La contraseña se almacena cifrada.

HU-2: Inicio de sesión
Historia: Como usuario registrado, quiero iniciar sesión con mi correo y contraseña, para acceder de forma segura a mi información.

Criterios de Aceptación: Con credenciales correctas se accede al panel principal; con credenciales incorrectas se muestra un mensaje de error sin indicar cuál dato falló.

HU-3: Cierre de sesión
Historia: Como usuario, quiero cerrar mi sesión, para que nadie más acceda a mis datos desde el mismo equipo.

Criterios de Aceptación: Al cerrar sesión se destruye la sesión y las páginas internas dejan de ser accesibles sin volver a autenticarse.

HU-4: Registrar ingresos
Historia: Como usuario, quiero registrar el dinero que recibo indicando fecha, monto, descripción y categoría, para llevar el control de mis entradas.

Criterios de Aceptación: El movimiento se guarda con tipo "ingreso". El monto debe ser mayor a cero y la fecha es obligatoria.

HU-5: Registrar gastos
Historia: Como usuario, quiero registrar mis gastos indicando fecha, monto, descripción y categoría, para saber en qué se va mi dinero.

Criterios de Aceptación: El movimiento se guarda con tipo "gasto". El monto debe ser mayor a cero y la categoría es obligatoria. Opcionalmente puede asociarse a un rubro de esa categoría, cuyo presupuesto cubra la fecha del gasto.

HU-6: Consultar movimientos
Historia: Como usuario, quiero ver el listado de mis movimientos ordenados del más reciente al más antiguo, para revisar mi historial.

Criterios de Aceptación: Se listan solo los movimientos del usuario autenticado, mostrando fecha, tipo, categoría, descripción y monto.

HU-7: Editar movimientos
Historia: Como usuario, quiero corregir un movimiento que registré con errores, para mantener mi información exacta.

Criterios de Aceptación: Dado que los movimientos no pueden eliminarse por seguridad e integridad referencial, la edición es el mecanismo oficial para corregir cualquier dato registrado con error. El formulario se carga con los datos actuales; al guardar, se actualiza el registro y los totales se recalculan.

HU-8: Restricción de eliminación de movimientos
Historia: Como usuario, comprendo que por políticas de seguridad, trazabilidad e integridad de las relaciones entre entidades no puedo eliminar movimientos, para garantizar la consistencia de mi historial financiero.

Criterios de Aceptación: Por políticas de seguridad, trazabilidad e integridad de las relaciones entre entidades, el sistema no permite eliminar movimientos una vez registrados; en caso de error, el usuario debe utilizar la función de modificación/edición.

HU-9: Filtrar movimientos
Historia: Como usuario, quiero filtrar mis movimientos por rango de fechas, tipo y categoría, para encontrar rápidamente la información que necesito.

Criterios de Aceptación: Los filtros pueden combinarse y el listado y los totales se actualizan según los filtros aplicados.

HU-10: Gestionar categorías
Historia: Como usuario, quiero crear, editar y eliminar categorías (alimentación, transporte, educación, entretenimiento, etc.), para clasificar mis movimientos a mi manera.

Criterios de Aceptación: No se permiten nombres duplicados dentro de las categorías del mismo usuario. No se puede eliminar una categoría que tenga movimientos o presupuestos asociados.

HU-11: Crear presupuesto
Historia: Como usuario, quiero crear un presupuesto con nombre, monto total, fecha de inicio y fecha de fin, para planificar cuánto dinero voy a gastar en un periodo.

Criterios de Aceptación: La fecha de fin debe ser posterior o igual a la de inicio y el monto total debe ser mayor a cero.

HU-12: Asignar categorías a un presupuesto
Historia: Como usuario, quiero asignar a un presupuesto las categorías en las que voy a gastar (por ejemplo, alimentación, transporte y educación), para organizar mi planificación por tipo de gasto.

Criterios de Aceptación: Se pueden asignar varias categorías a un presupuesto y una categoría puede estar en varios presupuestos. No se repite la misma categoría dentro de un presupuesto. La categoría no tiene monto propio: el dinero se asigna únicamente a través de sus rubros (HU-13).

HU-13: Definir rubros con presupuesto dentro de una categoría
Historia: Como usuario, quiero dividir una categoría de un presupuesto en rubros y asignar a cada uno su propio presupuesto (por ejemplo, en alimentación: \$250.000 para mercado y \$150.000 para restaurantes), para detallar en qué se distribuye mi dinero.

Criterios de Aceptación: Cada rubro pertenece a una categoría asignada al presupuesto y tiene nombre y monto mayor a cero. No se repite el nombre de un rubro dentro de la misma categoría. La suma de los presupuestos de todos los rubros de un presupuesto no puede superar el monto total del presupuesto; si lo supera, el sistema no guarda el cambio y muestra un mensaje de error. Una suma igual al monto total es válida. El sistema muestra el monto que aún no se ha distribuido.

HU-14: Editar y eliminar presupuestos
Historia: Como usuario, quiero modificar o eliminar presupuestos y sus categorías y rubros asignados, para ajustar mi planificación cuando cambien mis necesidades.

Criterios de Aceptación: Al eliminar un presupuesto se eliminan también sus asignaciones de categorías y sus rubros, sin eliminar los movimientos registrados; los que estaban asociados a un rubro eliminado quedan sin rubro.

HU-15: Comparar presupuesto contra gasto real por rubro
Historia: Como usuario, quiero ver por cada rubro de un presupuesto cuánto planifiqué, cuánto he gastado y cuánto me queda, para saber si voy bien en mi plan.

Criterios de Aceptación: Por cada rubro se muestra el monto presupuestado, gastado, disponible y el porcentaje de ejecución. El gasto real de un rubro considera solo movimientos de tipo gasto asociados a ese rubro dentro del periodo del presupuesto. La categoría solo agrupa sus rubros: no tiene presupuesto ni porcentaje de ejecución propios.

HU-16: Alerta de presupuesto de rubro excedido
Historia: Como usuario, quiero que el sistema me indique cuando un rubro está cerca de su presupuesto o ya lo superó, para corregir mis hábitos de gasto a tiempo.

Criterios de Aceptación: Si el gasto de un rubro supera el 80 % de su presupuesto, se muestra una advertencia; si supera el 100 %, se resalta visualmente y se muestra el valor excedido.

HU-17: Resumen financiero
Historia: Como usuario, quiero ver en mi página principal el total de ingresos, total de gastos y saldo del periodo, para conocer mi situación financiera de un vistazo.

Criterios de Aceptación: El saldo se calcula como ingresos menos gastos del periodo seleccionado, y se muestra junto con los últimos movimientos.

HU-18: Protección de mi información
Historia: Como usuario, quiero que nadie más pueda ver ni modificar mis datos, para tener la seguridad de que mi información financiera es privada.

Criterios de Aceptación: Las páginas internas redirigen al inicio de sesión si no hay sesión activa, y cualquier intento de acceder a registros de otro usuario es rechazado.

#

# 3.3 REQUERIMIENTOS FUNCIONALES (RF)
Los requerimientos funcionales se agrupan por módulo, siguiendo la estructura de la base de datos (usuarios, categorias, movimientos, presupuestos, presupuesto_categoria y rubros).

RF-01 Gestión de usuarios y autenticación

● ERF-01.1 Registrar usuario

● ERF-01.2 Iniciar sesión

● ERF-01.3 Cerrar sesión

● ERF-01.4 Controlar acceso a páginas internas

RF-02 Gestión de categorías

● ERF-02.1 Crear categoría

● ERF-02.2 Consultar categorías

● ERF-02.3 Editar categoría

● ERF-02.4 Eliminar categoría

RF-03 Gestión de movimientos

● ERF-03.1 Registrar movimiento

● ERF-03.2 Consultar movimientos

● ERF-03.3 Editar movimiento

● ERF-03.4 Restricción de eliminación de movimientos

RF-04 Gestión de presupuestos

● ERF-04.1 Crear presupuesto

● ERF-04.2 Consultar presupuestos

● ERF-04.3 Editar presupuesto

● ERF-04.4 Eliminar presupuesto

RF-05 Asignación de categorías a presupuestos (relación N:M)

● ERF-05.1 Asignar categoría a un presupuesto

● ERF-05.2 Consultar categorías asignadas a un presupuesto

● ERF-05.3 Quitar categoría de un presupuesto

● ERF-05.4 Calcular la distribución del presupuesto

RF-06 Gestión de rubros

● ERF-06.1 Crear rubro

● ERF-06.2 Consultar rubros

● ERF-06.3 Editar rubro

● ERF-06.4 Eliminar rubro

● ERF-06.5 Validar la suma de los rubros de un presupuesto

RF-07 Filtros y consultas

● ERF-07.1 Filtrar movimientos por rango de fechas

● ERF-07.2 Filtrar movimientos por tipo

● ERF-07.3 Filtrar movimientos por categoría

RF-08 Cálculos y resumen financiero

● ERF-08.1 Calcular total de ingresos y total de gastos

● ERF-08.2 Calcular saldo

● ERF-08.3 Comparar presupuesto contra gasto real por rubro

● ERF-08.4 Alertar presupuestos de rubros excedidos

● ERF-08.5 Mostrar panel de resumen

RF-09 Seguridad y validación de datos

● ERF-09.1 Cifrar contraseñas

● ERF-09.2 Usar sentencias preparadas

● ERF-09.3 Validar y sanear datos de entrada

● ERF-09.4 Aislar la información por usuario

RF-01: Gestión de usuarios y autenticación

ERF-01.1: Registrar usuario

● Descripción: Permite a un visitante crear una cuenta personal para acceder a las funciones del sistema.

● Entrada: Nombre completo, correo electrónico, contraseña y confirmación de contraseña.

● Salida: Cuenta creada en la tabla usuarios y mensaje de registro exitoso con redirección al inicio de sesión.

● Criterio de Aceptación: El sistema valida que todos los campos estén diligenciados, que el correo tenga formato válido y no esté registrado previamente, que la contraseña tenga mínimo 8 caracteres y que ambas contraseñas coinciden.

ERF-01.2: Iniciar sesión

● Descripción: Permite autenticar al usuario para acceder a su información.

● Entrada: Correo electrónico y contraseña.

● Salida: Sesión iniciada y redirección al panel principal, o mensaje de error.

● Criterio de Aceptación: La contraseña se verifica contra el hash almacenado. Ante credenciales inválidas se muestra un mensaje genérico ("Correo o contraseña incorrectos") sin revelar cuál dato falló. Al iniciar sesión se regenera el identificador de sesión.

ERF-01.3: Cerrar sesión

● Descripción: Permite al usuario salir de forma segura del sistema.

● Entrada: Clic en la opción "Cerrar sesión".

● Salida: Sesión destruida y redirección a la página de inicio de sesión.

● Criterio de Aceptación: Después de cerrar sesión, al intentar acceder a una página interna (incluso con el botón "atrás" del navegador), el sistema exige autenticarse de nuevo.

ERF-01.4: Controlar acceso a páginas internas

● Descripción: Verifica en cada página interna que exista una sesión activa.

● Entrada: Solicitud a una página interna del sistema.

● Salida: Acceso a la página o redirección al inicio de sesión.

● Criterio de Aceptación: Todas las páginas internas ejecutan la verificación de sesión antes de procesar cualquier dato. Un visitante solo puede acceder a las páginas de registro e inicio de sesión.

RF-02: Gestión de categorías

ERF-02.1: Crear categoría

● Descripción: Permite al usuario crear categorías para clasificar sus movimientos.

● Entrada: Nombre de la categoría (ejemplo: Alimentación, Transporte, Educación, Entretenimiento).

● Salida: Categoría registrada en la tabla categorias asociada al usuario, y confirmación.

● Criterio de Aceptación: El nombre es obligatorio, tiene una longitud máxima definida y no puede repetirse entre las categorías del mismo usuario.

ERF-02.2: Consultar categorías

● Descripción: Muestra el listado de categorías del usuario.

● Entrada: Acceso al módulo de categorías.

● Salida: Listado con el nombre de cada categoría y las opciones de editar y eliminar.

● Criterio de Aceptación: Solo se muestran las categorías pertenecientes al usuario autenticado.

ERF-02.3: Editar categoría

● Descripción: Permite modificar el nombre de una categoría existente.

● Entrada: Identificador de la categoría y nuevo nombre.

● Salida: Categoría actualizada y confirmación.

● Criterio de Aceptación: El nuevo nombre no puede duplicar otra categoría del mismo usuario. El cambio se refleja en todos los movimientos y presupuestos asociados.

ERF-02.4: Eliminar categoría

● Descripción: Permite eliminar una categoría que ya no se necesita.

● Entrada: Identificador de la categoría y confirmación del usuario.

● Salida: Categoría eliminada y confirmación, o mensaje de que no es posible eliminarla.

● Criterio de Aceptación: Si la categoría tiene movimientos o presupuestos asociados, el sistema impide la eliminación e informa el motivo, preservando la integridad referencial.

RF-03: Gestión de movimientos

ERF-03.1: Registrar movimiento

● Descripción: Permite registrar un ingreso o un gasto.

● Entrada: Tipo (ingreso o gasto), monto, fecha, descripción, categoría y rubro (opcional, solo para gastos).

● Salida: Movimiento almacenado en la tabla movimientos asociado al usuario, y confirmación.

● Criterio de Aceptación: El monto debe ser numérico y mayor a cero; la fecha es obligatoria y válida; la categoría debe pertenecer al usuario; la descripción tiene una longitud máxima definida. Si se indica un rubro, debe pertenecer al usuario y a la categoría del movimiento, el movimiento debe ser de tipo gasto y su fecha debe estar dentro del periodo del presupuesto al que pertenece el rubro.

ERF-03.2: Consultar movimientos

● Descripción: Muestra el listado de movimientos del usuario.

● Entrada: Acceso al módulo de movimientos (con filtros opcionales).

● Salida: Tabla con fecha, tipo, categoría, descripción y monto, ordenada del más reciente al más antiguo.

● Criterio de Aceptación: Sólo se listan los movimientos del usuario autenticado, y los ingresos y gastos se distinguen visualmente.

ERF-03.3: Editar movimiento

● Descripción: Permite corregir los datos de un movimiento registrado. Es la única operación de ajuste permitida sobre movimientos existentes, recalculando automáticamente saldos y ejecución presupuestal.

● Entrada: Identificador del movimiento y los nuevos datos.

● Salida: Movimiento actualizado y confirmación.

● Criterio de Aceptación: Se aplican las mismas validaciones del registro. El sistema impide editar movimientos que no pertenezcan al usuario. Los totales y comparaciones se recalculan automáticamente.

ERF-03.4: Restricción de eliminación de movimientos

● Descripción: Por motivos de seguridad, trazabilidad e integridad de las relaciones entre entidades (movimientos, categorías, rubros y presupuestos), el sistema no dispone de funcionalidad para eliminar movimientos ni acepta peticiones de borrado sobre la tabla movimientos, debiendo usarse la edición (ERF-03.3) para cualquier corrección.

● Entrada: Intento o solicitud de eliminación de un movimiento.

● Salida: Petición denegada o ausencia de opción de borrado en la interfaz.

● Criterio de Aceptación: La aplicación no provee rutas ni funciones para eliminar registros de la tabla movimientos. Cualquier ajuste debe realizarse mediante edición.

RF-04: Gestión de presupuestos

ERF-04.1: Crear presupuesto

● Descripción: Permite definir un presupuesto para un periodo determinado.

● Entrada: Nombre, monto total, fecha de inicio y fecha de fin.

● Salida: Presupuesto almacenado en la tabla presupuestos asociado al usuario, y confirmación.

● Criterio de Aceptación: El monto total debe ser mayor a cero y la fecha de fin no puede ser anterior a la de inicio.

ERF-04.2: Consultar presupuestos

● Descripción: Muestra el listado de presupuestos del usuario y el detalle de cada uno.

● Entrada: Acceso al módulo de presupuestos o selección de un presupuesto.

● Salida: Listado con nombre, periodo y monto total; en el detalle, las categorías asignadas y, dentro de cada una, sus rubros con su presupuesto.

● Criterio de Aceptación: Solo se muestran los presupuestos del usuario autenticado.

ERF-04.3: Editar presupuesto

● Descripción: Permite modificar los datos generales de un presupuesto.

● Entrada: Identificador del presupuesto y nuevos datos (nombre, monto total, fechas).

● Salida: Presupuesto actualizado y confirmación.

● Criterio de Aceptación: Se aplican las mismas validaciones de la creación. Las comparaciones contra el gasto real se recalculan con el nuevo período y monto. El nuevo monto total no puede ser menor a la suma de los presupuestos de los rubros ya definidos (ERF-06.5).

ERF-04.4: Eliminar presupuesto

● Descripción: Permite eliminar un presupuesto, sus asignaciones de categorías y sus rubros.

● Entrada: Identificador del presupuesto y confirmación del usuario.

● Salida: Presupuesto y sus registros en presupuesto_categoria y rubros eliminados, y confirmación.

● Criterio de Aceptación: La eliminación no afecta los movimientos ni las categorías del usuario; los movimientos asociados a los rubros eliminados quedan sin rubro.

RF-05: Asignación de categorías a presupuestos (relación N:M)

ERF-05.1: Asignar categoría a un presupuesto

● Descripción: Permite asignar categorías a un presupuesto para agrupar sus rubros; la categoría no tiene monto propio.

● Entrada: Identificador del presupuesto e identificador de la categoría.

● Salida: Registro creado en la tabla presupuesto_categoria y confirmación.

● Criterio de Aceptación: Una misma categoría no puede asignarse dos veces al mismo presupuesto. Tanto el presupuesto como la categoría deben pertenecer al usuario. La asignación no define ningún monto: el presupuesto se asigna mediante los rubros (RF-06).

ERF-05.2: Consultar categorías asignadas a un presupuesto

● Descripción: Muestra las categorías asignadas a un presupuesto junto con sus rubros.

● Entrada: Presupuesto seleccionado.

● Salida: Por cada categoría: nombre, rubros con su presupuesto y la suma de sus rubros (valor informativo).

● Criterio de Aceptación: Solo se muestran asignaciones de presupuestos del usuario autenticado. La suma de los rubros es informativa y no constituye un presupuesto de la categoría.

ERF-05.3: Quitar categoría de un presupuesto

● Descripción: Permite retirar una categoría de un presupuesto.

● Entrada: Presupuesto, categoría y confirmación del usuario.

● Salida: Registro eliminado de presupuesto_categoria, junto con sus rubros, y confirmación.

● Criterio de Aceptación: La acción no elimina la categoría ni sus movimientos, únicamente su asignación al presupuesto y los rubros definidos dentro de ella; los movimientos asociados a esos rubros quedan sin rubro.

ERF-05.4: Calcular la distribución del presupuesto

● Descripción: Calcula cuánto del monto total del presupuesto está asignado a rubros y cuánto queda sin distribuir.

● Entrada: Monto total del presupuesto y montos de sus rubros.

● Salida: Total asignado a rubros, monto sin distribuir y suma informativa de los rubros de cada categoría.

● Criterio de Aceptación: Total asignado = suma de los presupuestos de todos los rubros. Sin distribuir = monto total − total asignado. El resultado nunca es negativo, porque ERF-06.5 impide superar el monto total.

RF-06: Gestión de rubros

ERF-06.1: Crear rubro

● Descripción: Permite dividir una categoría asignada a un presupuesto en rubros, cada uno con su propio presupuesto.

● Entrada: Identificador del presupuesto, identificador de la categoría, nombre del rubro y monto del rubro.

● Salida: Rubro registrado en la tabla rubros y confirmación, o mensaje de error si no se cumple la validación de la suma.

● Criterio de Aceptación: El nombre es obligatorio, tiene una longitud máxima definida y no puede repetirse dentro de la misma categoría del mismo presupuesto. El monto debe ser mayor a cero. La categoría debe estar asignada al presupuesto y ambos deben pertenecer al usuario. Se aplica la validación de ERF-06.5.

ERF-06.2: Consultar rubros

● Descripción: Muestra los rubros de cada categoría de un presupuesto con su monto.

● Entrada: Selección de un presupuesto (detalle del presupuesto).

● Salida: Por cada categoría: sus rubros con nombre y monto y la suma de sus rubros (informativa); además, el total asignado a rubros del presupuesto y el monto total aún sin distribuir.

● Criterio de Aceptación: Solo se muestran los rubros de presupuestos pertenecientes al usuario autenticado.

ERF-06.3: Editar rubro

● Descripción: Permite modificar el nombre o el monto de un rubro existente.

● Entrada: Identificador del rubro y nuevos datos (nombre y monto).

● Salida: Rubro actualizado y confirmación, o mensaje de error.

● Criterio de Aceptación: Se aplican las mismas validaciones de la creación. Si el nuevo monto hace que la suma de los rubros del presupuesto supere el monto total del presupuesto, el cambio no se guarda y se conserva el valor anterior. El sistema impide editar rubros que no pertenezcan al usuario.

ERF-06.4: Eliminar rubro

● Descripción: Permite eliminar un rubro de una categoría.

● Entrada: Identificador del rubro y confirmación del usuario.

● Salida: Rubro eliminado y confirmación. El monto del presupuesto sin distribuir aumenta en el monto del rubro eliminado.

● Criterio de Aceptación: El sistema solicita confirmación previa y solo permite eliminar rubros propios. La acción no elimina la categoría, su asignación al presupuesto ni los movimientos, que quedan sin rubro si estaban asociados al rubro eliminado.

ERF-06.5: Validar la suma de los rubros de un presupuesto

● Descripción: Verifica que la suma de los presupuestos de todos los rubros de un presupuesto no supere el monto total del presupuesto.

● Entrada: Monto total del presupuesto y montos de todos sus rubros, incluido el rubro que se crea o modifica.

● Salida: Cambio aceptado, o cambio rechazado con un mensaje de error que indica la suma, el monto total y el valor excedido.

● Criterio de Aceptación: La validación se ejecuta en el servidor al crear un rubro, al editar su monto y al reducir el monto total del presupuesto (ERF-04.3). Si la suma es menor o igual al monto total, el cambio se guarda; una suma igual es válida. Si la suma lo supera, no se guarda ningún cambio y se muestra el mensaje: "La suma de los rubros (X) supera el monto total del presupuesto (Y) por \$Z." Eliminar un rubro siempre es válido.

RF-07: Filtros y consultas

ERF-07.1: Filtrar movimientos por rango de fechas

● Descripción: Permite consultar los movimientos dentro de un periodo.

● Entrada: Fecha desde y fecha hasta.

● Salida: Listado y totales de los movimientos del rango.

● Criterio de Aceptación: Si la fecha final es anterior a la inicial, se muestra un mensaje de error. Si solo se indica una de las fechas, se filtra desde o hasta esa fecha.

ERF-07.2: Filtrar movimientos por tipo

● Descripción: Permite consultar solo ingresos, solo gastos o ambos.

● Entrada: Tipo de movimiento (todos, ingreso, gasto).

● Salida: Listado y totales según el tipo seleccionado.

● Criterio de Aceptación: Por defecto se muestran todos los movimientos.

ERF-07.3: Filtrar movimientos por categoría

● Descripción: Permite consultar los movimientos de una categoría específica.

● Entrada: Categoría seleccionada.

● Salida: Listado y totales de la categoría.

● Criterio de Aceptación: Los filtros de fecha, tipo y categoría pueden combinarse, y existe una opción para limpiarlos.

RF-08: Cálculos y resumen financiero

ERF-08.1: Calcular total de ingresos y total de gastos

● Descripción: Suma automáticamente los movimientos por tipo.

● Entrada: Movimientos del usuario, con o sin filtros aplicados.

● Salida: Total de ingresos y total de gastos.

● Criterio de Aceptación: Los cálculos se realizan con consultas SQL de agregación sobre los datos del usuario autenticado y respetan los filtros activos.

ERF-08.2: Calcular saldo

● Descripción: Determina la diferencia entre ingresos y gastos.

● Entrada: Total de ingresos y total de gastos.

● Salida: Saldo del periodo.

● Criterio de Aceptación: Saldo = total de ingresos − total de gastos. Si es negativo, se muestra resaltado.

ERF-08.3: Comparar presupuesto contra gasto real por rubro

● Descripción: Contrasta lo presupuestado con lo gastado en cada rubro de un presupuesto.

● Entrada: Presupuesto seleccionado.

● Salida: Por cada rubro: presupuesto del rubro, monto gastado, monto disponible y porcentaje de ejecución. Además, totales del presupuesto y, de forma informativa, el gasto de cada categoría que no está asociado a ningún rubro.

● Criterio de Aceptación: El monto gastado de un rubro es la suma de los movimientos de tipo gasto asociados a ese rubro con fecha dentro del periodo del presupuesto. Disponible = presupuesto del rubro − gastado. Ejecución = (gastado ÷ presupuesto del rubro) × 100. Los gastos que no estén asociados a un rubro no afectan ningún rubro y se muestran como "gasto sin rubro".

ERF-08.4: Alertar presupuestos de rubros excedidos

● Descripción: Informa visualmente cuando un rubro supera su presupuesto o está cerca de alcanzarlo.

● Entrada: Resultado de la comparación presupuestal.

● Salida: Indicador visual de advertencia (más del 80 % del presupuesto del rubro) o de presupuesto excedido, con el valor excedido cuando aplica.

● Criterio de Aceptación: Si el gasto es mayor al presupuesto del rubro, el rubro se resalta (por ejemplo, en rojo) y se muestra la diferencia excedida. Además, si el gasto supera el 80 % del presupuesto sin haberlo excedido, el rubro muestra un indicador de advertencia (por ejemplo, en amarillo), distinto del resaltado de presupuesto excedido.

ERF-08.5: Mostrar panel de resumen

● Descripción: Presenta en la página principal un resumen de la situación financiera del usuario.

● Entrada: Acceso al panel principal y periodo de consulta (por defecto, el mes actual).

● Salida: Total de ingresos, total de gastos, saldo, últimos movimientos y estado de los presupuestos vigentes.

● Criterio de Aceptación: La información corresponde únicamente al usuario autenticado y se actualiza cada vez que se carga la página.

RF-09: Seguridad y validación de datos

ERF-09.1: Cifrar contraseñas

● Descripción: Protege las credenciales de los usuarios.

● Entrada: Contraseña ingresada en el registro.

● Salida: Hash almacenado en la tabla de usuarios.

● Criterio de Aceptación: Se utiliza password_hash de PHP y la verificación con password_verify. Las contraseñas nunca se almacenan ni se muestran en texto plano.

ERF-09.2: Usar sentencias preparadas

● Descripción: Previene la inyección SQL en todas las operaciones con la base de datos.

● Entrada: Datos recibidos desde formularios o parámetros de la URL.

● Salida: Consultas ejecutadas de forma segura.

● Criterio de Aceptación: Ninguna consulta concatena directamente datos del usuario; todas utilizan parámetros (mysqli o PDO).

ERF-09.3: Validar y sanear datos de entrada

● Descripción: Garantiza que los datos recibidos sean correctos y seguros.

● Entrada: Todos los campos de los formularios.

● Salida: Datos aceptados o mensajes de error de validación.

● Criterio de Aceptación: Se valida en el servidor tipo, formato, longitud y obligatoriedad. Los datos mostrados en pantalla se escapan (htmlspecialchars) para prevenir XSS.

ERF-09.4: Aislar la información por usuario

● Descripción: Asegura que cada usuario acceda solo a sus propios datos.

● Entrada: Identificador del usuario en sesión e identificador del registro solicitado.

● Salida: Acceso al registro o mensaje de acceso denegado.

● Criterio de Aceptación: Toda consulta de lectura, edición o eliminación incluye la condición del usuario propietario. Si se manipula un identificador en la URL para acceder a datos ajenos, el sistema lo rechaza.

# 3.4 REQUERIMIENTOS NO FUNCIONALES (RNF)
1\. Seguridad y Privacidad

● RNF-01: Protección de datos personales. El sistema debe respetar la Ley 1581 de 2012, tratando la información personal y financiera de los usuarios de forma confidencial y usándola únicamente para las funciones del sistema.

● RNF-02: Seguridad de contraseñas. Las credenciales deben almacenarse con un algoritmo de hash robusto con sal aleatoria (password_hash de PHP, con bcrypt o Argon2).

● RNF-03: Prevención de ataques comunes. El sistema debe mitigar inyección SQL (sentencias preparadas), XSS (escape de salida) y secuestro de sesión (regeneración del identificador al iniciar sesión).

● RNF-04: Cifrado de comunicaciones. En un eventual despliegue en producción, la comunicación debe realizarse mediante HTTPS. En el entorno local de Laragon se admite HTTP.

● RNF-05: Sesiones seguras. Las sesiones deben expirar tras 30 minutos de inactividad y destruirse completamente al cerrar sesión.

2\. Rendimiento

● RNF-06: Tiempo de respuesta. Las páginas y consultas deben responder en menos de 2 segundos bajo condiciones normales de uso.

● RNF-07: Eficiencia de consultas. Las columnas usadas en filtros y uniones (usuario, categoría, rubro, fecha) deben estar indexadas, de modo que los cálculos de totales se mantengan ágiles con miles de movimientos por usuario.

● RNF-08: Capacidad. El sistema debe funcionar correctamente con al menos 10 usuarios concurrentes en el entorno local.

3\. Disponibilidad y Robustez

● RNF-09: Integridad de datos. La base de datos debe garantizar la integridad referencial mediante llaves foráneas y evitar registros huérfanos.

● RNF-10: Precisión monetaria. Los montos deben almacenarse con tipo DECIMAL (por ejemplo, DECIMAL(12,2)) para evitar errores de redondeo.

● RNF-11: Manejo de errores. Ante un error del sistema o de la base de datos, el usuario debe ver un mensaje claro, sin exponer detalles técnicos ni consultas SQL.

● RNF-12: Respaldo. Debe ser posible generar una copia de seguridad de la base de datos mediante exportación, con una periodicidad recomendada mensual como mínimo.

4\. Usabilidad

● RNF-13: Facilidad de aprendizaje. Un usuario nuevo debe poder registrarse, crear una categoría y registrar su primer movimiento en menos de 5 minutos sin tutoriales.

● RNF-14: Diseño adaptable. La interfaz debe visualizarse correctamente en pantallas de computador, tablet y teléfono.

● RNF-15: Claridad de la información. Los montos deberán presentarse en formato de pesos colombianos con separador de miles, y los mensajes de validación deben ser claros y orientadores.

● RNF-16: Legibilidad. Se debe garantizar un contraste adecuado entre texto y fondo y un tamaño de fuente legible.

5\. Portabilidad y Mantenibilidad

● RNF-17: Compatibilidad de navegadores. El sistema debe ser compatible con las dos últimas versiones de Chrome, Firefox y Edge.

● RNF-18: Portabilidad del entorno. El sistema debe ejecutarse en Laragon sobre Windows con PHP 8.0 o superior, y poder trasladarse a otro entorno Apache/PHP/MySQL sin cambios en el código, solo en la configuración de conexión.

● RNF-19: Modularidad del código. Aunque se usa PHP estructurado, el código debe organizarse en archivos separados por responsabilidad: conexión a la base de datos, funciones reutilizables (validación, cálculos), páginas por módulo y archivos de estilos CSS.

● RNF-20: Documentación del código. El código debe incluir comentarios en las funciones y secciones principales, y el script SQL de creación de la base de datos debe estar documentado.

● RNF-21: Codificación. La aplicación y la base de datos deben utilizar codificación UTF-8 (utf8mb4) para el correcto manejo de tildes y caracteres especiales.

**4. LICENCIAMIENTO Y TÉRMINOS DE USO**

El "Sistema de Gestión Financiera Personal y Presupuestos" ha sido desarrollado de manera gratuita con fines estrictamente académicos y formativos dentro del programa Análisis y Desarrollo de Software (ADSO) del SENA (Ficha 3407799).

Durante su etapa académica, el software se distribuye bajo una licencia de uso libre y gratuito sin fines comerciales, permitiendo su consulta, prueba y evaluación educativa.

Dado su carácter académico en evolución, los términos de licenciamiento, condiciones de distribución y alcance de uso están sujetos a modificaciones futuras por parte del equipo de desarrollo en caso de evolucionar hacia un producto comercial, despliegue en producción o nuevas versiones del sistema.

Asimismo, se hace constar que las herramientas y tecnologías empleadas en su construcción (PHP, MySQL/MariaDB, Apache, HTML5, CSS3 y el entorno Laragon) se rigen por sus respectivas licencias de código abierto y uso gratuito.
