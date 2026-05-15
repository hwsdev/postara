<div align="center">

# Postara

**Open source, self-hosted email service platform.**

Transactional API + marketing campaigns in one product — deploy on any VPS in minutes.

[![License: AGPL-3.0](https://img.shields.io/badge/license-AGPL--3.0-black?style=flat-square)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-13-black?style=flat-square)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-black?style=flat-square)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-ready-black?style=flat-square)](docker-compose.yml)

</div>

---

## What is Postara?

Postara fills the gap between transactional email APIs (Resend, Mailersend) and marketing platforms (Listmonk, Brevo) — combining both in a single self-hosted product built on **Laravel 13**.

- **Developer API** — send transactional emails via REST, compatible with the Resend API shape
- **Campaign builder** — drag-drop email builder, contact lists, scheduled broadcasts
- **Full tracking** — open, click, bounce, complaint events with webhook delivery
- **DKIM signing** — per-domain RSA-2048 keypairs, SPF/DMARC guidance
- **Cloudflare integration** — auto-provision DNS records via Cloudflare API
- **Coolify-ready** — one-click deploy via Coolify or plain `docker compose up`

---

## Quick start

### Option A — Coolify (recommended)

1. In Coolify, add a new service → paste the `docker-compose.yml` from this repo
2. Set the required environment variables (see [Environment variables](#environment-variables))
3. Deploy — Coolify handles HTTPS via Traefik automatically
4. Open your app URL → the setup wizard runs on first visit

### Option B — Plain Docker Compose (FrankenPHP + Caddy)

```bash
# Clone
git clone https://github.com/hwsdev/postara.git
cd postara

# Copy env and generate key
cp .env.example .env
# Edit .env — set APP_KEY, POSTGRES_PASSWORD at minimum

# Start
docker compose -f docker-compose.standalone.yml up -d

# Run migrations
docker compose -f docker-compose.standalone.yml exec app php artisan migrate --force
```

### Option C — Nginx + PHP-FPM

```bash
git clone https://github.com/hwsdev/postara.git
cd postara

cp .env.example .env
# Edit .env — set APP_KEY, POSTGRES_PASSWORD, APP_URL

docker compose -f docker-compose.nginx.yml up -d
docker compose -f docker-compose.nginx.yml exec app php artisan migrate --force
```

Nginx config is at `docker/nginx/default.conf`. PHP-FPM config at `docker/php-fpm/`.

Open `http://localhost` → setup wizard.

### Option D — Local development

```bash
git clone https://github.com/hwsdev/postara.git
cd postara

composer install
cp .env.example .env
php artisan key:generate

# Edit .env — set DB_* to your local Postgres
php artisan migrate

npm install
npm run dev

php artisan serve
```

Open `http://localhost:8000/setup` → setup wizard.

---

## Setup wizard

On first visit, Postara redirects every request to `/setup`. The wizard cannot be skipped.

**Step 1 — App config**
- App name, public URL, timezone
- URL is used for tracking pixel links and unsubscribe URLs

**Step 2 — Mail transport**

| Option | Description |
|---|---|
| **Self-hosted (Postfix)** | Connect to Postfix on your VPS. No external provider. Requires open port 25 + PTR record. |
| **SMTP relay** | Any SMTP provider — Brevo, Mailgun, SES, Postmark, Resend. Quick-fill presets included. |
| **MailChannels API** | HTTP API, no SMTP needed. Free 100 emails/day. Works great with Cloudflare DNS. |
| **Log only** | Dev/testing — emails written to `storage/logs/laravel.log`, not sent. |

**Step 3 — Account**
- Creates the first user + workspace
- This is the only way to create an account — there is no public registration

---

## API reference

All API endpoints are under `/api/v1`. Authenticate with a Bearer token (API key generated in the dashboard).

### Send an email

```http
POST /api/v1/emails
Authorization: Bearer pk_your_api_key
Content-Type: application/json
Idempotency-Key: optional-unique-key

{
  "from": "noreply@mail.yourdomain.com",
  "to": ["user@example.com"],
  "subject": "Your order has shipped",
  "html": "<p>Hi there...</p>",
  "text": "Hi there...",
  "tags": ["order", "shipping"],
  "headers": { "X-Order-ID": "12345" }
}
```

**Response**
```json
{
  "id": "em_42",
  "status": "queued"
}
```

### Send with a template

```http
POST /api/v1/emails
Authorization: Bearer pk_your_api_key

{
  "from": "noreply@mail.yourdomain.com",
  "to": ["user@example.com"],
  "template_id": "tpl_welcome",
  "variables": {
    "name": "Andi",
    "order_id": "12345"
  }
}
```

### Get email status

```http
GET /api/v1/emails/em_42
Authorization: Bearer pk_your_api_key
```

### List emails

```http
GET /api/v1/emails?page=1
Authorization: Bearer pk_your_api_key
```

---

### Domains

```http
# Add a domain
POST /api/v1/domains
{ "domain": "mail.yourdomain.com" }

# Verify DNS records
POST /api/v1/domains/{id}/verify

# List domains
GET /api/v1/domains

# Delete domain
DELETE /api/v1/domains/{id}
```

Adding a domain returns the required DNS records (SPF, DKIM, DMARC). If Cloudflare is configured, records can be provisioned automatically from the dashboard.

---

### Contacts

```http
# Create or update a contact
POST /api/v1/contacts
{
  "email": "user@example.com",
  "name": "Andi",
  "tags": ["vip"],
  "custom_fields": { "plan": "pro" }
}

# List contacts
GET /api/v1/contacts

# Delete contact
DELETE /api/v1/contacts/{id}
```

---

### Webhooks

```http
# Create webhook
POST /api/v1/webhooks
{
  "name": "My endpoint",
  "url": "https://your-app.com/webhooks/postara",
  "events": ["email.delivered", "email.bounced", "email.opened", "email.clicked", "email.complained"]
}

# List webhooks
GET /api/v1/webhooks

# Update webhook
PATCH /api/v1/webhooks/{id}
{ "active": false }

# Rotate signing secret
POST /api/v1/webhooks/{id}/rotate-secret

# Delete webhook
DELETE /api/v1/webhooks/{id}
```

**Webhook payload**

```json
{
  "event": "email.delivered",
  "created_at": "2026-05-15T10:00:00+00:00",
  "data": {
    "email_id": 42,
    "message_id": "uuid@postara",
    "from": "noreply@mail.yourdomain.com",
    "to": ["user@example.com"],
    "subject": "Your order has shipped",
    "status": "delivered",
    "tags": ["order"]
  }
}
```

**Verifying webhook signatures**

Every request includes an `X-Postara-Signature` header:

```
X-Postara-Signature: sha256=<hmac-sha256-hex>
```

Verify in PHP:
```php
$payload = file_get_contents('php://input');
$expected = 'sha256=' . hash_hmac('sha256', $payload, $webhookSecret);
$valid = hash_equals($expected, $_SERVER['HTTP_X_POSTARA_SIGNATURE']);
```

---

## Webhook events

| Event | Triggered when |
|---|---|
| `email.delivered` | SMTP accepted the message |
| `email.opened` | Recipient loaded the tracking pixel |
| `email.clicked` | Recipient clicked a tracked link |
| `email.bounced` | Hard or soft bounce received |
| `email.complained` | Spam complaint received |

---

## Dashboard

| Page | Description |
|---|---|
| **Overview** | Sent today, sent this month, delivery rate, active campaigns |
| **Campaigns** | Create, schedule, and send broadcast campaigns |
| **Contacts** | Manage contacts, import via CSV, unsubscribe |
| **Domains** | Add sending domains, verify DNS, auto-provision via Cloudflare |
| **API Keys** | Generate and revoke API keys |
| **Webhooks** | Configure webhook endpoints and rotate secrets |
| **Suppression** | View and manage suppressed email addresses |

---

## Sending domains

Before sending, add and verify a domain:

1. Go to **Domains** → **Add domain**
2. Add the displayed DNS records to your DNS provider:
   - `TXT` — SPF record on your domain
   - `TXT` — DKIM record at `postara._domainkey.yourdomain.com`
   - `TXT` — DMARC record at `_dmarc.yourdomain.com`
3. Click **Verify** — Postara checks via Cloudflare DoH

**Cloudflare auto-provision**

If you set a Cloudflare API token (in setup wizard or settings), the **Add to Cloudflare** button appears in the DNS records modal and provisions all three records automatically.

Required token permissions: `Zone:DNS:Edit` + `Zone:Zone:Read`

---

## MailChannels transport

MailChannels is an HTTP-based email delivery API (not SMTP). Free plan: 100 emails/day.

**Setup:**
1. Sign up at [mailchannels.com](https://www.mailchannels.com)
2. Get your API key from the dashboard
3. Add a Domain Lockdown TXT record to your sending domain:
   - Host: `_mailchannels.yourdomain.com`
   - Value: `v=mc1 auth=your_account_id`
4. Enter the API key in the setup wizard or settings

---

## Environment variables

Postara stores its configuration in the database (set via the setup wizard). The following variables are needed at container start — before the wizard runs.

| Variable | Required | Description |
|---|---|---|
| `APP_KEY` | ✅ | Laravel app key — generate with `php artisan key:generate` |
| `APP_URL` | ✅ | Public URL of your instance, e.g. `https://mail.yourdomain.com` |
| `DB_CONNECTION` | ✅ | `pgsql` (recommended) or `sqlite` |
| `DB_HOST` | ✅ | Postgres host |
| `DB_DATABASE` | ✅ | Database name |
| `DB_USERNAME` | ✅ | Database user |
| `DB_PASSWORD` | ✅ | Database password |
| `REDIS_HOST` | ✅ | Redis host |
| `QUEUE_CONNECTION` | — | `redis` (production) or `database` (dev) |
| `CACHE_STORE` | — | `redis` (production) or `database` (dev) |
| `SESSION_DRIVER` | — | `redis` (production) or `database` (dev) |

Mail, Cloudflare, and MailChannels settings are configured via the setup wizard and stored in the `settings` table — no need to set them in `.env`.

---

## Architecture

```
Internet
    │
┌───▼────┐
│Traefik │  (Coolify managed, auto HTTPS)
└───┬────┘
    │
┌───▼────┐
│  app   │  FrankenPHP — serves web + API
└───┬────┘
    │
    ├── postgres:16
    ├── redis:7
    ├── queue-worker  (php artisan queue:work)
    └── scheduler     (php artisan schedule:work)
```

Three containers share the same Docker image, differentiated by their start command:

| Container | Command |
|---|---|
| `app` | `frankenphp run` |
| `queue-worker` | `php artisan queue:work --tries=3` |
| `scheduler` | `php artisan schedule:work` |

---

## Tech stack

| Layer | Choice |
|---|---|
| Language | PHP 8.3+ |
| Framework | Laravel 13 |
| Web server | FrankenPHP |
| Frontend | Livewire 3 + Alpine.js + Tailwind CSS v4 |
| Database | PostgreSQL 16 |
| Queue / Cache | Redis 7 |
| Auth | Laravel Sanctum (API keys) |
| Container | Docker + Docker Compose |
| CI | GitHub Actions (Pest, Pint, Docker build) |

---

## Development

```bash
# Install dependencies
composer install
npm install

# Setup
cp .env.example .env
php artisan key:generate
php artisan migrate

# Run (all in one)
composer dev
```

`composer dev` starts the Laravel server, queue worker, log watcher, and Vite dev server concurrently.

**Code style**

```bash
./vendor/bin/pint          # auto-fix
./vendor/bin/pint --test   # check only (used in CI)
```

**Tests**

```bash
php artisan test
php artisan test --parallel
```

**Reset setup wizard** (for local testing)

```bash
php artisan tinker --execute="DB::table('settings')->where('key','setup_completed')->delete(); DB::table('users')->truncate(); DB::table('workspaces')->truncate();"
```

---

## Project structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/          # REST API controllers
│   │   └── Dashboard/       # Web controllers
│   ├── Middleware/
│   │   ├── AuthenticateApiKey.php
│   │   └── RequireSetup.php
│   └── Requests/Api/        # Form request validation
├── Jobs/
│   ├── SendEmailJob.php
│   ├── DeliverWebhookJob.php
│   └── ProcessCampaignJob.php
├── Livewire/
│   ├── Setup/SetupWizard.php
│   ├── Domains/DomainList.php
│   ├── Campaigns/
│   ├── Contacts/
│   ├── ApiKeys/
│   └── Webhooks/
├── Models/                  # Eloquent models
├── Services/
│   ├── EmailService.php
│   ├── DkimService.php
│   ├── DomainVerifier.php
│   ├── TrackingService.php
│   ├── WebhookService.php
│   ├── CloudflareDnsService.php
│   └── MailChannelsTransport.php
└── Tenant/
    ├── BelongsToWorkspace.php
    └── WorkspaceScope.php
```

---

## Roadmap

- [x] Transactional email API
- [x] DKIM signing + domain verification
- [x] Open / click tracking
- [x] Webhook delivery with HMAC signing
- [x] Contact list + CSV import
- [x] Campaign creation + scheduled send
- [x] Suppression list
- [x] Setup wizard (no `.env` config needed)
- [x] Cloudflare DNS auto-provision
- [x] MailChannels transport
- [ ] Template builder (GrapesJS + MJML)
- [ ] Analytics dashboard with charts
- [ ] Magic link login
- [ ] Advanced contact segmentation
- [ ] A/B testing (v1.2)
- [ ] Drip / automation workflows (v1.2)
- [ ] AI subject line assist (v1.3)

---

## Contributing

Contributions are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a PR.

- **Bug reports** → [GitHub Issues](https://github.com/hwsdev/postara/issues)
- **Feature requests** → [GitHub Discussions](https://github.com/hwsdev/postara/discussions)
- **Security issues** → see [SECURITY.md](SECURITY.md)

---

## License

[AGPL-3.0](LICENSE) — you can use Postara commercially, but modifications must be open-sourced under the same license.

---

<div align="center">
Built by <a href="https://github.com/hwsdev">hwsdev</a>
</div>
