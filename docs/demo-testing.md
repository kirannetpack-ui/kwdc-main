# KWDC evaluation demo

Sign in at https://kwdc.vercel.app/login. All accounts below use the shared demo password `KwdcNew2026!`.

| Role | Email |
| --- | --- |
| Administrator | admin.access@kwdc.test |
| Client | client.access@kwdc.test |
| Driver | driver.access@kwdc.test |
| Warehouse owner | property.access@kwdc.test |
| Equipment owner | equipment.access@kwdc.test |
| Security agency | security.access@kwdc.test |

Start with the client dashboard and inspect warehouse listings, requests, dispatches, stock, invoices, and reminders. Sign out before switching roles. Use the administrator account to review operations, the driver account for assigned jobs, and the owner and agency accounts to inspect their respective workflows. Create a reminder or a text-only sample request to evaluate changes in the stored data.

This is a shared public demo. Records are fictional and changes are visible to anyone using these sample accounts. Do not enter real customer details, passwords, identity documents, tax information, bank account details, or merchant QR codes. Do not change the shared demo passwords.

## Deliberate evaluation limits

- Real payment initiation and callbacks are blocked. At `/payment`, a client can **Create sample unpaid invoice**, then **Simulate bank transfer** to record a pending fictional transaction. An administrator can approve or reject it under **Bank / QR Transfers**, allowing reconciliation to be evaluated without moving money. No real bank details or merchant QR codes are displayed.
- Email uses an in-memory mailer; SMS uses the logging driver. Outgoing delivery is not tested or enabled.
- Registration and password recovery are blocked. Use the supplied sample accounts.
- Uploads are blocked until durable public/private file storage is configured.
- Background workers, reminder delivery and live tracking infrastructure are not running. The demo is for manual workflow evaluation, not business operations.

## Deployment controls

Demo mode uses `PHASE_ONE_DEMO=true` and `APP_LAUNCH_READY=true`. Initial sample preparation uses the opt-in build flag `KWDC_SEED_DEMO=true`, then that flag must be restored to `false` so later deploys preserve evaluation edits. Seeding refuses databases containing accounts outside `@kwdc.test` and wraps sample preparation in a database transaction.

Before a real launch, prepare a clean production database without demo accounts, configure production services, remove demo access, and repeat end-to-end business checks. Do not simply switch off the demo flag while shared accounts remain enabled.
