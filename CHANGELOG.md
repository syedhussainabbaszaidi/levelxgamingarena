# Changelog

All notable changes to the Gaming Zone Booking System.

## 2026-10-20 — WhatsApp hero CTA, 24/7 branding, mobile card sliders, licensed photos, shared page width

### Added
- **WhatsApp CTA (`<x-whatsapp-button>`)** — opens `https://wa.me/<business number>` in a new tab with
  the prefilled message “Hi, I would like to inquire about your gaming zone and activities.” The number
  comes from Admin → Settings (`businesses.phone`) with `SITE_WHATSAPP_NUMBER` as the fallback
  (`config/site.php`, `Business::whatsAppUrl()`). Used in the hero, contact page, activity page and footer.
- **Reusable mobile card slider (`<x-mobile-slider>`)** — CSS scroll-snap row on phones (one full card
  plus roughly half of the next), normal grid from `sm` upwards, with pagination dots that track manual
  swipes. Applied to activities, gaming-experience features, pricing packages, account packages,
  why-choose-us and the gallery. No new dependency (vanilla JS in `resources/js/public.js`).
- **Photo credits page** (`/photo-credits`, `config/photo_credits.php`, `public/img/PHOTO_CREDITS.md`).

### Changed
- **24/7 operating hours** — migration `2026_10_20_000020` sets `opens_at = closes_at = 00:00` (overnight)
  and rebuilds the shared slot presets (Overnight / Daytime) so the grid covers 00:00–23:00. The public
  site no longer renders any “open now / closed” logic; every hours surface shows **Open 24/7**.
  `Business::is24Hours()` / `hoursLabel()` and the availability engine (business-hours validation skipped
  for 24/7) keep the backend and the copy in sync.
- **Activity and gallery photography replaced** with licensed photographs stored locally (16:9, 1200×675),
  credited on the new page. Bundled gallery line-art and the unused uploaded placeholders were removed.
- **Inner pages now share the homepage container** (`max-w-6xl`, `px-4`): About, Contact, Photo Credits,
  legal pages, booking confirmation and activity detail no longer nest their own narrower `max-w-*`.
- The “Ready when you are” band no longer offers a **Book Now** button.

### Tests
- Suite green: **123 passing / 541 assertions**. Added homepage checks for the WhatsApp CTA, 24/7 copy and
  the removed “Ready when you are” button; updated slot-grid and gallery-markup expectations.

## 2026-09-30 — Fix gaming-activity save redirect (RouteNotFoundException)

### Fixed
- **Saving a gaming activity (create or edit, with or without an image) no longer throws
  `Route [admin.resources.index] not defined.`** — the activities routes are registered as
  `admin.activities.*`, but `ResourceController::store()` / `update()` still redirected to the
  pre-rename `admin.resources.index`. Both now redirect to `admin.activities.index`.
- Calendar blocks for live sessions pulse again: the term used the removed `checked_in` status and
  now calls `Booking::isRunning()`.
- Regression coverage: `AdminScreensTest::test_activity_store_and_update_redirect_to_the_activities_index`
  (suite now **66 passing / 217 assertions**) — the existing screen test only rendered the
  activities pages, so the broken redirects went unnoticed.

## 2026-09-30 — Payment status engine, 4-status model, themed profile, dropdown fixes

### Added
- **Paid / Unpaid as a first-class booking flag** — `bookings.payment_status` (unpaid|paid),
  `paid_at`, `payment_method` (migration `2026_09_30_000010`). The flag is the single source of
  truth for **Today's Revenue / Total Payment / Paid / Unpaid-Due** across dashboard, bookings and
  payments screens; cancelled bookings never count towards revenue.
- **Payment status toggle** — `PATCH /admin/bookings/{booking}/payment-status`
  (`admin.bookings.payment-status`, gated by `bookings.edit`), driven by
  `BookingService::setPaymentStatus()`. Marking **Paid** records exactly one settle payment for the
  outstanding balance; marking **Unpaid** removes only that auto settle row (advance/manual payments
  are preserved) — toggling repeatedly can never duplicate amounts. Toggle exists inline on the
  bookings list (instant AJAX, totals update without reload) and on the booking detail page.
- **Live counts + totals endpoint** — `GET /admin/bookings/counts` (`admin.bookings.counts`); the
  bookings page polls it (15s) and refreshes tab counts and payment cards without a page reload.
- **Themed profile page** — `profile.edit` no longer renders Breeze's light `<x-app-layout>`:
  staff/admin get the neon admin shell, customers the public shell, with shared neon partials
  (account summary, information, password, delete account).

