# Mapeo y Ubicación de Instrumentos de Calidad
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
Este documento sirve como guía de referencia rápida para identificar los **instrumentos de aseguramiento y control de calidad** implementados en el desarrollo del proyecto, detallando exactamente en qué sección, anexo o herramienta externa se encuentran documentados o alojados.

## Tabla de Instrumentos de Calidad y su Ubicación
| **Instrumento de Calidad**                       | **Descripción Breve**                                                                                                       | **Ubicación Exacta en la Documentación / Entorno**                                                  |
|--------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------|
| **Lista de Verificación de Requisitos (SRS)**    | Guía de inspección para comprobar claridad, verificabilidad y consistencia de las Historias de Usuario y requerimientos.    | **PAC - Sección 5.1** *(Revisión de requisitos y hallazgos iniciales del SRS v2)*.                  |
| **Lista de Verificación de Diseño**              | Criterios para inspeccionar el modelo E-R de 6 tablas, llaves foráneas, aislamiento de datos y diseño responsive.           | **PAC - Sección 5.2** *(Revisión de diseño)*.                                                       |
| **Lista de Verificación de Código y Revisiones** | Pautas para revisar la seguridad (sentencias preparadas, XSS, contraseñas hash) y el cumplimiento modular en PHP.           | **PAC - Sección 5.3** *(Revisión de código y documentos)*.                                          |
| **Matriz de Casos de Prueba (CP-01 a CP-69)**    | Instrumento formal que detalla el procedimiento, datos y resultado esperado para verificar los 9 módulos funcionales y RNF. | **PAC - Sección 6.2** *(Casos de prueba agrupados de 6.2.1 a 6.2.9)*.                               |
| **Matriz de Trazabilidad**                       | Relación directa que vincula cada una de las 18 Historias de Usuario (HU) con sus respectivos Casos de Prueba (CP).         | **PAC - Anexo C** *(Matriz de trazabilidad: historias de usuario y casos de prueba)*.               |
| **Formato de Registro de Defectos (Bug Log)**    | Plantilla estandarizada con campos de severidad, pasos de reproducción, responsable y estado de corrección.                 | **PAC - Anexo A** *(Formato de registro de defectos)* y consolidado en el **Bug Log del Proyecto**. |
| **Formato de Ejecución de Pruebas**              | Plantilla de seguimiento de resultados obtenidos (*Aprobada / Fallida*) por cada caso de prueba ejecutado.                  | **PAC - Anexo B** *(Formato de casos de prueba)*.                                                   |
| **Tablero de Seguimiento Ágil (Kanban)**         | Instrumento visual de gestión de tareas con columnas estrictas y regla de independencia (*Backlog* hasta *Aceptado*).       | **Tablero de Trello** (*finanzas_personales*), regulado en la **Sección 3.5 del PAC**.              |
| **Indicadores de Calidad (KPIs)**                | Métricas cuantitativas para medir el porcentaje de pruebas aprobadas, avance y densidad de defectos.                        | **PAC - Sección 10** *(Indicadores de seguimiento)*.                                                |
| **Control de Versiones y Ramas**                 | Sistema de ramas de trabajo por integrante y fusiones controladas en rama principal mediante revisión por pares.            | **Repositorio Git / GitHub** (regulado en la **Sección 3.3 del PAC**).                              |
| **Entorno de Ejecución Estándar**                | Plataforma local con Apache, MySQL (InnoDB) y PHP 8.0+ para garantizar portabilidad y pruebas homogéneas.                   | **Entorno Local Laragon** (regulado en la **Sección 6.1 y RNF-18 del PAC/SRS**).                    |

##

## Conclusión
Todos los instrumentos de calidad descritos se encuentran centralizados en el **Plan de Aseguramiento de Calidad (PAC)** y en la **Especificación de Requisitos de Software (SRS v2)**, los cuales constituyen el marco normativo y operativo con el cual el equipo de desarrollo garantiza que el software entregado al instructor José de Jesús Motta Vargas sea seguro, funcional y cumpla con los estándares de calidad del programa ADSO.
