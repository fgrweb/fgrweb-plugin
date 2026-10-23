=== F.GR Plugin Boilerplate ===
Contributors: fgrweb
Donate link: https://fgrweb.es/
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.3.0
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

= 1.3.0 =
* 👷 GitHub Action que publica la release con el zip del plugin al subir un tag X.Y.Z: comprueba que cabecera, constante y Stable tag coinciden con el tag, falla si la release ya existe, genera el zip con git archive (respeta export-ignore) y toma las notas del changelog.
* 🔧 El updater solo acepta assets .zip de la release.
* 🔧 setup-plugin.sh procesa .github/ y los .yml, y elimina del README.md generado las secciones que solo aplican al boilerplate.
* 📝 README.md con el procedimiento para publicar una versión y corregir un tag con versiones no sincronizadas.

= 1.2.0 =
* 🔒 Guardado de ajustes solo vía Settings API (nonce y capacidad en options.php), pestaña activa validada contra lista blanca y escapada.
* 🐛 Ruta del updater independiente del nombre de la carpeta; función del updater con prefijo del plugin (evita "Cannot redeclare" entre plugins del boilerplate).
* 🐛 El campo del token GitHub ya no muestra asteriscos como valor: vacío conserva el token, checkbox explícito para eliminarlo.
* 🐛 Opción de seguridad y clase CSS prefijadas con el slug (fgrweb_plugin_security_options). Sin migración: volver a guardar la pestaña Seguridad.
* ✨ Pestaña Seguridad: cabeceras HTTP, limpieza de wp_head, XML-RPC, REST API, feeds, login, revisiones. Todo desactivado por defecto.
* ✨ Limpieza de opciones solo al desinstalar (multisitio con get_sites), no al desactivar.
* ✨ CSS/JS de admin solo en la página de ajustes.
* ⬆️ Plugin Update Checker 5.7.
* 🔧 bin/setup-plugin.sh versionado y portable (macOS/Linux); .gitattributes con export-ignore; phpcs.xml.dist con estándar WordPress.
* 🎨 Código conforme a WPCS (phpcs a 0).

= 1.1.0 =
* ✨ Add deactivation logic to remove plugin options

= 1.0.0 =
* Initial release of the plugin.
