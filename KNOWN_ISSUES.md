# KNOWN_ISSUES.md

> **2026-09-30 — production upgrade.** The app now runs on MySQL with RBAC, dynamic booking
> configuration and audit logs. See **`PROJECT_DOCUMENTATION.md`** for the current state, routes and
> remaining tasks. Entries below recorded before this upgrade are historical.

Open and resolved issues. Add one entry per significant bug; mark resolved with date + fix.

---

## Open

### ISSUE-004: No online payment gateway
- **Date:** 2026-09-30
- **Issue:** Online bookings are pay-on-site only; no card/wallet collection.
- **Cause:** No provider account/API credentials available.
- **Status:** Blocked (external dependency)
- **Fix (planned):** Integrate provider via webhooks; add `payments.status` + `provider_ref` columns; mark bookings paid on webhook.
- **Files:** new `app/Services/PaymentGatewayService.php`, `routes/web.php`, `app/Http/Controllers/PaymentWebhookController.php`

### ISSUE-005: No email/SMS notifications
- **Date:** 2026-09-30
- **Issue:** No confirmation/reminders sent for online bookings.
- **Cause:** No mail/SMS provider configured (credentials required).
- **Status:** Blocked (external dependency)
- **Fix (planned):** Mailables on booking created/confirmed; optional cron reminder for upcoming sessions; SMS via provider SDK.
- **Files:** `app/Mail/*`, `app/Notifications/*`, `app/Console/Kernel.php` (schedule)

### ISSUE-006: No-show status has no UI action
- **Date:** 2026-09-30 — **Closed by removal**: no-show/checked-in were removed from the admin UI per
  the latest requirements; the four statuses are Pending/Confirmed/Completed/Cancelled. Legacy
  `no_show` rows migrate to `cancelled` (`2026_09_30_000010_add_payment_status_to_bookings_table`).
- **Issue:** `no_show` exists as a booking status but staff cannot mark it from any screen.
- **Cause:** Not yet implemented (low priority).
- **Status:** Closed (status removed from the product)
- **Fix (planned):** superseded
- **Files:** `app/Http/Controllers/Admin/BookingController.php`, `resources/views/admin/bookings/show.blade.php`

### ISSUE-007: Booking code sequence uses count+1
- **Date:** 2026-09-30
- **Issue:** Concurrent booking creation on multiple terminals could theoretically duplicate `BK-...` codes (unique index would reject one insert).
- **Cause:** Simple sequence generator.
- **Status:** Open (low risk for single-counter venues)
- **Fix (planned):** Retry-on-collision loop or a dedicated sequence table/`LAST_INSERT_ID` trick.
- **Files:** `app/Services/BookingService.php` (`nextCode`, `nextPaymentCode`)
- **Note:** wording updated — walk-ins are now created from the admins' *New booking* form (Counter/POS removed).

### ISSUE-008: Cancellation does not refund advances
- **Date:** 2026-09-30
- **Issue:** Cancelling a booking with an advance payment leaves the payment recorded; no refund record.
- **Cause:** Refund policy not defined by the owner.
- **Status:** Open (policy decision required)
- **Fix (planned):** On cancel, create a negative `addon`/`refund` payment or flag for manual refund per the configured cancellation window.
- **Files:** `app/Services/BookingService.php` (`cancel`)

---

## Resolved

### ISSUE-001: Timezone double-conversion shifted booking times
- **Date:** 2026-09-30 (resolved same day)
- **Cause:** `Carbon::parse($input, 'Asia/Karachi')` converted the already-local wall time to another zone offset while the app runs in UTC, shifting stored timestamps.
- **Fix:** Parse without TZ argument (app timezone is the single source of truth); normalize with `startOfMinute()`.
- **Files:** `app/Services/BookingService.php`, `app/Services/AvailabilityService.php`, `app/Http/Controllers/{Admin/BookingController,PublicBookingController}.php`

### ISSUE-002: Phantom pricing segments from microsecond drift
- **Date:** 2026-09-30 (resolved same day)
- **Cause:** Breakpoint comparison used microseconds (e.g. 10:00:00.000001 > 10:00:00) splitting an hour into a 1-second segment and inflating totals (910 vs 900).
- **Fix:** `startOfMinute()` on all breakpoints/starts in `PricingService` and `BookingService`.
- **Files:** `app/Services/PricingService.php`, `app/Services/BookingService.php`

### ISSUE-003: Sub-minute overstay billed as a full minute
- **Date:** 2026-09-30 (resolved same day)
- **Cause:** `ceil()` on overstay diff rounded a ~1s processing delay to 1 billable minute (300 → 305).
- **Fix:** Whole-minute overstay counting (`intdiv(seconds, 60)`).
- **Files:** `app/Services/BookingService.php` (`endSession`)

### ISSUE-009: Admin 403 for customer role — verified working
- **Date:** 2026-09-30 (resolved/verified)
- **Cause:** n/a (test coverage added)
- **Fix:** `EnsureUserHasRole` middleware + feature test `test_customer_cannot_access_admin`.
- **Files:** `app/Http/Middleware/EnsureUserHasRole.php`, `tests/Feature/BookingEngineTest.php`
