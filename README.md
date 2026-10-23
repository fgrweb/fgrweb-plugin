# Fgrweb Plugin

Plugin de WordPress basado en el boilerplate de F.GR Web: estructura de
[WordPress Plugin Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate),
actualizaciones desde GitHub con [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker)
y una página de ajustes con pestañas **Actualizaciones** (token de GitHub) y **Seguridad**.

Este `README.md` es documentación para desarrolladores y no viaja en el zip de release
(`.gitattributes` → `export-ignore`). La descripción pública del plugin está en `README.txt`.

## Crear un plugin nuevo a partir del boilerplate

Solo aplica al repositorio del boilerplate (`fgrweb/fgrweb-plugin`). En un plugin ya generado
la carpeta `bin/` no existe.

```bash
mkdir mi-plugin && cd mi-plugin
curl -O https://raw.githubusercontent.com/fgrweb/fgrweb-plugin/main/bin/setup-plugin.sh
bash setup-plugin.sh
```

El script descarga el boilerplate, pide slug, nombre, autor y repositorio de GitHub, renombra
clases, constantes, funciones, opciones, ficheros y el workflow de release, crea `.gitignore`,
elimina `bin/` y, si se le indica, hace el primer push a `origin`.

## Cómo publicar una versión

Las releases se crean solas: al subir un tag `X.Y.Z` (sin `v`), la Action
`.github/workflows/release.yml` genera el zip y publica la release en GitHub. El updater del
plugin descarga ese zip en las webs donde esté instalado.

1. Sube la versión en los cuatro sitios. Los tres primeros tienen que coincidir exactamente
   con el tag:
   - `fgrweb-plugin.php`, cabecera `Version:`
   - `fgrweb-plugin.php`, constante `FGRWEB_PLUGIN_VERSION`
   - `README.txt`, `Stable tag:`
   - `README.txt`, nueva sección `= X.Y.Z =` en `== Changelog ==` (son las notas de la release)
2. Commit y push:

   ```bash
   git add -A
   git commit -m "🔖 Versión X.Y.Z"
   git push origin main
   ```

3. Tag y push del tag (solo ese tag, no `--tags`):

   ```bash
   git tag X.Y.Z
   git push origin X.Y.Z
   ```

> En el repositorio del boilerplate el remoto se llama `upstream`, no `origin`:
> sustituye `origin` por `upstream` en los comandos.

### Qué hace la Action

1. Comprueba que el tag coincide con la cabecera `Version:`, la constante y el `Stable tag:`.
2. Comprueba que no existe ya una release con ese tag.
3. Genera `fgrweb-plugin-X.Y.Z.zip` con `git archive` (carpeta raíz `fgrweb-plugin/`).
   Respeta `.gitattributes`, así que `bin/`, `.github/`, `phpcs.xml.dist` y demás ficheros
   de desarrollo no entran. Si aun así aparecen, el job falla.
4. Toma las notas de la sección `= X.Y.Z =` del changelog de `README.txt`. Si no existe,
   usa "Versión X.Y.Z." sin fallar.
5. Crea la release `X.Y.Z` con el zip adjunto.

Solo usa `actions/checkout` (fijada a un SHA) y el `gh` CLI del runner con `GITHUB_TOKEN`.
Permisos: `contents: write`.

### Si la Action falla por versiones no sincronizadas

El job termina con error, no publica nada y en el log aparece qué fichero no coincide.
El tag ya está subido, así que hay que rehacerlo:

```bash
# 1. Corrige la cabecera, la constante o el Stable tag y haz commit.
git add -A
git commit -m "🔖 Versión X.Y.Z"
git push origin main

# 2. Borra el tag en local y en el remoto.
git tag -d X.Y.Z
git push origin :refs/tags/X.Y.Z

# 3. Vuelve a crearlo sobre el commit corregido y súbelo.
git tag X.Y.Z
git push origin X.Y.Z
```

Si la Action falla porque **ya existe una release** con ese tag, no la toca: borra la release
(o elige otra versión) antes de repetir.

## Desarrollo

- Estándar de código: WordPress (`phpcs.xml.dist`). `phpcs` y `phpcbf` ignoran
  `includes/updater-checker/`.
- Opciones del plugin: `fgrweb_plugin_config` (token) y `fgrweb_plugin_security_options`.
  Se borran solo al desinstalar (`uninstall.php`), no al desactivar.
- El token de GitHub se guarda vía Settings API; el campo se muestra siempre vacío y
  guardar con el campo vacío conserva el token.
