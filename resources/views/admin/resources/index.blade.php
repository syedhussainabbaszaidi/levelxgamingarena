@extends('layouts.admin')

@section('title', 'Gaming Activities')
@section('subtitle', 'The bookable activities and packages customers can reserve')

@section('header')
    <a href="{{ route('admin.activities.create') }}" class="btn-neon"><x-icon name="plus" class="h-4 w-4" /> New activity</a>
@endsection

@section('content')
    <div class="neon-card mb-6 p-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <input name="q" value="{{ $filters['q'] }}" placeholder="Search name or code…" class="input-neon max-w-xs sm:w-64">
            <select name="category" class="select-neon !w-auto">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $filters['category'] === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="pricing" class="select-neon !w-auto">
                <option value="">All pricing types</option>
                @foreach(\App\Models\Resource::PRICING_TYPES as $value => $label)
                    <option value="{{ $value }}" @selected($filters['pricing'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="select-neon !w-auto">
                <option value="">All statuses</option>
                @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'maintenance' => 'Maintenance'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn-neon"><x-icon name="search" class="h-4 w-4" /> Filter</button>
            <a href="{{ route('admin.activities.index') }}" class="btn-ghost"><x-icon name="close" class="h-4 w-4" /> Reset</a>
        </form>
    </div>

    <div class="neon-card w-full max-w-full overflow-hidden">
        <div class="w-full max-w-full overflow-x-auto">
            <table class="table-neon min-w-[54rem]">
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>Category</th>
                        <th>Pricing</th>
                        <th>Booking window</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resources as $resource)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <img src="{{ $resource->imageUrl() }}" alt="" loading="lazy"
                                         onerror="this.onerror=null;this.src='{{ $resource->fallbackImageUrl() }}';"
                                         class="h-12 w-16 shrink-0 rounded-lg border border-white/10 object-cover">
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-white">
                                            {{ $resource->name }}
                                            @if($resource->featured)
                                                <x-icon name="star" class="mb-0.5 inline h-3.5 w-3.5 text-neon-400" />
                                            @endif
                                        </p>
                                        <p class="truncate text-xs text-zinc-500">
                                            {{ $resource->code ?: 'no code' }} · {{ $resource->capacity }} players
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap text-zinc-400">{{ $resource->category?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap">
                                <p class="font-semibold text-neon-300">{{ $resource->priceLabel(false) }}</p>
                                @if($resource->hasDiscount())
                                    <p class="text-[11px] text-zinc-500">
                                        <span class="line-through">{{ number_format($resource->regularUnitPrice(), 0) }}</span>
                                        · {{ $resource->discountPercent() }}% off
                                    </p>
                                @endif
                                <p class="text-[11px] uppercase tracking-wide text-zinc-600">
                                    {{ \App\Models\Resource::PRICING_TYPES[$resource->pricing_type] ?? 'Per hour' }}
                                    @if($resource->packageLabel()) · {{ $resource->packageLabel() }} @endif
                                </p>
                            </td>
                            <td class="whitespace-nowrap text-xs text-zinc-400">
                                {{ $resource->defaultDuration() }} min default
                                @if($resource->minimumMinutes())
                                    <span class="block text-zinc-600">min {{ $resource->minimumMinutes() }} min</span>
                                @endif
                                @if($resource->maximumMinutes())
                                    <span class="block text-zinc-600">max {{ $resource->maximumMinutes() }} min</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="badge {{ $resource->statusBadge() }}">{{ $resource->status }}</span>
                                <span class="mt-1 block text-[11px] text-zinc-500">
                                    {{ $resource->is_online_bookable ? 'Bookable online' : 'Counter only' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('admin.activities.edit', $resource) }}" class="btn-mini">
                                        <x-icon name="pencil" class="h-3.5 w-3.5" /> Edit
                                    </a>
                                    @if($resource->status === 'active')
                                        <a href="{{ route('public.activity', $resource) }}" target="_blank" rel="noopener" class="btn-mini">
                                            <x-icon name="eye" class="h-3.5 w-3.5" /> View
                                        </a>
                                    @endif
                                    @if($resource->status !== 'maintenance')
                                        <form method="POST" action="{{ route('admin.activities.toggle-active', $resource) }}">
                                            @csrf
                                            <button class="btn-mini {{ $resource->status === 'active' ? '!text-amber-300' : '!text-emerald-300' }}">
                                                <x-icon name="{{ $resource->status === 'active' ? 'x-circle' : 'check-circle' }}" class="h-3.5 w-3.5" />
                                                {{ $resource->status === 'active' ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.activities.destroy', $resource) }}"
                                          data-confirm="Delete “{{ $resource->name }}”?{{ $resource->bookings()->exists() ? ' It has bookings and will be marked inactive instead.' : '' }}">
                                        @csrf @method('DELETE')
                                        <button class="btn-danger">
                                            <x-icon name="trash" class="h-3.5 w-3.5" /> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-zinc-500">
                                No activities match these filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $resources->links() }}</div>
@endsection
