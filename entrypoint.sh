#!/bin/sh
set -eu

# ==================================================
# Application paths
# ==================================================
WEB_ROOT="/var/www/html"
DATA_DIR="/opt/mikhmon/data"

CONFIG_FILE="$DATA_DIR/config.php"
CONFIG_TEMPLATE="$WEB_ROOT/include/config.default.php"
WEB_CONFIG="$WEB_ROOT/include/config.php"

echo "=========================================="
echo "       Mikhmon initialization"
echo "=========================================="

# ==================================================
# 1. Prepare persistent data directory
# ==================================================
echo "[1/5] Preparing data directory..."

mkdir -p "$DATA_DIR"

# ==================================================
# 2. Initialize persistent configuration
# ==================================================
echo "[2/5] Checking persistent configuration..."

if [ ! -f "$CONFIG_FILE" ]; then
    echo "Creating default configuration..."

    cp "$CONFIG_TEMPLATE" "$CONFIG_FILE"
else
    echo "Persistent configuration already exists."
fi

# ==================================================
# 3. Configure application
# ==================================================
echo "[3/5] Configuring application..."

# Remove deployment template from public web directory
rm -f "$CONFIG_TEMPLATE"

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
# 4. Set application ownership
# ==================================================
echo "[4/5] Setting application ownership..."

chown -R www-data:www-data "$WEB_ROOT"
chown www-data:www-data "$CONFIG_FILE"

# ==================================================
# 5. Start Apache
# ==================================================
echo "[5/5] Initialization completed."

echo "Mikhmon is ready."
echo "Web root: $WEB_ROOT"
echo "Data directory: $DATA_DIR"
echo "=========================================="

exec apache2-foreground
