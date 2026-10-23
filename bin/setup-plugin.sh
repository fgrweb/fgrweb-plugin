#!/bin/bash

# Script para personalizar el boilerplate de plugin de WordPress
# Autor: F.GR Web
# Versión: 2.0.0
#
# Uso:
#   a) En un directorio vacío (descarga el boilerplate):
#        curl -O https://raw.githubusercontent.com/fgrweb/fgrweb-plugin/main/bin/setup-plugin.sh
#        bash setup-plugin.sh
#   b) Dentro de un clon del boilerplate:
#        bash bin/setup-plugin.sh
#
# Variables de entorno opcionales:
#   BOILERPLATE_REPO    URL o ruta del repositorio del boilerplate.
#   BOILERPLATE_BRANCH  Rama a clonar (por defecto: main).

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Variables
BOILERPLATE_REPO="${BOILERPLATE_REPO:-https://github.com/fgrweb/fgrweb-plugin.git}"
BOILERPLATE_BRANCH="${BOILERPLATE_BRANCH:-main}"
TEMP_DIR=".boilerplate-temp"
SCRIPT_NAME="$(basename "$0")"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

# Identificadores del boilerplate que se reemplazan.
OLD_SLUG="fgrweb-plugin"
OLD_CLASS="Fgrweb_Plugin"
OLD_CONST="FGRWEB_PLUGIN"
OLD_FUNCTION="fgrweb_plugin"
OLD_TITLE="Fgrweb Plugin"

# Función para imprimir mensajes con color
print_message() {
    echo -e "${2}${1}${NC}"
}

print_success() {
    print_message "✓ $1" "$GREEN"
}

print_error() {
    print_message "✗ $1" "$RED"
}

print_info() {
    print_message "ℹ $1" "$BLUE"
}

print_warning() {
    print_message "⚠ $1" "$YELLOW"
}

# Función para limpiar en caso de error
cleanup() {
    if [ -d "$TEMP_DIR" ]; then
        rm -rf "$TEMP_DIR"
    fi
}

trap cleanup EXIT

# sed in-place portable (GNU usa -i, BSD/macOS usa -i ''). Sin ficheros .bak.
if sed --version >/dev/null 2>&1; then
    SED_INPLACE=(sed -i)
else
    SED_INPLACE=(sed -i '')
fi

sedi() {
    "${SED_INPLACE[@]}" "$@"
}

# Escapa un texto para usarlo como reemplazo en sed con delimitador "|".
sed_escape() {
    printf '%s' "$1" | sed -e 's/[\\&|]/\\&/g'
}

# Función para convertir slug a formato de clase: mi-plugin-largo -> Mi_Plugin_Largo
slug_to_class() {
    echo "$1" | awk -F'-' '{
        out = ""
        for (i = 1; i <= NF; i++) {
            if ($i == "") continue
            part = toupper(substr($i, 1, 1)) substr($i, 2)
            out = (out == "") ? part : out "_" part
        }
        print out
    }'
}

# Función para convertir slug a formato de constante (mayúsculas con guiones bajos)
slug_to_const() {
    echo "$1" | tr '[:lower:]' '[:upper:]' | tr '-' '_'
}

# Función para convertir slug a formato de función (minúsculas con guiones bajos)
slug_to_function() {
    echo "$1" | tr '-' '_'
}

# Función para convertir slug a título: mi-plugin-largo -> Mi Plugin Largo
slug_to_title() {
    slug_to_class "$1" | tr '_' ' '
}

# Banner
clear 2>/dev/null || true
print_info "================================================"
print_info "  WordPress Plugin Boilerplate Setup Script"
print_info "================================================"
echo ""

# 1. Localizar el boilerplate: clon existente o descarga.
if [ -f "$SCRIPT_DIR/../${OLD_SLUG}.php" ]; then
    # Modo b): el script vive en bin/ dentro de un clon del boilerplate.
    cd "$SCRIPT_DIR/.."
    print_info "Boilerplate detectado en: $(pwd)"

    if [ -d ".git" ]; then
        read -p "$(echo -e ${YELLOW}Se eliminará el historial git del boilerplate. ¿Continuar? \(s/n\): ${NC})" REMOVE_GIT
        if [[ ! "$REMOVE_GIT" =~ ^[sS]$ ]]; then
            print_warning "Operación cancelada"
            exit 0
        fi
        rm -rf .git
    fi
