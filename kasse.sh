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
QZ_PUBLIC_CERT="${QZ_DIR}/public-cert.pem"
QZ_TRAY_JAVA="/opt/qz-tray/runtime/bin/java"
QZ_TRAY_JAR="/opt/qz-tray/qz-tray.jar"

log() {
	printf "\n[%s] %s\n" "$(date +%H:%M:%S)" "$1"
}

wait_for_qz_cert() {
	log "Waiting for QZ certificate..."
	for i in {1..30}; do
		if [ -s "${QZ_PUBLIC_CERT}" ]; then
			log "QZ certificate found."
			return 0
		fi
		sleep 1
	done

	log "QZ certificate was not created in time."
	return 1
}

start_qz_tray() {
	if [ ! -x "${QZ_TRAY_JAVA}" ] || [ ! -f "${QZ_TRAY_JAR}" ]; then
		log "QZ Tray is not installed under /opt/qz-tray, skipping start."
		return 0
	fi

	log "Starting QZ Tray..."
	nohup "${QZ_TRAY_JAVA}" -jar "${QZ_TRAY_JAR}" \
		--steal \
		--headless \
		--allow "${QZ_PUBLIC_CERT}" \
		>/tmp/qz-tray.log 2>&1 &
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
	-e FORCE_HTTPS=false \
	-v "${DB_FILE}:/app/database/database.sqlite:Z" \
	-v "${QZ_DIR}:/app/storage/app/private/qz:Z" \
	"${IMAGE}"

log "Container started."

wait_for_qz_cert
start_qz_tray

popd

cat <<EOF

Application available at:
	http://localhost:${PORT}

EOF

