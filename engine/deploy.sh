#!/bin/bash
# =============================================================
# Deploy Script untuk Hosting dengan struktur engine/
# 
# Struktur yang dihasilkan:
#   public_html/           ← document root
#   ├── .htaccess
#   ├── index.php          ← dari index_hosting.php
#   ├── build/             ← Vite build
#   ├── upload/            ← user uploads
#   ├── fonts/
#   ├── favicon.ico
#   ├── robots.txt
#   ├── *.png, *.jpg
#   └── engine/            ← semua file Laravel
#       ├── app/
#       ├── bootstrap/
#       ├── storage/
#       ├── vendor/
#       └── .env
#
# Cara pakai:
#   1. Upload seluruh project ke hosting (misal ke /home/user/bpmpntb-vue/)
#   2. Jalankan: bash deploy.sh /home/user/public_html
# =============================================================

set -e

# Document root target
PUBLIC_HTML="${1:-public_html}"

echo "=== Deploy BPMP NTB ==="
echo "Target: $PUBLIC_HTML"

# Buat struktur folder
mkdir -p "$PUBLIC_HTML/engine"
mkdir -p "$PUBLIC_HTML/upload"
mkdir -p "$PUBLIC_HTML/build"
mkdir -p "$PUBLIC_HTML/fonts"
mkdir -p "$PUBLIC_HTML/pwa"

# Copy Laravel app ke engine/
echo ">> Copy Laravel files to engine/..."
rsync -av --exclude='public/' \
    --exclude='node_modules/' \
    --exclude='.git/' \
    --exclude='*.zip' \
    --exclude='*.pdf' \
    --exclude='*.png' \
    --exclude='deploy.sh' \
    ./ "$PUBLIC_HTML/engine/"

# Copy public files ke document root
echo ">> Copy public files..."
cp public/.htaccess "$PUBLIC_HTML/.htaccess"
cp public/index_hosting.php "$PUBLIC_HTML/index.php"
cp public/favicon.ico "$PUBLIC_HTML/favicon.ico" 2>/dev/null || true
cp public/robots.txt "$PUBLIC_HTML/robots.txt" 2>/dev/null || true
cp public/manifest.json "$PUBLIC_HTML/manifest.json" 2>/dev/null || true
cp public/sw.js "$PUBLIC_HTML/sw.js" 2>/dev/null || true
cp public/offline.html "$PUBLIC_HTML/offline.html" 2>/dev/null || true

# Copy build assets
echo ">> Copy build assets..."
cp -r public/build/* "$PUBLIC_HTML/build/" 2>/dev/null || true

# Copy fonts
echo ">> Copy fonts..."
cp -r public/fonts/* "$PUBLIC_HTML/fonts/" 2>/dev/null || true

# Copy PWA files
echo ">> Copy PWA files..."
cp -r public/pwa/* "$PUBLIC_HTML/pwa/" 2>/dev/null || true

# Copy static images
echo ">> Copy static images..."
for f in public/*.png public/*.jpg public/*.jpeg public/*.webp public/*.svg; do
    [ -f "$f" ] && cp "$f" "$PUBLIC_HTML/" 2>/dev/null || true
done

# Copy existing uploads (jika ada)
if [ -d "public/upload" ]; then
    echo ">> Copy existing uploads..."
    cp -r public/upload/* "$PUBLIC_HTML/upload/" 2>/dev/null || true
fi

# Set permissions
echo ">> Set permissions..."
chmod -R 755 "$PUBLIC_HTML/engine/storage" 2>/dev/null || true
chmod -R 755 "$PUBLIC_HTML/engine/bootstrap/cache" 2>/dev/null || true
chmod -R 755 "$PUBLIC_HTML/upload" 2>/dev/null || true

# Create storage symlink (relative)
echo ">> Create storage symlink..."
cd "$PUBLIC_HTML/engine"
php artisan storage:link --force 2>/dev/null || true

# Create symlink dari engine/storage/app/public ke upload di document root
# Ini agar file yang diupload via storage bisa diakses
ln -sf "../../upload" storage/app/public 2>/dev/null || true

echo ""
echo "=== Deploy selesai! ==="
echo ""
echo "Langkah selanjutnya:"
echo "  1. Edit $PUBLIC_HTML/engine/.env"
echo "     - APP_URL=https://domain-anda.com"
echo "     - SESSION_DOMAIN=.domain-anda.com"
echo "     - SANCTUM_STATEFUL_DOMAINS=domain-anda.com"
echo "     - CORS_ALLOWED_ORIGINS=https://domain-anda.com"
echo "  2. cd $PUBLIC_HTML/engine && php artisan config:clear"
echo "  3. cd $PUBLIC_HTML/engine && php artisan migrate --force"
