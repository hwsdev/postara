# PRD — Open Source Mail Service Platform

**Project codename:** Postara (placeholder — bebas diganti)
**Author:** hyuu.dev
**License:** AGPL-3.0 (rekomendasi, lihat section 14)
**Repo target:** github.com/hwsdev/postara
**Status:** Draft v3.0
**Last updated:** 2026-05-11

---

## 1. Executive Summary

Postara adalah platform email service **open source dan self-hosted** yang menggabungkan kapabilitas Brevo, Mailersend, dan Resend dalam satu produk. Dibangun dengan **Laravel 11** sebagai backbone dan dirancang untuk bisa di-deploy dengan satu klik via **Coolify** (atau plain Docker Compose) di VPS Ubuntu mana saja.

UI mengadopsi **estetika Uber** — monochrome, bold typography, banyak whitespace, sudut tajam — untuk menyampaikan keandalan dan kesan profesional yang sejalan dengan brand hyuu.dev.

Target pengguna: developer yang butuh API kirim email transactional + tim marketing yang butuh campaign builder, semuanya dalam satu instance yang mereka kontrol sendiri.

---

## 2. Problem Statement

Saat ini opsi mail service yang ada punya gap berikut:

1. **Provider SaaS** (Brevo, Mailersend, Resend, Sendgrid) — biaya dalam USD, data di luar yurisdiksi Indonesia, free tier terbatas.
2. **Open source existing** (Postal, Mailcow, Listmonk) — sebagian besar fokus ke satu use case saja. Postal hebat untuk transactional tapi tidak punya campaign builder. Listmonk bagus untuk newsletter tapi tidak punya API transactional yang clean. Mailcow lebih ke full mail server (inbox + outbound), bukan ESP.
3. **Belum ada** open source ESP modern yang menggabungkan transactional API + marketing campaign + UX modern dalam satu paket yang gampang di-deploy.

Postara mengisi gap tersebut, dengan **PHP/Laravel stack** yang familiar untuk komunitas web developer Indonesia.

---

## 3. Target Users

### Primary

- **Developer / startup founder** yang butuh API untuk kirim email transactional (signup verification, password reset, notifikasi order).
- **Marketing team / UMKM** yang butuh kirim newsletter, promo, dan campaign broadcast.

### Secondary

- **Agency / software house** yang ingin sediakan email service untuk klien (multi-tenant).
- **Komunitas Laravel Indonesia & global** — sebagai kontributor dan early adopter.

### Self-host Users (bukan customer)

Karena open source, pengguna adalah **operator** yang menjalankan instance sendiri. Prioritas: deployment experience, dokumentasi, dan upgrade path sama pentingnya dengan fitur.

---

## 4. Goals & Non-Goals

### Goals (v1.0 — MVP)

1. Developer bisa kirim email transactional via REST API.
2. Marketer bisa bikin campaign dengan drag-drop builder, kirim broadcast.
3. Tracking dasar: delivered, opened, clicked, bounced.
4. **Deployment via Coolify dengan 1 klik** (atau plain `docker compose up`).
5. Multi-tenant: 1 instance bisa melayani banyak workspace.
6. Dokumentasi developer lengkap supaya kontributor baru onboard dalam 1 jam.

### Non-Goals (v1.0)

- ❌ Inbox / email receiving (IMAP)
- ❌ A/B testing campaign (v1.2)
- ❌ AI-generated copy (v1.3)
- ❌ SMS / WhatsApp gateway
- ❌ Managed/hosted cloud version

---

## 5. Success Metrics

| Metric | Target 6 bulan post-launch |
|---|---|
| GitHub stars | 1.000 |
| Active deployments (via opt-in telemetry) | 200 |
| External contributors (merged PR) | 15 |
| Closed issues | 80% dalam 30 hari |
| Docker image pulls/bulan | 5.000 |
| Coolify one-click service inclusion | Diterima ke marketplace |

