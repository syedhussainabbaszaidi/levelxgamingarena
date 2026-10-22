<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BookingConfigController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\BookingFieldController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PricingRuleController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TimeSlotController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicBookingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / customer portal
|--------------------------------------------------------------------------
*/

/*
| The /demo design-concept landing page is now the homepage: it renders the
| same live activities, categories, images and settings as the portal. The
| /demo alias is kept so existing links keep working.
*/
Route::get('/', [PageController::class, 'demo'])->name('public.index');
Route::get('/demo', [PageController::class, 'demo'])->name('public.demo');

Route::get('/activities/{resource}', [PublicBookingController::class, 'show'])->name('public.activity');
Route::get('/book/slots', [PublicBookingController::class, 'slots'])->name('public.slots');
Route::get('/book/price', [PublicBookingController::class, 'price'])->name('public.price');

/*
|--------------------------------------------------------------------------
| Customer information pages (linked from the public footer)
|--------------------------------------------------------------------------
*/

Route::get('/about-us', [PageController::class, 'about'])->name('public.about');
Route::get('/contact-us', [PageController::class, 'contact'])->name('public.contact');
Route::post('/contact-us', [PageController::class, 'sendContact'])->name('public.contact.send');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('public.privacy');
Route::get('/cookie-policy', [PageController::class, 'cookies'])->name('public.cookies');
Route::get('/terms-and-conditions', [PageController::class, 'terms'])->name('public.terms');
Route::get('/disclaimer', [PageController::class, 'disclaimer'])->name('public.disclaimer');
Route::get('/photo-credits', [PageController::class, 'credits'])->name('public.credits');

/*
|--------------------------------------------------------------------------
| Authenticated customers
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Booking requires an account (request #2).
    Route::post('/book', [PublicBookingController::class, 'store'])->name('public.store');

    Route::get('/book/confirmation/{code}', [PublicBookingController::class, 'confirmation'])->name('public.confirmation');

    /*
     | Shared profile screen (back-office layout) for staff/admin accounts;
     | customers are redirected to their own portal profile by the controller.
     */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // After-login landing: back office → admin dashboard, customers → dashboard.
    Route::get('/dashboard', function () {
        return auth()->user()->isStaffOrAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('customer.dashboard');
    })->name('dashboard');

    Route::get('/customer/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');

    /*
     | Customer profile — rendered with the public portal layout, exactly like
     | the customer dashboard and My Bookings. Never the back-office layout.
     */
    Route::get('/customer/profile', [CustomerProfileController::class, 'edit'])->name('customer.profile');
    Route::patch('/customer/profile', [CustomerProfileController::class, 'update'])->name('customer.profile.update');
    Route::delete('/customer/profile', [CustomerProfileController::class, 'destroy'])->name('customer.profile.destroy');

    Route::get('/my-bookings', [CustomerBookingController::class, 'index'])->name('public.my-bookings');
    Route::get('/my-bookings/{booking}', [CustomerBookingController::class, 'show'])->name('public.booking');
});

