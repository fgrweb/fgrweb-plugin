=== F.GR Plugin Boilerplate ===
Contributors: fgrweb
Donate link: https://fgrweb.es/
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Base para plugins de WordPress a medida con actualizaciones desde GitHub y ajustes de seguridad opcionales.

== Description ==

Boilerplate de plugin de WordPress usado como punto de partida en los desarrollos de F.GR Web. Está basado en la estructura de WordPress Plugin Boilerplate (loader de hooks, clases separadas para admin y público, i18n) e incluye:

* Actualizaciones automáticas desde un repositorio de GitHub mediante Plugin Update Checker, con soporte de token para repositorios privados.
* Página de ajustes en Ajustes > Fgrweb Plugin con dos pestañas:
  * Actualizaciones: token de GitHub.
  * Seguridad: cabeceras HTTP, limpieza de `wp_head`, XML-RPC, REST API, feeds, errores de login, revisiones, etc. Todas las opciones están desactivadas por defecto.
* Limpieza completa de opciones al desinstalar (también en multisitio).
* Script `bin/setup-plugin.sh` para generar un plugin nuevo a partir del boilerplate renombrando slug, clases, constantes y funciones.

== Installation ==

1. Sube la carpeta del plugin a `/wp-content/plugins/`.
2. Activa el plugin desde el menú Plugins de WordPress.
3. Configura el token de GitHub en Ajustes > Fgrweb Plugin > Actualizaciones si el repositorio es privado.

== Frequently Asked Questions ==

= ¿Cómo creo un plugin nuevo a partir del boilerplate? =

Ejecuta `bin/setup-plugin.sh` en un directorio vacío. El script descarga el boilerplate, pide el slug y los datos del plugin, renombra todos los identificadores y se elimina a sí mismo al terminar.

= ¿Qué se borra al desinstalar? =

Las opciones `fgrweb_plugin_config`, `fgrweb_plugin_security_options` y `external_updates-fgrweb-plugin`. Al desactivar no se borra nada.

== Changelog ==

= 1.1.0 =
* ✨ Add deactivation logic to remove plugin options

= 1.0.0 =
* Initial release of the plugin.
