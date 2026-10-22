# BookingZone — Project Documentation

> Living document. Update after **every** major development stage so another developer or AI
> agent can continue the project without prior conversation context.

Last updated: **2026-09-30** · Status: **production-ready core, MySQL-backed, all tests green (56 passing)**

---

## 1. What this project is

A Laravel 11 gaming/entertainment venue management system with:

- A **customer booking portal** (register → pick activity → pick date/time → see price → confirm → track).
- A **back office** for bookings (incl. walk-in creation), customers, activities, pricing, booking
  configuration, users, roles/permissions, reports, a day calendar and a full **audit trail**.
- **Live sessions** (check-in, extend, settle, cancel) run from the booking detail page for staff.
- **Neon dark gaming UI** (black + red neon) shared by the customer site and the admin panel.

Internal terminology: the database table `resources` holds **gaming activities** (snooker table,
PS5 booth, PC station, VIP room). It is the bookable unit carrying name, description, image, price
and default duration. The UI always calls them *Gaming Activities*.

---

## 2. Stack & environment

| Item | Value |
| --- | --- |
| Framework | Laravel 11.x |
| Starter kit | Laravel Breeze 2.x (Blade + Alpine) |
| PHP | 8.3.16 (XAMPP at `C:\xampp2\php\php.exe`) |
| Database | **MySQL / MariaDB 10.4.32** (XAMPP) — database `booking_zone` |
| CSS | Tailwind CSS 3 + Vite 6 |
| Tests | PHPUnit 11 (`php artisan test`) |
| Frontend build | `npm run build` |

