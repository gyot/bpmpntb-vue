# BPMP Provinsi NTB - Website

> Website resmi Balai Penjaminan Mutu Pendidikan Provinsi Nusa Tenggara Barat

**Stack:** Laravel 10 + Vue 3 SPA + Tailwind CSS + MySQL
**Domain:** https://bpmpntb.kemendikdasmen.go.id
**Repository:** https://github.com/gyot/bpmpntb-vue

---

## Daftar Isi

1. [Ikhtisar Proyek](#1-ikhtisar-proyek)
2. [Teknologi](#2-teknologi)
3. [Persyaratan Sistem](#3-persyaratan-sistem)
4. [Instalasi](#4-instalasi)
5. [Konfigurasi](#5-konfigurasi)
6. [Struktur Direktori](#6-struktur-direktori)
7. [Arsitektur Aplikasi](#7-arsitektur-aplikasi)
8. [Database](#8-database)
9. [API Reference](#9-api-reference)
10. [Autentikasi & Otorisasi](#10-autentikasi--otorisasi)
11. [Fitur Utama](#11-fitur-utama)
12. [Pengembangan Frontend](#12-pengembangan-frontend)
13. [Pengembangan Backend](#13-pengembangan-backend)
14. [Deployment](#14-deployment)
15. [Testing](#15-testing)
16. [Troubleshooting](#16-troubleshooting)
17. [Kontribusi](#17-kontribusi)

---

## 1. Ikhtisar Proyek

BPMP Provinsi NTB Website adalah aplikasi web yang menyediakan:

- **CMS** - 9 jenis konten (berita, artikel, buletin, jurnal, kliping, pengumuman, galeri, unduhan, profil)
- **Chatbot AI Si Intan** - Asisten virtual berbasis AI dengan RAG knowledge base, live chat, WhatsApp gateway
- **PPID** - Pejabat Pengelola Informasi dan Dokumentasi
- **Dashboard Admin** - Statistik postingan, visitor, chart interaktif
- **Aksesibilitas** - Widget 16 fitur untuk kelompok rentan
- **Tematisasi Dinamis** - Warna tema bisa diubah dari admin panel

---

## 2. Teknologi

### Backend
| Teknologi | Versi | Fungsi |
|-----------|-------|--------|
| PHP | >= 8.1 | Bahasa pemrograman |
| Laravel | 10.10 | Framework web |
| MySQL | 8.0+ | Database |
| Laravel Sanctum | 3.3 | Autentikasi API |
| Guzzle | 7.2+ | HTTP client |
| DomPDF | 3.1 | Generate PDF |
| Smalot PDFParser | latest | Parse PDF (RAG) |
| PHPWord | 1.4 | Generate DOCX |
| Intervention Image | 3.11 | Manipulasi gambar |

### Frontend
| Teknologi | Versi | Fungsi |
|-----------|-------|--------|
| Vue.js | 3.5 | Framework SPA |
| Vue Router | 4.6 | Client-side routing |
| Axios | 1.18 | HTTP client |
| Tailwind CSS | 3.4 | Utility-first CSS |
| SweetAlert2 | 11.26 | Notifikasi & dialog |
| Chart.js | (via CDN) | Grafik statistik |
| Vite | 5.0 | Build tool & dev server |

### Infrastruktur
| Komponen | Detail |
|----------|--------|
| Web Server | Apache (XAMPP local) / Shared hosting |
| PHP Version | 8.1+ |
| Node.js | 18+ (untuk build) |
| Composer | 2.x |

---

## 3. Persyaratan Sistem

PHP >= 8.1 dengan ekstensi: openssl, pdo, mbstring, tokenizer, xml, curl, zip, gd, bcmath
Composer >= 2.0
Node.js >= 18
NPM >= 9
MySQL >= 8.0

---

## 4. Instalasi

### 4.1 Clone Repository
git clone https://github.com/gyot/bpmpntb-vue.git
cd bpmpntb-vue

### 4.2 Install Dependencies
composer install
npm install

### 4.3 Setup Environment
cp .env.example .env
php artisan key:generate

Edit .env sesuai konfigurasi lokal (database, URL, Sanctum domains).

### 4.4 Setup Database
php artisan migrate
php artisan storage:link

### 4.5 Build dan Run
npm run build
php artisan serve

Buka http://localhost:8000

---

## 5. Konfigurasi

### 5.1 Environment Variables

| Variable | Deskripsi | Contoh |
|----------|-----------|--------|
| APP_NAME | Nama aplikasi | BPMP Provinsi NTB |
| APP_ENV | Environment | local / production |
| APP_DEBUG | Debug mode | true / false |
| APP_URL | Base URL | http://localhost:8000 |
| DB_DATABASE | Nama database | bpmpntb |
| SANCTUM_STATEFUL_DOMAINS | Domain Sanctum | localhost:8000,localhost |

### 5.2 Tema Website

Tema diatur dari database tabel settings sebagai CSS custom properties.
Ubah tema: Admin Panel > Pengaturan > Tema Website

### 5.3 Konfigurasi AI Chatbot

Diatur dari admin panel: Si Intan > Konfigurasi AI.
Mendukung provider OpenAI-compatible dengan model chat dan embedding.

---

## 6. Struktur Direktori

`
bpmpntb-vue/
├── app/
│   ├── Console/Kernel.php
│   ├── Exceptions/Handler.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/              # 10 controller API
│   │   │   ├── AiSettingsController.php
│   │   │   ├── IntentController.php
│   │   │   ├── KnowledgeBaseController.php
│   │   │   └── PdfController.php
│   │   ├── Middleware/
│   │   │   ├── AdminOnly.php     # Middleware role admin
│   │   │   ├── SecurityHeaders.php
│   │   │   └── VisitorLogger.php
│   │   └── Kernel.php
│   ├── Models/                   # 40 model files
│   ├── Providers/
│   └── Services/
│       └── AIService.php         # Service AI chatbot
├── config/                       # Konfigurasi Laravel
├── database/
│   ├── migrations/               # 21 migration files
│   └── seeders/
├── public/
│   ├── build/                    # Hasil vite build
│   └── upload/                   # File upload
├── resources/
│   ├── css/app.css               # Design system & tema
│   ├── js/
│   │   ├── app.js                # Entry point Vue
│   │   ├── bootstrap.js          # Axios setup
│   │   ├── theme.js              # Theme loader
│   │   ├── swal.js               # SweetAlert helpers
│   │   ├── components/           # 6 reusable components
│   │   ├── layouts/
│   │   │   ├── AdminLayout.vue   # Layout admin panel
│   │   │   └── PublicLayout.vue  # Layout website publik
│   │   ├── pages/
│   │   │   ├── admin/            # 18 halaman admin
│   │   │   └── 6 halaman publik
│   │   └── router/index.js       # Vue Router config
│   └── views/app.blade.php       # SPA entry
├── routes/
│   ├── api.php                   # API routes
│   └── web.php                   # Web routes
├── .env.example
├── .env.production.example
├── composer.json
├── package.json
├── vite.config.js
├── tailwind.config.js
├── DEPLOY.md                     # Panduan deployment
└── README.md                     # Dokumentasi ini
`

---

## 7. Arsitektur Aplikasi

### 7.1 Alur Request

Browser > Vue SPA > Axios (baseURL: /api) > Laravel Routes > Middleware > Controller > Model > MySQL

### 7.2 Alur Autentikasi

1. User input email + password
2. Vue POST /api/login (AuthController)
3. Laravel validasi, buat Sanctum token
4. Return token + user data
5. Vue simpan token di localStorage
6. Setiap request: Authorization: Bearer {token}
7. Middleware auth:sanctum validasi
8. Middleware admin cek role

### 7.3 Pola Pengembangan

- Controller langsung query Model (tanpa Repository/Service layer)
- Model: Eloquent ORM dengan relasi
- Vue: Composition API dengan ref/reactive untuk state lokal
- API: JSON response tanpa API Resource class
- Tidak ada Pinia/Vuex (state management lokal per komponen)

---

## 8. Database

### 8.1 Tabel Utama

| No | Tabel | Fungsi |
|----|-------|--------|
| 1 | users | Pengguna sistem |
| 2 | settings | Konfigurasi website |
| 3 | sliders | Slider/hero banner |
| 4 | externallink | Link eksternal |
| 5 | visitors | Log pengunjung |
| 6-14 | kategori_* | Kategori per jenis konten (9 tabel) |
| 15-23 | berita, artikel, buletin, jurnal, kliping, pengumuman, galeri, unduhan, profil | Konten (9 tabel) |
| 24 | layanans | Layanan BPMP |
| 25 | user_menu_access | Akses menu per user |
| 26 | chatbot_responses | Respon keyword chatbot |
| 27 | chatbot_intent | Intent chatbot |
| 28 | chatbot_user | User chatbot |
| 29 | chatbot_conversation_logs | Log percakapan |
| 30 | chatbot_settings | Pengaturan chatbot |
| 31 | ai_configurations | Konfigurasi AI |
| 32-34 | knowledge_categories, knowledge_documents, knowledge_chunks | Knowledge Base RAG |
| 35-39 | ppid_profiles, ppid_informations, ppid_standards, ppid_regulations, ppid_external_links | Modul PPID |
| 40 | personal_access_tokens | Sanctum tokens |

### 8.2 Relasi Penting

- konten.belongsTo(kategori) via id_kategori
- knowledge_documents.belongsTo(knowledge_categories)
- knowledge_chunks.belongsTo(knowledge_documents)
- user_menu_access.belongsTo(users)
- chatbot_conversation_logs.belongsTo(chatbot_user)

---

## 9. API Reference

### 9.1 Public API

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| GET | /api/beranda | Data halaman utama |
| GET | /api/settings | Pengaturan website |
| GET | /api/theme | Konfigurasi tema |
| GET | /api/visitor-stats | Statistik visitor |
| GET | /api/sliders-public | Slider publik |
| GET | /api/external-links | Link eksternal |
| GET | /api/posts-front/{jenis} | List konten publik |
| GET | /api/posts-front/{jenis}/{id} | Detail konten |
| GET | /api/layanans-public | Layanan publik |
| GET | /api/ppid | Data PPID publik |
| POST | /api/login | Login (throttle: 5/menit) |
| POST | /api/logout | Logout |

### 9.2 Admin API (auth:sanctum + admin)

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| GET | /api/dashboard-stats | Statistik dashboard |
| CRUD | /api/sliders | Kelola slider |
| GET/POST | /api/settings-admin | Kelola pengaturan |
| CRUD | /api/users | Kelola user |
| GET/PUT | /api/users/{id}/menu-access | Menu access user |
| GET | /api/my-menu-access | Menu access user login |
| CRUD | /api/posts/{jenis} | Kelola konten |
| CRUD | /api/kategori/{jenis} | Kelola kategori |
| CRUD | /api/layanans | Kelola layanan |
| CRUD | /api/external-links | Kelola link eksternal |
| CRUD | /api/ppid/* | Kelola PPID |

### 9.3 Chatbot API

**Public (session-based):**
| Method | Endpoint | Fungsi |
|--------|----------|--------|
| POST | /api/chatbot/respond-stream | Chat streaming (SSE) |
| GET | /api/chatbot/check_identity | Cek identitas user |
| POST | /api/chatbot/save_identity | Simpan identitas |
| POST | /api/chatbot/start_live | Mulai live chat |

**Admin (auth:sanctum + admin):**
| Method | Endpoint | Fungsi |
|--------|----------|--------|
| GET | /api/chatbot/admin/analytics | Statistik chatbot |
| GET | /api/chatbot/admin/sessions | Sesi live chat |
| GET | /api/chatbot/admin/knowledge-base/* | Knowledge Base RAG |
| GET | /api/chatbot/admin/intent | Kelola intent |
| GET | /api/chatbot/admin/ai-settings | Konfigurasi AI |

---

## 10. Autentikasi dan Otorisasi

### 10.1 Sistem Autentikasi

- **Library:** Laravel Sanctum (token-based)
- **Token Storage:** localStorage (browser)
- **Header:** Authorization: Bearer {token}
- **CSRF:** Sanctum CSRF cookie untuk SPA

### 10.2 Role & Permission

| Role | Akses |
|------|-------|
| user | Hanya menu yang diizinkan (user_menu_access) |
| admin | Semua menu admin |
| superadmin | Semua menu + dilindungi dari edit/hapus |

### 10.3 Middleware

| Middleware | Fungsi |
|-----------|--------|
| auth:sanctum | Validasi token Sanctum |
| admin (AdminOnly) | Cek role admin/superadmin |
| SecurityHeaders | Tambah security headers |
| VisitorLogger | Log pengunjung ke tabel visitors |

---

## 11. Fitur Utama

### 11.1 Content Management System

- 9 jenis konten dengan kategori masing-masing
- Upload gambar, file, dan thumbnail
- Editor Quill (rich text)
- Status publish/draft
- Viewer counter
- Generate PDF per konten

### 11.2 Chatbot AI Si Intan

- **Chat Streaming** - Respons real-time via SSE (Server-Sent Events)
- **RAG Knowledge Base** - Upload dokumen PDF, chunking otomatis, embedding
- **Live Chat** - User bisa chat langsung dengan admin
- **Intent Detection** - Deteksi intent dari pesan user
- **Keyword Match** - Respon berdasarkan keyword
- **Analytics** - Statistik penggunaan, token, kuota per user
- **WhatsApp Gateway** - Integrasi WhatsApp

### 11.3 Dashboard Admin

- Statistik konten per kategori
- Grafik postingan per bulan (Chart.js)
- Statistik visitor
- Dropdown tahun untuk filter
- Copy tabel/grafik ke clipboard (untuk Word)

### 11.4 Aksesibilitas

Widget floating dengan 16 fitur:
- Ukuran teks (4 level)
- Kontras (normal/tinggi/terbalik)
- Jarak baris, huruf, kata
- Font disleksia
- Kursor besar, tombol besar
- Sorot link, fokus keyboard
- Mode sederhana, hemat data
- Hentikan animasi
- Text-to-Speech (hover-to-read)
- Tingkat bacaan mudah
- Keyboard shortcut Alt+U

---

## 12. Pengembangan Frontend

### 12.1 Struktur Vue

`
resources/js/
├── app.js              # Entry point, mount Vue app
├── bootstrap.js        # Axios instance + interceptors
├── theme.js            # Load tema dari API
├── swal.js             # SweetAlert2 helpers
├── App.vue             # Root: <router-view />
├── components/         # Komponen reusable
├── layouts/            # AdminLayout, PublicLayout
├── pages/              # Halaman (route-level components)
└── router/index.js     # Vue Router config
`

### 12.2 Conventions

- **Composition API** - Semua komponen menggunakan <script setup>
- **State Management** - ref() dan reaktif() per komponen (tidak ada Pinia)
- **API Calls** - Axios via bootstrap.js dengan baseURL '/api'
- **Styling** - Tailwind CSS + CSS custom properties untuk tema
- **Notifications** - swalSuccess(), swalError(), swalConfirm() dari swal.js
- **Naming** - PascalCase untuk komponen, camelCase untuk fungsi/variabel

### 12.3 Menambah Halaman Baru

1. Buat file di 
esources/js/pages/admin/YourPage.vue
2. Tambah route di 
esources/js/router/index.js
3. Tambah menu di 
esources/js/layouts/AdminLayout.vue (allMenuGroups)
4. Tambah key di UserController@menus (allMenus array)

### 12.4 Menambah Komponen

1. Buat file di 
esources/js/components/YourComponent.vue
2. Import di halaman yang membutuhkan
3. Gunakan <script setup> untuk Composition API

---

## 13. Pengembangan Backend

### 13.1 Menambah Fitur Baru

1. **Migration:** php artisan make:migration create_xxx_table
2. **Model:** php artisan make:model Xxx
3. **Controller:** php artisan make:controller Api/XxxController --api
4. **Route:** Tambah di 
outes/api.php
5. **Middleware:** Gunakan uth:sanctum dan dmin untuk rute admin

### 13.2 Conventions

- Controller API di pp/Http/Controllers/Api/
- Validasi inline di controller (tanpa Form Request)
- Response: 
esponse()->json([...])
- Model di pp/Models/
- Foreign key: oreignId()->constrained()

### 13.3 Middleware Custom

| File | Fungsi |
|------|--------|
| AdminOnly.php | Cek role admin/superadmin |
| SecurityHeaders.php | Tambah HSTS, CSP, X-Frame-Options, dll |
| VisitorLogger.php | Log visitor ke tabel visitors (dedup 10 menit) |

---

## 14. Deployment

### 14.1 Build Production

`ash
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
`

### 14.2 Shared Hosting (cPanel)

Struktur direktori di hosting:

`
/home/user/
├── laravel/          # Semua file kecuali public/
│   ├── app/
│   ├── config/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   └── .env
└── public_html/      # Isi folder public/
    ├── index.php     # Diubah path-nya ke ../laravel/
    ├── .htaccess
    └── build/        # Hasil npm run build
`

Ubah public/index.php:
- 
equire __DIR__.'/../vendor/autoload.php' menjadi 
equire __DIR__.'/../laravel/vendor/autoload.php'
- 
equire_once __DIR__.'/../bootstrap/app.php' menjadi 
equire_once __DIR__.'/../laravel/bootstrap/app.php'

### 14.3 Perintah di Hosting (SSH)

`ash
cd /home/user/laravel
php artisan storage:link --relative
php artisan migrate --force
php artisan config:cache
php artisan route:cache
`

### 14.4 Permission

`
/storage          -> 755 (recursive)
/bootstrap/cache  -> 755 (recursive)
`

---

## 15. Testing

### 15.1 Menjalankan Test

`ash
php artisan test
`

### 15.2 Manual Testing Checklist

- [ ] Login/logout berfungsi
- [ ] CRUD konten (berita, artikel, dll)
- [ ] Upload gambar dan file
- [ ] Slider reorder
- [ ] Chatbot respond streaming
- [ ] Knowledge Base upload PDF
- [ ] Live chat admin-user
- [ ] Dashboard statistik
- [ ] Export PDF/DOCX
- [ ] Tema berubah dari admin
- [ ] Aksesibilitas widget
- [ ] Responsive di mobile

---

## 16. Troubleshooting

### Umum

| Masalah | Solusi |
|---------|--------|
| 419 Page Expired | Jalankan php artisan session:table && php artisan migrate atau cek CSRF token |
| 403 Forbidden | Cek role user di database, pastikan 'admin' |
| 401 Unauthorized | Cek token di localStorage, login ulang |
| Vite manifest not found | Jalankan 
pm run build |
| Storage link error | php artisan storage:link atau buat manual symlink |
| PDF parser not found | composer require smalot/pdfparser lalu composer dump-autoload |
| CORS error | Cek config/cors.php, tambahkan domain ke allowed_origins |

### Chatbot

| Masalah | Solusi |
|---------|--------|
| AI tidak merespon | Cek konfigurasi AI di admin (api_key, model, base_url) |
| Knowledge Base kosong | Upload dokumen lalu Generate Embeddings |
| Live chat tidak jalan | Pastikan session driver = file/database |

### Database

| Masalah | Solusi |
|---------|--------|
| Migration error | php artisan migrate:fresh --seed (WARNING: hapus data) |
| Table already exists | php artisan migrate:status cek status migrasi |
| Foreign key constraint | Pastikan tabel parent dibuat duluan |

---

## 17. Kontribusi

### Alur Kontribusi

1. Fork repository
2. Buat branch fitur baru: git checkout -b fitur/nama-fitur
3. Commit dengan pesan jelas: git commit -m "feat: tambah fitur X"
4. Push ke fork: git push origin fitur/nama-fitur
5. Buat Pull Request ke branch main

### Commit Convention

`
feat:     Fitur baru
fix:      Perbaikan bug
docs:     Dokumentasi
style:    Formatting, missing semi colons, dll
refactor: Refactoring code
test:     Menambah test
chore:    Maintenance, dependencies
`

### Code Style

- **PHP:** PSR-12, gunakan Laravel Pint untuk formatting
- **Vue:** Composition API, <script setup>
- **CSS:** Tailwind CSS + custom properties
- **JavaScript:** camelCase untuk variabel/fungsi, PascalCase untuk komponen

### Review Checklist

- [ ] Code berfungsi dengan benar
- [ ] Tidak ada error di console
- [ ] Responsive di mobile
- [ ] Tidak ada data sensitif di commit (.env, api_key)
- [ ] Mengikuti conventions yang ada

---

## License

Proprietary - BPMP Provinsi NTB

---

## Kontak

**BPMP Provinsi Nusa Tenggara Barat**
Jl. Pendidikan No. 1, Mataram, NTB 83127
Website: https://bpmpntb.kemendikdasmen.go.id