Metrics teknikal:
- Delivery rate > 97% (assuming proper DNS setup)
- API p95 latency < 300ms
- Cold start (`docker compose up`) → ready < 60 detik

---

## 6. Features & Requirements

### 6.1 Authentication & Workspace

- Email + password signup (bcrypt via Laravel `Hash`).
- Magic link login (v1.1) menggunakan Laravel Signed URLs.
- Workspace = tenant boundary. Satu user bisa join banyak workspace, role: owner, admin, member.
- API key per workspace, di-hash di DB, bisa di-revoke dan di-rotate (Laravel Sanctum).
- First-run setup wizard: bikin admin account + workspace pertama.

### 6.2 Sending Domain

- User tambah domain (contoh: `mail.toko-andi.com`).
- Sistem generate DKIM keypair (RSA 2048), tampilkan record SPF, DKIM, DMARC.
- Tombol "verify" cek DNS via DoH (Cloudflare/Google).
- Status: pending → verified → failed.
- Tanpa domain verified, user tidak bisa kirim email production.

### 6.3 Transactional Email (Developer API)

```http
POST /v1/emails
Authorization: Bearer {API_KEY}
Content-Type: application/json
Idempotency-Key: 8f4c2a... (optional)

{
  "from": "noreply@mail.toko-andi.com",
  "to": ["user@example.com"],
  "subject": "Pesanan kamu sudah dikirim",
  "html": "<p>Hai...</p>",
  "text": "Hai...",
  "tags": ["order", "shipping"],
  "headers": { "X-Order-ID": "12345" }
}
```

- Response: `{ "id": "em_abc123", "status": "queued" }`
- Webhook events: `email.delivered`, `email.opened`, `email.clicked`, `email.bounced`, `email.complained`
- Rate limit per API key (Laravel `RateLimiter`): 100 req/s default, configurable per workspace.
- **API shape mengikuti Resend** untuk familiarity.

**Template support:**

```http
POST /v1/emails
{
  "template_id": "tpl_welcome",
  "variables": { "name": "Andi", "order_id": "12345" }
}
```

Template pakai Blade syntax (`{{ $name }}`) atau Handlebars (untuk portability). Default: Blade — karena native Laravel dan punya power penuh (loops, conditionals, partials).

### 6.4 Campaign / Broadcast (Marketing UI)