### Environment (`.env`)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_zone
DB_USERNAME=root
DB_PASSWORD=
APP_NAME=BookingZone
```

Test runs use in-memory SQLite (`phpunit.xml`) so the MySQL dev database is never wiped.

### First-time setup

```bash
composer install
npm install
cp .env.example .env            # then set DB_DATABASE=booking_zone
php artisan key:generate
# create the database (XAMPP MySQL must be running):
#   C:/xampp2/mysql/bin/mysql.exe -u root -e "CREATE DATABASE booking_zone CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate:fresh --seed
php artisan storage:link         # activity images
npm run build
php artisan serve
```

### Demo accounts (from `DatabaseSeeder`)

| Email | Password | Role |
| --- | --- | --- |
| `admin@example.com` | `password` | Super Admin |
| `manager@example.com` | `password` | Booking Manager |
| `staff@example.com` | `password` | Staff |
| `customer@example.com` | `password` | Customer (has demo bookings) |

---

## 3. Database schema (MySQL, normalized, FK + indexes)

### Core booking tables

| Table | Purpose | Key points |
| --- | --- | --- |
| `businesses` | Venue | `opens_at`/`closes_at`/`overnight`, `currency`, `slot_minutes`, `min_duration_minutes`, `max_advance_days` |
| `categories` | Groups activities | `slug` unique per business, calendar `color` |
| `resources` | **Gaming activities** | `category_id`, `image_path`, `description`, `capacity`, `duration_minutes`, `hourly_rate`, `status`, `is_online_bookable`, `featured`, `sort_order`, `meta` |
| `pricing_rules` | Differential pricing | window (`start_time`/`end_time`/`overnight`/`day_of_week`), `hourly_rate`, `min_minutes`, `priority` |
| `customers` | Customer records | `business_id`, **`user_id`** (links an account), `phone` unique per business, `total_visits`, `total_spent` |
| `bookings` | Reservations | `code` unique, `source`, `status`, `starts_at`/`ends_at` (datetime), costs, session timestamps; index `(resource_id, starts_at, ends_at)` |
| `payments` | Money received | `booking_id`, `customer_id`, `amount`, `method`, `kind` |
| `settings` | Key/value config | cached via `Setting::allCached()` |

### RBAC tables (added in the production upgrade)

| Table | Purpose |
| --- | --- |
| `roles` | `name`, `slug` (unique), `description`, `is_system` |
| `permissions` | `name`, `slug` (unique), `group`, `description` |
| `permission_role` | pivot, unique `(role_id, permission_id)` |
| `role_user` | pivot, unique `(role_id, user_id)` |

`users.role` (enum `admin|staff|customer`) is kept as a **legacy mirror** of the highest assigned
role so older code/redirects keep working. It is synced automatically by `User::syncLegacyRole()`.

### Dynamic booking configuration tables

| Table | Purpose |
| --- | --- |
| `booking_fields` | Admin-defined form fields: `resource_id` (null = all activities), `key`, `label`, `type`, `placeholder`, `options` (JSON), `default_value`, `is_required`, `is_visible`, `is_active`, `sort_order` |
| `booking_field_values` | Answers per booking (unique `(booking_id, booking_field_id)`) |
| `time_slots` | Admin-defined bookable windows: `resource_id` (null = shared), `label`, `start_time`, `end_time`, `day_of_week`, `slot_minutes`, `is_active`, `sort_order` |

### Audit table

| Table | Purpose |
| --- | --- |
| `activity_logs` | `user_id`, `action` (indexed), `description`, `subject_type`/`subject_id` (morph), `ip_address`, `user_agent`, `meta` (JSON), timestamps; indexes on action, subject and created_at |

### Migrations

`2026_01_01_*` (core) and `2026_02_01_*` (production upgrade):

```
000010_create_roles_and_permissions_tables
000020_create_activity_logs_table
000030_add_config_fields_to_resources_table
000040_create_booking_fields_table
000050_create_time_slots_table
000060_create_booking_field_values_table
000070_add_booking_policy_to_businesses_table
```

---

## 4. Roles & permissions (backend-enforced)

### Permission catalog — `App\Models\Permission::CATALOG`

| Group | Slugs |
| --- | --- |
| Bookings | `bookings.view`, `bookings.create`, `bookings.edit`, `bookings.delete`, `bookings.status` |
| Activities | `activities.manage` |
| Customers | `customers.manage` |
| Pricing | `pricing.manage` |
| Users | `users.manage`, `roles.manage` |
| Reports | `reports.view`, `logs.view` |
| System | `config.manage`, `settings.manage` |

### Default role matrix (`App\Models\Role::MATRIX`)

| Role | Permissions |
| --- | --- |
| **Super Admin** | everything (also bypasses all gates via `Gate::before`) |
| **Admin** | everything |
| **Booking Manager** | bookings view/create/edit/status, activities, customers, pricing, config, reports, logs |
| **Staff** | bookings view/create/status, customers |

### Enforcement (never front-end only)

1. **Route middleware** — `permission:bookings.view` (`EnsureUserHasPermission`) on every admin route.
2. **Role middleware** — `role:admin,staff,booking_manager` (`EnsureUserHasRole`) on the `/admin` group.
3. **Gates** — `AppServiceProvider` defines one gate per permission slug, so `$this->authorize('bookings.view')` works in controllers.
4. **Model-level guards** — `User::hasPermissionTo()` and `Booking::scopeForUser()` for ownership.
5. **Ownership checks** — customer booking detail pages 403 unless the account owns the booking.

The sidebar hides links a user cannot use, but hiding is cosmetic — the server rejects the request
independently.

---

## 5. Activity / audit logging

`App\Services\ActivityLogger` is the single funnel. Injected into `BookingService`, the auth
controllers and the config/CRUD controllers.

Logged actions (`ActivityLogger` constants): `auth.login`, `auth.logout`, `auth.registered`,
`booking.created`, `booking.updated`, `booking.status_changed`, `booking.cancelled`,
`activity.created`, `activity.updated`, `activity.deleted`, `price.changed`, `customer.created`,
`customer.updated`, `user.created`, `user.updated`, `user.deleted`, `role.updated`,
`settings.updated`, `config.updated`.

Each entry stores user, action, description, related record (morph), timestamp, IP address and
user agent. Admin page: **`/admin/activity-logs`** with free-text search, action filter, user
filter and date range.

---

## 6. Routes

### Public / customer

| Method | URI | Name | Notes |
| --- | --- | --- | --- |
| GET | `/` | `public.index` | Landing + catalogue |
| GET | `/activities/{resource}` | `public.activity` | Booking planner |
| GET | `/book/slots` | `public.slots` | AJAX availability |
| GET | `/book/price` | `public.price` | AJAX price breakdown |
| POST | `/book` | `public.store` | **auth required** |
| GET | `/book/confirmation/{code}` | `public.confirmation` | **auth + owner** |
| GET | `/dashboard` | `dashboard` | Role-aware redirect |
| GET | `/customer/dashboard` | `customer.dashboard` | Customer dashboard |
| GET | `/my-bookings` | `public.my-bookings` | Booking history |
| GET | `/my-bookings/{booking}` | `public.booking` | Booking detail (**owner only**) |

### Admin (`/admin`, `role:admin,staff,booking_manager`, per-route `permission:`)

| Area | Routes |
| --- | --- |
| Dashboard / calendar | `admin.dashboard`, `admin.calendar` |
| Live session lifecycle | `admin.sessions.check-in/extend/end/cancel` (`permission:bookings.status`) |
| Bookings | `admin.bookings.index/create/store/show/price`, `admin.bookings.status` (PATCH), **`admin.bookings.export`** (CSV) |
| Gaming activities | `admin.activities.index/create/store/edit/update/destroy` |
| Categories | `admin.categories.*` |
| Pricing | `admin.pricing.*` |
| Booking configuration | `admin.config.index/update`, `admin.fields.*`, `admin.slots.*` |
| Customers | `admin.customers.*` |
| Payments / reports | `admin.payments.*`, `admin.reports.index` |
| Users / roles / logs | `admin.users.*`, `admin.roles.*`, `admin.logs.index` |
| System settings | `admin.settings.*` |

---

## 7. Application architecture

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/           Dashboard, Booking, Resource(activities), Category, PricingRule,
│   │   │                    Customer, Payment, Report, Settings, Calendar, Session,
│   │   │                    BookingConfig, BookingField, TimeSlot, User, Role, ActivityLog
│   │   ├── Auth/            Breeze controllers (login/logout/registration audited)
│   │   ├── Customer/        DashboardController, BookingController (history/detail)
│   │   └── PublicBookingController
│   └── Middleware/          EnsureUserHasRole (role), EnsureUserHasPermission (permission)
├── Models/                  Business, Category, Resource, PricingRule, Customer, Booking,
│                            Payment, Setting, User, Role, Permission, ActivityLog,
│                            BookingField, TimeSlot, BookingFieldValue
├── Providers/               AppServiceProvider (gates + morph map)
└── Services/                AvailabilityService, PricingService, BookingService, ActivityLogger

resources/views/
├── layouts/                 admin, public, guest
├── partials/                flash, price-breakdown
├── admin/                   dashboard, bookings, resources, categories, pricing, customers,
│                            payments, reports, settings, calendar,
│                            config, fields, slots, users, roles, logs
├── customer/                dashboard, bookings/{index,show}
└── public/                  index, activity, confirmation, _card
```

