# =============================================================
# Deploy Script untuk Hosting dengan struktur engine/ (Windows)
#
# Struktur yang dihasilkan:
#   public_html/           ← document root
#   ├── .htaccess
#   ├── index.php          ← dari index_hosting.php
#   ├── build/
#   ├── upload/
#   └── engine/            ← semua file Laravel
#       ├── app/
#       ├── bootstrap/
#       ├── storage/
#       └── .env
#
# Cara pakai:
#   .\deploy.ps1 -Target "C:\path\to\public_html"
# =============================================================

param(
    [string]$Target = "public_html"
)

$ErrorActionPreference = "Stop"
$Source = Split-Path -Parent $MyInvocation.MyCommand.Path

Write-Host "=== Deploy BPMP NTB ===" -ForegroundColor Cyan
Write-Host "Source: $Source"
Write-Host "Target: $Target"

# Buat struktur folder
$dirs = @(
    "$Target\engine",
    "$Target\upload",
    "$Target\build",
    "$Target\fonts",
    "$Target\pwa"
)
foreach ($d in $dirs) {
    if (!(Test-Path $d)) { New-Item -ItemType Directory -Path $d -Force | Out-Null }
}

# Copy Laravel app ke engine/
Write-Host ">> Copy Laravel files to engine/..." -ForegroundColor Yellow
$excludeDirs = @("public", "node_modules", ".git")
$excludeFiles = @("*.zip", "*.pdf", "deploy.sh", "deploy.ps1")

# Copy semua kecuali folder tertentu
Get-ChildItem -Path $Source -Exclude $excludeDirs | ForEach-Object {
    $dest = Join-Path "$Target\engine" $_.Name
    if ($_.PSIsContainer) {
        if ($_.Name -notin $excludeDirs) {
            Copy-Item -Path $_.FullName -Destination $dest -Recurse -Force
        }
    } else {
        $skip = $false
        foreach ($pat in $excludeFiles) {
            if ($_.Name -like $pat) { $skip = $true; break }
        }
        if (!$skip) {
            Copy-Item -Path $_.FullName -Destination $dest -Force
        }
    }
}

# Copy public files ke document root
Write-Host ">> Copy public files..." -ForegroundColor Yellow
$publicFiles = @(
    ".htaccess",
    "favicon.ico",
    "robots.txt",
    "manifest.json",
    "sw.js",
    "offline.html"
)
foreach ($f in $publicFiles) {
    $src = Join-Path "$Source\public" $f
    if (Test-Path $src) {
        Copy-Item $src "$Target\$f" -Force
    }
}

# Rename index_hosting.php ke index.php
Copy-Item "$Source\public\index_hosting.php" "$Target\index.php" -Force

# Copy build assets
Write-Host ">> Copy build assets..." -ForegroundColor Yellow
if (Test-Path "$Source\public\build") {
    Copy-Item "$Source\public\build\*" "$Target\build\" -Recurse -Force
}

# Copy fonts
if (Test-Path "$Source\public\fonts") {
    Copy-Item "$Source\public\fonts\*" "$Target\fonts\" -Recurse -Force
}

# Copy PWA files
if (Test-Path "$Source\public\pwa") {
    Copy-Item "$Source\public\pwa\*" "$Target\pwa\" -Recurse -Force
}

# Copy static images (*.png, *.jpg, dll)
Write-Host ">> Copy static images..." -ForegroundColor Yellow
Get-ChildItem "$Source\public\*" -Include *.png,*.jpg,*.jpeg,*.webp,*.svg | ForEach-Object {
    Copy-Item $_.FullName "$Target\" -Force
}

# Copy existing uploads
if (Test-Path "$Source\public\upload") {
    Write-Host ">> Copy existing uploads..." -ForegroundColor Yellow
    Copy-Item "$Source\public\upload\*" "$Target\upload\" -Recurse -Force
}

Write-Host ""
Write-Host "=== Deploy selesai! ===" -ForegroundColor Green
Write-Host ""
Write-Host "Langkah selanjutnya:" -ForegroundColor Cyan
Write-Host "  1. Edit $Target\engine\.env"
Write-Host "     - APP_URL=https://domain-anda.com"
Write-Host "     - SESSION_DOMAIN=.domain-anda.com"
Write-Host "     - CORS_ALLOWED_ORIGINS=https://domain-anda.com"
Write-Host "  2. Jalankan: cd $Target\engine; php artisan config:clear"
Write-Host "  3. Jalankan: cd $Target\engine; php artisan migrate --force"
