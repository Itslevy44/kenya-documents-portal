# Implementation Plan — Kenya Document Assistant

> **Greenfield Laravel 11 project.** No existing code was found in the workspace.
> The plan is decomposed into 8 sequential FEATs; full artifact details live under
> `.agents/tasks/task-kenya-document-assistant/`.

---

## Overview

A Kenyan document-generation SaaS built on **Laravel 11 + MySQL + vanilla JS**.
Users pick a document template, fill a dynamic form, pay via M-Pesa (PayHero STK push),
then download a PDF and Word file. Files are stored via Telegram Bot API.

**Stack:** PHP 8.2 · Laravel 11 · MySQL · Blade · vanilla JS · Africa's Talking SMS ·
PayHero M-Pesa · Telegram Bot API · InfinityFree hosting · GitHub Actions FTP CI/CD

---

## FEAT Execution Order

| # | FEAT | Summary |
|---|------|---------|
| 1 | FEAT-001 | Laravel scaffold, config, frontend shell, CI/CD |
| 2 | FEAT-002 | Database migrations (14 tables + 1 alter) |
| 3 | FEAT-003 | Eloquent models, relationships, seeders |
| 4 | FEAT-004 | Authentication (OTP via Africa's Talking) |
| 5 | FEAT-005 | Document generation (PDF + Word, preview, download) |
| 6 | FEAT-006 | Payments (PayHero STK push, webhooks, promo codes) |
| 7 | FEAT-007 | Document library (Telegram storage, FULLTEXT search) + ancillary controllers |
| 8 | FEAT-008 | Admin panel, routes wiring, PWA, final config |

---

## Detailed Steps by FEAT

### FEAT-001 — Project Scaffold & Frontend Shell

- [ ] 1. Run `composer create-project laravel/laravel .` inside the workspace to create
       the full Laravel 11 skeleton (composer.json, artisan, app/, config/, database/,
       resources/, routes/, public/, etc.).
       **Files:** entire Laravel skeleton
       **Verify:** `php artisan --version` outputs Laravel 11.x

- [ ] 2. Add composer dependencies:
       `barryvdh/laravel-dompdf`, `phpoffice/phpword`, `africastalking/africastalking`,
       `laravel/sanctum` (for CSRF), `spatie/laravel-sluggable` (optional helper).
       **Files:** composer.json, composer.lock, vendor/
       **Verify:** `composer install --no-dev` exits 0

- [ ] 3. Create Blade layout `resources/views/layouts/app.blade.php` with HTML5
       boilerplate, CSS vars (primary #1B5E20, accent #FF8F00, bg #F5F5F5), header,
       footer, toast div, PWA manifest link, and per-page `@stack('scripts')`.
       **Files:** resources/views/layouts/app.blade.php
       **Verify:** `php artisan view:cache` exits 0

- [ ] 4. Create `public/css/app.css` — CSS vars, reset, layout grid, mobile-first
       responsive styles matching Kenyan gov-doc aesthetic.
       **Files:** public/css/app.css
       **Verify:** file exists, no PHP errors on `php artisan view:clear`

- [ ] 5. Create `public/js/api.js` — shared fetch wrapper with CSRF token header,
       JSON parsing, error toasting.
       `public/js/home.js`, `public/js/builder.js`, `public/js/payment.js`,
       `public/js/admin.js` — page-specific stubs.
       **Files:** public/js/api.js, public/js/home.js, public/js/builder.js,
       public/js/payment.js, public/js/admin.js
       **Verify:** `php artisan optimize:clear` exits 0

- [ ] 6. Create all Blade view stubs:
       home, category, builder, preview, payment, download,
       library/index, library/show, signin, my-documents, profile,
       admin/dashboard, errors/404, errors/500.
       **Files:** resources/views/*.blade.php (14 files)
       **Verify:** `php artisan view:cache` exits 0 with no compilation errors

- [ ] 7. Write `.env.example` with every required key (see Phase 15 spec).
       Copy to `.env`, generate app key.
       **Files:** .env.example, .env
       **Verify:** `php artisan key:generate` exits 0

- [ ] 8. Create `public/manifest.json` and `public/sw.js` (PWA service worker with
       offline cache for shell assets).
       **Files:** public/manifest.json, public/sw.js
       **Verify:** valid JSON in manifest.json (`php -r "json_decode(file_get_contents('public/manifest.json')); echo json_last_error();"`)

- [ ] 9. Create `public/.htaccess` tuned for InfinityFree (PHP handler, index.php
       rewrite, cache headers for assets).
       **Files:** public/.htaccess
       **Verify:** file exists and contains `RewriteEngine On`

- [ ] 10. Create `.github/workflows/deploy.yml` — PHP 8.2, `composer install --no-dev`,
        `php artisan optimize`, FTP deploy via `SamKirkland/FTP-Deploy-Action`.
        **Files:** .github/workflows/deploy.yml
        **Verify:** YAML parses (`php -r "echo 'ok';"` after confirming file exists)

- [ ] 11. Create `README.md` and `InfinityFree-setup.md`.
        **Files:** README.md, InfinityFree-setup.md
        **Verify:** files exist with content

---

### FEAT-002 — Database Migrations

Migrations in strict dependency order. All tables use InnoDB, utf8mb4, timestamps unless noted.

- [ ] 1. `2024_01_01_000001_create_users_table.php`
       id, phone UNIQUE, name, email nullable, is_admin bool default false, timestamps.
       **Files:** database/migrations/2024_01_01_000001_create_users_table.php

- [ ] 2. `2024_01_01_000002_create_otp_codes_table.php`
       id, phone, code (hashed), expires_at, used_at nullable, created_at.

- [ ] 3. `2024_01_01_000003_create_categories_table.php`
       id, name, slug UNIQUE, description, icon, is_active bool, sort_order, timestamps.

- [ ] 4. `2024_01_01_000004_create_templates_table.php`
       id, category_id FK→categories, name, slug UNIQUE, description, price decimal(8,2),
       schema JSON, definition JSON, is_active bool default false, sort_order,
       meta_title, meta_description, timestamps.

- [ ] 5. `2024_01_01_000005_create_generated_documents_table.php`
       id, user_id FK→users nullable, session_token, template_id FK→templates,
       form_data JSON, status enum(draft/paid/cancelled), pdf_path nullable,
       word_path nullable, preview_path nullable, timestamps.

- [ ] 6. `2024_01_01_000006_create_library_items_table.php`
       id, title, description TEXT, file_name, telegram_file_id, file_size,
       mime_type, category nullable, is_free bool, download_count default 0,
       is_active bool, timestamps.
       FULLTEXT index on (title, description).

- [ ] 7. `2024_01_01_000007_create_payments_table.php`
       id, generated_document_id FK, user_id FK nullable, phone, amount decimal(8,2),
       mpesa_receipt nullable, payhero_reference nullable,
       status enum(pending/completed/failed/refunded),
       callback_payload JSON nullable, promo_code_id FK nullable,
       discount_amount decimal(8,2) default 0, timestamps.
       (promo_code_id added as nullable now; FK added after promo_codes table.)

- [ ] 8. `2024_01_01_000008_create_downloads_table.php`
       id, generated_document_id FK, user_id FK nullable, ip_address, user_agent,
       file_type enum(pdf/word), downloaded_at.

- [ ] 9. `2024_01_01_000009_create_audit_logs_table.php`
       id, user_id FK nullable, action, subject_type, subject_id,
       payload JSON nullable, ip_address, created_at.

- [ ] 10. `2024_01_01_000010_add_edit_window_to_generated_documents.php`
        ALTER: add edit_count default 0, edit_window_expires_at nullable,
        payment_completed_at nullable.

- [ ] 11. `2024_01_01_000011_create_user_profiles_table.php`
        id, user_id FK UNIQUE, full_name, id_number_encrypted text nullable,
        phone, address nullable, city nullable, timestamps.

- [ ] 12. `2024_01_01_000012_create_missing_document_requests_table.php`
        id, user_id FK nullable, search_query, document_description nullable,
        phone nullable, created_at.

- [ ] 13. `2024_01_01_000013_create_promo_codes_table.php`
        id, code UNIQUE, type enum(percent/fixed), value decimal(8,2),
        max_uses nullable, uses_count default 0, is_active bool,
        expires_at nullable, is_referral bool default false,
        referrer_user_id FK→users nullable, timestamps.

- [ ] 14. `2024_01_01_000014_create_promo_code_uses_table.php`
        id, promo_code_id FK, user_id FK nullable, payment_id FK, used_at.

- [ ] 15. `2024_01_01_000015_add_promo_code_fk_to_payments.php`
        ALTER payments: add FK constraint on promo_code_id→promo_codes (now that
        promo_codes table exists).

       **All 15 files:** database/migrations/
       **Verify:** `php artisan migrate --pretend` exits 0 with no errors (requires DB)

---

### FEAT-003 — Eloquent Models & Seeders

- [ ] 1. Create all 13 Eloquent models with `$fillable`, `$casts` (JSON→array, dates),
       and relationships exactly as specified:
       User, OtpCode, Category, Template, GeneratedDocument, LibraryItem,
       Payment, Download, AuditLog, UserProfile, MissingDocumentRequest,
       PromoCode, PromoCodeUse.
       **Files:** app/Models/*.php (13 files)
       **Verify:** `php artisan model:show User` exits 0

- [ ] 2. Create `DatabaseSeeder.php` that calls sub-seeders in order.
       `CategorySeeder`: 7 categories (Letters, Affidavits, Business Documents,
       School Documents, Legal Agreements, Employment, Community) with slugs, icons.
       `TemplateSeeder`: 15 templates (is_active=false) with complete realistic
       `schema` JSON (field definitions for the form builder) and `definition` JSON
       (document structure/content template) for all 15 slugs listed in spec.
       **Files:** database/seeders/DatabaseSeeder.php,
       database/seeders/CategorySeeder.php,
       database/seeders/TemplateSeeder.php
       **Verify:** `php artisan db:seed --pretend` exits 0 (or with live DB: `php artisan migrate:fresh --seed`)

---

### FEAT-004 — Authentication

- [ ] 1. Create `app/Services/AfricasTalkingService.php` — wraps the AT SDK,
       reads AT_USERNAME, AT_API_KEY, AT_SENDER_ID from env,
       exposes `sendSms(string $phone, string $message): bool`.
       **Files:** app/Services/AfricasTalkingService.php

- [ ] 2. Create `app/Http/Controllers/AuthController.php` with:
       - `sendOtp(Request $r)` — validates phone (07XXXXXXXX), rate-limits (3 per 10 min),
         generates 6-digit OTP, hashes and stores in otp_codes, sends via AT.
       - `verifyOtp(Request $r)` — checks hash, expiry (10 min), not-used; marks used,
         upserts User, starts session.
       - `logout(Request $r)` — invalidates session.
       **Files:** app/Http/Controllers/AuthController.php

- [ ] 3. Create `app/Http/Middleware/AuthMiddleware.php` — checks `auth()->check()`,
       redirects to /sign-in with intended URL stored in session.
       Create `app/Http/Middleware/AdminMiddleware.php` — checks `user->is_admin`.
       Register both in `bootstrap/app.php`.
       **Files:** app/Http/Middleware/AuthMiddleware.php,
       app/Http/Middleware/AdminMiddleware.php,
       bootstrap/app.php

- [ ] 4. Create signin Blade view with phone input form, OTP step (JS-driven),
       CSRF token, and POST to api routes.
       **Files:** resources/views/signin.blade.php (update stub)
       **Verify:** `php artisan route:list` shows auth routes

---

### FEAT-005 — Document Generation

- [ ] 1. Create `app/Services/DocumentService.php`:
       - `generatePreview(GeneratedDocument $doc): string` — builds HTML from
         template->definition + form_data, renders via dompdf with diagonal watermark
         "PREVIEW — Not for official use" in red 20% opacity, saves to
         `storage/app/documents/{token}-preview.pdf`, returns path.
       - `generateDocuments(GeneratedDocument $doc): array` — generates clean A4 PDF
         (25mm margins, Times New Roman 12pt, 1.5 line spacing, signature lines,
         page numbers) via dompdf; generates .docx via phpoffice/phpword;
         saves as `{slug}-{token}.pdf` and `{slug}-{token}.docx`;
         returns ['pdf'=>path, 'word'=>path].
       **Files:** app/Services/DocumentService.php

- [ ] 2. Create `app/Services/StorageService.php` — thin wrapper managing
       `storage/app/documents/` path resolution and file cleanup helpers.
       **Files:** app/Services/StorageService.php

- [ ] 3. Create `app/Http/Controllers/DocumentController.php`:
       - `show(string $slug)` — load template, return builder view.
       - `preview(Request $r, string $slug)` — validate form_data against template schema,
         create GeneratedDocument(status=draft), call DocumentService::generatePreview,
         return preview URL + session_token JSON.
       - `generate(Request $r, string $slug)` — called after payment; trigger full doc gen.
       - `download(string $slug, string $token, string $type)` — auth or session check,
         verify status=paid, stream file, log to downloads.
       **Files:** app/Http/Controllers/DocumentController.php

- [ ] 4. Implement the edit window: re-generate allowed if `edit_count < 3` AND
       `edit_window_expires_at > now()`. Increment edit_count on each re-gen.
       Logic lives in DocumentController::generate().
       **Files:** app/Http/Controllers/DocumentController.php (same file as above)

- [ ] 5. Create builder, preview, download Blade views (update stubs) with the JS
       form-builder that reads `template.schema` JSON to render dynamic form fields.
       builder.js reads schema from a data attribute and renders input fields dynamically.
       **Files:** resources/views/builder.blade.php, resources/views/preview.blade.php,
       resources/views/download.blade.php, public/js/builder.js
       **Verify:** `php artisan route:list` shows document routes

---

### FEAT-006 — Payments

- [ ] 1. Create `app/Services/PayHeroService.php`:
       - `initiateStk(string $phone, float $amount, string $reference): array`
         — POST to PayHero STK push endpoint with PAYHERO_API_KEY, PAYHERO_CHANNEL_ID.
       - `verifySignature(Request $r): bool` — HMAC check on webhook payload.
       **Files:** app/Services/PayHeroService.php

- [ ] 2. Create `app/Http/Controllers/PaymentController.php`:
       - `initiate(Request $r)` — validate promo code (percent/fixed, max_uses, expiry,
         active), compute final amount, create Payment(status=pending), call PayHero STK.
       - `status(Request $r)` — return current payment status (for 3s polling).
       - `webhook(Request $r)` — verify signature, idempotency check, verify amount,
         write callback_payload; on success: set status=paid, payment_completed_at=now,
         edit_window_expires_at=now+24h, PromoCodeUse record, trigger doc generation,
         send SMS via AT with receipt + download link.
       **Files:** app/Http/Controllers/PaymentController.php

- [ ] 3. Update payment Blade view (stub) with:
       - Phone input, promo code field, final amount display.
       - payment.js: polls `/api/payments/{id}/status` every 3s, 2-minute timeout,
         shows countdown, redirects on success.
       **Files:** resources/views/payment.blade.php, public/js/payment.js
       **Verify:** `php artisan route:list` shows payment routes

---

### FEAT-007 — Document Library + Ancillary Controllers

- [ ] 1. Create `app/Services/TelegramService.php`:
       - `uploadFile(string $localPath, string $caption): string` — POST to Telegram
         Bot API sendDocument, returns file_id.
       - `streamFile(string $fileId): StreamedResponse` — getFile + stream URL to browser.
       **Files:** app/Services/TelegramService.php

- [ ] 2. Create `app/Http/Controllers/LibraryController.php`:
       - `index()` — paginated list, cached 1h, filter by category.
       - `show(int $id, string $slug)` — single item view.
       - `download(int $id)` — free: stream direct; paid: check payment; increment count.
       - `search(Request $r)` — FULLTEXT search on title+description.
       **Files:** app/Http/Controllers/LibraryController.php

- [ ] 3. Create `app/Http/Controllers/ProfileController.php` — CRUD for UserProfile;
       encrypt id_number with `Crypt::encrypt()`, decrypt only for owner.
       **Files:** app/Http/Controllers/ProfileController.php

- [ ] 4. Create `app/Http/Controllers/MissingDocumentController.php`:
       - `store(Request $r)` — log request (user_id nullable, search_query, phone).
       - `index()` — admin only, grouped by document_description with count.
       **Files:** app/Http/Controllers/MissingDocumentController.php

- [ ] 5. Create `app/Http/Controllers/UserController.php`:
       - `destroy(Request $r)` — delete user + all related data; log to audit_logs;
         ODPC-compliant (keep anonymised payment record).
       **Files:** app/Http/Controllers/UserController.php

- [ ] 6. Create `app/Http/Controllers/SitemapController.php` and
       `app/Http/Controllers/RobotsController.php`.
       **Files:** app/Http/Controllers/SitemapController.php,
       app/Http/Controllers/RobotsController.php

- [ ] 7. Create library Blade views (update stubs).
       **Files:** resources/views/library/index.blade.php,
       resources/views/library/show.blade.php
       **Verify:** `php artisan route:list` shows library routes

---

### FEAT-008 — Admin Panel, Full Route Wiring, PWA Finalization & Config

- [ ] 1. Create `app/Http/Controllers/AdminController.php` with all admin actions:
       - Templates: list, create, update, preview, activate/deactivate.
       - Library: upload (to Telegram), update metadata, delete.
       - Payments: list with filters, view callback_payload, manual unlock.
       - Users: search, delete with reason (audit log).
       - Stats: total revenue, paid doc count, top 5 templates, 30-day daily revenue.
       - Missing requests: grouped view.
       - Promo codes: create, toggle, delete, generate referral codes.
       **Files:** app/Http/Controllers/AdminController.php

- [ ] 2. Wire all routes in `routes/web.php` and `routes/api.php` exactly as specified
       in Phase 11. Apply `auth` middleware to protected web routes. Apply `admin`
       middleware to admin routes. Add cache headers for category/template pages.
       **Files:** routes/web.php, routes/api.php
       **Verify:** `php artisan route:list` shows all expected routes with correct middleware

- [ ] 3. Update admin Blade view (update stub) with dashboard HTML using admin.js
       for AJAX calls to admin API endpoints.
       **Files:** resources/views/admin/dashboard.blade.php, public/js/admin.js

- [ ] 4. Finalize `public/manifest.json` with correct app name ("Kenya Docs"),
       icons array (192×192, 512×512 placeholder PNGs), theme_color #1B5E20.
       Finalize `public/sw.js` — precache shell assets, network-first for API.
       Register service worker in layout via `@push('scripts')`.
       **Files:** public/manifest.json, public/sw.js,
       resources/views/layouts/app.blade.php

- [ ] 5. Add services config for Africa's Talking, PayHero, Telegram to
       `config/services.php`.
       **Files:** config/services.php

- [ ] 6. Add file cache driver config; set cache TTL=3600 for category/template/library
       routes using `Cache::remember()` in respective controllers.
       **Files:** config/cache.php (verify driver = file for InfinityFree compatibility)

- [ ] 7. Verify `.env.example` is complete with all keys:
       APP_*, DB_*, AT_USERNAME, AT_API_KEY, AT_SENDER_ID,
       PAYHERO_API_KEY, PAYHERO_CHANNEL_ID, PAYHERO_WEBHOOK_SECRET,
       TELEGRAM_BOT_TOKEN, TELEGRAM_CHANNEL_ID, CACHE_DRIVER=file,
       SESSION_DRIVER=file, QUEUE_CONNECTION=sync.
       **Files:** .env.example

- [ ] 8. Final verification: `php artisan optimize` and `php artisan route:list`
       exit 0 with no errors.
       **Verify:** `php artisan route:list | Select-String "GET\|POST"` — all routes listed

---

## Cross-Cutting Implementation Notes

1. **Money**: all amounts stored as `decimal(8,2)` in KSh.
2. **Phone format**: 07XXXXXXXX — validated with regex `/^07\d{8}$/`.
3. **OTP**: SHA-256 hashed in DB; 6-digit; 10-minute expiry; 3-attempt rate limit per phone per 10 min.
4. **IDs encrypted**: `Crypt::encrypt()` / `Crypt::decrypt()` for id_number_encrypted.
5. **Admin check**: `is_admin = true` on users table (no separate roles table).
6. **No queues**: all operations synchronous; `QUEUE_CONNECTION=sync`.
7. **No cron**: external ping endpoint `/api/cron` if needed.
8. **Cache**: file driver (InfinityFree has no Redis); 1-hour TTL on categories/templates/library.
9. **Sessions**: file driver for InfinityFree.
10. **Document filenames**: `{slug}-{token}.pdf` and `{slug}-{token}.docx`.
11. **PDF spec**: A4 210×297mm, 25mm margins, Times New Roman 12pt, 1.5 line spacing, signature lines, page numbers.
12. **Watermark**: diagonal "PREVIEW — Not for official use", red, 20% opacity.
13. **Edit window**: 3 re-edits within 24h of payment.
14. **Telegram storage**: library files uploaded to private channel; streamed server-side for downloads.
15. **FULLTEXT**: MySQL FULLTEXT index on library_items(title, description) — requires MyISAM or InnoDB ≥5.6.
