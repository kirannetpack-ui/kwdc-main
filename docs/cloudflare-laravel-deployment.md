# Cloudflare + Laravel Deployment Plan

Date: August 20, 2026

## Current Decision

KTM-WDC is a Laravel/PHP application. Cloudflare Pages and Workers do not provide a normal PHP runtime for this app, so the real website should not be deployed as a Cloudflare Worker or a static Pages site.

Use Cloudflare for:

- DNS and public domain routing.
- CDN, TLS, WAF, bot protection, and rate limiting.
- Turnstile on public forms.
- R2 for private/public object storage if desired.
- Cloudflare Email Routing only for inbound routing, not transactional app mail.

Use a PHP-capable application host for Laravel:

- Railway, Fly.io, DigitalOcean App Platform, Laravel Forge/VPS, or cPanel.
- MySQL or Postgres database on the same provider or a managed database provider.
- Queue worker and scheduler process.
- Persistent private file storage.
- SMTP/transactional mail provider.

## Recommended Production Stack

- App host: Standard container or VPS host (e.g. Railway, Fly.io, Laravel Forge), because this repository already includes `nixpacks.toml`, `Procfile`, and `scripts/deploy/start-production.sh`.
- Database: managed MySQL or Postgres. Prefer MySQL if the hosting provider offers it easily, because the project already includes MySQL/PDO support in Nixpacks.
- Cloudflare: point the production domain to the app host with proxied DNS enabled.
- Mail: SMTP provider configured with `MAIL_*` environment variables.
- Payments: Khalti and eSewa production credentials configured as secrets.

## Required Environment Variables

- `APP_NAME`
- `APP_ENV=production`
- `APP_KEY`
- `APP_DEBUG=false`
- `APP_URL=https://your-production-domain`
- `DB_CONNECTION=mysql` or `pgsql`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `MAIL_MAILER=smtp`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_ENCRYPTION`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`
- `SESSION_ENCRYPT=true`
- `SESSION_SECURE_COOKIE=true`
- `QUEUE_CONNECTION=database` or a production queue backend
- `KHALTI_PUBLIC_KEY`
- `KHALTI_SECRET_KEY`
- `ESEWA_MERCHANT_CODE`
- `ESEWA_SECRET_KEY`

## Deployment Steps

1. Create a PHP/Laravel web service on your production host from GitHub repository `kirannetpack-ui/kwdc`.
2. Select branch `production-readiness-reminders-notifications`.
3. Let Nixpacks detect the project from `nixpacks.toml`.
4. Add a managed MySQL/Postgres database.
5. Configure all production environment variables.
6. Deploy the web process from `Procfile`.
7. Add worker and scheduler services from `Procfile`.
8. Confirm `php artisan app:production-preflight` passes during startup.
9. Add the final domain in Cloudflare DNS as a proxied CNAME to the app host.
10. Enable Cloudflare SSL/TLS Full Strict mode after host TLS is active.
11. Add Cloudflare WAF/rate limits for login, password reset, payment callback, and registration endpoints.
12. Run a final production smoke test: landing page, registration, email, login, password reset, payment init/verify in sandbox, document access, dispatch tracking.

## Cloudflare Agent Setup Status

The official Cloudflare agent setup instructions from `https://developers.cloudflare.com/agent-setup/prompt.md` were followed as far as this local Codex environment allowed:

- Cloudflare skills were installed/copied into `~/.agents/skills`.
- Cloudflare MCP server entries were added to `~/.codex/config.toml`.
- The bundled Codex CLI could not run `codex mcp login cloudflare` because Windows denied executing the packaged `codex.exe`.
- The current Codex session must be restarted or plugin/MCPs reloaded before those MCP servers appear as tools.
- Cloudflare OAuth/login still needs to be completed after reload.

## Important Note

Cloudflare D1 is SQLite-compatible, not MySQL. It is useful for Workers applications, but it is not the best database target for this Laravel app without a dedicated adapter and architecture changes. For this project, managed MySQL or Postgres is the safer production choice.
