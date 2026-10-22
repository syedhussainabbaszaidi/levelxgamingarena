@extends('layouts.admin')

@section('title', 'Bookings')
@section('subtitle', 'Search, filter and manage every reservation')

@php
    $sortLink = fn (string $col) => route('admin.bookings.index', array_merge(request()->query(), [
        'sort' => $col,
        'dir' => ($filters['sort'] === $col && $filters['dir'] === 'asc') ? 'desc' : 'asc',
    ]));
    $arrow = fn (string $col) => $filters['sort'] === $col ? ($filters['dir'] === 'asc' ? '↑' : '↓') : '';
@endphp

@section('header')
    <div class="flex flex-wrap gap-2">
        @if(auth()->user()->hasPermissionTo('bookings.create'))
            <a href="{{ route('admin.bookings.create') }}" class="btn-neon"><x-icon name="plus" class="h-4 w-4" /> New booking</a>
        @endif
        <a href="{{ route('admin.bookings.export', request()->query()) }}" class="btn-ghost"><x-icon name="download" class="h-4 w-4" /> Export CSV</a>
        @if(($unreadCount ?? 0) > 0)
            <form method="POST" action="{{ route('admin.bookings.mark-all-read') }}">
                @csrf
                <button class="btn-ghost"><x-icon name="check-circle" class="h-4 w-4" /> Mark {{ $unreadCount }} new as read</button>
            </form>
        @endif
    </div>
@endsection

