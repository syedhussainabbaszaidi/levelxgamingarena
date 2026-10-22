@extends('layouts.admin')

@section('title', 'Roles & Permissions')
@section('subtitle', 'Backend-enforced access control')

@section('header')
    <a href="{{ route('admin.roles.create') }}" class="btn-neon"><x-icon name="plus" class="h-4 w-4" /> New role</a>
@endsection

@section('content')
    <div class="grid gap-4 md:grid-cols-2">
        @foreach($roles as $role)
            <div class="neon-card neon-card-hover p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-display text-lg font-bold text-white">{{ $role->label() }}</h3>
                        <p class="font-mono text-xs text-zinc-500">{{ $role->slug }}</p>
                    </div>
                    @if($role->is_system)
                        <span class="badge border-neon-500/30 bg-neon-500/10 text-neon-300">system</span>
                    @endif
                </div>

                <p class="mt-3 min-h-[2.5rem] text-sm text-zinc-400">{{ $role->description }}</p>

                <div class="mt-4 flex items-center gap-4 text-xs text-zinc-500">
                    <span class="chip">{{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}</span>
                    <span class="chip">
                        {{ $role->isSuperAdmin() ? 'all' : $role->permissions_count }} permissions
                    </span>
                </div>

                <div class="mt-5 flex gap-2">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn-neon !py-1.5 !text-xs">Edit permissions</a>
                    @unless($role->is_system)
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?')">
                            @csrf @method('DELETE')
                            <button class="btn-danger">Delete</button>
                        </form>
                    @endunless
                </div>
            </div>
        @endforeach
    </div>
@endsection
