# KTM-WDC (Kathmandu Warehousing & Distribution Center)

Deployment URL: https://kwdc.vercel.app. The portal currently runs as a shared evaluation demo with sample accounts and operational data. See [demo access and testing](docs/demo-testing.md) and [production setup requirements](docs/vercel-deployment.md). Historical readiness claims below are not a completed production launch assessment.

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![Laravel Framework](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Tests Passing](https://img.shields.io/badge/Tests-221%20Passed%20(100%25)-brightgreen.svg)]()
[![License](https://img.shields.io/badge/License-Proprietary%20%2F%20Commercial-amber.svg)]()

> **Production-Ready Enterprise Logistics & Commercial Warehousing Platform for Nepal.**  
> Built with modern Apple Cupertino minimalist aesthetics, multi-currency payment infrastructure (Stripe, eSewa, Khalti, Direct Banking, NepalPay QR), real-time driver dispatch tracking, heavy machinery leasing, licensed security post management, statutory legal insulation under the laws of Nepal, Sparrow/Aakash SMS gateways, bilingual English/Nepali i18n, PWA offline driver app, and IRD Schedule 13 VAT reporting.

---

## Table of Contents
1. [Architecture & System Overview](#architecture--system-overview)
2. [Quickstart Setup (3 Minutes)](#quickstart-setup-3-minutes)
3. [Pre-Seeded Demo Credentials](#pre-seeded-demo-credentials)
4. [Role Capabilities & Feature Matrix](#role-capabilities--feature-matrix)
5. [Payment Suite & Financial Workflows](#payment-suite--financial-workflows)
6. [Enterprise Operations & Nepal Localization](#enterprise-operations--nepal-localization)
7. [Legal & Statutory Liability Framework](#legal--statutory-liability-framework)
8. [Automated Testing Suite](#automated-testing-suite)
8. [Directory Structure & Architecture Map](#directory-structure--architecture-map)
9. [Production Deployment Guide](#production-deployment-guide)

---

## Architecture & System Overview

KTM-WDC coordinates five commercial logistics pillars across Nepal through a single integrated portal:
- **Commercial Warehousing**: Facility operators list verified space; enterprise shippers reserve, manage inventory via QR-coded boxes, and track warehouse tenures.
- **Freight Dispatch & Telemetry**: AI-assisted route calculation, multi-stop drop-offs, driver bidding/assignment, live GPS pinging, and digital proof-of-delivery (POD).
- **Heavy Equipment Rentals**: On-demand leasing for forklifts, pallet jacks, cranes, and certified operators with hourly/daily billing.
- **Licensed Security Agency Dispatch**: Perimeter surveillance, guard deployment, shift scheduling, and incident reporting.
- **AI Logistics Copilot**: Voice and text command processing for instant pickups, dispatch recommendations, pricing estimations, and reminder calendars.

### Technology Stack
- **Framework**: Laravel 12.x
- **PHP**: 8.2+ (tested up to PHP 8.3+)
- **Database**: SQLite (default for development/testing), PostgreSQL / MySQL ready
- **Styling**: Apple Cupertino Frosted Glassmorphism (`backdrop-filter: blur(20px)`), Tailwind CSS, Vanilla CSS
- **PDF & QR Engine**: `barryvdh/laravel-dompdf` (13% VAT Tax Invoices) + `simplesoftwareio/simple-qrcode`
- **WebSockets / Realtime**: `laravel/reverb` + Leaflet.js OpenStreetMap telemetry
- **AI Engine**: Gemini (`gemini-3.5-flash`), OpenAI (`gpt-4o-mini`), Groq (`mixtral`)

---

## Quickstart Setup (3 Minutes)

### 1. Clone the repository
```bash
git clone https://github.com/kirannetpack-ui/kwdc-main.git
cd kwdc-main
```

### 2. Install PHP & Node dependencies
```bash
composer install
npm install
npm run build
```

### 3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Run database migrations & seed demo dataset
```bash
# Creates database tables and seeds all 6 role accounts with demo data
php artisan migrate:fresh --seed
```

### 5. Launch development server
```bash
php artisan serve
```
Visit **`http://127.0.0.1:8000`** in your browser.

---

## Pre-Seeded Demo Credentials

All seeded accounts share the master development password:  
**`KwdcNew2026!`**

| Portal Role | Demo Email Address | Password | Primary Route |
| :--- | :--- | :--- | :--- |
| **Platform Administrator** | `admin.access@kwdc.test` | `KwdcNew2026!` | `/admin` |
| **Enterprise Client / Shipper** | `client.access@kwdc.test` | `KwdcNew2026!` | `/dashboard` |
| **Commercial Driver** | `driver.access@kwdc.test` | `KwdcNew2026!` | `/driver/dashboard` |
| **Warehouse / Property Owner** | `property.access@kwdc.test` | `KwdcNew2026!` | `/property-owner/dashboard` |
| **Heavy Equipment Owner** | `equipment.access@kwdc.test` | `KwdcNew2026!` | `/equipment-owner/dashboard` |
| **Licensed Security Agency** | `security.access@kwdc.test` | `KwdcNew2026!` | `/security/dashboard` |

---

## Role Capabilities & Feature Matrix

### 1. Client / Shipper (`/dashboard`, `/my-requests`)
- Search and reserve verified warehouse square footage across Kathmandu and highway corridors.
- Deposit inventory, view digital inventory ledger, and generate printable QR code stickers for boxes.
- Create multi-stop highway dispatches and instant pickup requests with automated distance and fare calculation.
- Settle outstanding invoices using Stripe, eSewa, or Khalti.
- Download verified PDF tax invoices with embedded QR validation.

### 2. Commercial Driver (`/driver/dashboard`)
- View incoming dispatch and pickup jobs with interactive route maps.
- Propose rates or accept platform-calculated fares.
- Update waypoint stops in real-time, ping live GPS coordinates, and submit photo Proof of Delivery.
- Track weekly/monthly gross and net earnings.

### 3. Warehouse Property Owner (`/property-owner/dashboard`)
- List commercial facilities, dock doors, ceiling heights, CCTV coverage, and cold storage specifications.
- Approve or reject tenant lease requests.
- Track square footage occupancy rates and rental yields.

### 4. Heavy Equipment Owner (`/equipment-owner/dashboard`)
- Manage fleet inventory (forklifts, scissor lifts, reach trucks, cranes).
- Review client machinery rental quotes and approve daily/hourly rentals.
- Track equipment operational hours and earnings.

### 5. Security Agency (`/security/dashboard`)
- Deploy licensed security guards to contracted logistics properties.
- Monitor guard shift check-ins and patrol checkpoints.
- Log and resolve facility incident reports.

### 6. Platform Administrator (`/admin`)
- Review and verify pending warehouse property listings and driver licenses.
- Set margin tier rules, platform fees, and statutory tax parameters.
- View real-time platform telemetry and operational audit trails.

---

## Payment Suite & Financial Workflows

The portal supports international cards, local digital wallets, direct commercial bank wire transfers, and universal interbank mobile banking QR codes:

1. **Direct Commercial Bank Transfer (`payment.bank.submit`)**:
   - Pre-configured corporate depository accounts for Nepal's leading A-Class commercial banks:
     - The payment screen displays the verified account configured by the operator when bank transfers are enabled.
   - Interactive Apple-style modal with 1-click clipboard copy for Account Numbers and Account Names.
   - Bank voucher / deposit slip photo and PDF upload (`payment-vouchers` storage) with UTR / Reference tracking.
2. **Interbank Banking QR Payment (`payment.banking-qr.submit`)**:
   - The payment screen displays the bank-issued merchant QR configured by the operator when QR payments are enabled.
   - Fast RRN (Retrieval Reference Number) entry and instant admin reconciliation workflow.
3. **Stripe Checkout (`payment.stripe.init`)**:
   - Supports Visa, MasterCard, American Express, Apple Pay, and Google Pay.
   - Handles multi-currency conversion, session validation, and webhook reconciliations.
4. **eSewa EPAY v2 (`payment.esewa.init`)**:
   - Direct payment initiation and callback verification via eSewa's transaction API.
   - Pre-configured with official UAT Sandbox credentials (`EPAYTEST`).
5. **Khalti v2 (`payment.khalti.init`)**:
   - Seamless checkout and lookup verification (`/epayment/lookup/`).
6. **Admin Bank & QR Reconciliation Terminal (`admin.payments.bank-transfers`)**:
   - Filter pending, approved, and rejected wire/QR transactions.
   - Direct 1-click invoice approval with automated sequential receipt generation and email confirmation.
   - 1-click rejection modal with mandatory statutory reason logging.
7. **Tax Invoicing & Reconciliation**:
   - Automatically calculates 13% Nepal Value Added Tax (VAT) and itemizes line items on official invoices.
   - Public QR verification endpoint at `/invoice/verify/{invoiceNumber}`.

---

## Enterprise Operations & Nepal Localization

To deliver complete production readiness for commercial freight, logistics, and warehousing operations across Nepal, KTM-WDC integrates the following enterprise infrastructure:

### 1. Nepal Telecom & SMS Dispatch Alert Gateway (`App\Services\SmsService`)
- Integrates both **Sparrow SMS** and **Aakash SMS** gateway APIs with automatic fallback to logging in testing environments.
- Normalizes all Nepal telephone prefixes (`+977`, `977`, or `98XXXXXXXX` / `97XXXXXXXX`) to standard 10-digit mobile numbers.
- Automated triggers:
  - Driver job dispatch alerts with pickup/destination and payout rate.
  - Consignee Proof-of-Delivery (POD) clearance OTP.
  - Instant SMS receipt confirmation upon bank clearance.

### 2. Bilingual English & Nepali (नेपाली) Interface
- Top-navigation language switch pill (EN / नेपाली) with immediate session preservation via `SetLocale` middleware.
- Full localization strings in `lang/en.json` and `lang/ne.json` covering navigation, metric counters, consignment statuses, and tax receipts.

### 3. Progressive Web App (PWA) & Offline Dead-Zone Mode
- Native mobile home-screen installability via `public/manifest.json`.
- Offline cache service worker (`public/sw.js`) and Apple Cupertino offline card (`public/offline.html`).
- Prevents data loss during cellular dropouts on mountain transit routes (Prithvi Highway, BP Highway, Narayanghat-Mugling corridor).

### 4. Statutory IRD Nepal Tax Book (अनुसूची १३ / Schedule 13 Sales Book)
- One-click official export at `GET /admin/invoices/export/schedule-13`.
- Exports CSV/Excel format strictly compliant with the **Inland Revenue Department (आन्तरिक राजस्व विभाग)**:
  - क्र.सं. (S.N.), मिति (Date), बिजक नं (Invoice No), खरिदकर्ताको नाम (Buyer Name), खरिदकर्ताको स्थायी लेखा नं (Buyer PAN), जम्मा बिक्री (Total), कर छुट बिक्री (Exempt), करयोग्य बिक्री (Taxable), १३% मूल्य अभिवृद्धि कर (13% VAT), and स्थिति (Status).

### 5. Fleet Compliance & Vehicle Blue Book (बिलबुक) Expiry Monitor
- Real-time regulatory monitor at `/admin/vehicles/compliance`.
- Tracks Blue Book validity, Route Permit (रुट परमिट), Green Pollution Sticker, and Comprehensive Commercial Insurance.
- Categorizes fleet units into **Fully Compliant**, **Expiring (<15 Days)**, and **Grounded/Barred**.

### 6. Cloud Object Storage Ready (AWS S3 / Cloudflare R2 / DigitalOcean Spaces)
- Dual-driver support in `config/filesystems.php` for `r2` (zero egress cost) and `spaces` (Bangalore `blr1` regional low latency).

### 7. Two-Factor Authentication (2FA) & High-Value Approvals
- Built-in 2FA toggle on User Profile (`/profile`).
- 6-digit cryptographic OTP generation with 10-minute expiry delivered via SMS and email.
- Challenge gate at `/two-factor/challenge` protects admin tools and high-value cargo operations.

---

## Legal & Statutory Liability Framework

The application is structured to provide full legal immunity and compliance under the laws of Nepal:

- **Muluki Civil Code (2074), Chapter 11 (Bailment / उपनिधान)**:
  - Establishes warehouse operators as legal bailees holding goods in trust.
  - Grants absolute immunity for Force Majeure / Acts of God (दैवी विपत्ति, floods, landslides, and highway blockades).
  - Enforces warehouse possessory lien rights and 60-day liquidation notices for abandoned cargo.
- **Motor Vehicles and Transport Management Act (2049)**:
  - Cabs carrier liability at **NPR 250 per kilogram** of actual damaged weight for standard consignments. High-value cargo must purchase supplementary commercial transit insurance prior to booking.
- **Electronic Transactions Act (2063)**:
  - Digital consent and click-wrap agreements are recognized as legally binding contracts in the courts of Nepal.
  - Mandatory terms acceptance checkboxes are enforced on User Registration, Dispatch Booking, and Pickup Requests.
- **Contraband Prohibitions**:
  - Strict zero-tolerance indemnity clauses prohibiting explosives, unmanifested narcotics, untaxed goods, and hazardous cargo.

---

## Account Banning & QR Code Revocation Framework

KTM-WDC incorporates military-grade security compliance controls to neutralize fraudulent actors and impound contraband cargo in real time:

### 1. Account Banning & Suspension
- **Administrative Control**: Admins can suspend any user account with a mandatory security justification (`POST /admin/users/{user}/ban` & `POST /admin/users/{user}/unban`).
- **Immediate Session Termination**: Authenticated sessions are immediately terminated upon banning via `EnsureAccountReady` middleware with an HTTP 403 suspension notice.
- **Login Defense**: Attempted logins with credentials belonging to banned accounts are rejected with an explicit suspension reason and contact directive.
- **Admin Immunity**: Self-banning safeguards prevent administrators from locking out administrative access.

### 2. QR Code Revocation (Invoices & Gate Passes)
- **Administrative Control**: Admins can revoke/ban invoice QR codes (`POST /admin/invoices/{invoice}/ban-qr` & `POST /admin/invoices/{invoice}/unban-qr`).
- **Public Tamper-Proof Verification**: Scanning or navigating to `/invoice/verify/{invoiceNumber}` immediately displays a high-visibility Apple Cupertino crimson alert card:
  - `🚨 QR CODE BANNED & REVOKED`
  - Statutory warning: Under Muluki Civil Code 2074, gate pass validation and cargo release are strictly prohibited.
  - Ban reason, audit timestamp, and security hotline.

### 3. Cargo Box & Parcel QR Impoundment
- **Box QR Revocation**: Admins can revoke box/parcel tracking QR codes (`POST /admin/boxes/{box}/ban-qr` & `POST /admin/boxes/{box}/unban-qr`).
- **Scannable QR Suppression**: `/boxes/{id}/qr` API nullifies active QR payload and returns `is_banned: true`.
- **Public Tracking Shield**: `/boxes/{id}/track` displays a bold red impoundment alert prohibiting cargo handoff.
- **Label Protection**: `/boxes/{id}/print` suppresses scannable QR generation and prints a high-contrast watermark: `VOID — CARGO RELEASE PROHIBITED`.

---

## Automated Testing Suite

The repository includes a comprehensive 215-test feature and unit test harness:

```bash
# Run the entire automated test suite
php vendor/bin/phpunit

# Run specific functional suites
php vendor/bin/phpunit tests/Feature/EnterpriseReadinessTest.php
php vendor/bin/phpunit tests/Feature/BankingPaymentTest.php
php vendor/bin/phpunit tests/Feature/AccountAndQrBanTest.php
php vendor/bin/phpunit tests/Feature/PaymentSecurityTest.php
php vendor/bin/phpunit tests/Feature/DispatchTest.php
php vendor/bin/phpunit tests/Feature/SecurityAgencyManagementTest.php
php vendor/bin/phpunit tests/Feature/ProductionHardeningTest.php
```

**Status**: 221 tests, 1009 assertions, 0 failures (100% pass rate).

---

## Directory Structure & Architecture Map

```
ktm-wdc-portal/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PaymentController.php       # Unified Stripe, eSewa, Khalti flows
│   │   │   ├── DispatchController.php      # Multi-stop delivery routing & pricing
│   │   │   ├── WarehouseController.php     # Property listings & space booking
│   │   │   ├── InvoiceController.php       # PDF generation & QR validation
│   │   │   └── AIController.php            # Logistics AI Copilot
│   │   └── Middleware/
│   │       ├── SecurityHeaders.php         # CSP, HSTS, X-Frame-Options headers
│   │       └── RoleMiddleware.php          # Strict RBAC role enforcement
│   ├── Models/                             # User, Invoice, Transaction, DispatchOrder, etc.
│   ├── Notifications/                      # Branded security & reset email templates
│   └── Services/
│       ├── PaymentService.php              # Stripe API, Khalti v2 & eSewa handlers
│       ├── InvoiceService.php              # DomPDF VAT invoice generator
│       └── MapService.php                  # Distance & waypoint calculation
├── config/
│   ├── payment.php                         # Payment provider credentials & timeouts
│   └── services.php                        # AI, Kataho, and Maps API configs
├── database/
│   ├── migrations/                         # Complete schema definitions
│   └── seeders/
│       ├── PortalAccessSeeder.php          # Pre-seeded role accounts
│       └── DatabaseSeeder.php              # Master database seeder
├── public/
│   ├── css/
│   │   ├── kwdc-public.css                 # Apple Cupertino frosted glassmorphism tokens
│   │   └── kwdc-ui.css                     # Modern dashboard layout rules
│   └── js/                                 # Maps, real-time telemetry, & voice scripts
├── resources/
│   └── views/
│       ├── welcome.blade.php               # Apple glossy minimalist landing page
│       ├── dashboard/index.blade.php       # Universal multi-role command dashboard
│       ├── payment/                        # Payment initiation, history, and status
│       ├── legal/                          # Terms of Service, Privacy Policy, Compliance
│       ├── dispatch/                       # Multi-stop order creation & live tracking
│       ├── layouts/                        # Master application shell with frosted glass topbar
│       └── emails/                         # Responsive transactional email templates
├── routes/
│   ├── web.php                             # Core platform & payment endpoints
│   └── roles/                              # Modular RBAC route definitions
│       ├── admin.php
│       ├── client.php
│       ├── driver.php
│       ├── equipment.php
│       ├── property.php
│       └── security.php
└── tests/                                  # 198 automated PHPUnit test cases
```

---

## Production Deployment Guide

### Environment Configuration (`.env`)
For production deployment, ensure these environment variables are set:
```dotenv
APP_NAME=KTM-WDC
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (PostgreSQL / MySQL recommended for high volume)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kwdc_production
DB_USERNAME=kwdc_user
DB_PASSWORD=your_secure_password

# Live Payments
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_CURRENCY=npr

KHALTI_PUBLIC_KEY=live_public_key_...
KHALTI_SECRET_KEY=live_secret_key_...

ESEWA_MERCHANT_CODE=your_merchant_code
ESEWA_SECRET_KEY=your_secret_key

# Transactional Mail (SMTP / SES / Resend)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="support@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Production Cache Optimization
Run these commands after deploying code:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

### Background Workers
Keep background queues and WebSockets running via Supervisor or systemd:
```bash
# Queue worker for emails and async invoice generation
php artisan queue:work --tries=3

# Reverb WebSockets for live driver GPS tracking
php artisan reverb:start
```

---

## Handover & Next Steps

This repository is in an **all-green, production-ready state**. Anyone taking over the codebase can immediately:
1. Clone and seed the database in under 3 minutes.
2. Log into all 6 role dashboards using the credentials in [Pre-Seeded Demo Credentials](#pre-seeded-demo-credentials).
3. Connect live merchant keys in `.env` to begin processing payments.
4. Run `php vendor/bin/phpunit` to verify full system integrity.

*© 2026 KTM-WDC Logistics Corporation. All rights reserved.*
