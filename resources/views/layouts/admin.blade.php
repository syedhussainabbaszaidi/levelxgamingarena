<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
<div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-ink-950/70 backdrop-blur-sm lg:hidden"></div>
@php($user = auth()->user())
@php($timezone = \App\Models\Business::primary()?->timezone ?: config('app.timezone', 'UTC'))
@php($now = now()->timezone($timezone))

{{-- Navigation / submit progress bar --}}
<div id="admin-progress" class="admin-progress" aria-hidden="true"></div>

<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full overflow-y-auto border-r border-white/[0.06] bg-ink-950/95 backdrop-blur transition-transform duration-200 lg:translate-x-0">
        <div class="flex h-16 items-center border-b border-white/[0.06] px-5">
            <x-brand-logo href="{{ route('admin.dashboard') }}" height="h-8" />
        </div>

        <nav class="space-y-0.5 px-3 py-4">
            <a href="{{ route('admin.dashboard') }}" class="side-link {{ request()->routeIs('admin.dashboard') ? 'side-link-active' : '' }}"><x-icon name="dashboard" class="h-5 w-5 shrink-0" /> <span>Dashboard</span></a>
            @if($user->hasPermissionTo('bookings.view'))
                @php($unreadBookings = \App\Models\Booking::unreadForAdmin()->count())
                <a href="{{ route('admin.bookings.index') }}" class="side-link {{ request()->routeIs('admin.bookings.*') ? 'side-link-active' : '' }}">
                    <x-icon name="calendar-days" class="h-5 w-5 shrink-0" /> <span>Bookings</span>
                    <span id="bookings-unread-badge"
                          class="ml-auto grid h-5 min-w-[1.25rem] place-items-center rounded-full bg-rose-500 px-1.5 text-[10px] font-bold text-white {{ $unreadBookings > 0 ? '' : 'hidden' }}"
                          title="New customer bookings">{{ $unreadBookings }}</span>
                </a>
            @endif
            @if($user->hasPermissionTo('customers.manage'))
                <a href="{{ route('admin.customers.index') }}" class="side-link {{ request()->routeIs('admin.customers.*') ? 'side-link-active' : '' }}"><x-icon name="users" class="h-5 w-5 shrink-0" /> <span>Customers</span></a>
            @endif

            @if($user->hasAnyPermission(['activities.manage', 'pricing.manage']))
                <div class="side-group">Catalogue</div>
                @if($user->hasPermissionTo('activities.manage'))
                    <a href="{{ route('admin.activities.index') }}" class="side-link {{ request()->routeIs('admin.activities.*') ? 'side-link-active' : '' }}"><x-icon name="gamepad" class="h-5 w-5 shrink-0" /> <span>Gaming Activities</span></a>
                    <a href="{{ route('admin.categories.index') }}" class="side-link {{ request()->routeIs('admin.categories.*') ? 'side-link-active' : '' }}"><x-icon name="tag" class="h-5 w-5 shrink-0" /> <span>Categories</span></a>
                @endif
                @if($user->hasPermissionTo('pricing.manage'))
                    <a href="{{ route('admin.pricing.index') }}" class="side-link {{ request()->routeIs('admin.pricing.*') ? 'side-link-active' : '' }}"><x-icon name="banknotes" class="h-5 w-5 shrink-0" /> <span>Pricing</span></a>
                @endif
            @endif

            @if($user->hasPermissionTo('config.manage'))
                <div class="side-group">Booking config</div>
                <a href="{{ route('admin.config.index') }}" class="side-link {{ request()->routeIs('admin.config.*') ? 'side-link-active' : '' }}"><x-icon name="cog" class="h-5 w-5 shrink-0" /> <span>Configuration</span></a>
                <a href="{{ route('admin.fields.index') }}" class="side-link {{ request()->routeIs('admin.fields.*') ? 'side-link-active' : '' }}"><x-icon name="sliders" class="h-5 w-5 shrink-0" /> <span>Booking Fields</span></a>
                <a href="{{ route('admin.slots.index') }}" class="side-link {{ request()->routeIs('admin.slots.*') ? 'side-link-active' : '' }}"><x-icon name="clock" class="h-5 w-5 shrink-0" /> <span>Time Slots</span></a>
            @endif

            @if($user->hasAnyPermission(['bookings.view', 'reports.view', 'logs.view']))
                <div class="side-group">Finance & insight</div>
                @if($user->hasPermissionTo('bookings.view'))
                    <a href="{{ route('admin.payments.index') }}" class="side-link {{ request()->routeIs('admin.payments.*') ? 'side-link-active' : '' }}"><x-icon name="banknotes" class="h-5 w-5 shrink-0" /> <span>Payments</span></a>
                @endif
                @if($user->hasPermissionTo('reports.view'))
                    <a href="{{ route('admin.reports.index') }}" class="side-link {{ request()->routeIs('admin.reports.*') ? 'side-link-active' : '' }}"><x-icon name="chart-bar" class="h-5 w-5 shrink-0" /> <span>Reports</span></a>
                @endif
                @if($user->hasPermissionTo('logs.view'))
                    <a href="{{ route('admin.logs.index') }}" class="side-link {{ request()->routeIs('admin.logs.*') ? 'side-link-active' : '' }}"><x-icon name="document" class="h-5 w-5 shrink-0" /> <span>Activity Logs</span></a>
                @endif
            @endif

            @if($user->hasAnyPermission(['users.manage', 'roles.manage', 'settings.manage']))
                <div class="side-group">Access</div>
                @if($user->hasPermissionTo('users.manage'))
                    <a href="{{ route('admin.users.index') }}" class="side-link {{ request()->routeIs('admin.users.*') ? 'side-link-active' : '' }}"><x-icon name="user-circle" class="h-5 w-5 shrink-0" /> <span>Users</span></a>
                @endif
                @if($user->hasPermissionTo('roles.manage'))
                    <a href="{{ route('admin.roles.index') }}" class="side-link {{ request()->routeIs('admin.roles.*') ? 'side-link-active' : '' }}"><x-icon name="shield" class="h-5 w-5 shrink-0" /> <span>Roles & Permissions</span></a>
                @endif
                @if($user->hasPermissionTo('settings.manage'))
                    <a href="{{ route('admin.settings.index') }}" class="side-link {{ request()->routeIs('admin.settings.*') ? 'side-link-active' : '' }}"><x-icon name="cog" class="h-5 w-5 shrink-0" /> <span>System Settings</span></a>
                @endif
            @endif
        </nav>
    </aside>

    {{-- Main --}}
    <div class="flex min-h-screen min-w-0 flex-1 flex-col lg:pl-64">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-white/[0.06] bg-ink-900/85 px-4 backdrop-blur lg:px-8">
            <div class="flex items-center gap-3">
                <button type="button" id="sidebar-toggle" aria-controls="sidebar" aria-expanded="false"
                        class="btn-ghost !px-2.5 !py-1.5 lg:hidden" aria-label="Toggle navigation">
                    <x-icon name="menu" class="h-5 w-5" />
                </button>
                <div>
                    <h1 class="text-base font-semibold text-white">@yield('title', 'Dashboard')</h1>
                    <p class="text-xs text-zinc-500">@yield('subtitle', 'Back office')</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1.5 text-xs text-zinc-500"
                      data-live-clock
                      data-timezone="{{ $timezone }}">
                    <x-icon name="clock" class="h-3.5 w-3.5" />
                    <span class="hidden sm:inline" data-clock-date>{{ $now->format('D, M j') }}</span>
                    <span class="text-zinc-700">·</span>
                    <span data-clock-time class="font-medium text-zinc-300">{{ $now->format('g:i A') }}</span>
                    <span class="text-[10px] uppercase tracking-wide text-zinc-600" data-clock-zone></span>
                </span>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.03] px-2 py-1 transition hover:border-neon-500/40">
                        <span class="grid h-7 w-7 place-items-center rounded-full bg-neon-600 text-xs font-bold text-white">{{ substr($user->name, 0, 1) }}</span>
                        <span class="hidden text-sm text-zinc-300 md:inline">{{ $user->name }}</span>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false"
                         class="absolute right-0 mt-2 w-52 overflow-hidden rounded-xl border border-white/10 bg-ink-800 py-1 shadow-panel">
                        <div class="border-b border-white/[0.06] px-4 py-2">
                            <p class="text-sm font-semibold text-white">{{ $user->name }}</p>
                            <span class="badge mt-1 {{ $user->roleBadge() }}">{{ $user->primaryRoleLabel() }}</span>
                        </div>
                        <a href="{{ route('public.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-zinc-300 hover:bg-white/[0.04]"><x-icon name="eye" class="h-4 w-4 shrink-0" /> View customer site</a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-zinc-300 hover:bg-white/[0.04]"><x-icon name="user-circle" class="h-4 w-4 shrink-0" /> Profile</a>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="flex items-center gap-2 px-4 py-2 text-sm text-rose-400 hover:bg-white/[0.04]"><x-icon name="logout" class="h-4 w-4 shrink-0" /> Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="min-w-0 flex-1 p-4 lg:p-8">
            @include('partials.flash')

            @hasSection('header')
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    @yield('header')
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>

