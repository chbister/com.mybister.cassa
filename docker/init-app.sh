#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/app"
ENV_FILE="${APP_DIR}/.env"

DB_DIR="${APP_DIR}/database"
DB_FILE="${DB_DIR}/database.sqlite"

QZ_DIR="${APP_DIR}/storage/app/private/qz"
QZ_PRIVATE_KEY="${QZ_DIR}/private-key.pem"
QZ_PUBLIC_CERT="${QZ_DIR}/public-cert.pem"
QZ_FINGERPRINT_FILE_SHA1="${QZ_DIR}/fingerprint-sha1.txt"
QZ_FINGERPRINT_FILE_SHA256="${QZ_DIR}/fingerprint-sha256.txt"

echo "[init] Preparing application directories..."

mkdir -p \
  "${APP_DIR}/storage/framework/cache" \
  "${APP_DIR}/storage/framework/sessions" \
  "${APP_DIR}/storage/framework/views" \
  "${APP_DIR}/storage/logs" \
  "${QZ_DIR}" \
  "${APP_DIR}/bootstrap/cache" \
  "${DB_DIR}"

if [ ! -f "${ENV_FILE}" ]; then
  echo "[init] Creating .env from .env.example ..."
  cp "${APP_DIR}/.env.example" "${ENV_FILE}"
fi

if ! grep -Eq '^APP_KEY=base64:' "${ENV_FILE}"; then
  echo "[init] Generating APP_KEY ..."
  php artisan key:generate --force
else
  echo "[init] APP_KEY already present."
fi

if [ ! -f "${DB_FILE}" ]; then
  echo "[init] Creating SQLite database file ..."
  touch "${DB_FILE}"
else
  echo "[init] SQLite database already exists."
fi

if grep -q '^DB_CONNECTION=' "${ENV_FILE}"; then
  sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' "${ENV_FILE}"
else
  printf '\nDB_CONNECTION=sqlite\n' >> "${ENV_FILE}"
fi

if grep -q '^DB_DATABASE=' "${ENV_FILE}"; then
  sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_FILE}|" "${ENV_FILE}"
else
  printf 'DB_DATABASE=%s\n' "${DB_FILE}" >> "${ENV_FILE}"
fi

if [ ! -f "${QZ_PRIVATE_KEY}" ] || [ ! -f "${QZ_PUBLIC_CERT}" ]; then
  echo "[init] Generating QZ certificate pair ..."
  openssl req -x509 -newkey rsa:2048 -sha256 -nodes \
    -keyout "${QZ_PRIVATE_KEY}" \
    -out "${QZ_PUBLIC_CERT}" \
    -days 3650 \
    -subj "/C=DE/O=Cassa/CN=localhost"
else
  echo "[init] QZ certificate pair already exists."
fi

echo "[init] Writing certificate fingerprint ..."
openssl x509 -in "${QZ_PUBLIC_CERT}" -noout -fingerprint -sha1 > "${QZ_FINGERPRINT_FILE_SHA1}"
openssl x509 -in "${QZ_PUBLIC_CERT}" -noout -fingerprint -sha256 > "${QZ_FINGERPRINT_FILE_SHA256}"

echo "[init] Fixing permissions ..."
chown -R application:application \
  "${APP_DIR}/storage" \
  "${APP_DIR}/bootstrap/cache" \
  "${DB_DIR}"

echo "[init] Running database migrations ..."
php artisan migrate --force || true

echo "[init] Clearing Laravel caches ..."
php artisan optimize:clear || true

echo "[init] Initialization finished."
