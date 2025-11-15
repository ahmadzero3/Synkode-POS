#!/bin/sh
set -e
echo "🚀 Setting up Synkode-POS Docker environment..."

# ✅ Set global Composer timeout (fixes build timeouts)
export COMPOSER_PROCESS_TIMEOUT=2000

# ✅ Ensure storage dirs exist
mkdir -p storage bootstrap/cache
chmod -R 775 storage bootstrap/cache || true

# ✅ Create .env if missing
if [ ! -f .env ]; then
    cp .env.example .env
fi

# ✅ Skip installer
sed -i 's/INSTALLATION_STATUS=false/INSTALLATION_STATUS=true/' .env || true

# ✅ Hide warnings for users
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env || true

# ✅ PhpSpreadsheet + pg_dump Fix (with bash + apk update)
docker compose run --rm app sh -c "
    apk update &&
    apk add --no-cache bash libzip-dev unzip libpng-dev libxml2-dev postgresql-client &&
    docker-php-ext-install zip gd dom || true
"

# ✅ Build with longer timeout
COMPOSER_PROCESS_TIMEOUT=2000 docker compose build app || {
    echo "⚠️ Composer build failed, retrying with --prefer-source..."
    COMPOSER_PROCESS_TIMEOUT=2000 COMPOSER_PREFER_SOURCE=1 docker compose build --no-cache app
}

docker compose up -d
docker compose exec app git config --global --add safe.directory /var/www/html

# ✅ Composer install with retry (inside container)
docker compose exec app composer install --no-interaction --optimize-autoloader || \
COMPOSER_PROCESS_TIMEOUT=2000 docker compose exec app composer install --prefer-source --no-interaction --optimize-autoloader

docker compose exec app php artisan key:generate || true
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize

# ✅ Permanent Fix for Permissions
docker compose exec app chown -R www-data:www-data storage bootstrap/cache || true
docker compose exec app chmod -R 775 storage bootstrap/cache || true
docker compose exec app chmod -R 775 storage/app || true
docker compose exec app chmod -R 775 storage/app/backups || true

# ✅ Ensure backups folder exists inside container
docker compose exec app mkdir -p storage/app/backups
docker compose exec app chown -R www-data:www-data storage/app/backups
docker compose exec app chmod -R 775 storage/app/backups

# 🧩 NEW: Fix .env permissions for license activation
echo "🔐 Fixing .env permissions for license system..."
docker compose exec app sh -c "
    if [ -f /var/www/html/.env ]; then
        chown www-data:www-data /var/www/html/.env || true
        chmod 664 /var/www/html/.env || true
    fi
"

echo "✅ Docker setup complete! Visit http://localhost/login"
