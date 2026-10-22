@extends('layouts.admin')

@section('title', 'Customers')
@section('subtitle', 'Everyone who has booked with you — newest first')

@section('header')
    <a href="{{ route('admin.customers.create') }}" class="btn-neon"><x-icon name="plus" class="h-4 w-4" /> Add customer</a>
@endsection

@section('content')
    {{-- Filters --}}
    <div class="neon-card mb-6 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="min-w-[220px] flex-1">
                <label class="neon-label" for="q">Search</label>
                <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Name, phone or email…" class="input-neon">
            </div>
            <div>
                <label class="neon-label" for="sort">Sort by</label>
                <select id="sort" name="sort" class="select-neon !w-auto">
                    <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                    <option value="name" @selected($filters['sort'] === 'name')>Name (A–Z)</option>
                    <option value="spent" @selected($filters['sort'] === 'spent')>Highest spend</option>
                    <option value="visits" @selected($filters['sort'] === 'visits')>Most visits</option>
                </select>
            </div>
            <button class="btn-neon">Filter</button>
            <a href="{{ route('admin.customers.index') }}" class="btn-ghost">Reset</a>
            <span class="ml-auto self-center text-xs text-zinc-500">{{ $customers->total() }} {{ Str::plural('customer', $customers->total()) }}</span>
        </form>
    </div>

    <div class="neon-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-neon">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Visits</th>
                        <th>Total spent</th>
                        <th>Joined</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $c)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-ink-500 text-sm font-bold text-white">{{ substr($c->name, 0, 1) }}</span>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.customers.show', $c) }}" class="block truncate font-medium text-white hover:text-neon-300">{{ $c->name }}</a>
                                        <p class="truncate text-xs text-zinc-500">{{ $c->email ?: '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm text-zinc-300 whitespace-nowrap">{{ $c->phone }}</td>
                            <td class="text-sm">{{ $c->bookings_count }}</td>
                            <td class="text-sm font-semibold text-neon-400">{{ number_format($c->total_spent, 0) }}</td>
                            <td class="text-xs text-zinc-500 whitespace-nowrap">{{ $c->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.customers.show', $c) }}" class="btn-mini">View</a>
                                    <a href="{{ route('admin.customers.edit', $c) }}" class="btn-mini">Edit</a>
                                    @if(auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('admin.customers.destroy', $c) }}" class="inline"
                                              data-confirm="Delete {{ $c->name }}? This cannot be undone.">
                                            @csrf @method('DELETE')
                                            <button class="btn-danger">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-sm text-zinc-500">
                                No customers found @if($filters['q'])for “{{ $filters['q'] }}” @endif.
                                <a href="{{ route('admin.customers.create') }}" class="ml-1 text-neon-400 hover:text-neon-300">Add the first one →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $customers->links() }}</div>
@endsection
