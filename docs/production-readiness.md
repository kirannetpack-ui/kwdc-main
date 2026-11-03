# KTM-WDC Production Readiness

This checklist tracks what must be true before KTM-WDC is safe to launch for real users.

## Must Fix Before Launch

- Rotate the exposed OpenAI API key from the earlier screenshot and replace it only in the server environment.
- Set production environment values:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_URL=https://your-real-domain`
  - `LOG_LEVEL=warning`
  - `SESSION_SECURE_COOKIE=true`
  - `SESSION_ENCRYPT=true`
  - `SESSION_SAME_SITE=lax` or stricter if the payment flow allows it
- Generate and store a unique production `APP_KEY`. Never use the placeholder/local key.
- Use `.env.production.example` as the deployment template, then set every blank value in the hosting provider's secret store. Do not run `php artisan key:generate` during each deployment.
- Configure a real database with backups. SQLite is fine for local work, but production should use managed MySQL/Postgres unless traffic will stay very small.
- Configure real email delivery. Current local defaults use log mail, so activation, reset, invoice, and notification email will not reach users.
- Configure payment credentials and callback URLs for Khalti/eSewa in live mode.
- Decide the AI provider for production. If using `free`, no external AI key is needed. If using OpenAI/Gemini/Groq, set the key only in production secrets.
- Build frontend assets for production with `npm run build`; do not expose the Vite dev server to public traffic.
- Run migrations on the production database and seed only intentional production data.
- Set up a queue worker for notifications, reminders, invoices, and any background jobs. The `Procfile` includes a `worker` process for platforms that support multiple process types.
- Set up a scheduler/cron entry for Laravel scheduled tasks. The `Procfile` includes a `scheduler` process; on VPS hosting, use cron to run `php artisan schedule:run` every minute.
- Configure storage for uploaded warehouse documents, proofs, images, and QR assets. Use durable object storage if deploying to ephemeral servers.
- Keep sensitive verification documents private. Warehouse ownership/tax/fire/building documents and security-agency certificates are stored on `private_uploads` and served only through the authenticated `documents.private.show` route.
- Add monitoring for errors, failed jobs, uptime, and disk/storage usage.
- Confirm role access for every user type: admin, client, driver, equipment owner, property owner, and security agency.

## Security Checks

- Confirm no secrets are committed to git or visible in screenshots.
- Confirm debug/test routes remain disabled in production.
- Confirm uploaded files are validated, stored outside public code paths, and served only when authorized.
- Confirm generated artifacts are not tracked or deployed from archive folders, especially SQL backups, cookies, login captures, and archived `node_modules`.
- Confirm admin-only routes require admin middleware.
- Confirm payment callbacks verify provider signatures or lookup responses before marking invoices paid.
- Confirm password reset, activation, and registration emails are sent through a trusted mail provider.
- Review mass assignment on admin update endpoints before allowing staff to edit users, drivers, equipment, and warehouses.
- Confirm security headers are present on HTTPS responses and that production HTTP traffic redirects to HTTPS.

## Deployment Notes

- Railway/Nixpacks: the app builds composer dependencies, installs Node dependencies, runs `npm run build`, and starts through `scripts/deploy/start-production.sh`.
- The production start script requires `APP_KEY` to already exist, runs `storage:link`, applies migrations with `--force`, clears stale optimization files, then caches config, routes, and views.
- Docker/Apache: the Dockerfile serves only the Laravel `public` directory, enables rewrite/header modules, installs PHP database extensions, and builds assets at image build time.
- Real production deployments still need separately configured worker and scheduler processes; the web process should not be the only long-running process once email, reminders, invoices, or notifications are live.

## Performance Checks

- Run `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache` during deployment.
- Use a production web server such as Nginx/Apache/Caddy with PHP-FPM. Do not use `php artisan serve` for production.
- Use Redis or a managed cache if traffic grows beyond small internal use.
- Paginate large admin lists instead of loading every record at once.
- Move expensive AI, email, PDF, and notification work to queues.

## Current Local Findings

- Local app mode is `local` with debug enabled.
- Local database is SQLite.
- Backend tests pass locally with `composer test` / `vendor/bin/phpunit`.
- `npm audit --audit-level=moderate` passes after upgrading Vite, the Laravel Vite plugin, and Sass.
- `php tools/composer.phar audit` reports no known security vulnerability advisories after upgrading to Laravel 12 and patched HTTP/PDF/Markdown dependencies.
- `php artisan test` is not available in this Laravel 12 dependency set, so use `composer test` for the standard local backend test run.
