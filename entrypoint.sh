#!/bin/sh
set -eu

APP_SOURCE="/opt/mikhmon"
WEB_ROOT="/var/www/html"
DATA_DIR="/opt/mikhmon/data"
CONFIG_FILE="$DATA_DIR/config.php"
CONFIG_TEMPLATE="$APP_SOURCE/include/config.default.php"
WEB_CONFIG="$WEB_ROOT/include/config.php"

echo "=== Mikhmon initialization ==="

# Prepare directories
mkdir -p "$DATA_DIR"
mkdir -p "$WEB_ROOT"

# Copy application files only when web directory is empty
if [ -z "$(ls -A "$WEB_ROOT")" ]; then
    echo "Installing Mikhmon application files..."

    find "$APP_SOURCE" -mindepth 1 -maxdepth 1 ! -name data \
        -exec cp -a {} "$WEB_ROOT/" \;
else
    echo "Web directory already contains files. Skipping copy."
fi

# Remove deployment template from public web directory
rm -f "$WEB_ROOT/include/config.default.php"

# Initialize persistent configuration only once
if [ ! -f "$CONFIG_FILE" ]; then
    echo "Creating default configuration..."
    cp "$CONFIG_TEMPLATE" "$CONFIG_FILE"
fi

# Ensure include directory exists
mkdir -p "$WEB_ROOT/include"

# Create configuration symlink safely
if [ -L "$WEB_CONFIG" ]; then
    rm "$WEB_CONFIG"
elif [ -e "$WEB_CONFIG" ]; then
    echo "ERROR: $WEB_CONFIG exists and is not a symlink."
    echo "Please check this file before continuing."
    exit 1
fi

ln -s "$CONFIG_FILE" "$WEB_CONFIG"

# Set ownership of persistent configuration (best effort)
chown www-data:www-data "$CONFIG_FILE" 2>/dev/null || true

echo "Mikhmon initialization completed."

exec apache2-foreground
