#!/usr/bin/env bash

set -euo pipefail

IMAGE="ghcr.io/chbister/com.mybister.cassa:latest"
CONTAINER_NAME="cassa"
PORT="${PORT:-8000}"

DESKTOP_DIR="$(xdg-user-dir DESKTOP)"
pushd "${DESKTOP_DIR}"

BASE_DIR="$(pwd)"
DB_DIR="${BASE_DIR}/var/database"
DB_FILE="${DB_DIR}/database.sqlite"
QZ_DIR="${BASE_DIR}/var/storage/app/private/qz"

log() {
	printf "\n[%s] %s\n" "$(date +%H:%M:%S)" "$1"
}

log "Pulling latest image..."
podman pull "${IMAGE}"

if podman container exists "${CONTAINER_NAME}"; then
	log "Stopping existing container..."
	podman stop "${CONTAINER_NAME}" || true
	log "Removing existing container..."
	podman rm "${CONTAINER_NAME}" || true
fi

log "Preparing local directories..."

mkdir -p "${DB_DIR}"
if [ ! -f "${DB_FILE}" ]; then
	log "Creating SQLite database file..."
	touch "${DB_FILE}"
fi
mkdir -p "${QZ_DIR}"

log "Starting new container..."

podman run -d \
	--name "${CONTAINER_NAME}" \
	-p "${PORT}:80" \
	-v "${DB_FILE}:/app/database/database.sqlite:Z" \
	-v "${QZ_DIR}:/app/storage/app/private/qz:Z" \
	"${IMAGE}"

log "Container started."

popd

cat <<EOF

Application available at:
	http://localhost:${PORT}

EOF