{{-- Global confirm dialog: forms/links using [data-confirm] route through it. --}}
<div id="confirm-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-ink-950/80 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
    <div class="neon-card w-full max-w-md p-6">
        <div class="flex items-start gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-rose-500/15 text-rose-300"><x-icon name="warning" class="h-5 w-5" /></span>
            <div>
                <h3 id="confirm-modal-title" class="font-display text-base font-bold text-white">Confirm action</h3>
                <p id="confirm-modal-message" class="mt-1 text-sm text-zinc-400">Are you sure?</p>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" data-confirm-cancel class="btn-ghost">Cancel</button>
            <button type="button" data-confirm-accept class="btn-neon !bg-rose-600 !shadow-none hover:!bg-rose-500">Yes, continue</button>
        </div>
    </div>
</div>

{{-- Scripts load after the DOM so inline page scripts can query elements. --}}
@if($user->hasPermissionTo('bookings.view'))
{{-- Live new-booking badge: polls the count so it updates without a reload. --}}
<script>
(function () {
    var badge = document.getElementById('bookings-unread-badge');
    if (!badge) return;

    function paint(count) {
        count = Number(count) || 0;
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    function refresh() {
        fetch('{{ route('admin.bookings.unread-count') }}', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) { if (data && data.ok) paint(data.unread); })
            .catch(function () {});
    }

    setInterval(refresh, 15000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) refresh(); });
})();
</script>
@endif

