# 📋 Changelog

Todos los cambios importantes en este proyecto se documentarán en este archivo.

Este proyecto sigue (en lo posible) el formato de [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y utiliza [SemVer](https://semver.org/lang/es/).

---

## [1.1.0] - 2026-11-24

### Cambios

- Actualización a CodeIgniter 4.7.6 y requisito mínimo de PHP 8.2.
- Actualización de Bootstrap y Bootswatch a 5.3.8.
- El CRUD generado usa `DELETE` con protección CSRF para borrar registros. En módulos generados anteriormente, actualizá las rutas, el controlador y la vista de listado en conjunto.

### Correcciones

- `--only` acepta `model`, `controller` y `view`, además de sus alias en plural.
- La regeneración de rutas por tabla conserva las rutas personalizadas y las de los demás módulos.
- Git vuelve a mostrar las diferencias de los archivos de texto.

## [1.0.0] - 2025-05-28
### 🎉 Primer lanzamiento oficial

- Comando `make:ciadmin` para generar toda la aplicación automáticamente.
- Generación de:
  - Modelos
  - Controladores
  - Vistas (index, create, edit, view)
  - Dashboard inicial
  - Rutas completas
- Soporte para temas de [Bootswatch](https://bootswatch.com)
- Paginación automática de resultados
- Exclusión de campos pesados en listados
- Soporte para claves primarias personalizadas
- Mensajes flash integrados con Bootstrap
- Estilo limpio y responsive basado en Bootstrap 5

---

Las ideas para próximas versiones están en el [roadmap](ROADMAP.md).
