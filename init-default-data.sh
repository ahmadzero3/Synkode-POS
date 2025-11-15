set -e
echo "🚀 Initializing Synkode-POS with default data..."
echo "================================================"

if ! docker ps | grep -q "app-1"; then
    echo "❌ Containers not running. Run: docker compose up -d"
    exit 1
fi

docker compose exec -T db pg_isready -U postgres -d laravel || {
    echo "❌ Database not ready."
    exit 1
}
echo "✅ Database connection successful"

# 🧩 NEW: Ensure .env is writable before any seed/migration
echo "🔐 Ensuring .env and storage permissions are correct..."
docker compose exec app sh -c "
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/.env || true
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true
    chmod 664 /var/www/html/.env || true
"

docker compose exec app php artisan migrate --force
echo "✅ Migrations done"

docker compose exec app php artisan db:seed --class=VersionSeeder --force || {
    echo "❌ Version seeding failed. Check VersionSeeder.php"
    exit 1
}
echo "✅ VersionSeeder applied"

docker compose exec app php artisan db:seed --force || {
    echo "❌ Database seeding failed."
    exit 1
}
echo "✅ Default data seeded!"

# 🧩 NEW: Final permission validation
echo "✅ Re-checking file permissions..."
docker compose exec app sh -c "
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/.env || true
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true
    chmod 664 /var/www/html/.env || true
"
