# Security Policy

## Supported versions

| Version | Supported |
|---|---|
| latest (`main`) | ✅ |
| older releases | security patches only |

## Reporting a vulnerability

**Do not open a public GitHub issue for security vulnerabilities.**

Email: **security@hyuu.dev**

Include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Any suggested fix (optional)

You'll receive an acknowledgement within 48 hours. We aim to release a fix within 14 days for critical issues.

## Scope

In scope:
- Authentication bypass
- API key exposure
- SQL injection
- XSS in dashboard
- SSRF in webhook delivery or DNS verification
- Privilege escalation between workspaces

Out of scope:
- Issues requiring physical access to the server
- Social engineering
- Denial of service via resource exhaustion (rate limiting is configurable)
- Issues in third-party dependencies (report to them directly)

## Security design notes

- API keys are stored as bcrypt hashes — plain text is shown only once at creation
- Webhook payloads are signed with HMAC-SHA256
- Tracking URLs use Laravel signed URLs (tamper-proof)
- All user input goes through Form Request validation
- Blade auto-escapes output — no raw `{!! !!}` in user-facing views
- CSRF protection on all dashboard forms
- Multi-tenant isolation via global Eloquent scope on `workspace_id`