@stack('scripts')
@stack('modals')

<script>
(function () {
    // Mobile sidebar: one toggle controls the panel, the backdrop and a11y state.
    var sidebar = document.getElementById('sidebar');
    var backdrop = document.getElementById('sidebar-backdrop');
    var sidebarToggle = document.getElementById('sidebar-toggle');

    function setSidebar(open) {
        if (!sidebar) return;
        sidebar.classList.toggle('-translate-x-full', !open);
        if (backdrop) backdrop.classList.toggle('hidden', !open);
        document.body.classList.toggle('overflow-hidden', open);
        if (sidebarToggle) sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    if (sidebarToggle) sidebarToggle.addEventListener('click', function () {
        setSidebar(sidebar.classList.contains('-translate-x-full'));
    });
    if (backdrop) backdrop.addEventListener('click', function () { setSidebar(false); });
    if (sidebar) sidebar.addEventListener('click', function (e) {
        if (e.target.closest('a') && window.innerWidth < 1024) setSidebar(false);
    });
})();

(function () {
    var modal = document.getElementById('confirm-modal');
    var messageEl = document.getElementById('confirm-modal-message');
    var acceptBtn = modal ? modal.querySelector('[data-confirm-accept]') : null;
    var cancelBtn = modal ? modal.querySelector('[data-confirm-cancel]') : null;
    var bar = document.getElementById('admin-progress');
    var pending = null;

    function startLoading() {
        if (!bar) return;
        bar.classList.add('is-active');
        setTimeout(function () { bar.classList.remove('is-active'); }, 9000);
    }

    function openConfirm(message, onAccept) {
        if (!modal || !acceptBtn) {
            if (window.confirm(message || 'Are you sure?')) onAccept();
            return;
        }
        pending = onAccept;
        messageEl.textContent = message || 'Are you sure?';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        acceptBtn.focus();
    }

    function closeConfirm() {
        pending = null;
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    if (acceptBtn) acceptBtn.addEventListener('click', function () {
        var callback = pending;
        closeConfirm();
        if (callback) { startLoading(); callback(); }
    });
    if (cancelBtn) cancelBtn.addEventListener('click', closeConfirm);
    if (modal) modal.addEventListener('click', function (e) { if (e.target === modal) closeConfirm(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) closeConfirm();
    });

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches || !form.matches('form[data-confirm]')) return;
        if (form.dataset.confirmed === '1') return;
        e.preventDefault();
        openConfirm(form.dataset.confirm, function () {
            form.dataset.confirmed = '1';
            form.submit();
        });
    }, false);

    document.addEventListener('click', function (e) {
        var link = e.target && e.target.closest ? e.target.closest('a[data-confirm]') : null;
        if (!link) return;
        e.preventDefault();
        openConfirm(link.dataset.confirm, function () { window.location.href = link.href; });
    }, false);

    document.addEventListener('submit', function (e) {
        if (e.defaultPrevented) return;
        startLoading();

        var form = e.target;
        var button = form.querySelector ? form.querySelector('button[type="submit"], button:not([type])') : null;
        if (button && !button.dataset.loading) {
            button.dataset.loading = '1';
            button.disabled = true;
            button.classList.add('opacity-70', 'cursor-wait');
            var spinner = document.createElement('span');
            spinner.className = 'spinner';
            button.prepend(spinner);
        }
    }, false);

    document.addEventListener('click', function (e) {
        var link = e.target && e.target.closest ? e.target.closest('a[href]') : null;
        if (!link) return;
        if (link.target === '_blank' || link.hasAttribute('data-confirm') || link.hasAttribute('download')) return;
        var href = link.getAttribute('href') || '';
        if (href === '' || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return;
        startLoading();
    }, false);
})();
</script>
</body>
</html>
