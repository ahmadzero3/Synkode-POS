#!/bin/bash
set -e

echo "🗄️  Starting PostgreSQL Backup..."
echo "------------------------------------"

# === AUTO-INSTALL zip/unzip IF NOT INSTALLED ===
if ! command -v zip >/dev/null 2>&1; then
    echo "📦 Installing 'zip' and 'unzip'..."
    sudo apt update -y >/dev/null && sudo apt install -y zip unzip >/dev/null
    echo "✅ 'zip' installed successfully!"
fi

# === CONFIGURATION ===
BACKUP_DIR="storage/app/backups"
DB_CONTAINER="db"
DB_NAME="laravel"
DB_USER="postgres"
DB_PASS="postgres"
DB_HOST="db"
DATE=$(date +%Y-%m-%d_%H-%M-%S)

# === DETECT WINDOWS DESKTOP PATH ===
# Works on WSL or Linux
if grep -qi microsoft /proc/version 2>/dev/null; then
    # Running inside WSL → map to Windows Desktop
    WIN_HOME=$(wslpath "$(cmd.exe /c "echo %USERPROFILE%" 2>/dev/null | tr -d '\r')")
    DESKTOP_PATH="$WIN_HOME/Desktop"
else
    # Native Linux fallback
    DESKTOP_PATH="$HOME/Desktop"
fi

# === PREPARE DIRECTORIES ===
mkdir -p "$BACKUP_DIR" || true
mkdir -p "$DESKTOP_PATH" || true

# === FILE NAMES ===
SQL_FILE="$BACKUP_DIR/database_backup_${DATE}.sql"
BACKUP_FILE="$BACKUP_DIR/database_backup_${DATE}.backup"
ZIP_FILE="$BACKUP_DIR/database_backup_${DATE}.zip"
ZIP_DESKTOP="$DESKTOP_PATH/database_backup_${DATE}.zip"

# === DETECT DOCKER OR LOCAL ===
if docker compose ps $DB_CONTAINER >/dev/null 2>&1; then
    echo "🐳 Using Docker container: $DB_CONTAINER"
    DOCKER_EXEC="docker compose exec -T $DB_CONTAINER"
else
    echo "💻 Running locally..."
    DOCKER_EXEC=""
fi

# === BACKUP DATABASE ===
echo "📦 Dumping SQL..."
if ! $DOCKER_EXEC bash -c "PGPASSWORD=$DB_PASS pg_dump -h $DB_HOST -U $DB_USER -F p $DB_NAME" > "$SQL_FILE"; then
    echo "❌ Failed to create SQL dump!"
    exit 1
fi

echo "📦 Dumping Custom Backup..."
if ! $DOCKER_EXEC bash -c "PGPASSWORD=$DB_PASS pg_dump -h $DB_HOST -U $DB_USER -F c $DB_NAME" > "$BACKUP_FILE"; then
    echo "❌ Failed to create Custom backup!"
    exit 1
fi

# === ZIP FILES ===
echo "🗜️  Compressing..."
zip -j "$ZIP_FILE" "$SQL_FILE" "$BACKUP_FILE" >/dev/null || {
    echo "❌ Failed to zip files!"
    exit 1
}

# === COPY TO WINDOWS DESKTOP ===
if cp "$ZIP_FILE" "$ZIP_DESKTOP"; then
    echo "💾 Copied to Windows Desktop: $ZIP_DESKTOP"
else
    echo "⚠️  Could not copy to Windows Desktop!"
fi

# === CLEANUP TEMP FILES ===
rm -f "$SQL_FILE" "$BACKUP_FILE"

# === DONE ===
SIZE=$(du -h "$ZIP_FILE" | cut -f1)
echo "✅ Backup complete!"
echo "📁 Stored in: $BACKUP_DIR"
echo "🖥️  Also on Windows Desktop: $ZIP_DESKTOP ($SIZE)"
echo "------------------------------------"
echo "🕒 Finished at: $(date)"
