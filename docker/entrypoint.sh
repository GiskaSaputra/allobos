#!/bin/sh
# Peran wadah: app (server web, menjalankan migrasi), queue, atau scheduler.
set -e

ROLE="${CONTAINER_ROLE:-app}"

echo "Menunggu basis data ${DB_HOST}:${DB_PORT:-3306}"
until php -r 'try { new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: 3306), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); exit(0); } catch (Throwable $e) { exit(1); }'; do
    sleep 2
done

if [ "$ROLE" = "app" ]; then
    if [ -z "$APP_KEY" ]; then
        echo "APP_KEY kosong. Isi APP_KEY di .env (php artisan key:generate --show)." >&2
        exit 1
    fi
    php artisan storage:link --force >/dev/null 2>&1 || true
    php artisan migrate --force
    php artisan db:seed --force
    if [ "$SEED_DEMO" = "true" ]; then
        php artisan allobos:demo --jika-kosong
    fi
    php artisan optimize
else
    # Tunggu wadah app selesai migrasi sebelum memproses antrean atau jadwal.
    until php artisan migrate:status >/dev/null 2>&1; do sleep 3; done
    php artisan optimize
fi

case "$ROLE" in
    queue) exec su -s /bin/sh www-data -c "php artisan queue:work --sleep=2 --tries=3 --max-time=3600" ;;
    scheduler) exec su -s /bin/sh www-data -c "php artisan schedule:work" ;;
    *) chown -R www-data:www-data storage bootstrap/cache; exec "$@" ;;
esac
