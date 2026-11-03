# KTM-WDC completion brief

Act as the senior engineer and product designer responsible for completing this existing Laravel logistics application. Work in the actual KTM-WDC repository, preserve existing data and changes, and keep all work local until deployment is explicitly requested.

## Product standard

Deliver a calm, premium, usable logistics workspace for clients, administrators, drivers, warehouse owners, equipment owners, and security agencies. Retain KTM-WDC's identity. Use spacious layouts, rounded controls and surfaces, restrained color, clear hierarchy, short labels, and consistent navigation. Avoid repeated slogans, ornamental feature cards, oversized dashboard headings, fabricated statistics, and unnecessary explanations. Separate operational sections clearly. Make the main action obvious on each screen.

## Functional completion

Inventory routes and role permissions before editing. Exercise registration, verification, resend, login, logout, recovery, profiles, warehouse registration and requests, inventory, dispatch, pickups, assignments, status transitions, tracking, equipment, security, invoices, documents, payments, notifications, reminders, and reports. Fix failures at their source. Preserve entered form data after validation errors. Provide meaningful empty, loading, error, success, and unavailable states. Never show success for a failed external operation.

Location entry must resolve to real coordinates, show the chosen location on a map, and persist correctly. Handle ambiguous or missing addresses, rate limits, network failures, stale responses, and manual pin placement. Do not invent coordinates or present straight-line distance as a road route. Use a configurable provider, caching, and documented usage limits.

## Security and operations

Enforce authentication, verification, active-account status, role access, and resource ownership on the server. Cover privilege escalation, cross-account access, private uploads, activation replay, throttling, session regeneration, payment verification, and mass assignment. Keep secrets out of source, logs, reports, and screenshots. Demo activation codes must never be available in production. Inspect dependency advisories and production configuration. Keep email transport failures recoverable and observable without exposing provider credentials. Distinguish automated mail tests, SMTP acceptance, and confirmed inbox delivery.

## Interface and accessibility

Review actual browser output at narrow mobile and desktop widths. Require readable contrast, visible keyboard focus, associated labels, usable tap targets, accessible menus, reduced-motion support, no page-wide horizontal scrolling, and no text overlap. Use established icons with accessible names, concise validation, consistent field spacing, and progressive disclosure for long forms. Build CSS locally for release. Check that images and maps load.

## Verification and delivery

Run meaningful regression tests, compilation, route/view cache checks, dependency audits, and browser checks. Add regression tests for substantive bugs. Do not reset the database or seed over real records. Document changes, verification evidence, known limitations, external credentials/services required, backup/restore procedures, workers, scheduling, and launch gates. Leave the local app running and open a useful preview. Never describe the whole project as production-ready solely because tests pass; explicitly report unverified workflows and outstanding launch dependencies.