@section('content')
    {{-- Payment totals (updated live via polling + toggle responses) --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" id="payment-totals">
        <div class="neon-card p-5">
            <span class="text-xs uppercase tracking-wider text-zinc-500">Today's revenue</span>
            <p class="stat-value mt-2 text-emerald-400" data-total="today_revenue">{{ number_format($totals['today_revenue'], 0) }}</p>
        </div>
        <div class="neon-card p-5">
            <span class="text-xs uppercase tracking-wider text-zinc-500">Total payment</span>
            <p class="stat-value mt-2" data-total="total_payment">{{ number_format($totals['total_payment'], 0) }}</p>
        </div>
        <div class="neon-card p-5">
            <span class="text-xs uppercase tracking-wider text-zinc-500">Paid</span>
            <p class="stat-value mt-2 text-sky-400" data-total="paid_amount">{{ number_format($totals['paid_amount'], 0) }}</p>
        </div>
        <div class="neon-card p-5">
            <span class="text-xs uppercase tracking-wider text-zinc-500">Unpaid / due</span>
            <p class="stat-value mt-2 text-amber-400" data-total="unpaid_amount">{{ number_format($totals['unpaid_amount'], 0) }}</p>
            <p class="mt-1 text-xs text-zinc-500"><span data-total="unpaid_count">{{ $totals['unpaid_count'] }}</span> bookings due</p>
        </div>
    </div>

    {{-- Status tabs (counts refresh in real time, no page reload) --}}
    <div class="mb-5 flex flex-wrap gap-2" id="status-tabs">
        <a href="{{ route('admin.bookings.index', request()->except('status', 'page')) }}"
           class="chip {{ !$filters['status'] ? '!border-neon-500/60 !bg-neon-500/10 !text-white' : '' }}">
            All <span class="text-zinc-500" data-count="all">{{ array_sum($counts) }}</span>
        </a>
        @foreach(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $status => $label)
            <a href="{{ route('admin.bookings.index', array_merge(request()->except('page'), ['status' => $status])) }}"
               class="chip {{ $filters['status'] === $status ? '!border-neon-500/60 !bg-neon-500/10 !text-white' : '' }}">
                {{ $label }}
                <span class="text-zinc-500" data-count="{{ $status }}">{{ $counts[$status] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="neon-card mb-6 p-4">
        <form method="GET" class="grid gap-3 md:grid-cols-6">
            @if($filters['status'])<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
            <input name="q" value="{{ $filters['q'] }}" placeholder="Code, customer, phone…" class="input-neon md:col-span-2">
            <select name="resource" class="select-neon">
                <option value="">All activities</option>
                @foreach($resources as $resource)
                    <option value="{{ $resource->id }}" @selected((string) $filters['resource'] === (string) $resource->id)>{{ $resource->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ $filters['from'] }}" class="input-neon" title="From date">
            <input type="date" name="to" value="{{ $filters['to'] }}" class="input-neon" title="To date">
            <div class="flex gap-2">
                <button class="btn-neon flex-1">Filter</button>
                <a href="{{ route('admin.bookings.index') }}" class="btn-ghost">Reset</a>
            </div>
        </form>
    </div>

    <div class="neon-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-neon">
                <thead>
                    <tr>
                        <th><a href="{{ $sortLink('code') }}" class="hover:text-neon-400">Code {{ $arrow('code') }}</a></th>
                        <th>Activity</th>
                        <th>Customer</th>
                        <th><a href="{{ $sortLink('starts_at') }}" class="hover:text-neon-400">When {{ $arrow('starts_at') }}</a></th>
                        <th><a href="{{ $sortLink('final_cost') }}" class="hover:text-neon-400">Cost {{ $arrow('final_cost') }}</a></th>
                        <th>Payment</th>
                        <th><a href="{{ $sortLink('status') }}" class="hover:text-neon-400">Status {{ $arrow('status') }}</a></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                        <tr>
                            <td class="font-mono text-xs">
                                <a href="{{ route('admin.bookings.show', $b) }}" class="text-neon-400 hover:text-neon-300">{{ $b->code }}</a>
                                @if($b->isUnreadForAdmin())
                                    <span class="badge ml-1 border-rose-500/40 bg-rose-500/15 text-rose-300">New</span>
                                @endif
                            </td>
                            <td class="text-sm text-zinc-200">{{ $b->resource?->name }}</td>
                            <td class="text-sm">
                                {{ $b->customer?->name ?? '—' }}
                                <div class="text-xs text-zinc-500">{{ $b->customer?->phone }}</div>
                            </td>
                            <td class="text-sm whitespace-nowrap">{{ $b->starts_at->format('d M, g:i A') }}
                                <div class="text-xs text-zinc-500">→ {{ $b->ends_at->format('g:i A') }}</div>
                            </td>
                            <td class="text-sm font-semibold text-white">{{ number_format($b->final_cost, 0) }}</td>
                            <td>
                                @if(auth()->user()->hasPermissionTo('bookings.edit'))
                                    <div class="relative inline-block" x-data="{ open: false }" data-payment-toggle
                                         data-booking-id="{{ $b->id }}" data-current="{{ $b->payment_status }}">
                                        <button type="button" @click="open = !open"
                                                class="badge {{ $b->paymentBadge() }} cursor-pointer transition hover:brightness-125">
                                            {{ ucfirst($b->payment_status) }} ▾
                                        </button>
                                        <div x-show="open" x-cloak @click.outside="open = false"
                                             class="absolute left-0 z-20 mt-1 w-40 overflow-hidden rounded-xl border border-white/10 bg-ink-800 py-1 shadow-panel">
                                            <button type="button" class="block w-full px-3 py-1.5 text-left text-xs text-emerald-300 hover:bg-white/[0.04]"
                                                    data-set-payment="paid">{{ __('Mark as Paid') }}</button>
                                            <button type="button" class="block w-full px-3 py-1.5 text-left text-xs text-amber-300 hover:bg-white/[0.04]"
                                                    data-set-payment="unpaid">{{ __('Mark as Unpaid') }}</button>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge {{ $b->paymentBadge() }}">{{ ucfirst($b->payment_status) }}</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $b->statusBadge() }}">{{ str_replace('_', ' ', $b->status) }}</span></td>
                            <td class="text-right">
                                <a href="{{ route('admin.bookings.show', $b) }}" class="btn-mini">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-12 text-center text-sm text-zinc-500">No bookings match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>
@endsection

@push('scripts')
<script>
    (function () {
        var csrf = document.querySelector('meta[name="csrf-token"]').content;
        var canEdit = @json(auth()->user()->hasPermissionTo('bookings.edit'));

        function money(value) {
            return Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });
        }

        function applyTotals(totals) {
            if (!totals) return;
            document.querySelectorAll('#payment-totals [data-total]').forEach(function (el) {
                var key = el.dataset.total;
                if (!(key in totals)) return;
                el.textContent = key === 'unpaid_count' ? totals[key] : money(totals[key]);
            });
        }

        function applyCounts(counts) {
            if (!counts) return;
            document.querySelectorAll('#status-tabs [data-count]').forEach(function (el) {
                if (el.dataset.count in counts) el.textContent = counts[el.dataset.count];
            });
        }

        function refreshMeta() {
            fetch('{{ route('admin.bookings.counts') }}', { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) { if (data && data.ok) { applyCounts(data.counts); applyTotals(data.totals); } })
                .catch(function () {});
        }

        // Real-time tab counts + totals without a page reload.
        setInterval(refreshMeta, 15000);

        // Paid / Unpaid toggles.
        document.addEventListener('click', function (e) {
            var option = e.target.closest ? e.target.closest('[data-set-payment]') : null;
            if (!option) return;
            e.preventDefault();

            var wrapper = option.closest('[data-payment-toggle]');
            var bookingId = wrapper ? wrapper.dataset.bookingId : null;
            var status = option.dataset.setPayment;
            var current = wrapper ? wrapper.dataset.current : null;
            if (!bookingId || status === current || !canEdit) return;

            var label = option.textContent;
            option.textContent = '…';
            option.disabled = true;

            fetch('{{ url('admin/bookings') }}/' + bookingId + '/payment-status', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ payment_status: status, method: 'cash' })
            })
                .then(function (r) { return r.json().catch(function () { return { ok: r.ok }; }); })
                .then(function (data) {
                    if (data.ok) {
                        wrapper.dataset.current = status;
                        var badge = wrapper.querySelector('button');
                        if (badge) {
                            badge.classList.toggle('!border-emerald-500/30', status === 'paid');
                            badge.classList.toggle('!text-emerald-300', status === 'paid');
                            badge.classList.toggle('!bg-emerald-500/15', status === 'paid');
                            badge.classList.toggle('!border-amber-500/30', status !== 'paid');
                            badge.classList.toggle('!text-amber-300', status !== 'paid');
                            badge.classList.toggle('!bg-amber-500/15', status !== 'paid');
                            badge.innerHTML = status.charAt(0).toUpperCase() + status.slice(1) + ' ▾';
                        }
                        applyTotals(data.totals);
                    }
                })
                .catch(function () {})
                .finally(function () { option.textContent = label; option.disabled = false; });
        }, false);
    })();
</script>
@endpush
