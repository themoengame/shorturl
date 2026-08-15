#!/usr/bin/env bash
set -euo pipefail

cd /home/moen/opencodeproject/shorturl

LOCAL=$(git rev-parse HEAD 2>/dev/null || echo "")
REMOTE=$(git ls-remote origin main 2>/dev/null | awk '{print $1}' || echo "")

if [ -z "$REMOTE" ]; then
  echo "$(date '+%F %T') remote tidak dapat dijangkau, lewati"
  exit 0
fi

if [ "$LOCAL" != "$REMOTE" ]; then
  echo "$(date '+%F %T') perubahan terdeteksi ($LOCAL -> $REMOTE), deploy..."
  git pull origin main
  docker compose up -d --build
  echo "$(date '+%F %T') deploy selesai"
else
  echo "$(date '+%F %T') sudah terbaru, tidak ada aksi"
fi