### Services

- **`AvailabilityService`** — generates a slot grid (`slotsForResource`) or honours admin-configured
  windows (`configuredSlots`), validates a proposed window against business hours + active bookings,
  and returns busy ranges. Overlap rule: conflict iff `start < otherEnd && end > otherStart`.
- **`PricingService`** — splits a range at pricing-window edges, bills per minute at the matched
  rule rate, applies `min_minutes`. Returns `total`, `minutes` and `segments` (used for the
  customer-facing breakdown).
- **`BookingService`** — the single create/check-in/extend/end/cancel/status engine used by the
  back office and the online flow. Re-checks overlap inside a DB transaction, resolves/link customers,
  stores dynamic field values and writes audit entries.
- **`ActivityLogger`** — audit trail writer.

### Invariants (do not break)

- Times are parsed with `Carbon::parse($input)->startOfMinute()` and **no timezone argument** —
  the app timezone is the single source of truth (a TZ arg caused double conversion).
- Pricing breakpoints are normalised with `startOfMinute()` to avoid microsecond phantom segments.
- Walk-ins pass `allow_past: true` to skip past-start/business-hours checks (overnight venues).
- Overlap is enforced in validation **and** re-checked inside the transaction.

---

## 8. Feature reference

### Customer

- Register (name, email, phone, password) / login / logout — all audit-logged.
- Dashboard: profile, booking stats, recent bookings.
- Booking flow: catalogue → activity → date → configured slot → duration → dynamic details →
  **live price breakdown before confirming** → confirmation with final price and segments.
- Booking history with status filter, individual booking detail, payments and amounts due.
- Duplicate/unavailable slots are rejected server-side; cancelled bookings free the slot.

### Admin

- Bookings: search (code/customer/phone/activity), filter by status/activity/date range, sort by
  column, status change (audited), CSV export; walk-in creation via **New booking**.
- Calendar: responsive day grid per activity with colour-coded blocks and a mobile agenda view.
- Live sessions: check-in, extend, end & settle and cancel from the booking detail page.
- Gaming activities: CRUD with name, description, image upload, price, duration, capacity,
  status, online-bookable flag, featured flag and display order.
- Booking configuration: visibility toggles for the customer site (descriptions, images, rates,
  planner, remaining slots, online booking, auto-confirm) plus booking policy (slot length, minimum
  duration, advance days, cancellation window).
- Booking fields: full CRUD, global or per-activity, required/visible/active flags, ordering.
- Time slots: full CRUD, shared or per-activity, weekday scoping, slot length, ordering.
- Users: search/filter, create, edit, role assignment, activate/deactivate, delete (with
  "can't delete yourself" and "can't delete the last super admin" guards).
