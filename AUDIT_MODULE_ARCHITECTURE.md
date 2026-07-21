# WEBSITE AUDIT MODULE — System Architecture & Implementation Plan
## BPMP Provinsi NTB — Laravel 10 + Vue 3 SPA

---

## 1. ANALISIS SISTEM

### 1.1 Kondisi Existing
- Laravel 10.10 (bukan 11, perlu upgrade jika ingin Laravel 11)
- Vue 3.5 Composition API (tanpa Pinia, tanpa state management terpusat)
- Auth: Sanctum + custom admin middleware
- Tidak ada Repository Pattern, Form Request, API Resource, Policy
- Flat structure: semua model di Models/, semua controller di Api/
- Tailwind CSS 3.4 + Font Awesome 6

### 1.2 Tantangan
- Menambahkan modul kompleks ke codebase yang belum menggunakan clean architecture
- Perlu install Pinia untuk state management audit module
- Perlu queue system untuk audit async (database driver)
- Perlu HTTP client untuk crawling website target

### 1.3 Keputusan Arsitektur
- Gunakan Laravel 10 (existing) — jangan upgrade dulu, cukup modular
- Install Pinia untuk state management audit module
- Gunakan Repository + Service Layer HANYA untuk module audit (tidak refactor existing)
- Gunakan Laravel Queue (database driver) untuk audit async
- Gunakan Guzzle HTTP untuk crawling website
- Buat modul terpisah di folder terstruktur

---

## 2. DESAIN ARSITEKTUR

### 2.1 Backend Architecture (Laravel)

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── Audit/
│   │           ├── AuditController.php          (main audit CRUD)
│   │           ├── WebsiteController.php        (website CRUD)
│   │           ├── AuditDashboardController.php (dashboard stats)
│   │           ├── AuditHistoryController.php   (history & comparison)
│   │           └── NotificationController.php   (notifications)
│   ├── Requests/
│   │   └── Audit/
│   │       ├── StoreWebsiteRequest.php
│   │       ├── UpdateWebsiteRequest.php
│   │       ├── StoreAuditRequest.php
│   │       └── UpdateSettingRequest.php
│   └── Resources/
│       └── Audit/
│           ├── WebsiteResource.php
│           ├── AuditResource.php
│           ├── AuditResultResource.php
│           └── AuditDetailResource.php
├── Models/
│   └── Audit/
│       ├── AuditWebsite.php
│       ├── Audit.php
│       ├── AuditResult.php
│       ├── AuditDetail.php
│       ├── AuditHistory.php
│       ├── AuditNotification.php
│       └── AuditSetting.php
├── Repositories/
│   └── Audit/
│       ├── WebsiteRepository.php
│       ├── AuditRepository.php
│       └── NotificationRepository.php
├── Services/
│   └── Audit/
│       ├── AuditService.php              (orchestrator)
│       ├── Checkers/
│       │   ├── PerformanceChecker.php
│       │   ├── SeoChecker.php
│       │   ├── SecurityChecker.php
│       │   ├── AccessibilityChecker.php
│       │   └── BestPracticeChecker.php
│       ├── ScoringService.php            (hitung skor)
│       └── NotificationService.php       (kirim notif)
├── Jobs/
│   └── Audit/
│       ├── RunAuditJob.php               (main job)
│       ├── CheckPerformanceJob.php
│       ├── CheckSeoJob.php
│       ├── CheckSecurityJob.php
│       ├── CheckAccessibilityJob.php
│       └── CheckBestPracticeJob.php
├── Events/
│   └── Audit/
│       ├── AuditStartedEvent.php
│       ├── AuditCompletedEvent.php
│       ├── AuditFailedEvent.php
│       └── ScoreDropDetectedEvent.php
├── Listeners/
│   └── Audit/
│       ├── SendAuditNotificationListener.php
│       └── LogAuditActivityListener.php
└── Policies/
    └── Audit/
        └── WebsitePolicy.php
