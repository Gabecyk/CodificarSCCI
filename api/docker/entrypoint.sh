#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

set_env() {
    key="$1"
    value="$2"
    if grep -q "^${key}=" .env; then
        sed -i "s|^${key}=.*|${key}=${value}|" .env
    else
        echo "${key}=${value}" >> .env
    fi
}

# O .env é montado do host (bind mount) e pode conter valores de outro
# ambiente (ex.: DB_HOST=127.0.0.1 de um setup local). Garantimos aqui que
# ele sempre reflita o que o docker-compose configurou para este container.
set_env "APP_ENV" "${APP_ENV:-local}"
set_env "APP_DEBUG" "${APP_DEBUG:-true}"
set_env "APP_URL" "${APP_URL:-http://localhost:8000}"
set_env "DB_CONNECTION" "${DB_CONNECTION:-pgsql}"
set_env "DB_HOST" "${DB_HOST:-db}"
set_env "DB_PORT" "${DB_PORT:-5432}"
set_env "DB_DATABASE" "${DB_DATABASE:-codificar}"
set_env "DB_USERNAME" "${DB_USERNAME:-postgres}"
set_env "DB_PASSWORD" "${DB_PASSWORD:-root}"

if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

php artisan migrate --force

exec "$@"
