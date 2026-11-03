# KTM-WDC Phase One Completion Report

Date: September 9, 2026

## Scope

The local Laravel project was upgraded toward a phase-one production-ready website and portal. The work focused on design polish, navigation clarity, authentication safety, email behavior, map-backed location flows, equipment-request workflows, and release checks.

## User-Facing Design

- Rebuilt the public homepage as a proper KTM-WDC company website instead of sending visitors straight to login.
- Added a custom logistics campus hero image at `public/images/logistics-campus.png`.
- Reduced homepage copy and separated the main company, services, partner roles, and account entry sections.
- Restyled login, register, activation, error, dashboard, dispatch, pickup, warehouse, and portal pages with cleaner spacing, rounded surfaces, better contrast, and more modern form controls.
- Replaced bulky explanatory text with shorter task-focused labels across key screens.
- Fixed the stale Vite hot-file issue that caused missing Tailwind utility styles and broken dashboard layout.
- Built production frontend assets with Vite.

## Authentication And Account Safety

- Added `account.ready` middleware to block inactive or unverified users from protected portal areas.
- Prevented users from bypassing email activation by directly opening dashboard, pickup, dispatch, profile, or notifications pages.
- Added registration throttling.
- Fixed registration validation around optional role-specific fields.
- Preserved activation-code integrity when email sending fails.
- Removed demo-code disclosure from activation responses.
- Prevented disabled accounts from using an activation code.
- Regenerated the session after successful activation login.

## Email

- Configured local `.env` for Gmail SMTP using the supplied Gmail address.
- Added `MAIL_SCHEME` support in `config/mail.php`.
- Made activation email delivery fail safely without crashing registration or claiming a code was sent.
- Verified that the current Gmail app password is rejected by Google SMTP with `535 5.7.8 Username and Password not accepted`.
- Current email blocker: replace the local `MAIL_PASSWORD` with a newly generated Gmail app password, then retest SMTP.

## Maps And Location Workflows

- Added `MapController` and authenticated endpoints for geocoding, reverse geocoding, and route distance.
- Added cached server-side calls for location search and OSRM-style road distance.
- Added browser-side `public/js/kwdc-maps.js` with request queueing, timeout handling, and response reuse.
- Replaced direct public Nominatim calls in major forms with local `/maps/*` endpoints.
- Dispatch and pickup route pricing now uses mapped road distance instead of random distance.
- Location failures now show clear unavailable states instead of fake markers or zero-price success.
- Added production preflight checks so live deployment cannot silently use public demo map endpoints.

## Equipment Requests

- Rebuilt the equipment request controller around the actual database schema.
- Added scoped request listing for clients, admins, and equipment owners.
- Added request index, detail, and payment-history views.
- Added safe creation, cancellation, approval, rejection, fulfillment, and return flows.
- Added approval-time equipment assignment so a request cannot become approved without a real available machine.
- Equipment fulfillment now marks assigned equipment as rented; return marks it available again.
- Rejected executable file uploads for equipment documents.

## Security And Data Honesty

- Removed fabricated occupancy values and fake warehouse analytics fallbacks.
- Removed stale mock/random distance behavior from dispatch, pickup, and equipment map flows.
- Replaced several raw `innerHTML` UI writes with text-safe DOM writes.
- Improved production preflight for app, session, mail, payment, and map settings.
- Confirmed `composer audit` reports no PHP package advisories.
- Confirmed `npm audit --audit-level=moderate` reports zero vulnerabilities.

## Verification

- `npm run build` passed.
- `php tools/composer.phar audit` passed with no advisories.
- `npm audit --audit-level=moderate` passed with zero vulnerabilities.
- `php tools/composer.phar test` passed: 181 tests, 796 assertions, 1 skipped.
- Browser checked:
  - Homepage desktop/mobile rendering.
  - Login flow into local demo client account.
  - Dashboard styling and spacing.
  - Dispatch page map rendering and form layout.

## Not Yet Launch-Complete

- Gmail SMTP still needs a new valid Gmail app password.
- Production database and environment variables still need to be set before deployment.
- Production map provider endpoints must be configured before live launch.
- Payment provider credentials must be real before payment workflows can be considered production-ready.
- The current work is local only; no live deployment was performed in this phase because the latest instruction was to keep everything local for now.

