#!/bin/sh
set -eu

# ==================================================
# Application paths
# ==================================================
APP_SOURCE="/opt/mikhmon"
WEB_ROOT="/var/www/html"
DATA_DIR="/opt/mikhmon/data"

CONFIG_FILE="$DATA_DIR/config.php"
CONFIG_TEMPLATE="$APP_SOURCE/include/config.default.php"
WEB_CONFIG="$WEB_ROOT/include/config.php"

echo "=========================================="
echo "       Mikhmon initialization"
echo "=========================================="

# ==================================================
# 1. Prepare required directories
# ==================================================
echo "[1/6] Preparing directories..."

mkdir -p "$DATA_DIR"
mkdir -p "$WEB_ROOT"

# ==================================================
# 2. Install application files
# ==================================================
echo "[2/6] Checking application files..."

if [ -z "$(ls -A "$WEB_ROOT")" ]; then
    echo "Installing application files..."

    find "$APP_SOURCE" -mindepth 1 -maxdepth 1 ! -name data \
        -exec cp -a {} "$WEB_ROOT/" \;
else
    echo "Web directory already contains files. Skipping copy."
fi

# ==================================================
# 3. Initialize persistent configuration
# ==================================================
echo "[3/6] Checking persistent configuration..."

if [ ! -f "$CONFIG_FILE" ]; then
    echo "Creating default configuration..."

    cp "$CONFIG_TEMPLATE" "$CONFIG_FILE"
else
    echo "Persistent configuration already exists."
fi

# ==================================================
# 4. Configure application files
# ==================================================
echo "[4/6] Configuring application..."

mkdir -p "$WEB_ROOT/include"

# Remove deployment template from public web directory
rm -f "$WEB_ROOT/include/config.default.php"

# Create configuration symlink safely
if [ -L "$WEB_CONFIG" ]; then
    rm "$WEB_CONFIG"
elif [ -e "$WEB_CONFIG" ]; then
    echo "ERROR: $WEB_CONFIG exists and is not a symlink."
    echo "Please check this file before continuing."
    exit 1
fi

ln -s "$CONFIG_FILE" "$WEB_CONFIG"

# ==================================================
# 5. Set application ownership
# ==================================================
echo "[5/6] Setting application ownership..."

chown -R www-data:www-data "$WEB_ROOT"

# Set ownership of persistent configuration
chown www-data:www-data "$CONFIG_FILE" 2>/dev/null || true

# ==================================================
# 6. Start Apache
# ==================================================
echo "[6/6] Initialization completed."

echo "Mikhmon is ready."
echo "=========================================="

exec apache2-foreground