### Changed
- **Booking statuses reduced to four** — Pending, Confirmed, Completed, Cancelled in every admin
  surface (tabs, detail form, badges, CSV). `Booking::STATUSES` shrunk accordingly; `checked_in` and
  `no_show` were removed from the UI. Live sessions are now internal state only
  (`checked_in_at`/`ended_at` on a `confirmed` booking); the DB enum keeps legacy values for safe
  rollbacks and old rows migrate (`checked_in`→`confirmed`, `no_show`→`cancelled`).
- **Revenue = paid bookings** — dashboard (`today_revenue`, `week_revenue`, new `due_amount`) and
  the payments screen (new "Outstanding due" card) all derive from the flag instead of
  `SUM(payments.amount)`, so unpaid bookings can no longer inflate revenue.
- **Booking method dropdowns** now offer **Cash | Online** only (new-booking advance, payment
  recording, end-session settle, payments filter); other methods are coerced to cash server-side.
- **Bookings list defaults to newest first** (`created_at DESC`) and no longer shows the **Source**
  column or the old "Revenue (filtered set)" filter chip; the Source column was replaced by the
  Payment column in the CSV export.
- Customer-facing views (my bookings, dashboards, detail pages) show a Paid/Unpaid badge next to
  status; my-bookings filter tabs use the four statuses.
