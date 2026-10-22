@extends('layouts.admin')

@section('title', 'Pricing')
@section('subtitle', 'Time-windowed hourly rates — highest priority match wins')

@section('header')
    <button type="button" onclick="openRuleModal('new')" class="btn-neon"><x-icon name="plus" class="h-4 w-4" /> New rule</button>
@endsection

@section('content')
    <div class="neon-panel mb-6 border-amber-500/20 bg-amber-500/[0.06] text-sm text-amber-200">
        <strong class="font-semibold">How pricing works:</strong> rules define hourly rates for time windows.
        Priority order: resource-specific &gt; category &gt; business-wide. If no rule matches, the activity's base rate applies.
        “Overnight” means the window ends the next day (e.g. 22:00 → 02:00).
    </div>

    <div class="neon-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-neon">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Applies to</th>
                        <th>Days</th>
                        <th>Window</th>
                        <th>Rate/hr</th>
                        <th>Min</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                        <tr>
                            <td class="font-medium text-white">{{ $rule->name }}</td>
                            <td class="text-xs">
                                @if($rule->resource_id)
                                    <span class="badge border-neon-500/30 bg-neon-500/10 text-neon-300">Resource: {{ $rule->resource?->name }}</span>
                                @elseif($rule->category_id)
                                    <span class="badge border-white/10 bg-white/[0.03] text-zinc-300">Category: {{ $rule->category?->name }}</span>
                                @else
                                    <span class="badge border-white/10 bg-white/[0.03] text-zinc-400">All resources</span>
                                @endif
                            </td>
                            <td class="text-xs text-zinc-400">{{ is_null($rule->day_of_week) ? 'Every day' : \Carbon\Carbon::create()->startOfWeek()->addDays($rule->day_of_week)->format('l') }}</td>
                            <td class="text-xs text-zinc-400 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($rule->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($rule->end_time)->format('g:i A') }}
                                @if($rule->overnight)<span class="text-amber-400">(+1d)</span>@endif
                            </td>
                            <td class="font-semibold text-neon-400">{{ number_format($rule->hourly_rate, 0) }}</td>
                            <td class="text-xs text-zinc-400">{{ $rule->min_minutes }}m</td>
                            <td class="text-sm">{{ $rule->priority }}</td>
                            <td>
                                @if($rule->is_active)
                                    <span class="badge border-emerald-500/30 bg-emerald-500/10 text-emerald-300">Active</span>
                                @else
                                    <span class="badge border-white/10 bg-white/[0.03] text-zinc-400">Off</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="openRuleModal({{ $rule->id }})" class="btn-mini">Edit</button>
                                    @if(auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('admin.pricing.destroy', $rule) }}" class="inline"
                                              data-confirm="Delete the “{{ $rule->name }}” pricing rule?">
                                            @csrf @method('DELETE')
                                            <button class="btn-danger">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-sm text-zinc-500">
                                No pricing rules — the activity base rate applies everywhere.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $rules->links() }}</div>

    {{-- Edit modals (one per rule) --}}
    @foreach($rules as $rule)
        <div id="rule-modal-{{ $rule->id }}" class="fixed inset-0 z-[65] hidden items-center justify-center bg-ink-950/80 p-4 backdrop-blur-sm">
            <div class="neon-card max-h-[90vh] w-full max-w-lg overflow-y-auto p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-display text-base font-bold text-white">Edit: {{ $rule->name }}</h3>
                    <button type="button" onclick="closeRuleModal({{ $rule->id }})" class="text-zinc-500 transition hover:text-white" aria-label="Close"><x-icon name="close" class="h-4 w-4 pointer-events-none" /></button>
                </div>
                <form method="POST" action="{{ route('admin.pricing.update', $rule) }}">
                    @csrf @method('PATCH')
                    @include('admin.pricing._form', ['rule' => $rule, 'categories' => $categories, 'resources' => $resources, 'businesses' => $businesses])
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" onclick="closeRuleModal({{ $rule->id }})" class="btn-ghost">Cancel</button>
                        <button class="btn-neon">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- Create modal --}}
    <div id="rule-modal-new" class="fixed inset-0 z-[65] hidden items-center justify-center bg-ink-950/80 p-4 backdrop-blur-sm">
        <div class="neon-card max-h-[90vh] w-full max-w-lg overflow-y-auto p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-display text-base font-bold text-white">New pricing rule</h3>
                <button type="button" onclick="closeRuleModal('new')" class="text-zinc-500 transition hover:text-white" aria-label="Close"><x-icon name="close" class="h-4 w-4 pointer-events-none" /></button>
            </div>
            <form method="POST" action="{{ route('admin.pricing.store') }}">
                @csrf
                @include('admin.pricing._form', ['rule' => null, 'categories' => $categories, 'resources' => $resources, 'businesses' => $businesses])
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" onclick="closeRuleModal('new')" class="btn-ghost">Cancel</button>
                    <button class="btn-neon">Create rule</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openRuleModal(id) {
        var m = document.getElementById('rule-modal-' + id);
        if (!m) return;
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function closeRuleModal(id) {
        var m = document.getElementById('rule-modal-' + id);
        if (!m) return;
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('[id^="rule-modal-"]').forEach(function (m) {
            m.classList.add('hidden');
            m.classList.remove('flex');
        });
    });
</script>
@endpush