```

### 2.2 Frontend Architecture (Vue 3)

```
resources/js/
├── stores/
│   └── audit/
│       ├── useWebsiteStore.js
│       ├── useAuditStore.js
│       ├── useHistoryStore.js
│       └── useNotificationStore.js
├── pages/
│   └── admin/
│       └── audit/
│           ├── AuditDashboard.vue
│           ├── WebsiteList.vue
│           ├── WebsiteForm.vue
│           ├── AuditRunner.vue
│           ├── AuditResult.vue
│           ├── AuditHistory.vue
│           ├── AuditComparison.vue
│           └── AuditSettings.vue
├── components/
│   └── audit/
│       ├── layout/
│       │   ├── AuditSidebar.vue
│       │   └── AuditBreadcrumb.vue
│       ├── dashboard/
│       │   ├── OverallScoreCard.vue
│       │   ├── CategoryScoreCard.vue
│       │   ├── ScoreTrendChart.vue
│       │   └── RecentAuditList.vue
│       ├── shared/
│       │   ├── CircularProgress.vue
│       │   ├── GaugeChart.vue
│       │   ├── ProgressBar.vue
│       │   ├── StatusBadge.vue
│       │   ├── SeverityBadge.vue
│       │   ├── ScoreCard.vue
│       │   ├── TrendIndicator.vue
│       │   └── LoadingSkeleton.vue
│       ├── audit/
│       │   ├── FindingCard.vue
│       │   ├── FindingDetail.vue
│       │   ├── RecommendationCard.vue
│       │   ├── AuditTimeline.vue
│       │   ├── AuditProgress.vue
│       │   └── AuditCompare.vue
│       └── website/
│           ├── WebsiteCard.vue
│           ├── WebsiteForm.vue
│           └── WebsiteStatusBadge.vue
└── composables/
    └── audit/
        ├── useAuditRunner.js
        ├── useScoreCalculator.js
        └── useAuditExport.js
```

---

## 3. ENTITY RELATIONSHIP DIAGRAM (ERD)

```
┌─────────────────────┐       ┌─────────────────────┐
│   audit_websites    │       │    audit_settings    │
├─────────────────────┤       ├─────────────────────┤
│ id                  │       │ id                  │
│ name                │       │ key                 │
│ url                 │       │ value               │
│ description         │       │ created_at          │
│ logo                │       │ updated_at          │
│ environment         │       └─────────────────────┘
│ is_active           │
│ last_audited_at     │
│ created_at          │
│ updated_at          │
└────────┬────────────┘
         │ 1:N
         ▼
┌─────────────────────┐
│      audits         │
├─────────────────────┤
│ id                  │
│ audit_website_id    │──FK
│ user_id             │──FK → users
│ status              │ (pending,running,completed,failed)
│ overall_score       │
│ performance_score   │
│ seo_score           │
│ security_score      │
│ accessibility_score │
│ best_practice_score │
│ started_at          │
│ completed_at        │
│ duration_ms         │
│ error_message       │
│ created_at          │
│ updated_at          │
└────────┬────────────┘
         │ 1:N
         ▼
┌─────────────────────┐
│   audit_results     │
├─────────────────────┤
│ id                  │
│ audit_id            │──FK
│ category            │ (performance,seo,security,accessibility,best_practice)
│ score               │ (0-100)
│ status              │ (excellent,good,fair,poor)
│ total_checks        │
│ passed_checks       │
│ warning_checks      │
│ failed_checks       │
│ created_at          │
│ updated_at          │
└────────┬────────────┘
         │ 1:N
         ▼
┌─────────────────────┐
│   audit_details     │
├─────────────────────┤
│ id                  │
│ audit_result_id     │──FK
│ check_name          │
│ check_key           │
│ status              │ (pass,warning,failed)
│ severity            │ (low,medium,high,critical)
│ value               │ (actual value found)
│ expected            │ (expected/ideal value)
│ description         │
│ solution            │
│ example             │
│ reference_url       │
│ created_at          │
│ updated_at          │
└─────────────────────┘

