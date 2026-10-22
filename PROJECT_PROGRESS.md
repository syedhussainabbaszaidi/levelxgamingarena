# PROJECT_PROGRESS.md — Gaming Zone & Snooker Booking System

> **⚠️ AI AGENTS / DEVELOPERS: READ THIS FILE FIRST, plus `AGENT_HANDOVER.md`, `CHANGELOG.md`, and `KNOWN_ISSUES.md` before making any changes. Do not ask the user to re-explain the project.**

Last updated: 2026-09-30

---

## 1. Project Overview

A **time-based booking + Counter/POS management system** for entertainment venues, built with Laravel 11.

**What it does:** Manages bookable resources (snooker tables, PS5 booths, gaming PCs, pool tables, VIP rooms), handles walk-in sessions at the counter, online reservations, live session tracking with extensions, hourly/peak pricing, payments, and reporting.

**Target users:**
- **Admin** — full control: setup, pricing, staff, reports, settings
- **Counter staff** — daily operation: walk-ins, check-ins, extensions, billing
- **Customers** — browse availability and request bookings online

**Main business concept:** Venues sell *time* on *resources*. The system prices time by configurable windows (off-peak/peak), prevents double-booking, tracks live sessions, and settles payment at the end.

**Supported business types (via categories):** Gaming Zone, Snooker, PS5, PC Gaming, Pool, VIP Rooms — any time-based resource fits the model.

---

## 2. Technology Stack

| Component | Version / Choice |
|---|---|
| PHP | 8.3.16 |
| Laravel | 11.x |
| Database | SQLite (dev default; MySQL-ready — see §14) |
| Frontend | Blade + Tailwind CSS 3 (via Vite), vanilla JS for AJAX |
| Auth | Laravel Breeze 2.x (Blade stack), session-based |
| Roles | Custom enum column `users.role` (admin/staff/customer) + `role:` middleware |
| Testing | PHPUnit 11 (39 tests passing) |
| Packages | No external booking packages — custom booking engine |
| Node | Vite build (`npm run build`) — production assets committed |

---

## 3. System Architecture

Standard Laravel MVC + service layer:

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/            # Dashboard, Bookings, Calendar, Resources, Categories,
│   │   │                     # PricingRule, Customers, Payments, Reports, Staff,
│   │   │                     # Settings, LiveFloor, Session (lifecycle)
│   │   ├── Counter/          # CounterController (POS)
│   │   ├── PublicBookingController
│   │   └── ProfileController (Breeze)
│   ├── Middleware/EnsureUserHasRole.php   # 'role:admin,staff'
├── Models/                   # Business, Category, Resource, PricingRule,
│                             # Customer, Booking, Payment, User, Setting
└── Services/                 # ← the core engine
    ├── AvailabilityService   # slot grids, overlap + business-hours validation
    ├── PricingService        # window-based price calculation
    └── BookingService        # create/walkIn/checkIn/extend/endSession/cancel/addPayment
