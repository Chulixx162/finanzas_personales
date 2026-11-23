# MANUAL TÉCNICO:
# Sistema de Gestión Financiera Personal y Presupuestos
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

## 1. INTRODUCCIÓN Y JUSTIFICACIÓN
El presente informe detalla la construcción y actualización del "Sistema de Gestión Financiera Personal y Presupuestos", una aplicación web multiusuario orientada a solucionar la falta de control formal sobre el dinero por parte de personas naturales. A diferencia de las herramientas complejas del mercado, este sistema destaca por su lógica de **planificación por presupuestos**, donde el usuario asigna un monto global a un periodo, vincula categorías, y subdivide estas en **rubros**, los cuales son los únicos que poseen un presupuesto estricto a controlar.

En su versión más reciente, el sistema ha integrado de forma nativa un **Módulo de Gestión Documental**, permitiendo alojar, proteger y consultar todos los artefactos de diseño, manuales y evidencias de calidad directamente desde la plataforma.

## 2. ARQUITECTURA DE SOFTWARE Y DISEÑO ESTRUCTURAL
El proyecto implementa una arquitectura Cliente-Servidor utilizando el patrón de diseño **Modelo-Vista-Controlador (MVC)**, garantizando la separación de responsabilidades, la escalabilidad y el mantenimiento seguro.

### 2.1. Capa de Modelos (/models)
Encargada de la persistencia de datos y transacciones con MySQL. Utiliza sentencias preparadas para mitigar inyecciones SQL.

- usersModel.php: Gestiona el CRUD de usuarios y validación de credenciales.

- categoriesModel.php y rubrosModel.php: Administran la agrupación de gastos y la lógica de verificación de límites de rubros.

- categoryReportModel.php y reportsModel.php: Consultas de agregación y consolidación para reportería financiera.

### 2.2. Capa de Controladores (/controllers)
Actúan como intermediarios, aplicando reglas de negocio y retornando la vista correspondiente.

- Controladores Core: authController.php, transactionsController.php, dashboardController.php, budgetHistoryController.php.

- **Módulo Documental:** documentsController.php procesa las peticiones de descarga y visualización de los artefactos del proyecto, apoyado por la configuración de rutas en config/documents.php.

### 2.3. Capa de Vistas (/views)
La interfaz (UI) está construida de forma responsiva y agrupada por módulos funcionales (/auth, /categories, /rubros, /transactions, /reports).

### 2.4. Diseño de la Estructura de Directorios (Actualizada)
El árbol de directorios refleja un alto nivel de organización técnica, aislando el código y exponiendo únicamente la carpeta pública.

finanzas_personales/

├── config/

│ ├── connection.php \# Conexión a MySQL usando variables de entorno

│ ├── documents.php \# Configuración de permisos y rutas para documentos

│ └── session.php \# Gestión de inicio, validación y expiración de sesión

├── controllers/

│ ├── authController.php

│ ├── documentsController.php \# Lógica de acceso a archivos del proyecto

│ ├── categoryReportController.php \# Generación de informes financieros

│ └── ...

├── database/

│ └── schema.sql \# Script DDL de creación de la base de datos

├── models/ \# Lógica de acceso a datos (Transacciones MySQL)

├── public/ \# Directorio público (Punto único de entrada web)

│ ├── css/ \# Hojas de estilo estructuradas (layout, docs, reports, etc.)

│ ├── js/ \# Scripts modulares en Vanilla JS

│ ├── docs/ \# REPOSITORIO DOCUMENTAL INTEGRADO

│ │ ├── MER.jpeg \# Modelo Entidad-Relación visual

│ │ ├── Manual de Usuario.docx \# Guía operativa para el usuario final

│ │ ├── Manual técnico.docx \# Guía de despliegue y arquitectura

│ │ ├── Plan de Aseguramiento de Calidad.docx \# PAC detallado

│ │ ├── SRS_Sistema de Gestión Financiera...docx \# Requisitos funcionales y alcance

│ │ ├── Mapeo y Ubicación de Instrumentos...docx \# Matrices de calidad

│ │ ├── log de errores y sugerencias.docx \# Bitácora de incidencias

│ │ └── schema.sql \# Respaldo SQL accesible

│ └── index.php \# Enrutador principal (Front Controller)

├── vendor/ \# Dependencias gestionadas por Composer

├── views/ \# Interfaces de usuario modulares

├── .env \# Variables de entorno seguras (credenciales)

└── composer.json \# Gestor de paquetes PHP

### **2.5. Diseño de la Estructura de Navegación (Mapa del Sitio)**

- **Zona Pública:** Login \| Registro \| Recuperación de contraseña \| Términos y Privacidad

