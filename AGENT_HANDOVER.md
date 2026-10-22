# AGENT_HANDOVER.md

> **New AI agent / developer? Start here. Then read `PROJECT_DOCUMENTATION.md` (current, authoritative state) and `KNOWN_ISSUES.md` (open bugs). `PROJECT_PROGRESS.md` is the earlier build log. Do not ask the user to re-explain the project.**

Last updated: 2026-10-20

---

## What this project is

A **Laravel 11 time-based booking system** for gaming venues (snooker, PS5, PC, pool, VIP rooms). It sells *time* on *resources*: online reservations, back-office walk-in creation, live-session tracking with extensions, window-based pricing, payment settlement, reports and a day calendar.

**Location:** `booking-zone/` (Laravel root). Dev DB is **MySQL/MariaDB** (`booking_zone`).

## Current status

- **Functionally complete end-to-end.** All core modules implemented and tested: **56 passing** (`php artisan test`), including a screen-render pass over every admin page.
- **Admin UI** is fully on one neon design system (bookings, calendar, customers, categories, pricing, payments, reports, settings, users, roles, logs, config).
- **Not implemented:** payment gateway, email/SMS notifications, no-show UI action, receipts — see `KNOWN_ISSUES.md` (mostly missing external credentials).

## Architecture in 60 seconds

- **One booking engine**, two entry points: `BookingService::create()` is used by the back office (`BookingController@store`, incl. walk-ins via `walkIn()`) and online (`PublicBookingController@store`). Controllers contain no business logic.
- **Services** (`app/Services/`):
  - `AvailabilityService` — slot grids, admin-configured windows, overlap + business-hours validation. `validate($resource, $start, $end, $ignoreId, $allowPast)`.
  - `PricingService` — splits time into segments at rule-window edges, bills per-minute by highest-priority matching rule, falls back to `resources.hourly_rate`, enforces `min_minutes`.
  - `BookingService` — create / walkIn / checkIn / extend / endSession / cancel / changeStatus / addPayment.
  - `ActivityLogger` — audit-trail writer.
- **Roles (DB-backed):** Super Admin, Admin, Booking Manager, Staff, Customer. `permission:` middleware (`EnsureUserHasPermission`) + per-permission gates; `role:` alias (`EnsureUserHasRole`). `users.role` is kept as a legacy mirror.
- **Time:** everything stored in app timezone; inputs parsed with `Carbon::parse(...)->startOfMinute()` (no TZ arg — see resolved ISSUE-001).

## Important files

| File | Why it matters |
|---|---|
| `app/Services/BookingService.php` | The engine — start here |
| `app/Services/PricingService.php` | Price math (segments) |
| `app/Services/AvailabilityService.php` | Slots + validation rules |
| `app/Models/Booking.php` | status constants, `statusBadge()`, scopes |
| `routes/web.php` | Every route, grouped public / auth / admin (`role:` + `permission:`) |
| `app/Http/Controllers/Admin/SessionController.php` | check-in/extend/end/cancel for live sessions |
| `resources/views/admin/bookings/show.blade.php` | Booking detail incl. the live-session controls |
| `resources/views/layouts/admin.blade.php` | Admin shell — new admin pages must extend this |
| `resources/css/app.css` | The `.neon-*` component classes (single design system) |
| `resources/views/components/mobile-slider.blade.php` | Phone swipe carousel (grid from `sm` up); dots live in `resources/js/public.js` |
| `resources/views/components/whatsapp-button.blade.php` | Public WhatsApp CTA — `Business::whatsAppUrl()` + `config/site.php` |
| `config/photo_credits.php` | Source/licence of every third-party photo (rendered at `/photo-credits`) |
| `database/seeders/DatabaseSeeder.php` | Demo data (admin@example.com / password) |
| `tests/Feature/BookingEngineTest.php` | Engine coverage — extend when changing logic |

## Important tables