```

**Flow rule:** Controllers never contain booking/pricing logic — everything goes through the three services so counter, admin, and online flows share ONE engine.

**Database:** 9 custom tables (see §4). Migrations in `database/migrations/2026_01_01_*`.

**Views:** `resources/views/{layouts,admin,counter,public}` — admin uses `layouts.admin`, public uses `layouts.public`.

---

## 4. Database Documentation

### businesses
Venue/branch. Purpose: root entity; holds hours + currency.
- Columns: `id` PK, `name`, `slug` (unique), `parent_id` FK→businesses (nullable), `address`, `phone`, `opens_at` time, `closes_at` time, `overnight` bool, `timezone`, `currency`, `is_active`
- Rules: `overnight=true` means `closes_at` is on the next day (e.g. 10:00→02:00)

### categories
Resource groups per business (Snooker, PS5, …).
- Columns: `id` PK, `business_id` FK→businesses (cascade), `name`, `slug`, `description`, `color` (calendar), `is_active`
- Unique: (`business_id`, `slug`)

### resources
Bookable units (tables, booths, PCs).
- Columns: `id` PK, `business_id` FK (cascade), `category_id` FK (cascade), `name`, `code`, `description`, `capacity`, `hourly_rate` decimal(10,2) — fallback rate, `status` enum(active,inactive,maintenance), `sort_order`
- Index: (`business_id`,`category_id`,`status`)
- Rules: only `active` is bookable; `maintenance` blocks booking; resources with bookings are deactivated instead of deleted

### pricing_rules
Hourly rate windows.
- Columns: `id` PK, `business_id` FK, `category_id` FK nullable (nullOnDelete), `resource_id` FK nullable (cascade), `name`, `day_of_week` tinyint nullable (0=Sun), `start_time`, `end_time`, `overnight` bool, `hourly_rate`, `min_minutes`, `slot_minutes`, `is_active`, `priority` (higher wins)
- Precedence: resource-specific > category > business-wide; within ties, higher `priority`
- No rule match → resource `hourly_rate` applies

### customers
Per-business customer records (walk-ins included).
- Columns: `id` PK, `business_id` FK, `user_id` FK nullable (link to login account), `name`, `phone`, `email`, `notes`, `total_visits`, `total_spent`
- Unique: (`business_id`, `phone`)

### bookings
The central table.
- Columns: `id` PK, `business_id` FK, `resource_id` FK (restrict), `customer_id` FK nullable, `user_id` FK nullable (created by), `code` unique (BK-YYYYMMDD-#####), `source` enum(counter,online,admin), `status` enum(pending,confirmed,checked_in,completed,cancelled,no_show) — **the app only writes the four admin-facing statuses `pending/confirmed/completed/cancelled`** (legacy enum values retained for rollback safety) —, `payment_status` enum(unpaid,paid) **(single source of truth for revenue)**, `paid_at` nullable, `payment_method` nullable, `starts_at`, `ends_at`, `duration_minutes`, `estimated_cost`, `final_cost`, `extended_minutes`, `checked_in_at`, `ended_at`, `cancelled_at`, `cancellation_reason`, `notes`
- Index: (`resource_id`,`starts_at`,`ends_at`) — powers overlap prevention; `payment_status` indexed for totals

### payments
Money received.
- Columns: `id` PK, `business_id` FK, `booking_id` FK nullable, `customer_id` FK nullable, `user_id` FK (received by), `code` unique (PAY-YYYYMMDD-#####), `amount`, `method` enum(cash,card,online,wallet), `kind` enum(advance,full,partial,extension,addon), `notes`

### users (modified default)
- Added: `role` enum(admin,staff,customer) default customer, `is_active` bool

### settings
Key-value app settings (`key` PK, `value`). Keys used: `auto_confirm_online` (0/1), `cancellation_window_hours`. Cached forever under `settings.all` (invalidated on write).

---

## 5. Implemented Features

- [x] Laravel 11 + Breeze installation
- [x] Authentication (login/register/reset) + role system (admin/staff/customer)
- [x] Admin dashboard (stats: live sessions, today bookings/revenue, occupancy, upcoming)
- [x] Live Floor (running sessions, overstay alerts, starting soon, free resources, auto-refresh 30s)
- [x] Counter/POS (walk-in creation, future reservations, quick +30min extend, end & bill)
- [x] Booking management (list/filter/search, create with live price preview, detail + payment recording)
- [x] Calendar day-view grid (per-resource slots, category colors, prev/next/today nav)
- [x] Active Sessions lifecycle — check-in / extend (availability-checked) / end (auto overstay billing) / cancel
- [x] Customer management (CRUD, profile with history, auto-created from walk-ins & online bookings)
- [x] Resource management (CRUD, categories, base rates, maintenance status)
- [x] Category management (CRUD, calendar colors)
- [x] Pricing engine (window rules, peak/off-peak, priority precedence, min-billing)
- [x] Pricing rules admin (CRUD via modals)
- [x] Payments (auto-settle on end-session, manual recording, list + totals)
- [x] Reports (revenue by day/method, bookings by status, top resources, top customers)
- [x] Staff management (admin-only)
- [x] Settings (business hours incl. overnight, currency, timezone, auto-confirm, cancellation window)
- [x] Online booking flow (slot grid → price preview → request → confirmation page → pending status)
- [x] Customer booking history (my-bookings)
- [x] AJAX endpoints (slots, price) shared by public + counter + admin
- [x] Demo seeder (users, business, 6 categories, 11 resources, pricing rules, customers, bookings, payment)
- [x] Test suite (42 tests: booking engine, overlap, extensions, payments, pricing windows, auth/roles, all admin screens render with seeded data)
- [ ] Online payment gateway (blocked: no credentials — see KNOWN_ISSUES.md)
- [ ] Email/SMS notifications (blocked: no mail/SMS provider configured)
- [ ] No-show marking UI (status exists in DB, no button yet)
- [ ] PDF receipts / printing

---

## 6. Current Development Status

**Completed:** All core modules above — the system is functionally complete end-to-end (walk-in → live session → extend → end → payment → reports; online booking → pending → staff check-in).

**Known gaps:** payment gateway, notifications, no-show UI, receipts (see checklist + KNOWN_ISSUES.md).

**Known bugs:** none open — all 66 tests pass. Watch `KNOWN_ISSUES.md`.

**Payment model (2026-09-30):** `bookings.payment_status` (unpaid/paid) drives every revenue total
(Today's Revenue, Total Payment, Paid, Unpaid/Due). `BookingService::setPaymentStatus()` keeps the
payments ledger in sync without duplicates: paid records one settle payment for the outstanding
balance; unpaid removes only that auto settle row (advances/manual payments stay). Live sessions are
internal state (`checked_in_at`/`ended_at` on `confirmed` bookings) — the four statuses exposed
everywhere are Pending/Confirmed/Completed/Cancelled. Profile pages are themed per layout
(admin shell / public shell). Alpine.js is booted in `app.js` (dropdown fix).

**Temporary implementations:**
- Booking/payment codes use count+1 sequence (collision-safe for single-insert; not for high concurrency under multiple POS terminals)
- Cancel does not auto-refund advance payments (policy decision pending)

---

## 7. Booking Logic

**Availability calculation** (`AvailabilityService::slotsForResource`)
1. Resource must be `active` (maintenance/inactive → no slots).
2. Business hours from `businesses` (overnight adds a day to close time).
3. 30-min slot grid from open→close; each slot checked against busy ranges (active bookings) via interval overlap: `start < existingEnd && end > existingStart`.

**Overlap prevention** — three layers:
1. `Booking::overlapping()` scope used in validation
2. Re-check inside `DB::transaction` on create (race protection)
3. Composite DB index for performance

**Start/end/duration:** `ends_at = starts_at + duration_minutes`. Extensions increase `ends_at`, `duration_minutes`, `extended_minutes`. Seconds are truncated (`startOfMinute`) for consistency.

**Hourly pricing** (`PricingService::calculate`):
1. Load active rules applicable to resource (resource > category > business-wide, sorted by priority desc).
2. Split [start,end) at every rule-window edge → segments.
3. Each segment billed per-minute at its matching rule's rate (or resource base rate if none).
4. Whole booking shorter than a rule's `min_minutes` → charged the minimum.

**Peak pricing:** any rule matching a day/time window, e.g. Snooker 18:00–02:00 @ 600 with overnight=true; off-peak uses resource base rate.

**Extensions:** `extend()` re-validates overlap for [currentEnd, currentEnd+extra), computes extension price, updates totals. Blocked if next slot is taken.

**Cancellations:** allowed unless completed/cancelled; sets `cancelled_at`, `cancellation_reason`; frees the slot immediately (cancelled bookings don't block).

**Walk-ins:** `BookingService::walkIn()` = create(allow_past=true) + checkIn(). The allow-past flag skips past-start and business-hours checks (counter is physically open; sessions may start in the previous business day's window).

**Online bookings:** `PublicBookingController@store` → status `pending`, customer auto-created by phone. Staff confirms/checks-in from the admin panel. If `auto_confirm_online` setting is '1', bookings may be auto-confirmed (setting plumbed; default off).

**Admin bookings:** same `BookingService::create()` with `source=admin`, arbitrary future times, optional advance payment.

**End session:** overstay beyond `ends_at` (whole minutes) is auto-priced and billed; outstanding balance is recorded as a payment automatically.

---

## 8. Business Rules

1. A resource cannot have overlapping active bookings (checked twice + indexed).
2. Maintenance/inactive resources cannot be booked.
3. Bookings cannot start in the past (except walk-ins) or outside business hours (except walk-ins).
4. Extensions require availability until the new end time.
5. Pricing follows configured rules; resource base rate is the fallback; min-minutes enforced.
6. Online, walk-in, and admin bookings all use the same booking engine.
7. Cancelled/completed bookings do not block time slots.
8. Ending a session bills overstay automatically (whole minutes only).
9. Every payment updates the customer's `total_spent` aggregate.
10. Resources/customers/categories with history are soft-blocked from deletion (resources deactivated instead).
11. Staff may operate everything; only admin may delete data, manage staff, or change settings.

---

## 9. Routes Documentation

**Public (no auth):**
| Method | URL | Controller | Purpose |
|---|---|---|---|
| GET | `/` | PublicBookingController@index | Landing + booking widget |
| GET | `/book/slots` | @slots | AJAX slot grid |
| GET | `/book/price` | @price | AJAX price preview |
| POST | `/book` | @store | Create online booking |
| GET | `/book/confirmation/{code}` | @confirmation | Confirmation page |
| GET/POST | `/login`, `/register`, `/forgot-password`, … | Breeze | Auth |
| GET | `/dashboard` | closure | After-login redirect (admin→dashboard, customer→my bookings) |

**Admin/Staff (`auth` + `role:admin,staff`, prefix `/admin`):**
| Method | URL | Name | Purpose |
|---|---|---|---|
| GET | /dashboard | admin.dashboard | Stats |
| GET | /live-floor | admin.live-floor | Live floor |
| POST | /sessions/{booking}/check-in | admin.sessions.check-in | Start session |
| POST | /sessions/{booking}/extend | admin.sessions.extend | Extend (AJAX) |
| POST | /sessions/{booking}/end | admin.sessions.end | End + settle |
| POST | /sessions/{booking}/cancel | admin.sessions.cancel | Cancel |
| GET/POST | /bookings, /bookings/create, /bookings/{booking} | admin.bookings.* | Booking CRUD |
| GET | /bookings/price | admin.bookings.price | AJAX price |
| GET | /calendar | admin.calendar | Day grid |
| GET | /counter | admin.counter.index | POS screen |
| GET | /counter/slots, /counter/price | admin.counter.* | AJAX |
| POST | /counter/walk-in, /counter/reserve | admin.counter.* | POS actions |
| GET/POST/PATCH/DELETE | /customers… | admin.customers.* | Customer CRUD (DELETE: admin) |
| GET/POST/PATCH/DELETE | /activities… | admin.activities.* | Gaming-activity CRUD (DELETE: admin) |
| GET/POST/PATCH/DELETE | /categories… | admin.categories.* | Category CRUD (DELETE: admin) |
| GET/POST/PATCH/DELETE | /pricing… | admin.pricing.* | Pricing rules (DELETE: admin) |
| GET/POST | /payments… | admin.payments.* | Payment list/record |
| GET | /reports | admin.reports.index | Reports |
| GET/PATCH | /settings | admin.settings.* | Settings (admin) |
| GET/POST/PATCH/DELETE | /staff… | admin.staff.* | Staff mgmt (admin) |

**Customer (auth):** GET `/my-bookings` (admin.public.my-bookings), profile routes (Breeze).

---

## 10. Admin Panel Documentation

- **Dashboard** — KPI cards (live sessions, bookings today, revenue today, occupancy %), upcoming bookings, quick actions.
- **Live Floor** — overstay alert box, running-session cards with countdown targets, starting-soon (1h), free resources; auto-refresh 30s; extend/end/check-in/cancel actions.
- **Bookings** — filterable list (status tabs, search, date), create form with live price preview + customer picker, detail page with payments + record-payment form.
- **Calendar** — resource × 30-min-slot day grid, colored by category, click-through to booking, category filter, prev/next/today.
- **Active Sessions** — handled via Live Floor + Counter (checked_in bookings).
- **Customers** — list/search, create/edit, profile with bookings + payments history and totals.
- **Resources** — list/filter (category, status), create/edit (category, capacity, base rate, status), delete-protection.
- **Categories** — list with resource counts, CRUD with color picker.
- **Pricing** — rules table, create/edit modals, precedence explanation banner.
- **Payments** — totals (today/month), filter by method/date, record manual payments.
- **Reports** — monthly revenue by day (bar), by method, top resources/customers, status counts.
- **Staff** — admin-only staff CRUD (admin/staff roles, enable/disable).
- **Settings** — business profile, hours + overnight toggle, currency/timezone, auto-confirm, cancellation window.

## 11. Counter/POS Documentation

Workflow: **Customer → Resource → Date/Time → Duration → Price → Payment → Check-in → Live session → Extend → End & settle.**

1. Counter screen shows live sessions, upcoming (1h), and free-resource quick buttons.
2. `+ New Sale / Booking` (or a resource button) opens the modal: pick resource, start (defaults now), duration, optional customer name/phone, optional advance.
3. Live price preview via AJAX; slot validation prevents conflicts.
4. Mode **Walk-in** creates + checks in immediately (source=counter). Mode **Reserve** creates a future confirmed booking.
5. Running cards: `+30 min` quick extend, `End & Bill` (cash) settles overstay + balance automatically.
6. Everything is AJAX with JSON responses; page refreshes on success.

## 12. Frontend Documentation

**Customer flow:** Landing shows category cards (colors, descriptions) → resource buttons open the booking modal → pick date → slot grid loads (green = available, gray/struck = booked) → select slot → price preview → fill name/phone → submit → confirmation page (booking code, details, pending/confirmed state, "pay at counter").

**My Bookings:** logged-in customers see history with status badges and amounts.

**Payment flow:** online bookings are pay-at-counter; the counter settles on end-session. No gateway yet.

**UI kit:** Tailwind classes, modals with plain JS, `fetch` + CSRF meta tag; sidebar collapses on mobile.

## 13. API/AJAX Documentation

All AJAX routes are session-authenticated (public ones open) and return JSON.

**GET /book/slots** or **/admin/counter/slots**
- Params: `resource_id` (int), `date` (Y-m-d)
- Response: `{"slots":[{"time":"14:00","label":"2:00 PM","available":true}, ...],"business_open":true,"message":null}`
- Purpose: slot grid for a resource/date.

**GET /book/price, /admin/counter/price, /admin/bookings/price**
- Params: `resource_id`, `starts_at` (Y-m-d\TH:i), `duration_minutes` (int)
- Response: `{"ok":true,"message":null,"price":{"total":900,"minutes":120,"segments":[{"start":"17:00","end":"18:00","minutes":60,"rate":300,"rule":null,"cost":300},{"start":"18:00","end":"19:00","minutes":60,"rate":600,"rule":"Snooker Peak (18:00–02:00)","cost":600}]}}`
- Purpose: live pricing + availability check.

**POST /admin/sessions/{booking}/extend** — body `{"minutes":30}` → `{"ok":true,"message":"...","new_end":"8:30 PM","final_cost":"450.00"}`; 422 on conflict.

**POST /admin/sessions/{booking}/check-in | /end | /cancel** — end body `{"method":"cash"}`; cancel body `{"reason":"..."}`; all → `{"ok":true,"message":"..."}`; 422 on invalid transition.

**POST /book** — JSON body `{resource_id, starts_at, duration_minutes, name, phone, email?, notes?}` → `{"ok":true,"code":"BK-..."}` (client redirects to confirmation).

**POST /admin/counter/walk-in | /reserve** — form-JSON `{resource_id, starts_at, duration_minutes, customer_name?, customer_phone?, advance_amount?, advance_method?, notes?}` → `{"ok":true,"booking_code":"BK-..."}`.

## 14. Configuration

- **.env keys:** `APP_NAME`, `APP_URL`, `DB_CONNECTION` (sqlite dev / mysql prod), `DB_DATABASE`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, `MAIL_*` (unconfigured), `CACHE_STORE=database`.
- **MySQL switch:** set `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=`, `DB_USERNAME=`, `DB_PASSWORD=` then `php artisan migrate --seed`. All migrations are MySQL-compatible.
- **No secrets in these docs.** Payment gateway keys (future) go in `.env` only.
- **Cron/queues:** none required today. If email notifications are added: schedule `php artisan schedule:run` every minute and run `php artisan queue:work` (database driver already configured).

## 15. Installation Guide

1. Copy/clone the `booking-zone` folder.
2. `composer install`
3. `cp .env.example .env` → configure DB (see §14) → `php artisan key:generate`
4. Create the database (MySQL: `CREATE DATABASE booking_zone CHARACTER SET utf8mb4;` — SQLite file is auto-created).
5. `php artisan migrate`
6. `php artisan db:seed` (demo admin `admin@example.com` / `password`, staff `staff@example.com` / `password`)
7. `php artisan storage:link`
8. Permissions (Linux): `chmod -R 775 storage bootstrap/cache`
9. Cron (only if notifications added later): `* * * * * php /path/artisan schedule:run`
10. Dev: `php artisan serve` → http://127.0.0.1:8000 — Production: point web root to `public/`, `npm run build` (assets already committed).

## 16. Deployment Documentation

**Requirements:** PHP ≥ 8.2 with extensions: pdo (pdo_mysql/sqlite), mbstring, openssl, tokenizer, xml, ctype, json, fileinfo, gd (future receipts). Composer 2, Node 18+ (build only).

**Local:** `php artisan serve` (XAMPP: place in htdocs, or use serve to avoid Apache vhost hassle).

**cPanel:** upload to home dir (not public_html), point the domain's document root to `public/` (or create a symlink), set PHP version 8.2+, run `composer install --no-dev` via SSH or Terminal, configure `.env` with cPanel MySQL credentials, `php artisan migrate --force --seed`.

**VPS (nginx):** root → `public/`; try_files `$uri $uri/ /index.php?$query_string`; php-fpm 8.2; run `php artisan config:cache route:cache view:cache`; supervise `queue:work` if used.

**Production checklist:** `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, real DB credentials, `npm run build`.