┌─────────────────────┐
│  audit_histories    │
├─────────────────────┤
│ id                  │
│ audit_website_id    │──FK
│ audit_id            │──FK
│ overall_score       │
│ performance_score   │
│ seo_score           │
│ security_score      │
│ accessibility_score │
│ best_practice_score │
│ score_change        │ (delta from previous)
│ snapshot_date       │
│ created_at          │
└─────────────────────┘

┌──────────────────────────┐
│  audit_notifications     │
├──────────────────────────┤
│ id                       │
│ user_id                  │──FK → users
│ audit_website_id         │──FK
│ audit_id                 │──FK (nullable)
│ type                     │ (score_drop,ssl_expiry,website_down)
│ title                    │
│ message                  │
│ severity                 │ (info,warning,critical)
│ is_read                  │
│ data                     │ (json: extra context)
│ read_at                  │
│ created_at               │
│ updated_at               │
└──────────────────────────┘
```

### Relasi:
- audit_websites 1:N audits
- audits 1:N audit_results
- audit_results 1:N audit_details
- audit_websites 1:N audit_histories
- audits 1:N audit_notifications
- users 1:N audits (user yang menjalankan)
- users 1:N audit_notifications

---

## 4. STRUKTUR DATABASE (Migration Files)

```
database/migrations/
├── 2026_07_21_000001_create_audit_websites_table.php
├── 2026_07_21_000002_create_audits_table.php
├── 2026_07_21_000003_create_audit_results_table.php
├── 2026_07_21_000004_create_audit_details_table.php
├── 2026_07_21_000005_create_audit_histories_table.php
├── 2026_07_21_000006_create_audit_notifications_table.php
├── 2026_07_21_000007_create_audit_settings_table.php
└── 2026_07_21_000008_create_jobs_table.php      (Laravel queue)
```

---

## 5. API ENDPOINTS

```
Prefix: /api/audit

# Websites
GET    /api/audit/websites              (list, filter, paginate)
POST   /api/audit/websites              (create)
GET    /api/audit/websites/{id}         (show)
PUT    /api/audit/websites/{id}         (update)
DELETE /api/audit/websites/{id}         (delete)

# Audit
POST   /api/audit/run                   (start audit)
GET    /api/audit/{id}                  (get audit result)
GET    /api/audit/{id}/details          (get all findings)
GET    /api/audit/{id}/details/{cat}    (get findings by category)

# Dashboard
GET    /api/audit/dashboard             (overview stats)
GET    /api/audit/dashboard/trends      (score trends chart)

# History
GET    /api/audit/history               (all history)
GET    /api/audit/history/{website_id}  (history per website)
GET    /api/audit/compare               (compare 2+ audits)

# Notifications
GET    /api/audit/notifications         (list notif)
PUT    /api/audit/notifications/{id}/read
POST   /api/audit/notifications/read-all

