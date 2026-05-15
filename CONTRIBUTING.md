# Contributing to Postara

Thanks for taking the time to contribute. Here's everything you need to get started.

## Development setup

```bash
git clone https://github.com/hwsdev/postara.git
cd postara

composer install
npm install

cp .env.example .env
php artisan key:generate

# Edit .env — set DB_* to your local Postgres (or leave DB_CONNECTION=sqlite for quick start)
php artisan migrate

composer dev   # starts server + queue + vite concurrently
```

Open `http://localhost:8000/setup` to run the setup wizard.

## Code standards

- **PHP style** — [Laravel Pint](https://laravel.com/docs/pint) (opinionated, PSR-12 based). Run `./vendor/bin/pint` before committing.
- **Tests** — [Pest](https://pestphp.com). Add tests for new features and bug fixes.
- **Commits** — [Conventional Commits](https://www.conventionalcommits.org): `feat:`, `fix:`, `docs:`, `chore:`, etc.
- **Branches** — `feat/your-feature`, `fix/issue-description`

## Running tests

```bash
php artisan test
php artisan test --parallel   # faster
php artisan test --filter=SomeTest
```

## Pull request process

1. Fork the repo and create a branch from `main`
2. Make your changes — keep PRs focused on a single concern
3. Run `./vendor/bin/pint` and `php artisan test`
4. Open a PR with a clear description of what changed and why
5. Link any related issues

## What we're looking for

- Bug fixes with a failing test that proves the fix
- Features from the [roadmap](README.md#roadmap)
- Documentation improvements
- Performance improvements with benchmarks

## What we're not looking for (right now)

- Breaking API changes without prior discussion
- New dependencies without justification
- Features outside the project scope (inbox/IMAP, SMS, etc.)

## Code of conduct

Be respectful. See [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md).
