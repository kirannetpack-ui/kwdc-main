import { mkdirSync, writeFileSync } from 'node:fs';

mkdirSync('dist', { recursive: true });

const completedItems = [
  'Configured production deployment guardrails and preflight checks for required environment, mail, session, HTTPS, database, and payment settings.',
  'Removed inactive authentication scaffolding and kept one canonical password reset flow.',
  'Added professional security emails for password reset and verification-style flows, including clear anti-phishing guidance.',
  'Hardened payment provider sessions for Khalti and eSewa with configuration guards, timeouts, retries, amount validation, malformed-response handling, UUID transaction IDs, idempotent callbacks, and receipt generation fixes.',
  'Restored invoice PDF generation with a production-ready invoice PDF template and tested invoice creation for payable records.',
  'Protected dispatch PDF downloads, warehouse PDFs, dispatch stop invoice documents, stock documents, equipment compliance documents, warehouse request documents, box documents, and driver vehicle compliance documents behind private authorization routes.',
  'Hardened dispatch ownership and tracking by preventing client spoofing, requiring owners/drivers/admins for actions, and using tokenized public tracking links.',
  'Scoped security agency resources so agencies cannot modify another agency account or records.',
  'Validated admin update payloads to prevent mass assignment of protected fields.',
  'Added regression tests across security, payments, invoices, dispatch, documents, authentication, and production hardening.',
  'Verified the current phase with PHPUnit, Composer audit/validation, npm audit, Vite build, and whitespace checks.',
];

const html = `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>KTM-WDC Phase 1 Published</title>
  <style>
    :root {
      color-scheme: light;
      --ink: #18212f;
      --muted: #5c6675;
      --line: #d7dde6;
      --brand: #b91c1c;
      --brand-dark: #8f1515;
      --panel: #ffffff;
      --soft: #f4f6f8;
      --ok: #176b45;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      background: var(--soft);
      color: var(--ink);
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      line-height: 1.5;
    }
    header {
      background: linear-gradient(135deg, #991b1b 0%, #273142 100%);
      color: #fff;
      padding: 56px 24px 42px;
    }
    main {
      margin: 0 auto;
      max-width: 1120px;
      padding: 28px 24px 56px;
    }
    .wrap { margin: 0 auto; max-width: 1120px; }
    .eyebrow {
      font-size: 13px;
      font-weight: 800;
      letter-spacing: .08em;
      text-transform: uppercase;
      opacity: .9;
    }
    h1 {
      font-size: clamp(34px, 6vw, 62px);
      line-height: 1.02;
      margin: 14px 0 16px;
      max-width: 900px;
    }
    .lead {
      font-size: 18px;
      max-width: 840px;
      opacity: .92;
    }
    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 28px;
    }
    a.button {
      align-items: center;
      border: 1px solid rgba(255,255,255,.35);
      color: #fff;
      display: inline-flex;
      font-weight: 800;
      justify-content: center;
      min-height: 44px;
      padding: 10px 16px;
      text-decoration: none;
    }
    a.button.primary {
      background: #fff;
      border-color: #fff;
      color: var(--brand-dark);
    }
    .grid {
      display: grid;
      gap: 18px;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      margin-top: -46px;
    }
    .panel {
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 8px;
      box-shadow: 0 14px 38px rgba(15, 23, 42, .08);
      padding: 22px;
    }
    .metric {
      color: var(--brand);
      font-size: 30px;
      font-weight: 900;
      line-height: 1;
    }
    .label {
      color: var(--muted);
      font-size: 13px;
      font-weight: 700;
      margin-top: 8px;
      text-transform: uppercase;
    }
    section {
      margin-top: 26px;
    }
    h2 {
      font-size: 24px;
      margin: 0 0 14px;
    }
    ul {
      margin: 0;
      padding-left: 20px;
    }
    li {
      margin: 9px 0;
    }
    .status {
      border-left: 5px solid var(--ok);
    }
    .note {
      color: var(--muted);
      font-size: 14px;
    }
    code {
      background: #eef1f5;
      border: 1px solid #dde3eb;
      border-radius: 4px;
      padding: 2px 5px;
    }
    footer {
      color: var(--muted);
      font-size: 13px;
      padding: 18px 24px 36px;
      text-align: center;
    }
    @media (max-width: 820px) {
      .grid { grid-template-columns: 1fr; margin-top: 20px; }
      header { padding-top: 40px; }
    }
  </style>
</head>
<body>
  <header>
    <div class="wrap">
      <div class="eyebrow">Phase 1 Published</div>
      <h1>KTM Warehouse & Distribution Center</h1>
      <p class="lead">A public phase-1 publication page for the Laravel logistics platform, showing the security, payment, email, document privacy, and production-readiness work completed so far.</p>
      <div class="actions">
        <a class="button primary" href="https://github.com/kirannetpack-ui/kwdc">View GitHub Repository</a>
        <a class="button" href="https://github.com/kirannetpack-ui/kwdc/commits/production-readiness-reminders-notifications">View Phase 1 Commits</a>
      </div>
    </div>
  </header>
  <main>
    <div class="grid">
      <div class="panel">
        <div class="metric">160</div>
        <div class="label">PHPUnit tests passing</div>
      </div>
      <div class="panel">
        <div class="metric">0</div>
        <div class="label">npm audit vulnerabilities</div>
      </div>
      <div class="panel">
        <div class="metric">Phase 1</div>
        <div class="label">Published status</div>
      </div>
    </div>
    <section class="panel status">
      <h2>Current Status</h2>
      <p>The project source is committed and pushed to GitHub on branch <code>production-readiness-reminders-notifications</code>. This phase-1 public page has been generated from the same repository so stakeholders can review progress immediately.</p>
      <p class="note">The full application is a Laravel/PHP platform and still needs a Laravel-capable production host, database, queue worker, scheduler, mail provider, storage, and payment provider credentials before real customer traffic should use it end to end.</p>
    </section>
    <section class="panel">
      <h2>Completed Work</h2>
      <ul>
        ${completedItems.map((item) => `<li>${item}</li>`).join('\n        ')}
      </ul>
    </section>
    <section class="panel">
      <h2>Verification</h2>
      <ul>
        <li><code>php tools/composer.phar test</code> passed: 160 tests, 675 assertions, 1 skipped.</li>
        <li><code>php tools/composer.phar audit</code> found no security vulnerability advisories.</li>
        <li><code>php tools/composer.phar validate --strict --no-interaction</code> passed.</li>
        <li><code>npm audit --audit-level=moderate</code> found 0 vulnerabilities.</li>
        <li><code>npm run build</code> completed successfully.</li>
        <li><code>git diff --check</code> passed.</li>
      </ul>
    </section>
  </main>
  <footer>
    KTM-WDC Phase 1 publication. Generated from the production-readiness branch.
  </footer>
</body>
</html>`;

writeFileSync('dist/index.html', html);
writeFileSync(
  'dist/index.mjs',
  `const html = ${JSON.stringify(html)};

export default {
  async fetch() {
    return new Response(html, {
      headers: {
        'content-type': 'text/html; charset=utf-8',
        'cache-control': 'public, max-age=300',
      },
    });
  },
};
`
);