- **Contact list:** import via CSV, manual add, atau API. Field: email, nama, custom fields (JSON column).
- **Segmentation:** filter berdasarkan tag, custom field, engagement.
- **Template builder:** drag-drop pakai [GrapesJS Newsletter Preset](https://github.com/GrapesJS/preset-newsletter) embedded di UI Inertia/Livewire.
- **Campaign flow:**
  1. Pilih audience (list / segment)
  2. Pilih atau buat template
  3. Set sender, subject, preview text
  4. Schedule: kirim sekarang atau scheduled (Laravel Scheduler + Jobs)
  5. Review & send
- **Unsubscribe link** otomatis di-inject dengan Signed URL.

### 6.5 Analytics

Per email dan per campaign:

- Sent, delivered, bounced (hard/soft), complained
- Opened (unique & total), clicked (unique & total, per-link breakdown)
- Timeline chart
- Export ke CSV

### 6.6 Suppression List

- Auto-add: hard bounce, complaint, manual unsubscribe
- Sistem tolak kirim ke email di suppression list (Job middleware check)
- UI untuk lihat & manual remove

---

## 7. Tech Stack

### Kriteria Pemilihan

1. **Mature & well-documented** — kontributor baru gampang onboard.
2. **AI coding assistant friendly** — Laravel punya training data masif di Claude/Cursor/Copilot.
3. **Container-native** — semua komponen bisa jalan di Docker untuk Coolify.
4. **Battle-tested untuk web app** — bukan stack eksperimental.
5. **Open source license compatible** — semua dependency permissive (MIT) supaya project bisa AGPL-3.0.

### Stack Final

| Layer | Pilihan | Catatan |
|---|---|---|
| **Bahasa** | PHP 8.3+ | LTS, modern syntax, typed properties |
| **Framework** | Laravel 11 | Latest LTS-track |
| **Web Server** | FrankenPHP (default) atau Nginx + PHP-FPM | FrankenPHP = single binary, lebih simpel di Docker |
| **Frontend Stack** | **Livewire 3 + Alpine.js** (rekomendasi utama) | Tetap di server-side rendering, minim build complexity |
| *(alternatif)* | Inertia.js + Vue 3 | Kalau butuh SPA-feel lebih kuat — keputusan final di section 7.1 |
| **CSS** | Tailwind CSS v4 | Standar de facto di ekosistem Laravel modern |
| **UI Style** | Uber-inspired (lihat section 7.2) | Custom design tokens, bukan framework jadi |
| **Database** | PostgreSQL 16 | Pilihan utama (JSON column lebih kuat untuk custom fields, segmentation) |
| *(alternatif)* | MySQL 8 | Didukung tapi bukan primary |
| **ORM** | Eloquent | Native Laravel |
| **Queue** | Laravel Queue + Redis driver | Job dispatch untuk email send, webhook delivery, scheduled campaign |
| **Queue Monitor** | Laravel Horizon | UI untuk monitor queue, retry failed jobs |
| **Email Send (Mailer)** | Symfony Mailer (built-in Laravel Mail) | SMTP transport ke MTA pilihan |
| **Email Template Builder** | GrapesJS + MJML | MJML compile ke HTML email-safe |
| **Auth & API Token** | Laravel Sanctum | Untuk session web + API token |
| **Object Storage** | MinIO (default) atau S3-compatible | Filesystem driver `s3` Laravel |
| **Reverse Proxy** | **Traefik via Coolify**, atau Caddy untuk plain compose | Coolify auto-manage |
| **Container Runtime** | Docker + Docker Compose | Single source of truth |
| **Container Base** | [serversideup/php](https://serversideup.net/open-source/docker-php) atau custom FrankenPHP | Production-ready PHP Docker image |
| **Package Manager** | Composer (PHP), pnpm (JS assets) | Standard |
| **Testing** | Pest (di atas PHPUnit) + Laravel Dusk (untuk e2e) | Pest = syntax modern untuk testing |
| **Static Analysis** | Larastan (PHPStan untuk Laravel) | Type safety di CI |
| **Code Style** | Laravel Pint | Auto-format, opinionated |
| **Admin Panel (internal)** | Filament v3 (opsional untuk internal admin) | Untuk super-admin view di self-hosted instance |
| **Telemetry (opt-in)** | Plausible self-host | Untuk usage metrics, fully opt-in |

### 7.1 Frontend Approach: Livewire vs Inertia

**Rekomendasi: Livewire 3 + Alpine.js + Tailwind**

Alasan:
- Tetap server-side rendering, semua state di PHP — lebih sederhana untuk maintain.
- Build pipeline lebih ringan (tidak perlu compile bundle SPA).
- Dokumentasi & contoh masif di komunitas Laravel.
- Cocok untuk dashboard-style app (form, table, CRUD-heavy).
- AI coding assistant generate kode lebih akurat karena patternnya straightforward.

Komponen yang butuh interactivity tinggi (campaign builder canvas, real-time analytics chart) akan pakai Alpine.js atau di-mount Vue/React component lokal di dalam Livewire view.

Kalau di kemudian hari butuh full SPA, migrasi ke Inertia + Vue tetap memungkinkan tanpa restart project.

### 7.2 Design System: Uber-Inspired

Tujuan: UI yang terasa **profesional, confident, technical, minimal**. Berikut adalah **design tokens** yang akan ditetapkan di Tailwind config dan jadi single source of truth.

#### Color Palette

| Token | Value | Usage |
|---|---|---|
| `--color-black` | `#000000` | Primary text, headers, primary buttons |
| `--color-ink` | `#0A0A0A` | Background gelap (sejalan dengan brand hyuu.dev) |
| `--color-white` | `#FFFFFF` | Background terang utama |
| `--color-cream` | `#F5F0E8` | Surface alternatif, hover state ringan (brand hyuu.dev) |
| `--color-gray-50` | `#F6F6F6` | Background sekunder |
| `--color-gray-100` | `#EEEEEE` | Border ringan, divider |
| `--color-gray-300` | `#C9C9C9` | Disabled, placeholder |
| `--color-gray-500` | `#757575` | Secondary text |
| `--color-gray-700` | `#454545` | Body text di light mode |
| `--color-success` | `#06A763` | Delivered, success state |
| `--color-warning` | `#F6B100` | Pending, warning |
| `--color-danger` | `#E11900` | Bounced, error |
| `--color-info` | `#276EF1` | Link, info (Uber blue) |

Dominasi: **black & white**. Accent color dipakai sparingly hanya untuk state (success/warning/danger/info) — bukan untuk dekorasi.

#### Typography

- **Primary font:** Inter (open source, mirip dengan Uber Move di proporsi & weight)
- **Monospace:** JetBrains Mono (untuk code, API key, message-id)

Skala:

| Token | Size | Weight | Letter spacing | Usage |
|---|---|---|---|---|
| `text-display` | 56px / line 1.05 | 700 | -0.03em | Hero, landing |
| `text-h1` | 36px / 1.15 | 700 | -0.02em | Page title |
| `text-h2` | 28px / 1.2 | 700 | -0.015em | Section title |
| `text-h3` | 22px / 1.3 | 600 | -0.01em | Card title |
| `text-body-lg` | 18px / 1.5 | 400 | 0 | Lead paragraph |
| `text-body` | 16px / 1.5 | 400 | 0 | Default body |
| `text-body-sm` | 14px / 1.4 | 400 | 0 | Helper, caption |
| `text-mono` | 14px / 1.4 | 500 | 0 | Code, API key |
| `text-overline` | 12px / 1.2 | 600 | 0.08em | UPPERCASE LABELS |

Heading style: **bold, tight tracking**. Body: normal weight, ample line-height.

#### Spacing & Layout

- Grid system: 8px baseline. Semua spacing kelipatan 4 atau 8.
- Container max-width: 1280px untuk dashboard, 1440px untuk marketing pages.
- Whitespace murah hati: section padding minimum 64px vertical di desktop.

#### Border & Shape

- **Border radius:** sangat minimal. Default `4px` untuk button & input. `0` untuk card (sharp edges). `8px` hanya untuk modal/dialog.
- **Border width:** 1px solid `gray-100`. Tidak ada shadow berlebihan.
- **Shadow:** flat. Maksimum `0 1px 3px rgba(0,0,0,0.04)`. Modal pakai `0 24px 48px rgba(0,0,0,0.12)`.

#### Component Patterns

- **Button primary:** background hitam, text putih, padding 12px 24px, font-weight 600, border-radius 4px, no shadow. Hover: opacity 0.9.
- **Button secondary:** background putih, border 1px hitam, text hitam. Hover: background `gray-50`.
- **Input:** border 1px `gray-100`, focus border hitam (no glow), padding 12px 16px, border-radius 4px.
- **Card:** background putih, border 1px `gray-100`, border-radius 0 atau 4px, padding 24-32px.
- **Table:** zebra stripes off. Border bawah `gray-100`. Header text-overline style.
- **Badge:** padding 4px 8px, font 12px weight 600 uppercase letter-spacing 0.05em. Color sesuai state.
- **Tab:** underline aktif 2px hitam, inactive text `gray-500`. Tidak pakai pill style.

#### Iconography

- **Icon set:** [Lucide Icons](https://lucide.dev/) (MIT) — outline style, stroke 1.5–2px, konsisten dengan Uber's line icons.
- Ukuran standar: 16px, 20px, 24px.

#### Motion

- Transition standar 150ms ease-out untuk hover, 200ms untuk page transition.
- Tidak ada bounce/spring animation berlebihan — vibe Uber adalah crisp, bukan playful.

### 7.3 Project Structure

```
postara/
├── app/
│   ├── Console/
│   │   └── Commands/         # Artisan commands
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/       # API controllers (versioned)
│   │   │   └── Dashboard/    # Web controllers
│   │   ├── Middleware/
│   │   └── Requests/         # Form Requests (validation)
│   ├── Jobs/
│   │   ├── SendEmailJob.php
│   │   ├── DeliverWebhookJob.php
│   │   └── ProcessCampaignJob.php
│   ├── Livewire/             # Livewire components
│   │   ├── Campaigns/
│   │   ├── Contacts/
│   │   └── Templates/
│   ├── Mail/                 # Mailable classes
│   ├── Models/
│   ├── Services/             # Business logic
│   │   ├── EmailService.php
│   │   ├── DkimService.php
│   │   ├── DomainVerifier.php
│   │   └── TrackingService.php
│   └── Tenant/               # Multi-tenant scope logic
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── docker/
│   ├── frankenphp/
│   │   └── Caddyfile
│   └── entrypoint.sh
├── docs/                     # Markdown docs
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── livewire/
│       ├── layouts/
│       └── emails/
├── routes/
│   ├── api.php
│   ├── web.php
│   └── channels.php
├── tests/
│   ├── Feature/
│   ├── Unit/
│   └── Pest.php
├── Dockerfile
├── docker-compose.yml        # Coolify-ready
├── docker-compose.standalone.yml  # With Caddy untuk non-Coolify
├── .coolify/                 # Coolify-specific config
├── .env.example
└── README.md
```

### 7.4 Service Architecture (di Coolify)

```
                Internet
                    │
              ┌─────▼─────┐
              │  Traefik  │ (managed by Coolify, auto HTTPS)
              └─────┬─────┘
                    │
              ┌─────▼──────┐
              │    app     │  (FrankenPHP serving Laravel — web + API)
              └─────┬──────┘
                    │
        ┌───────────┼───────────────┐
        │           │               │
   ┌────▼─────┐ ┌───▼────┐  ┌───────▼────────┐
   │ postgres │ │ redis  │  │  queue-worker  │ (laravel queue:work)
   └──────────┘ └───┬────┘  └───────┬────────┘
                    │               │
                    │         ┌─────▼─────┐
                    │         │ scheduler │ (php artisan schedule:run)
                    │         └───────────┘
                    │
              ┌─────▼─────────┐
              │  SMTP relay   │ (SES/Mailgun/Postfix)
              └───────────────┘
```

Container terpisah: `app`, `queue-worker`, `scheduler`. Semua build dari Dockerfile yang sama, beda hanya command-nya:
- `app`: `frankenphp run` (serve HTTP)
- `queue-worker`: `php artisan queue:work --tries=3`
- `scheduler`: `php artisan schedule:work`

Pemisahan ini supaya tiap proses bisa di-scale independen di kemudian hari.

### 7.5 MTA Strategy

| Mode | MTA | Use Case | Default? |
|---|---|---|---|
| **A. Self-host (Postfix)** | Postfix container di compose | VPS dengan port 25 terbuka & IP reputable | No |
| **B. SMTP relay** | Tidak ada local MTA, relay ke AWS SES / Mailgun / Postmark | User mau delivery rate maksimal tanpa urus IP warmup | **Yes** |
| **C. Hybrid** | Postfix lokal + fallback relay | Advanced operator | No |

Default mode B karena banyak VPS provider block port 25 outbound.

---

## 8. Coolify Integration

### 8.1 One-Click Deployment

Target: project diterima ke [Coolify Services marketplace](https://coolify.io/docs/services/introduction) untuk deploy 1 klik.

### 8.2 docker-compose.yml untuk Coolify

```yaml
services:
  app:
    image: ghcr.io/hwsdev/postara-app:${IMAGE_TAG:-latest}
    environment:
      - APP_URL=${SERVICE_FQDN_APP}
      - APP_KEY=${APP_KEY}
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=postara
      - DB_USERNAME=postara
      - DB_PASSWORD=${POSTGRES_PASSWORD}
      - REDIS_HOST=redis
      - QUEUE_CONNECTION=redis
      - CACHE_STORE=redis
      - SESSION_DRIVER=redis
      - MAIL_MAILER=smtp
      - MAIL_HOST=${SMTP_HOST}
      - MAIL_PORT=${SMTP_PORT}
      - MAIL_USERNAME=${SMTP_USER}
      - MAIL_PASSWORD=${SMTP_PASSWORD}
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_started

  queue-worker:
    image: ghcr.io/hwsdev/postara-app:${IMAGE_TAG:-latest}
    command: php artisan queue:work --tries=3 --max-time=3600
    environment:
      # Sama dengan app, dishare via .env Coolify
      - APP_KEY=${APP_KEY}
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_DATABASE=postara
      - DB_USERNAME=postara
      - DB_PASSWORD=${POSTGRES_PASSWORD}
      - REDIS_HOST=redis
      - QUEUE_CONNECTION=redis
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_started

  scheduler:
    image: ghcr.io/hwsdev/postara-app:${IMAGE_TAG:-latest}
    command: php artisan schedule:work
    environment:
      - APP_KEY=${APP_KEY}
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_DATABASE=postara
      - DB_USERNAME=postara
      - DB_PASSWORD=${POSTGRES_PASSWORD}
      - REDIS_HOST=redis
    depends_on:
      postgres:
        condition: service_healthy

  postgres:
    image: postgres:16-alpine
    environment:
      - POSTGRES_USER=postara
      - POSTGRES_PASSWORD=${POSTGRES_PASSWORD}
      - POSTGRES_DB=postara
    volumes:
      - postgres-data:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U postara"]
      interval: 10s
      retries: 5

  redis:
    image: redis:7-alpine
    volumes:
      - redis-data:/data

volumes:
  postgres-data:
  redis-data:
```

Catatan penting:
- **Tidak define custom networks** — Coolify auto-create bridge network. Define custom networks bisa bikin intermittent outage di Traefik routing.
- **Pakai magic env var** `${SERVICE_FQDN_APP}` untuk URL auto-generated Coolify.
- **Service-to-service via service name** (`postgres`, `redis`).
- **Healthcheck wajib** untuk service yang punya dependent.
- **Pre-built image dari GHCR** — bukan build di server.

### 8.3 Dockerfile (Single Image untuk 3 Service)

```dockerfile
FROM dunglas/frankenphp:1-php8.3-alpine

# Install PHP extensions
RUN install-php-extensions \
    pdo_pgsql \
    redis \
    intl \
    opcache \
    pcntl \
    zip \
    gd

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application
COPY . .
RUN composer dump-autoload --optimize --no-dev

# Build frontend assets
COPY package.json pnpm-lock.yaml ./
RUN apk add --no-cache nodejs npm && npm install -g pnpm
RUN pnpm install --frozen-lockfile && pnpm run build

# Permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Default command: serve via FrankenPHP
CMD ["frankenphp", "run", "--config", "/app/docker/frankenphp/Caddyfile"]
```

Service `queue-worker` dan `scheduler` override `CMD` di compose file.

### 8.4 Plain Docker Compose (Non-Coolify)

Sediakan `docker-compose.standalone.yml` yang include Caddy untuk reverse proxy + HTTPS otomatis untuk user yang tidak pakai Coolify.

### 8.5 Environment Variables

Sediakan `.env.example` lengkap dengan komentar. Coolify parse ini untuk auto-fill form deployment.

---

## 9. Data Model (Eloquent Models)

```php
// Tables
workspaces (id, name, plan, created_at)
users (id, email, password, created_at)
workspace_user (workspace_id, user_id, role)  // pivot
api_keys (id, workspace_id, key_hash, name, last_used_at)

domains (id, workspace_id, domain, dkim_public, dkim_private, status, verified_at)

contacts (id, workspace_id, email, name, custom_fields jsonb, created_at)
contact_lists (id, workspace_id, name)
contact_list_contact (contact_list_id, contact_id)  // pivot

templates (id, workspace_id, name, subject, mjml, html, design_json, type)

emails (id, workspace_id, message_id, from, to, subject, status,
        template_id, campaign_id, tags jsonb, created_at)
email_events (id, email_id, type, data jsonb, created_at)

campaigns (id, workspace_id, name, template_id, contact_list_id,
           segment_filter jsonb, status, scheduled_at, sent_at)

suppressions (id, workspace_id, email, reason, created_at)
webhooks (id, workspace_id, url, events jsonb, secret, active)
```

Pakai trait `BelongsToWorkspace` untuk semua model multi-tenant — auto-scope query ke workspace user yang sedang login.

---

## 10. Security

- Password hash: bcrypt (Laravel default)
- API key: hash dengan `Hash::make()`, plain text hanya ditampilkan saat generate (Sanctum pattern)
- HTTPS wajib (auto via Traefik/Caddy)
- Rate limiting per API key via Laravel `RateLimiter`
- DKIM signing per sending domain
- Webhook signing pakai HMAC SHA-256
- CSRF protection di dashboard (Laravel default)
- Input validation pakai Form Request di semua endpoint
- Audit log pakai package `spatie/laravel-activitylog` untuk action sensitif
- SQL injection: Eloquent + parameter binding (Laravel default)
- XSS: Blade auto-escape

---

## 11. Open Source Operations

### 11.1 Repository Structure

```
.github/
├── ISSUE_TEMPLATE/
│   ├── bug_report.yml
│   ├── feature_request.yml
│   └── deployment_issue.yml
├── PULL_REQUEST_TEMPLATE.md
├── workflows/
│   ├── ci.yml             # Pest test, Pint, Larastan
│   ├── docker-build.yml   # Build & push ke GHCR on tag
│   └── release.yml        # Auto-changelog
├── CODE_OF_CONDUCT.md
├── CONTRIBUTING.md
└── SECURITY.md
```

### 11.2 Required Documents

- `README.md` — overview, quick start, screenshot, demo link
- `CONTRIBUTING.md` — setup dev env, coding standards, PR process
- `CODE_OF_CONDUCT.md` — Contributor Covenant 2.1
- `SECURITY.md` — cara report security issue
- `LICENSE` — AGPL-3.0
- `CHANGELOG.md` — auto-generated dari Conventional Commits
- `docs/` — full documentation (pakai VitePress atau Laravel-native docs)

### 11.3 Community

- **GitHub Discussions** untuk Q&A
- **Discord server** untuk real-time chat
- **Roadmap publik** di GitHub Projects
- **Issue triage** dengan label (`good first issue`, `help wanted`, dst)

### 11.4 Release Strategy

- **SemVer**: MAJOR.MINOR.PATCH
- **Minor release** tiap 4–6 minggu
- **LTS**: setiap MAJOR dapat 1 tahun security patches
- **Docker tag**: `latest`, `v1.2.3`, `1.2`, `1`
- **Migration guide** wajib untuk breaking changes

---

## 12. Roadmap

### v0.x — Alpha (target: 8 minggu)

- Auth + workspace + API key (Sanctum)
- Domain verification (DKIM, SPF)
- Transactional API
- Queue worker + SMTP relay
- Basic tracking (open, click)
- Webhook events
- Minimal dashboard dengan Uber-style design system

### v1.0 — Public Release (+6 minggu)

- Contact list + CSV import
- Template builder (GrapesJS + MJML)
- Campaign creation + scheduled send
- Suppression list
- Analytics dashboard
- Coolify one-click submission
- Full documentation
- Public Discord & GitHub launch

### v1.1

- Magic link auth
- Postfix-mode untuk full self-host
- Advanced segmentation
- Geo & device analytics

### v1.2

- A/B testing
- Drip/automation workflows
- Inbound parsing

### v1.3

- AI assist (subject line, copy) — pluggable LLM backend
- White-label per workspace

### v2.0 (long-term)

- Multi-server / HA mode
- Managed cloud offering

---

## 13. Development Milestones

| Minggu | Deliverable |
|---|---|
| 1 | Laravel 11 bootstrap, monorepo struktur, Dockerfile draft, Tailwind + design tokens |
| 2 | Auth + workspace + DB migrations + design system base components |
| 3 | API key (Sanctum), domain verification, DKIM keypair |
| 4 | Transactional API endpoint + Form Request validation |
| 5 | Mail queue + Job + SMTP relay integration |
| 6 | Tracking pixel + link rewriter + event ingestion |
| 7 | Webhook delivery (HMAC, retry) |
| 8 | Alpha release |
| 9–10 | Contact list, template builder (GrapesJS integration) |
| 11 | Campaign creator + scheduled (Laravel Scheduler) |
| 12 | Analytics dashboard, suppression list |
| 13 | Documentation site, Coolify one-click service |
| 14 | Public launch (v1.0) |

---

## 14. License Decision

Rekomendasi: **AGPL-3.0**

Alasan:
- **Prevents free-riding** — SaaS yang fork Postara wajib open source modifikasi mereka.
- **Allows commercial use** — user bisa pakai untuk bisnis tanpa share kode internal.
- **Compatible** dengan semua dependency (Laravel = MIT, semua package mainstream = MIT).
- **Standar** untuk ESP open source modern.

Alternatif:
- **MIT** — terlalu permissive, risiko Big Tech fork tanpa kontribusi balik.
- **BSL** — bukan OSI-approved open source, kontroversial di komunitas.

CLA vs DCO untuk kontributor: belum diputuskan (lihat open questions).

---

## 15. Open Questions

1. **Final naming** — Postara? Suratin? MailKita? Nama lain?
2. **CLA atau DCO?** — CLA protective tapi friction tinggi. DCO ringan tapi tidak izinkan relicensing.
3. **Frontend final: Livewire atau Inertia?** — Rekomendasi PRD: Livewire. Konfirmasi?
4. **Filament untuk super-admin?** — Worth it untuk panel internal, atau bikin custom?
5. **Hosting demo** — public demo instance di landing page? Risiko abuse vs adoption.
6. **Brand & landing page** — situs terpisah (postara.dev?) atau cukup GitHub README + docs?
7. **i18n awal** — English + Bahasa Indonesia dari awal, atau English saja dulu?
8. **Funding model** — donations, managed cloud, enterprise support?

---

## 16. Risks

| Risk | Mitigation |
|---|---|
| Kompetisi dari Listmonk/Postal/dll | Differensiasi: transactional + marketing dalam 1 produk, Coolify-first UX, Laravel stack untuk komunitas PHP |
| IP reputation jelek di SMTP | Default SES relay; dokumentasi DNS step-by-step |
| Project ditinggalkan | Build community sejak awal; sponsor dari hyuu.dev |
| User pakai untuk spam | Built-in suppression list; T&C jelas di self-host |
| Coolify ditolak include | Project tetap deployable via Docker Compose manual |
| Laravel major upgrade breaking | Pin version, ikuti upgrade guide Laravel resmi |
| PHP performance vs Node/Go untuk volume tinggi | FrankenPHP + Octane untuk performance boost; horizontal scale via worker container terpisah |
| Lisensi konflik | Dependency audit di CI |