- Alpine.js is now actually imported/started in `app.js` and `[x-cloak]` CSS added — this fixes the
  top navigation profile/logout dropdown (and the public site's) which never opened because Alpine
  was installed but never booted.
- Test suite: **65 passing / 212+ assertions** (new `PaymentStatusTest` covers the toggle ledger
  semantics, revenue deltas, 4-status counts and online-vs-manual parity).

## 2026-09-30 — Admin panel overhaul: remove Live Floor + Counter/POS, one neon design system

### Removed
- **Live Floor** removed entirely: `LiveFloorController`, `resources/views/admin/live-floor/`, the
  `admin.live-floor` route and its sidebar item.
- **Counter / POS** removed entirely: `CounterController`, `resources/views/counter/`, the
  `admin.counter.*` routes and its sidebar item.
- Session lifecycle routes (`admin.sessions.*`) are kept for the booking detail page but are now
  gated by `bookings.status` (previously `bookings.create`).
- No dangling links or empty pages remain; the admin test screen list was updated accordingly.

### Changed / Added
- **One neon design system across every admin module.** Restyled to the shared `.neon-*` component
  classes: New Booking form, Calendar, Customers (index/create/edit/show/_form), Categories,
  Pricing (+ rule modals), Payments, Reports and System Settings.
- **Neon pagination** — `resources/views/vendor/pagination/neon.blade.php` registered as the default
  paginator view in `AppServiceProvider`, so every listing's pager matches the theme.
- **Global confirmation modal** — the admin layout now routes any form or link with a
  `data-confirm="…"` attribute through a styled dialog instead of the browser-native `confirm()`.
- **Inline validation states** — new `<x-neon-error name="…" />` component and an `.input-invalid`
  field class applied across the admin forms.
- **Calendar redesign** — responsive day grid (sticky activity column, colour-coded blocks, pulsing
  marker for checked-in sessions) plus a mobile agenda list; prev/next/today/date navigation and a
  category filter.
- **Customers listing now defaults to newest-first** (`created_at DESC`) with sort options for
  name, spend and visits, plus search and pagination.
- Global top progress bar on navigation/submits and a neon 404 error page.
- Test suite: **56 passing**.

### Fixed
- Pricing rule form now pre-selects the saved weekday when editing (strict string comparison bug).

## 2026-09-30 — Production upgrade: MySQL, RBAC, dynamic config, audit logs, neon UI

### Added
- **MySQL support** — app switched from SQLite to MySQL (`booking_zone`); fixed non-null `timestamp`
  columns in `bookings` that MySQL rejects (`Invalid default value for 'ends_at'`) by using `dateTime`
- **Roles & permissions** — `roles`, `permissions`, `permission_role`, `role_user`; `Role`/`Permission`
  models with a canonical catalog; `RolePermissionSeeder` seeds Super Admin, Admin, Booking Manager,
  Staff; `EnsureUserHasPermission` middleware (`permission:`) plus per-permission gates in
  `AppServiceProvider`; super-admin bypass; legacy `users.role` kept in sync
- **Activity/audit logging** — `activity_logs` table, `ActivityLogger` service, admin page at
  `/admin/activity-logs` with search, action/user filters and date range; wired into the booking
  lifecycle, auth events and every config/CRUD controller
- **Dynamic booking configuration** — `booking_fields` (+ answers table) and `time_slots` tables with
  full admin CRUD; customer site reads them live (fields, required flags, ordering, slot windows)
- **Booking configuration page** — visibility toggles for the customer interface and booking policy
  (slot length, minimum duration, advance days, cancellation window)
- **Customer accounts flow** — registration now captures a phone number, `/customer/dashboard`, and
  auth-gated booking (`POST /book` requires login) with a live price breakdown before confirming
- **Admin booking tools** — column sorting, richer filters (activity + date range), CSV export,
  status-change action with audit note, and revenue totals
- **User management** — full user CRUD with multi-role assignment, activation and safe deletes,
  replacing the old staff-only screen
- **Neon dark UI** — red-neon/black design system (`tailwind.config.js` + `app.css` component layer),
  rebuilt admin and customer layouts, permission-aware sidebar, dashboard stat cards + 7-day trend
  chart, redesigned activities/bookings/auth/customer pages
- **Tests** — `ProductionSystemTest` (RBAC enforcement, dynamic field validation, time-slot config,
  customer flow, ownership isolation, audit log); suite now **54 passing / 158 assertions**
- **Documentation** — new `PROJECT_DOCUMENTATION.md` (living handover document)

### Changed
- `/admin/resources` is now `/admin/activities` (`admin.activities.*`) and presented as
  *Gaming Activities*; `StaffController` removed in favour of `UserController`
- `routes/web.php` rewritten: customer area, permission-gated admin sections, fixed route ordering
  so `/bookings/price` and `/bookings/export` no longer collide with `/bookings/{booking}`
- Booking confirmation pages are now private to the booking owner (or back office)
- `User::hasPermissionTo()` falls back to the legacy role when no pivot roles exist (BC for
  pre-existing admin/staff accounts)

### Fixed
- MySQL `timestamp` default error on `bookings.starts_at`/`ends_at`
- Customer phone collision when a registered account's phone matched another customer record
  (`customers.business_id + phone` unique) — now guarded before adopting a new number
- Guest layout supported neither `@extends` nor component slot correctly — now supports both
- A user with a `NULL` `is_active` flag was locked out of every permission-gated route
- **Booking form rejected with "The time field is required"** even after picking a slot: both layouts
  emitted `@stack('scripts')` inside `<head>`, so the planner's inline script ran before the body
  existed. `document.getElementById('date')` returned `null`, `addEventListener` threw, the script
  died — slots never rendered and the hidden `time` input stayed empty. Scripts now load at the end
  of `<body>` (with a `DOMContentLoaded` guard), the message reads "The time slot field is required.",
  and submitting without a slot is blocked client-side with a helpful hint. Two regression tests added.

## 2026-09-30 — Initial complete build

### Added
- Laravel 11 + Breeze (Blade) project with Tailwind/Vite build
- Role system: `users.role` (admin/staff/customer) + `role:` middleware
- Database: businesses, categories, resources, pricing_rules, customers, bookings, payments, settings (+ users.role/is_active)
- Models with relationships; `Booking::overlapping()` scope; cached `Setting` store
- Core engine: `AvailabilityService` (slots + validation), `PricingService` (window segments, peak/off-peak, min billing), `BookingService` (create/walkIn/checkIn/extend/endSession/cancel/addPayment)
- Admin panel: dashboard, live floor (auto-refresh, overstay alerts), bookings (filter/create/price-preview/detail), day calendar grid, customers, resources, categories, pricing rules, payments, reports, staff, settings
- Counter/POS: walk-in modal, future reservations, quick +30min extend, end & bill
- Session lifecycle AJAX endpoints (check-in/extend/end/cancel)
- Public portal: category cards, booking modal with slot grid + price preview, confirmation page, my-bookings
- Demo seeder (2 users, 1 business, 6 categories, 11 resources, 2 pricing rules, 5 customers, 4 bookings, 1 payment)
- Test suite: 42 passing (engine, overlap, extensions, payments, pricing windows, slots, online flow, roles, all admin screens render with seeded data)
- Documentation: PROJECT_PROGRESS.md, AGENT_HANDOVER.md, KNOWN_ISSUES.md, CHANGELOG.md

### Fixed
- Namespace collision `DashboardController` (admin vs root) causing class redeclaration
- Missing `HasFactory` on User broke Breeze tests
- Missing `dashboard` route broke auth flow tests — added role-aware redirect
- Timezone double-conversion when parsing booking times (parse without TZ arg, store app-TZ)
- Microsecond drift produced phantom pricing segments — `startOfMinute()` normalization
- Overstay billing rounded sub-minute processing delay up to a billable minute — now whole minutes only
- Walk-ins blocked by past-time/business-hours validation — `allow_past` counter mode
- Bookings index 500 when opened without filters (`$filters['status']` undefined) — defaults merged
- Settings screen 500: unqualified `Setting::get()` in Blade — fully qualified
- phpunit was hitting the dev database — enabled in-memory SQLite + `RefreshDatabase` on ExampleTest

### Changed
- Customers auto-created by phone lookup (unique per business)
- `APP_NAME=BookingZone` (was Laravel default)

### Database
- Initial schema (see PROJECT_PROGRESS.md §4)

### Next
- Online payment gateway (blocked on credentials)
- Email/SMS notifications
- No-show UI action
