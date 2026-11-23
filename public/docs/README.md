# Documentación del proyecto

**Sistema de Gestión Financiera Personal y Presupuestos** · Programa ADSO · Ficha 3407799

Toda la documentación está escrita en Markdown (`.md`) para que se pueda leer en GitHub y desde la
aplicación (perfil → *Manuales y anexos*).

## Manuales

| Documento | Archivo | Contenido |
| --- | --- | --- |
| Manual de usuario | [user_manual.md](user_manual.md) | Guía paso a paso para usar la página. |
| Manual técnico | [technical_manual.md](technical_manual.md) | Estructura del proyecto, instalación y base de datos. |

## Anexos

| Documento | Archivo | Contenido |
| --- | --- | --- |
| PAC | [pac.md](pac.md) | Plan de Aseguramiento de Calidad del proyecto. |
| SRS | [srs.md](srs.md) | Especificación de requisitos de software. |
| MER | [mer.md](mer.md) | Modelo entidad-relación (imagen `MER.jpeg`). |
| Log de errores y sugerencias | [error_log.md](error_log.md) | Registro de errores encontrados y mejoras propuestas. |
| Instrumentos de calidad | [quality_instruments.md](quality_instruments.md) | Formatos e instrumentos usados para verificar la calidad. |

## Organización de la carpeta

```text
public/docs/
├── README.md              ← este índice
├── user_manual.md
├── technical_manual.md
├── pac.md
├── srs.md
├── mer.md
├── error_log.md
├── quality_instruments.md
├── MER.jpeg               ← imagen usada por mer.md
└── img/
    └── user_manual/       ← capturas del manual de usuario
```

> Los archivos `.docx` originales pueden conservarse como respaldo: la aplicación siempre busca
> primero el `.md` de cada documento.
