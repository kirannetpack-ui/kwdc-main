# KWDC on Vercel

- Repository: https://github.com/kirannetpack-ui/kwdc-main
- Site: https://kwdc.vercel.app
- Project: https://vercel.com/kiran-thapa-s-projects/kwdc
- Database: dedicated Neon PostgreSQL resource `kwdc-db`, free plan, Singapore.

The PHP application runs through the community `vercel-php@0.7.4` runtime (PHP 8.3). Vite builds assets into `public/build`. The old phase-one report is optional via `npm run build:report` and is not the deployed application.

## Launch state

The current deployment is an evaluation demo with `PHASE_ONE_DEMO=true` and `APP_LAUNCH_READY=true`. See [demo testing](demo-testing.md). The requirements below apply to a real business launch.

`APP_LAUNCH_READY=false` leaves public information pages accessible and returns a friendly HTTP 503 for account access and business operations. Use this protection outside the evaluation demo until the following are complete:

1. Initial migrations have been applied to the dedicated database. Do not seed demo accounts into a live business database.
2. Connect transactional email using a verified sender and store SMTP credentials in Vercel. `MAIL_MAILER=log` does not deliver email.
3. Connect persistent object storage and migrate both `public` and `private_uploads` disks. Several profile uploads still write directly to `public/uploads` and need conversion before enabling uploads on Vercel. Private files must remain behind the existing authorized download controller.
4. Configure payment merchant credentials and dedicated routing/geocoding services, then run `php artisan app:production-preflight`.
5. Host queue workers, the every-minute scheduler, and Reverb on a persistent worker service if these features are enabled. Vercel functions do not run these persistent processes.
6. Create the initial administrator securely, complete login/email/document/payment checks, and set `APP_LAUNCH_READY=true` in Vercel before redeploying.

The project uses encrypted secure database sessions and a database cache, preserving shared rate limits across function instances. The evaluation deployment uses Neon's direct connection with native prepared statements (`DB_USE_DIRECT=true`, `DB_POOLED=false`); this avoids incompatible boolean binding with emulated prepares in Laravel 12. Schema changes also use the direct connection. The function runs in Singapore alongside the database.

## Deployment

Use `vercel deploy --prod --scope kiran-thapa-s-projects` from the checkout, or push to the connected GitHub main branch. Environment changes require a new deployment. Secrets belong in Vercel settings, never Git.

For future schema changes, explicitly set `VERCEL_RUN_MIGRATIONS=true` for the production deployment and restore it to `false` afterward. The build hook uses `DATABASE_URL_UNPOOLED` and does not seed accounts. Initial schema creation exceeded Composer's default five-minute script timeout, so the migration hook disables that timeout. Vercel's build limit still applies.

Temporary view, font and framework caches use the function's writable temporary directory. Never use that directory for business records or uploads. The production application key is generated once and stored in the project's encrypted environment.
