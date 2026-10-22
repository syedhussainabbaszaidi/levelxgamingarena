{{-- Shared profile summary card used by the admin + customer themed profile pages. --}}
@php($profileUser = $user ?? auth()->user())

<div class="neon-card flex flex-col items-start gap-5 p-6 sm:flex-row sm:items-center">
    <span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-neon-600 font-display text-2xl font-bold text-white shadow-neon-sm">
        {{ substr($profileUser->name, 0, 1) }}
    </span>
    <div class="min-w-0 flex-1">
        <p class="font-display text-xl font-bold uppercase tracking-wider text-white">{{ $profileUser->name }}</p>
        <p class="truncate text-sm text-zinc-400">{{ $profileUser->email }}</p>
        <div class="mt-2 flex flex-wrap items-center gap-2">
            @if(isset($profileUser->role) && method_exists($profileUser, 'roleBadge'))
                <span class="badge {{ $profileUser->roleBadge() }}">{{ method_exists($profileUser, 'primaryRoleLabel') ? $profileUser->primaryRoleLabel() : ucfirst($profileUser->role) }}</span>
            @endif
            <span class="chip">Member since {{ $profileUser->created_at?->format('M Y') }}</span>
        </div>
    </div>
    <a href="{{ auth()->user()->isStaffOrAdmin() ? route('admin.dashboard') : route('customer.dashboard') }}" class="btn-ghost shrink-0 !py-1.5 !text-xs">
        <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Back to dashboard
    </a>
</div>