else
    # Modo a): directorio vacío (salvo este script). Se descarga el boilerplate.
    if [ "$SCRIPT_DIR" = "$(pwd)" ]; then
        FILE_COUNT=$(ls -A | grep -v "^${SCRIPT_NAME}$" | wc -l | tr -d ' ')
    else
        FILE_COUNT=$(ls -A | wc -l | tr -d ' ')
    fi
    if [ "$FILE_COUNT" -gt 0 ]; then
        print_error "Este directorio no está vacío. El script debe ejecutarse en un directorio vacío o desde bin/ dentro del boilerplate."
        exit 1
    fi

    print_info "Paso 1: Descargando archivos del boilerplate..."
    echo ""
    print_info "Clonando repositorio desde: $BOILERPLATE_REPO (rama $BOILERPLATE_BRANCH)"
    git clone --depth 1 --branch "$BOILERPLATE_BRANCH" "$BOILERPLATE_REPO" "$TEMP_DIR" 2>/dev/null

    if [ ! -d "$TEMP_DIR" ]; then
        print_error "Error al clonar el repositorio"
        exit 1
    fi

    # Eliminar la referencia al repositorio original antes de mover.
    rm -rf "$TEMP_DIR/.git"

    # Mover archivos (incluidos los ocultos) del temporal al directorio actual.
    print_info "Copiando archivos..."
    shopt -s dotglob
    mv "$TEMP_DIR"/* .
    shopt -u dotglob
    rm -rf "$TEMP_DIR"

    # El script descargado a mano se borra ya (bash lo mantiene abierto hasta terminar).
    # El de bin/ se elimina al final junto con la carpeta.
    if [ "$SCRIPT_DIR" = "$(pwd)" ] && [ -f "./$SCRIPT_NAME" ]; then
        rm -f "./$SCRIPT_NAME"
    fi

    print_success "Archivos descargados correctamente"
    echo ""
fi

# 2. Solicitar datos al usuario
print_info "Paso 2: Configuración del plugin"
echo ""

# Slug del plugin
while true; do
    read -p "$(echo -e ${BLUE}Introduce el slug del plugin \(ej: mi-plugin-increible\): ${NC})" NEW_SLUG
    if [[ -z "$NEW_SLUG" ]]; then
        print_error "El slug no puede estar vacío"
    elif [[ ! "$NEW_SLUG" =~ ^[a-z0-9]+(-[a-z0-9]+)*$ ]]; then
        print_error "El slug solo puede contener letras minúsculas, números y guiones simples (sin empezar ni terminar en guion)"
    else
        break
    fi
done

# Nombre del plugin
DEFAULT_NAME="$(slug_to_title "$NEW_SLUG")"
read -p "$(echo -e ${BLUE}Introduce el nombre del plugin \(presiona Enter para usar \"$DEFAULT_NAME\"\): ${NC})" PLUGIN_NAME
if [[ -z "$PLUGIN_NAME" ]]; then
    PLUGIN_NAME="$DEFAULT_NAME"
fi

# Plugin URI
read -p "$(echo -e ${BLUE}Introduce la URI del plugin \(Enter: https://fgrweb.es\): ${NC})" PLUGIN_URI
if [[ -z "$PLUGIN_URI" ]]; then
    PLUGIN_URI="https://fgrweb.es"
fi

# Descripción
read -p "$(echo -e ${BLUE}Introduce la descripción del plugin: ${NC})" PLUGIN_DESCRIPTION
if [[ -z "$PLUGIN_DESCRIPTION" ]]; then
    PLUGIN_DESCRIPTION="Custom plugin"
fi

# Autor
read -p "$(echo -e ${BLUE}Introduce el nombre del autor \(Enter: Fernando García Rebolledo\): ${NC})" AUTHOR_NAME
if [[ -z "$AUTHOR_NAME" ]]; then
    AUTHOR_NAME="Fernando García Rebolledo"
fi

# Autor URI
read -p "$(echo -e ${BLUE}Introduce la URI del autor \(Enter: https://fgrweb.es\): ${NC})" AUTHOR_URI
if [[ -z "$AUTHOR_URI" ]]; then
    AUTHOR_URI="https://fgrweb.es"
fi

# Repositorio GitHub para el updater
DEFAULT_UPDATE_REPO="https://github.com/fgrweb/${NEW_SLUG}/"
read -p "$(echo -e ${BLUE}Introduce la URL del repositorio GitHub para actualizaciones \(Enter: $DEFAULT_UPDATE_REPO\): ${NC})" UPDATE_REPO
if [[ -z "$UPDATE_REPO" ]]; then
    UPDATE_REPO="$DEFAULT_UPDATE_REPO"
fi

# Calcular diferentes formatos del slug
NEW_CLASS=$(slug_to_class "$NEW_SLUG")
NEW_CONST=$(slug_to_const "$NEW_SLUG")
NEW_FUNCTION=$(slug_to_function "$NEW_SLUG")

# Confirmación
echo ""
print_info "================================================"
print_info "Resumen de la configuración:"
print_info "================================================"
echo "Slug:          $NEW_SLUG"
echo "Nombre:        $PLUGIN_NAME"
echo "Plugin URI:    $PLUGIN_URI"
echo "Descripción:   $PLUGIN_DESCRIPTION"
echo "Autor:         $AUTHOR_NAME"
echo "Autor URI:     $AUTHOR_URI"
echo "Repo updater:  $UPDATE_REPO"
echo "Clase:         $NEW_CLASS"
echo "Constante:     $NEW_CONST"
echo "Función:       $NEW_FUNCTION"
print_info "================================================"
echo ""

read -p "$(echo -e ${YELLOW}¿Continuar con estos datos? \(s/n\): ${NC})" CONFIRM
if [[ ! "$CONFIRM" =~ ^[sS]$ ]]; then
    print_warning "Operación cancelada"
    exit 0
fi

echo ""
print_info "Paso 3: Aplicando transformaciones..."
echo ""

# 3. Reemplazar contenido en archivos
print_info "Actualizando contenido de archivos..."

# Buscar todos los archivos PHP, CSS, JS, TXT, POT y MD (fuera de bin/, vendor y node_modules).
# El text domain es siempre el slug, así que lo cubre el reemplazo de fgrweb-plugin.
FILES=$(find . -type f \( -name "*.php" -o -name "*.css" -o -name "*.js" -o -name "*.txt" -o -name "*.pot" -o -name "*.md" -o -name "*.xml" -o -name "*.dist" \) ! -path "*/\.*" ! -path "./bin/*" ! -path "*/node_modules/*" ! -path "*/vendor/*" ! -path "*/updater-checker/*")

# Título para ficheros no PHP (README, .pot) y para PHP (apóstrofes escapados: ' -> \').
ESC_TITLE="$(sed_escape "$PLUGIN_NAME")"
ESC_TITLE_PHP="$(sed_escape "$(printf '%s' "$PLUGIN_NAME" | sed "s/'/\\\\'/g")")"

for file in $FILES; do
    if [ -f "$file" ]; then
        # Orden: clase, constante, función, slug (también text domain), título.
        sedi "s|${OLD_CLASS}|${NEW_CLASS}|g" "$file"
        sedi "s|${OLD_CONST}|${NEW_CONST}|g" "$file"
        sedi "s|${OLD_FUNCTION}|${NEW_FUNCTION}|g" "$file"
        sedi "s|${OLD_SLUG}|${NEW_SLUG}|g" "$file"
        if [[ "$file" == *.php ]]; then
            sedi "s|${OLD_TITLE}|${ESC_TITLE_PHP}|g" "$file"
        else
            sedi "s|${OLD_TITLE}|${ESC_TITLE}|g" "$file"
        fi
    fi
done

print_success "Contenido actualizado en todos los archivos"

# 4. Actualizar cabecera del plugin principal (aún con el nombre antiguo, se renombra en el paso 5)
print_info "Actualizando cabecera del plugin..."

MAIN_FILE="${OLD_SLUG}.php"
if [ -f "$MAIN_FILE" ]; then
    sedi "s|^\( \* Plugin Name:[[:space:]]*\).*|\1$(sed_escape "$PLUGIN_NAME")|" "$MAIN_FILE"
    sedi "s|^\( \* Plugin URI:[[:space:]]*\).*|\1$(sed_escape "$PLUGIN_URI")|" "$MAIN_FILE"
    sedi "s|^\( \* Description:[[:space:]]*\).*|\1$(sed_escape "$PLUGIN_DESCRIPTION")|" "$MAIN_FILE"
    sedi "s|^\( \* Author:[[:space:]]*\).*|\1$(sed_escape "$AUTHOR_NAME")|" "$MAIN_FILE"
    sedi "s|^\( \* Author URI:[[:space:]]*\).*|\1$(sed_escape "$AUTHOR_URI")|" "$MAIN_FILE"

    # URL del repositorio para el updater.
    sedi "s|'https://github.com/fgrweb/${NEW_SLUG}/'|'$(sed_escape "$UPDATE_REPO")'|" "$MAIN_FILE"

    # @link en todos los PHP.
    for file in $FILES; do
        if [[ "$file" == *.php ]]; then
            sedi "s|^\( \* @link[[:space:]]*\)https://fgrweb.es$|\1$(sed_escape "$PLUGIN_URI")|" "$file"
        fi
    done

    print_success "Cabecera actualizada"
else
    print_warning "Archivo principal no encontrado: $MAIN_FILE"
fi

# README: nombre del plugin en la cabecera === ... ===
if [ -f "README.txt" ]; then
    sedi "s|^=== .* ===$|=== $(sed_escape "$PLUGIN_NAME") ===|" "README.txt"
fi

# 5. Renombrar archivos
print_info "Renombrando archivos..."

FILES_TO_RENAME=$(find . -type f -name "*${OLD_SLUG}*" ! -path "*/\.*" ! -path "./bin/*" ! -path "*/node_modules/*" ! -path "*/vendor/*" ! -path "*/updater-checker/*")

for old_file in $FILES_TO_RENAME; do
    new_file=$(echo "$old_file" | sed "s|${OLD_SLUG}|${NEW_SLUG}|g")
    if [ "$old_file" != "$new_file" ]; then
        mkdir -p "$(dirname "$new_file")"
        mv "$old_file" "$new_file"
        print_success "Renombrado: $(basename "$old_file") → $(basename "$new_file")"
    fi
done

# 6. Renombrar directorios si es necesario
DIRS_TO_RENAME=$(find . -type d -name "*${OLD_SLUG}*" ! -path "*/\.*" ! -path "./bin/*" ! -path "*/node_modules/*" ! -path "*/vendor/*" ! -path "*/updater-checker/*" | sort -r)

for old_dir in $DIRS_TO_RENAME; do
    new_dir=$(echo "$old_dir" | sed "s|${OLD_SLUG}|${NEW_SLUG}|g")
    if [ "$old_dir" != "$new_dir" ] && [ -d "$old_dir" ]; then
        mv "$old_dir" "$new_dir"
        print_success "Directorio renombrado: $(basename "$old_dir") → $(basename "$new_dir")"
    fi
done

echo ""
print_success "Plugin personalizado correctamente"

# 7. Crear archivo .gitignore
echo ""
print_info "Creando archivo .gitignore..."

cat > .gitignore << 'EOF'
# Logs and databases
*.log
*.sql
*.sqlite

# Environment files with sensitive information
.env
.env.*

# Development tools
/node_modules/
/vendor/
npm-debug.log
yarn-error.log
/dist/
/build/
package-lock.json
composer.lock

# System files
.DS_Store
Thumbs.db
*.swp
*.swo

# IDE and editor folders
/.idea/
/.vscode/*
!/.vscode/settings.json
!/.vscode/tasks.json
!/.vscode/launch.json
!/.vscode/extensions.json

# Local by Flywheel specific
/local-site.json
/local-xdebuginfo.php

# Client-specific override files
.client-overrides.json

# Deployment exclusions
.deployignore

# Temporary files
*.tmp
*.bak
*.zip
EOF

print_success ".gitignore creado correctamente"

# 8. Eliminar bin/ (este script) del plugin generado.
echo ""
print_info "Eliminando bin/ del plugin generado..."
rm -rf bin
print_success "bin/ eliminado"

# 9. Preguntar si quiere añadir a repositorio remoto
echo ""
read -p "$(echo -e ${BLUE}¿Deseas subir este plugin a un repositorio remoto? \(s/n\): ${NC})" ADD_REMOTE
if [[ "$ADD_REMOTE" =~ ^[sS]$ ]]; then
    echo ""
    read -p "$(echo -e ${BLUE}Introduce la URL del repositorio remoto: ${NC})" REMOTE_URL

    if [[ -z "$REMOTE_URL" ]]; then
        print_warning "URL vacía. No se configurará repositorio remoto."
    else
        print_info "Inicializando repositorio Git..."
        git init -q
        git branch -M main
        git remote add origin "$REMOTE_URL"
        git add .
        git commit -q -m "🎉 Initial commit"

        print_info "Subiendo archivos al repositorio remoto..."
        if git push -u origin main; then
            print_success "Plugin subido correctamente a: $REMOTE_URL"
        else
            print_error "Error al subir al repositorio remoto"
            print_warning "Puedes intentar subirlo manualmente con: git push -u origin main"
        fi
    fi
else
    print_info "Plugin no subido a repositorio remoto"
    print_info "Si deseas crear un repositorio local, ejecuta: git init"
fi

# 10. Resumen final
echo ""
print_info "================================================"
print_success "¡Plugin personalizado exitosamente!"
print_info "================================================"
echo ""
print_info "Próximos pasos:"
echo "  1. Revisa los archivos generados"
echo "  2. Instala el plugin en WordPress"
echo "  3. Comienza a desarrollar tu funcionalidad"
echo ""
print_info "Archivo principal del plugin: ${NEW_SLUG}.php"
print_info "================================================"
echo ""