`bookings` (core: status enum pending→confirmed→checked_in→completed / cancelled / no_show; starts_at/ends_at/duration_minutes/final_cost), `resources` (gaming activities; status active/inactive/maintenance, hourly_rate fallback), `pricing_rules` (day/time windows, priority, overnight), `customers` (unique business+phone), `payments` (kind/method), `businesses` (hours, overnight flag), `roles`/`permissions` + pivots, `activity_logs`, `booking_fields`/`booking_field_values`/`time_slots`, `settings` (kv, cached).

## Booking + pricing logic (essentials)

1. **Overlap rule:** two active bookings conflict iff `start < otherEnd && end > otherStart`. Enforced in validate + inside a DB transaction (race-safe).
2. **Walk-ins** pass `allow_past=true` → skips past-start and business-hours checks (venues are open now; overnight venues have sessions crossing midnight).
3. **Extend:** re-checks overlap for the extra window only; blocked if the next slot is taken; adds `extended_minutes`, recomputes `final_cost`.
4. **End session:** whole-minute overstay is auto-priced and billed; remaining balance auto-recorded as payment (method param).
5. **Pricing example:** 17:00–19:00 with Peak rule 18:00–02:00 @600 (category Snooker) + base rate 300 → 300 + 600 = 900.
6. **Online bookings** land as `pending`; staff checks them in (auto-confirm setting exists, default off).

## Current task

None — clean state. Last completed: **public-site polish (2026-10-20)** — WhatsApp hero CTA (number from
business settings, prefilled message), genuine 24/7 hours + branding everywhere (no open/closed logic),
reusable `<x-mobile-slider>` swipe carousels with synced pagination dots on the homepage, licensed
activity/gallery photography stored locally with a `/photo-credits` page, and one shared page container
(`max-w-6xl px-4`) across all inner pages.

## Next recommended tasks (in order)

1. **No-show UI** (small, no external deps) — add a mark-no-show action on the booking detail page.
2. **Receipts** — printable receipt view for completed bookings (Blade print CSS; no dependency).
3. **Booking code robustness** (ISSUE-007) — retry loop on unique collision.
4. **Notifications** (needs mail/SMS credentials) — Mailables + optional schedule.
5. **Payment gateway** (needs provider account) — see ISSUE-004 plan.

## Known problems

See `KNOWN_ISSUES.md` — all open items are external-dependency or policy blockers except the low-risk code-sequence issue. Nothing currently broken.

## Key implementation decisions

- **Custom engine, no packages** — full control over overlap/pricing semantics.
- **MySQL dev DB** (`booking_zone`); tests run on in-memory SQLite (`phpunit.xml`) so they never touch dev data.
- **Services over controllers** — mandatory pattern for any new booking-related feature.
- **One neon design system** — reuse the `.neon-*` classes in `resources/css/app.css`; do not introduce a second visual language. New listings should use `data-confirm="…"` forms (global modal) and the neon paginator.
- **Vanilla JS + fetch** for AJAX (no Alpine/Inertia) — keep it that way unless asked.
- **Codes are count+1 based** (BK-YYYYMMDD-#####) — acceptable single-terminal; fix planned.

## Commands

```bash
cd booking-zone
composer install                 # deps
php artisan key:generate         # first run
php artisan migrate --seed       # schema + demo data (MySQL)
php artisan test                 # run all tests (must stay green; uses in-memory SQLite)
php artisan serve                # dev server http://127.0.0.1:8000
npm run build                    # rebuild assets only after editing CSS/JS
php artisan pail                 # (optional) watch logs
```

**Demo logins (password `password`):** admin@example.com (Super Admin) · manager@example.com (Booking Manager) · staff@example.com (Staff) · customer@example.com (Customer).

**Manual smoke test:** login as admin → **Bookings → New booking** → create one; open the booking → check-in → extend → End & settle → verify in Payments & Reports. Public: `/` → pick a resource → slot grid → request booking → confirmation.