/*
|--------------------------------------------------------------------------
| Back office (admin + staff + booking manager) — permission enforced
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:admin,staff,booking_manager'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
     | Live session lifecycle (check-in, extend, end, cancel).
     */
    Route::middleware('permission:bookings.status')->group(function () {
        Route::post('/sessions/{booking}/check-in', [SessionController::class, 'checkIn'])->name('sessions.check-in');
        Route::post('/sessions/{booking}/extend', [SessionController::class, 'extend'])->name('sessions.extend');
        Route::post('/sessions/{booking}/end', [SessionController::class, 'end'])->name('sessions.end');
        Route::post('/sessions/{booking}/cancel', [SessionController::class, 'cancel'])->name('sessions.cancel');
    });

    /*
     | Creating bookings from the back office.
     */
    Route::middleware('permission:bookings.create')->group(function () {
        Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/price', [BookingController::class, 'price'])->name('bookings.price');
    });

    /*
     | Bookings (view / status / export / payment toggles / live counts).
     */
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index')->middleware('permission:bookings.view');
    Route::get('/bookings/counts', [BookingController::class, 'counts'])->name('bookings.counts')->middleware('permission:bookings.view');
    Route::get('/bookings/unread-count', [BookingController::class, 'unreadCount'])->name('bookings.unread-count')->middleware('permission:bookings.view');
    Route::post('/bookings/mark-all-read', [BookingController::class, 'markAllRead'])->name('bookings.mark-all-read')->middleware('permission:bookings.view');
    Route::get('/bookings/export', [BookingController::class, 'export'])->name('bookings.export')->middleware('permission:bookings.view');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show')->middleware('permission:bookings.view');
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.status')->middleware('permission:bookings.status');
    Route::patch('/bookings/{booking}/payment-status', [BookingController::class, 'updatePaymentStatus'])->name('bookings.payment-status')->middleware('permission:bookings.edit');

    /*
     | Gaming activities (a.k.a. resources) + categories.
     */
    Route::middleware('permission:activities.manage')->group(function () {
        Route::get('/activities', [ResourceController::class, 'index'])->name('activities.index');
        Route::get('/activities/create', [ResourceController::class, 'create'])->name('activities.create');
        Route::get('/activities/image-search', [ResourceController::class, 'searchImage'])->name('activities.image-search');
        Route::post('/activities', [ResourceController::class, 'store'])->name('activities.store');
        Route::get('/activities/{resource}/edit', [ResourceController::class, 'edit'])->name('activities.edit');
        Route::post('/activities/{resource}/toggle-active', [ResourceController::class, 'toggleActive'])->name('activities.toggle-active');
        Route::patch('/activities/{resource}', [ResourceController::class, 'update'])->name('activities.update');
        Route::delete('/activities/{resource}', [ResourceController::class, 'destroy'])->name('activities.destroy');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::patch('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    /*
     | Pricing.
     */
    Route::get('/pricing', [PricingRuleController::class, 'index'])->name('pricing.index')->middleware('permission:pricing.manage');
    Route::post('/pricing', [PricingRuleController::class, 'store'])->name('pricing.store')->middleware('permission:pricing.manage');
    Route::patch('/pricing/{rule}', [PricingRuleController::class, 'update'])->name('pricing.update')->middleware('permission:pricing.manage');
    Route::delete('/pricing/{rule}', [PricingRuleController::class, 'destroy'])->name('pricing.destroy')->middleware('permission:pricing.manage');

    /*
     | Customers.
     */
    Route::middleware('permission:customers.manage')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::patch('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    /*
     | Payments.
     */
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index')->middleware('permission:bookings.view');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store')->middleware('permission:bookings.edit');

    /*
     | Reports + audit logs.
     */
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index')->middleware('permission:reports.view');
    Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index')->middleware('permission:logs.view');

    /*
     | Booking configuration: business config, custom fields, time slots.
     */
    Route::middleware('permission:config.manage')->group(function () {
        Route::get('/config', [BookingConfigController::class, 'index'])->name('config.index');
        Route::patch('/config', [BookingConfigController::class, 'update'])->name('config.update');

        Route::get('/fields', [BookingFieldController::class, 'index'])->name('fields.index');
        Route::post('/fields', [BookingFieldController::class, 'store'])->name('fields.store');
        Route::patch('/fields/{field}', [BookingFieldController::class, 'update'])->name('fields.update');
        Route::delete('/fields/{field}', [BookingFieldController::class, 'destroy'])->name('fields.destroy');

        Route::get('/slots', [TimeSlotController::class, 'index'])->name('slots.index');
        Route::post('/slots', [TimeSlotController::class, 'store'])->name('slots.store');
        Route::patch('/slots/{slot}', [TimeSlotController::class, 'update'])->name('slots.update');
        Route::delete('/slots/{slot}', [TimeSlotController::class, 'destroy'])->name('slots.destroy');
    });

    /*
     | Access management: users, roles, system settings.
     */
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('permission:roles.manage')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::patch('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('permission:settings.manage');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update')->middleware('permission:settings.manage');
});

require __DIR__.'/auth.php';
