# Deploy ke Hosting: /home/dwctagpp/public_html/

## Struktur di Hosting

```
/home/dwctagpp/
+-- laravel/                  <- Upload semua file Laravel (KECUALI folder public/)
|   +-- app/
|   +-- bootstrap/
|   +-- config/
|   +-- database/
|   +-- resources/
|   +-- routes/
|   +-- storage/
|   +-- vendor/
|   +-- .env                  <- Edit untuk production
|   +-- artisan
+-- public_html/              <- Upload isi folder public/
    +-- index.php             <- Yang sudah diubah path-nya
    +-- .htaccess
    +-- build/                <- Hasil npm run build
    +-- upload/               <- storage:link akan buat symlink di sini
```

## Langkah-langkah

### 1. Build di Local
cd bpmpntb-vue
npm run build
composer install --optimize-autoloader --no-dev

### 2. Upload via File Manager / FTP

Folder /home/dwctagpp/laravel/:
Upload semua file dari bpmpntb-vue/ KECUALI: public/, node_modules/, .git/, tests/

Folder /home/dwctagpp/public_html/:
Upload isi dari bpmpntb-vue/public/

### 3. Edit .env di hosting

### 4. Jalankan di Terminal Hosting (SSH)
cd /home/dwctagpp/laravel
php artisan storage:link --relative
php artisan migrate --force
php artisan config:cache
php artisan route:cache