- **Zona Privada (Autenticada):**

  - Dashboard: Panel resumen de ingresos, gastos, saldos y alertas.

  - Movimientos: Listado histórico y formulario de registro.

  - Categorías: Creación y edición de agrupaciones.

  - Presupuestos: Gestión de periodos y asignación de **Rubros**.

  - Reportes: Informes analíticos y exportación a PDF.

  - **Documentación:** Interfaz embebida para visualizar/descargar manuales, especificaciones (SRS), diagramas (MER) y planes de calidad (PAC).

## 3. TECNOLOGÍAS Y HERRAMIENTAS UTILIZADAS
- **Back-end:** **PHP 8.0+** (Estructurado y orientado a objetos para el patrón MVC).

- **Base de Datos:** **MySQL / MariaDB** (Sistema gestor de bases de datos relacional).

- **Front-end:** **HTML5**, **CSS3** (Modulares, con soporte para impresión PDF) y **JavaScript (Vanilla JS)**.

- **Gestor de Dependencias:** **Composer** (vlucas/phpdotenv para variables de entorno).

- **Entorno de Desarrollo:** **Laragon** (Suite de servidor local para Windows).

- **Documentación y Calidad:** Herramientas ofimáticas (.docx) centralizadas mediante el módulo documentsController y diagramas técnicos en formato .jpeg.

## 4. MODELO DE BASE DE DATOS
Modelo relacional estricto compuesto por 6 tablas documentadas (cuyo diagrama visual MER.jpeg y script schema.sql están integrados en el sistema).

1.  **usuarios**: id_usuario (PK). correo (UNIQUE). Contraseñas cifradas mediante hash.

2.  **categorias**: Clasificación general (sin monto propio). id_categoria (PK), id_usuario (FK).

3.  **presupuestos**: Monto global definido para un periodo. id_presupuesto (PK).

4.  **presupuesto_categoria**: Resuelve la relación N:M (presupuesto-categoría).

5.  **rubros**: Subdivisiones con límite financiero estricto. id_rubros (PK), id_categoria (FK). Validado desde el servidor.

6.  **movimientos**: Registro transaccional real. id_movimiento (PK), rubro_id (FK opcional).

##

##

## 5. ESPECIFICACIÓN DE REQUISITOS Y CALIDAD (SRS Y PAC)
El proyecto documenta su alcance, reglas de negocio e historias de usuario en el documento **SRS_Sistema de Gestión Financiera Personal y Presupuestos.docx**. Para garantizar que el software se adhiera a estos requerimientos, se implementó el **Plan de Aseguramiento de Calidad (PAC)**.

En la versión actual del repositorio documental (/public/docs/) se incluyen instrumentos de control críticos:

- **Mapeo y Ubicación de Instrumentos de Calidad.docx:** Define la trazabilidad exacta de las pruebas.

- **Log de errores y sugerencias.docx:** Bitácora viva de resolución de defectos (QA). El sistema aprueba bloqueos lógicos estrictos (Ej. *La suma de los rubros no puede exceder el presupuesto total*), alertando visualmente superaciones del 80% y 100% de la ejecución presupuestal.

## 6. MANUALES TÉCNICOS Y DE ACCESO
Toda la documentación final ha sido integrada en la plataforma. Sin embargo, para su primer despliegue, siga estas instrucciones:

### 6.1. Manual Técnico de Instalación (Entorno Laragon)
1.  Extraiga el código fuente del proyecto (finanzas_personales.zip) en el directorio público de Laragon (ej: C:\laragon\www\finanzas_personales).

2.  Abra la terminal, navegue al directorio del proyecto y ejecute composer install para instalar las dependencias requeridas.

3.  En el gestor de bases de datos (HeidiSQL/phpMyAdmin), cree la base de datos finanzas_personales.

4.  Importe el archivo de estructura ejecutando el script database/schema.sql.

5.  Renombre .env.example a .env (o cree el archivo) y configure las credenciales:


DB_HOST=localhost

DB_USER=root

DB_PASS=

DB_NAME=finanzas_personales

### **6.2. Acceso a la Plataforma y Documentación Interna**

1.  Inicie los servicios de Apache y MySQL en Laragon.

2.  Acceda mediante la URL: **http://finanzas_personales.test/public/index.php**

3.  Una vez registrado e iniciada la sesión, **podrá acceder al módulo de "Documentación"**.

4.  En este módulo encontrará, listos para visualizar o descargar:

    - Manual de Usuario.docx (Guía de operación paso a paso).

    - Manual técnico.docx (Detalles de despliegue y arquitectura).

    - Diagramas (MER.jpeg) y reportes de calidad.