- Roles & permissions: create custom roles, edit permission matrices; system roles protected.
- Activity logs: search + filters.
- Dashboard: live sessions, today's bookings/revenue, pending count, floor occupancy, 7-day trend
  chart, top activities, upcoming bookings, recent activity.
- Customers, pricing, payments, reports and settings screens, all on the shared neon design system.

---

## 9. Security

- CSRF tokens on every form (`@csrf`); Breeze session auth with regeneration.
- Password hashing via the `hashed` cast / `Hash::make`; `Password::defaults()` validation.
- Server-side validation on every write; `$fillable` mass-assignment protection on all models.
- Authorization: middleware + gates + ownership scopes (see §4).
- SQL injection: Eloquent/query builder with bound parameters; `booking` sort columns are
  whitelisted (`BookingController::SORTABLE`) before use.
- XSS: Blade auto-escaping everywhere; no raw user HTML output.
- File uploads: validated `image|mimes:jpg,jpeg,png,webp|max:2048`, stored on the `public` disk,
  old files deleted on replace.
- Sensitive data: only the authenticated owner (or back office) can read a booking; passwords and
  remember tokens are `$hidden`.
- Rate limiting: Breeze login throttle; Laravel defaults for the rest.

---

## 10. Testing

```
php artisan test --compact      # 56 passed (162 assertions)
```

| File | Covers |
| --- | --- |
| `tests/Feature/BookingEngineTest.php` | pricing, overlap, walk-ins, extend/end, availability grid, online flow, admin check-in, customer lockout |
| `tests/Feature/AdminScreensTest.php` | every admin screen + public pages render with seeded data; confirmation privacy |
| `tests/Feature/ProductionSystemTest.php` | RBAC seeding/enforcement, dynamic field validation, time-slot config, customer booking flow, child-ownership, audit logging + log filters |
| `tests/Feature/ExampleTest.php` | landing page |

---

## 11. Completed tasks

- [x] Connect the application to MySQL; migrations + seeders run cleanly.
- [x] Roles & permissions (Super Admin, Admin, Booking Manager, Staff) with granular permissions,
      enforced on the backend.
- [x] Activity/audit log system with a searchable admin page.
- [x] Dynamic booking configuration: activities CRUD, booking fields, time slots, display toggles,
      booking policy — admin changes reflect on the customer site with no code changes.
- [x] Customer auth-gated booking flow with price breakdown, history and detail pages.
- [x] Admin booking management: search, filter, sort, status change, CSV export, revenue totals.
- [x] User management with role assignment; role/permission matrix editor.
- [x] Neon dark UI redesign (customer + admin), responsive — **every** admin screen now uses the
      shared design system (bookings, calendar, customers, categories, pricing, payments, reports,
      settings), with neon pagination, a global confirmation modal and themed 404 page.
- [x] Automated test coverage for the new features; full suite green.

## 12. Remaining / future tasks

- [ ] Payment gateway integration (no provider credentials yet — cash/on-site payments only).
- [ ] Email/SMS notifications for booking confirmation and reminders.
- [ ] Refund handling on cancellation (advance-refund policy not yet decided).
- [ ] Booking-code generation uses `count + 1` and can race under heavy concurrency; switch to a
      sequence table or ULID when volume grows.
- [ ] `no_show` status exists but has no dedicated one-click UI action yet.

## 13. Known issues

See `KNOWN_ISSUES.md`. Current highlights:

1. Payment gateway absent (no credentials available).
2. Notification providers not configured.
3. Booking code sequence concurrency (see above).
4. `mysql` CLI is not on `PATH`; use the full path `C:/xampp2/mysql/bin/mysql.exe`.

## 14. Deployment notes

1. Set production `.env` values (`APP_ENV=production`, `APP_DEBUG=false`, strong `DB_PASSWORD`,
   real `APP_URL`).
2. `composer install --no-dev --optimize-autoloader`
3. `npm ci && npm run build`
4. `php artisan migrate --force` and `php artisan db:seed --class=RolePermissionSeeder --force`
   (roles/permissions must exist before users are assigned).
5. `php artisan storage:link`
6. `php artisan config:cache route:cache view:cache`
7. Web root → `public/`; ensure `storage/` and `bootstrap/cache/` are writable.
8. Ensure the web server passes `Authorization`/`X-CSRF-TOKEN` headers and `LimitRequestBody`
   allows the 2 MB image upload.
