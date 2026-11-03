# Render Deployment Guide for KTM-WDC

## Overview

KTM-WDC is configured for one-click deployment on **Render** using Docker and PostgreSQL.

### Architecture on Render
1. **Web Service (`kwdc-web`)**:
   - **Runtime**: Docker (Apache + PHP 8.2 + GD + PDO MySQL/PostgreSQL + Node.js 20)
   - **Plan**: Free or Starter
   - **Entrypoint**: `bash scripts/deploy/start-production.sh`
   - **Health Check**: `/ping` (or `/up`)
2. **Database (`kwdc-db`)**:
   - **Type**: Managed PostgreSQL
   - **Plan**: Free

---

## Method 1: Deploy with Render Blueprint (Recommended)

1. Go to the [Render Dashboard](https://dashboard.render.com).
2. Click **New +** in the top right and select **Blueprint**.
3. Connect your GitHub repository: `https://github.com/kirannetpack-ui/kwdc`.
4. Select branch: `production-readiness-reminders-notifications` (or `main`).
5. Render detects `render.yaml` and plans:
   - Web Service: `kwdc-web`
   - PostgreSQL Database: `kwdc-db`
6. Provide the environment values marked `sync: false`:
   - **`APP_KEY`**: Generate a unique secret with `php artisan key:generate --show` and save it only in the hosting environment.
   - **`APP_URL`**: Your Render URL (e.g. `https://kwdc-web.onrender.com`)
   - **`GEMINI_API_KEY`**: Your Gemini API key
7. Click **Apply**.
8. Render will build the Docker image, link PostgreSQL, run migrations, automatically seed all demo users and data via `start-production.sh`, and launch!

---

## Method 2: Manual Web Service & Database

If creating manually on Render:
1. **Create Database**:
   - **New +** -> **PostgreSQL**
   - Name: `kwdc-db`, Database: `kwdc`, User: `kwdc_user`
   - Copy the **Internal Database URL**.
2. **Create Web Service**:
   - **New +** -> **Web Service**
   - Connect `kirannetpack-ui/kwdc`
   - Environment: **Docker**
   - Docker Command: `bash scripts/deploy/start-production.sh`
   - Health Check Path: `/ping`
3. **Set Environment Variables**:
   - `APP_NAME` = `KTM Warehouse & Distribution Center`
   - `APP_ENV` = `production`
   - `APP_DEBUG` = `false`
   - `APP_KEY` = a unique generated production secret
   - `APP_URL` = `https://<your-service>.onrender.com`
   - `DB_CONNECTION` = `pgsql`
   - `DATABASE_URL` = `<Internal Database URL from kwdc-db>`
   - `SESSION_DRIVER` = `database`
   - `SESSION_ENCRYPT` = `true`
   - `SESSION_SECURE_COOKIE` = `true`
   - `SESSION_HTTP_ONLY` = `true`
   - `SESSION_SAME_SITE` = `lax`
   - `CACHE_STORE` = `database`
   - `QUEUE_CONNECTION` = `sync`
   - `MAIL_MAILER` = `log`
   - `PHASE_ONE_DEMO` = `true`
   - `SEED_PORTAL_ACCOUNTS` = `true`
   - `AI_PROVIDER` = `gemini`
   - `GEMINI_MODEL` = `gemini-3.5-flash`
   - `GEMINI_API_KEY` = `<your-gemini-key>`

---

## Portal Logins for Render

Once deployed, you can immediately log into the live URL with the pre-seeded accounts:

**Universal Demo Password:** `KwdcNew2026!`

| Role | Email | User Code |
| :--- | :--- | :--- |
| **Admin** | `admin.access@kwdc.test` | `ADM-DEMO` |
| **Client** | `client.access@kwdc.test` | `CLI-DEMO` |
| **Driver** | `driver.access@kwdc.test` | `DRV-DEMO` |
| **Property Owner** | `property.access@kwdc.test` | `PRO-DEMO` |
| **Equipment Owner** | `equipment.access@kwdc.test` | `EQP-DEMO` |
| **Security Agency** | `security.access@kwdc.test` | `SEC-DEMO` |