# Settings
GET    /api/audit/settings
PUT    /api/audit/settings
```

---

## 6. CHECKER STRATEGY

Setiap checker adalah class terpisah yang mengimplementasikan interface:

```
interface AuditCheckerInterface {
    public function check(string $url, array $html, array $headers): AuditCheckResult;
    public function getCategory(): string;
}
```

### Performance Checker:
- DNS Lookup: `dns_get_record()` + timing
- TTFB: `curl_getinfo(CURLINFO_STARTTRANSFER_TIME)`
- FCP/LCP/CLS/INP: perlu headless browser (Puppeteer) atau PageSpeed Insights API
- Speed Index: PageSpeed Insights API
- Total Request/Asset Size: parse HTML, hitung resource
- Image Optimization: cek format, lazy loading, dimensions
- Compression: cek Content-Encoding header
- Minify: cek ukuran CSS/JS

### SEO Checker:
- Title/Meta/H1/H2: parse HTML DOM
- Sitemap: cek /sitemap.xml
- Robots.txt: cek /robots.txt
- Canonical: parse <link rel="canonical">
- Open Graph/Twitter Card: parse meta tags
- Structured Data: parse JSON-LD
- Alt Image: parse <img> tags
- Broken Link: HTTP HEAD request per link

### Security Checker:
- HTTPS: cek URL scheme
- SSL: `stream_context_create()` + `openssl_x509_parse()`
- Headers: cek HSTS, CSP, X-Frame-Options, X-XSS-Protection, X-Content-Type-Options, Referrer-Policy
- Cookie: cek Secure, HttpOnly flags
- TLS: cek `STREAM_CRYPTO_METHOD_TLS_*`

### Accessibility Checker:
- Contrast: hitung ratio warna (WCAG 2.1)
- Alt Image: cek semua <img> punya alt
- Form Label: cek semua input punya label
- ARIA: cek atribut aria-*
- Heading Structure: cek hierarki h1-h6
- Keyboard: cek tabindex, focusable elements

### Best Practice Checker:
- Console Error: perlu headless browser atau skip
- Mixed Content: cek http:// resources di halaman https
- HTTP Redirect: cek redirect chain
- Large JS/CSS: parse dan hitung ukuran
- Unused CSS/JS: perlu PurgeCSS analysis (simplified: flag file > 250KB)

---

## 7. IMPLEMENTATION ROADMAP

### Phase 1: Foundation (Hari 1-2)
- [ ] Install Pinia, setup store structure
- [ ] Create migrations (7 tables + jobs table)
- [ ] Create Models with relationships
- [ ] Create base Repository & Service interfaces
- [ ] Create Form Requests
- [ ] Create API Resources
- [ ] Setup routes
- [ ] Create seeders

### Phase 2: Website CRUD (Hari 3)
- [ ] WebsiteRepository + WebsiteService
- [ ] WebsiteController (CRUD API)
- [ ] Vue: WebsiteList page
- [ ] Vue: WebsiteForm (create/edit)
- [ ] Pinia: useWebsiteStore
- [ ] Add menu to AdminLayout

### Phase 3: Audit Engine (Hari 4-6)
- [ ] AuditCheckerInterface
- [ ] SecurityChecker (simplest, no external API)
- [ ] SeoChecker (HTML parsing)
- [ ] BestPracticeChecker (HTML parsing)
- [ ] AccessibilityChecker (HTML parsing)
- [ ] PerformanceChecker (HTTP timing + basic checks)
- [ ] ScoringService (calculate 0-100 per category)
- [ ] RunAuditJob (orchestrate all checkers)
- [ ] AuditService (create audit, dispatch job)

### Phase 4: Audit UI (Hari 7-9)
- [ ] Vue: AuditRunner (progress bar, live status)
- [ ] Vue: AuditResult (score cards, findings)
- [ ] Vue components: CircularProgress, GaugeChart, StatusBadge
- [ ] Vue components: FindingCard, RecommendationCard
- [ ] Pinia: useAuditStore

### Phase 5: Dashboard & History (Hari 10-11)
- [ ] AuditDashboardController (stats, trends)
- [ ] Vue: AuditDashboard (overall score, trends, recent audits)
- [ ] Vue: AuditHistory (table with filters)
- [ ] Vue: AuditComparison (compare 2+ audits)
- [ ] ScoreTrendChart (Chart.js)

### Phase 6: Notifications (Hari 12)
- [ ] AuditNotification model + migration
- [ ] NotificationService (score drop, SSL, downtime)
- [ ] Listeners for AuditCompletedEvent
- [ ] Vue: notification bell + dropdown
- [ ] Vue: notification page

### Phase 7: Polish & Testing (Hari 13-14)
- [ ] Loading skeletons
- [ ] Error handling
- [ ] Responsive design
- [ ] Dark mode support
- [ ] Export PDF/CSV
- [ ] Rate limiting
- [ ] Documentation

---

## 8. DEPENDENCIES YANG DIBUTUHkan

### Backend (Composer)
```json
{
    "require": {
        "guzzlehttp/guzzle": "^7.8",        // HTTP client (sudah ada via Laravel)
        "symfony/dom-crawler": "^7.0",       // HTML parsing
        "symfony/css-selector": "^7.0",      // CSS selector for DOM
        "spatie/ssl-certificate": "^2.6",    // SSL certificate checker (optional)
        "spatie/browsershot": "^4.0"         // Headless browser (optional, untuk advanced checks)
    }
}
```

### Frontend (NPM)
```json
{
    "dependencies": {
        "pinia": "^2.1.7",
        "chart.js": "^4.4.0",
        "vue-chartjs": "^5.3.0"
    }
}
```

---

## 9. SEQUENCE DIAGRAM: FLOW AUDIT

```
User                Frontend              Backend              Queue              Checkers
 │                    │                     │                    │                    │
 │  Klik "Mulai"      │                     │                    │                    │
 │───────────────────>│  POST /audit/run    │                    │                    │
 │                    │────────────────────>│                    │                    │
 │                    │                     │  Create Audit      │                    │
 │                    │                     │  (status=pending)  │                    │
 │                    │                     │                    │                    │
 │                    │                     │  Dispatch Job      │                    │
 │                    │                     │───────────────────>│                    │
 │                    │  Return audit_id    │                    │                    │
 │                    │<────────────────────│                    │                    │
 │  Show progress     │                     │                    │                    │
 │<───────────────────│                     │                    │                    │
 │                    │                     │                    │  RunAuditJob       │
 │                    │                     │                    │───────────────────>│
 │                    │                     │                    │                    │
 │                    │                     │                    │  PerformanceCheck  │
 │                    │                     │                    │───────────────────>│
 │                    │                     │                    │  Return results    │
 │                    │                     │                    │<───────────────────│
 │                    │                     │                    │  SEO Check         │
 │                    │                     │                    │───────────────────>│
 │                    │                     │                    │  Return results    │
 │                    │                     │                    │<───────────────────│
 │                    │                     │                    │  Security Check    │
 │                    │                     │                    │───────────────────>│
 │                    │                     │                    │  Return results    │
 │                    │                     │                    │<───────────────────│
 │                    │                     │                    │  Accessibility     │
 │                    │                     │                    │───────────────────>│
 │                    │                     │                    │  Return results    │
 │                    │                     │                    │<───────────────────│
 │                    │                     │                    │  Best Practice     │
 │                    │                     │                    │───────────────────>│
 │                    │                     │                    │  Return results    │
 │                    │                     │                    │<───────────────────│
 │                    │                     │                    │                    │
 │                    │                     │                    │  Save Results      │
 │                    │                     │                    │──────────────────>│
 │                    │                     │  Update Audit      │                    │
 │                    │                     │  (status=completed)│                    │
 │                    │                     │                    │                    │
 │                    │                     │  Fire Event        │                    │
 │                    │                     │  AuditCompleted    │                    │
 │                    │                     │                    │                    │
 │                    │  Polling status     │                    │                    │
 │                    │────────────────────>│                    │                    │
 │                    │  Return results     │                    │                    │
 │                    │<────────────────────│                    │                    │
 │  Show dashboard    │                     │                    │                    │
 │<───────────────────│                     │                    │                    │
```

---

## 10. REKOMENDASI LIBRARY

| Kebutuhan | Library | Alasan |
|-----------|---------|--------|
| HTTP Client | Guzzle (built-in) | Sudah ada di Laravel |
| HTML Parser | Symfony DOM Crawler | Sudah ada di Laravel |
| SSL Check | stream_context + openssl | Built-in PHP |
| Queue | Laravel Queue (database) | Sudah ada, mudah setup |
| Chart | Chart.js + vue-chartjs | Ringan, fleksibel |
| State | Pinia | Official Vue state management |
| PDF Export | DomPDF (sudah ada) | Sudah terinstall |
| Notification | Laravel Notification (database) | Built-in |

---

## CATATAN PENTING

1. Modul audit dibuat TERPISAH dari modul existing (BPMP website content)
2. Tidak mengubah struktur existing, hanya menambahkan
3. Audit module punya database tables sendiri (prefix audit_)
4. Checker classes bisa di-extend tanpa mengubah core
5. Queue menggunakan database driver (tidak perlu Redis/RabbitMQ)
6. Scoring algorithm bisa disesuaikan tanpa mengubah UI
7. Semua API endpoint dilindungi auth:sanctum + admin middleware
