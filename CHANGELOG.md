# Changelog

All notable changes to Postara are documented here.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Versioning follows [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

### Added
- Initial project scaffold — Laravel 13, Livewire 3, Tailwind CSS v4
- Setup wizard — 3-step first-run configuration (app, mail, account)
- Transactional email API (`POST /api/v1/emails`) compatible with Resend API shape
- Template support with Blade variable substitution
- DKIM RSA-2048 keypair generation per sending domain
- DNS verification via Cloudflare DoH (SPF, DKIM, DMARC)
- Cloudflare DNS auto-provision via API
- MailChannels HTTP transport (free 100 emails/day)
- Self-hosted Postfix transport option
- Open and click tracking with signed URLs
- Webhook delivery with HMAC-SHA256 signing and retry logic
- Contact list management with CSV import
- Campaign creation with scheduled send
- Suppression list (hard bounce, complaint, manual unsubscribe)
- API key management (bcrypt-hashed, revocable)
- Multi-workspace support with role-based access (owner, admin, member)
- Docker Compose for Coolify and standalone deployments
- GitHub Actions CI (Pest tests, Pint linting, Docker build on tag)

---

## [0.1.0] — TBD

First public alpha release.
