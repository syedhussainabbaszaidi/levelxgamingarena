@extends('layouts.admin')

@section('title', 'Edit User')
@section('subtitle', $user->name)

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="neon-card space-y-5 p-6 lg:col-span-2">
            @csrf
            @method('PATCH')

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="neon-label">Full name</label>
                    <input name="name" value="{{ old('name', $user->name) }}" class="input-neon" required>
                </div>
                <div>
                    <label class="neon-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="input-neon" required>
                </div>
            </div>

            <div>
                <label class="neon-label">New password (leave blank to keep)</label>
                <input type="password" name="password" class="input-neon">
            </div>

            <div>
                <label class="neon-label">Roles</label>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach($roles as $role)
                        <label class="flex cursor-pointer items-start gap-2.5 rounded-lg border border-white/[0.06] px-3 py-2 transition hover:border-neon-500/40">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                   class="mt-0.5 rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500"
                                   @checked(in_array($role->id, old('roles', $user->roles->pluck('id')->all())))>
                            <span>
                                <span class="block text-sm text-zinc-200">{{ $role->label() }}</span>
                                <span class="block text-xs text-zinc-500">{{ $role->description }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-zinc-300">
                <input type="checkbox" name="is_active" value="1" class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500"
                       @checked(old('is_active', $user->is_active))>
                Account active
            </label>

            <div class="flex gap-2">
                <button class="btn-neon">Save changes</button>
                <a href="{{ route('admin.users.index') }}" class="btn-ghost">Back</a>
            </div>
        </form>

        <div class="neon-card h-fit space-y-4 p-6">
            <h3 class="text-sm font-semibold text-white">Access summary</h3>
            <div>
                <p class="neon-label">Current role</p>
                <span class="badge {{ $user->roleBadge() }}">{{ $user->primaryRoleLabel() }}</span>
            </div>
            <div>
                <p class="neon-label">Effective permissions</p>
                <div class="flex flex-wrap gap-1.5">
                    @forelse($user->permissionSlugs() as $slug)
                        <span class="chip font-mono !text-[11px]">{{ $slug }}</span>
                    @empty
                        <span class="text-xs text-zinc-500">No elevated permissions (customer account).</span>
                    @endforelse
                </div>
            </div>
            @if($customer)
                <div class="neon-panel">
                    <p class="neon-label">Linked customer</p>
                    <p class="text-sm text-zinc-300">{{ $customer->name }}</p>
                    <p class="text-xs text-zinc-500">{{ $customer->phone }}</p>
                    <p class="mt-1 text-xs text-zinc-500">{{ $customer->bookings()->count() }} bookings</p>
                </div>
            @endif
        </div>
    </div>
@endsection
