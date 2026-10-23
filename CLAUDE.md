# F.GR Plugin Boilerplate

Base de la mayoría de los plugins de F.GR Web. Estructura de WordPress Plugin Boilerplate (loader de hooks, clases admin/public/i18n), Plugin Update Checker 5.7 para actualizar desde GitHub, pestañas de ajustes **Actualizaciones** (token) y **Seguridad**. Se genera un plugin nuevo con `bin/setup-plugin.sh`, que renombra `fgrweb-plugin` / `Fgrweb_Plugin` / `FGRWEB_PLUGIN` / `fgrweb_plugin` / "Fgrweb Plugin".

## Reglas de trabajo

- **Plan primero, por bloques; esperar OK antes de tocar nada.** Un commit por bloque, gitmoji + mensaje en español, `Co-Authored-By` de Claude al final.
- Trabajo siempre en rama nueva desde `main`. **Nunca** merge, push, tag ni release sin OK explícito. Merge con `--no-ff` y mensaje `🔀 Merge rama <rama>: versión X.Y.Z`.
- Tras cada bloque: `php -l` en los PHP tocados y `phpcs` (estándar WordPress vía `phpcs.xml.dist`). Debe quedar a 0.
- Enseñar el diff de cada bloque antes de seguir con el siguiente.
- No crear repos, releases ni borrar nada remoto sin preguntar.
- Respuestas en español.

## Git y releases

- Remoto del boilerplate: **`upstream`** (`https://github.com/fgrweb/fgrweb-plugin`). Los plugins generados usan `origin`.
- Tags `X.Y.Z` **sin "v"**. Al subir un tag, `.github/workflows/release.yml` crea la release con `fgrweb-plugin-X.Y.Z.zip` (git archive, respeta `.gitattributes` export-ignore). Procedimiento completo y recuperación de un tag fallido en `README.md`.
- Antes de un tag deben coincidir: cabecera `Version:`, `FGRWEB_PLUGIN_VERSION`, `Stable tag:` y nueva sección `= X.Y.Z =` en el changelog de `README.txt`.
- `README.txt` = descripción pública (va en el zip). `README.md` = docs de desarrollo (no va). Secciones de `README.md` entre `<!-- solo-boilerplate:inicio -->` y `:fin` se eliminan del plugin generado.

## Tooling

- `phpcs` del PATH es 3.13.6 con WPCS (composer global). **`phpcbf` del PATH es 3.5.3 (viejo, `/usr/local/bin/wpcs/`)**: usar `~/.composer/vendor/bin/phpcbf`.
- `phpcs.xml.dist` excluye `includes/updater-checker/`, `vendor/`, `node_modules/` y el docblock de fichero en los `index.php`.
- Prueba en seco del script: `printf 'slug\nNombre\n\n\n\n\n\ns\nn\n' | BOILERPLATE_REPO=<ruta local> BOILERPLATE_BRANCH=<rama> bash setup-plugin.sh` en un directorio vacío del scratchpad, y después `grep -rE "fgrweb-plugin|FGRWEB_PLUGIN|fgrweb_plugin|Fgrweb_Plugin"` (solo deben quedar métodos de clase `fgrweb_*` y URLs `fgrweb.es`).

## Convenciones de código

- WPCS: tabs, espacios dentro de paréntesis, docblocks, `esc_*` en toda salida, `sanitize_*` en toda entrada.
- Opciones: `fgrweb_plugin_config` (token) y `fgrweb_plugin_security_options`. Todo identificador persistente (opciones, transients, cron, handles) lleva el slug completo `fgrweb_plugin` / `fgrweb-plugin` para que el script lo renombre y no colisione entre plugins. Los métodos de clase `fgrweb_*` no hace falta.
- Guardado de ajustes solo por Settings API (`options.php` + `sanitize_callback`). `fgrweb_sanitize_config()` reconstruye la opción: cualquier clave nueva hay que añadirla ahí.
- Opciones se borran solo en `uninstall.php` (multisitio incluido), nunca al desactivar.
- `WP_POST_REVISIONS` se define en el constructor (antes de `wp_functionality_constants()`), no vía filtro: respeta `wp-config.php`.

## Revisión de plugins antiguos

Plugins generados antes de 1.2.0 que estén en producción. Comprobar y portar:

1. Opción `fgrweb_security_options` compartida entre plugins (colisión; el uninstall de uno borra la de todos).
2. `function plugin_updater_checker` sin prefijo → fatal con dos plugins en la misma web.
3. Guardado con `$_POST['submit']` sin nonce (anteriores a `f0afbe5`).
4. Token con `value="*********"`: guardar sin tocar lo pisa.
5. `$_GET['tab']` sin sanear ni escapar (XSS reflejado en admin).
6. Ruta del updater con `dirname(__FILE__) . '<slug>/...'`.
7. Deactivator que borra opciones.
8. Grupo de `settings_fields` distinto del registrado.
9. PUC 5.6 → 5.7: sustituir carpeta tal cual.

## Sesiones

- [2026-10-23](.claude/session-2026-10-23.md) — revisión completa, rama `seguridad` → 1.2.0 (seguridad, updater, token, script, PUC 5.7, WPCS), rama `release-action` → 1.3.0 (Action de release, README.md, prueba en repo temporal).