## 17–18. Bug log & Changelog

See **`KNOWN_ISSUES.md`** and **`CHANGELOG.md`** (kept as separate living files).

## 19. Agent handover

See **`AGENT_HANDOVER.md`** — the quick-start file for any new agent.

## 20. Important File Tracking

| File | Purpose |
|---|---|
| `app/Services/BookingService.php` | Booking engine: create/walkIn/checkIn/extend/endSession/cancel/addPayment |
| `app/Services/AvailabilityService.php` | Slot grids, overlap + business-hours validation |
| `app/Services/PricingService.php` | Window-based price calculation |
| `app/Models/Booking.php` | Central model + `overlapping()` scope + status constants |
| `app/Http/Middleware/EnsureUserHasRole.php` | `role:` middleware |
| `app/Http/Controllers/Counter/CounterController.php` | POS controller |
| `app/Http/Controllers/Admin/SessionController.php` | Session lifecycle endpoints |
| `app/Http/Controllers/PublicBookingController.php` | Customer portal |
| `routes/web.php` | All routes (documented §9) |
| `resources/views/layouts/admin.blade.php` | Admin shell (sidebar/topbar/alerts) |
| `resources/views/counter/index.blade.php` | POS screen |
| `resources/views/public/index.blade.php` | Booking widget |
| `database/seeders/DatabaseSeeder.php` | Full demo dataset |
| `tests/Feature/BookingEngineTest.php` | Engine test coverage |

## 21–23. Process rules

- Implement → test → fix → update **PROJECT_PROGRESS.md, CHANGELOG.md, AGENT_HANDOVER.md**, and KNOWN_ISSUES.md when needed — immediately after each meaningful step.
- Never document unimplemented features; docs must mirror reality.
- Continue to the next logical task automatically; no placeholders.
