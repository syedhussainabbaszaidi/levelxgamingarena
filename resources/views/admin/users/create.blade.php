@extends('layouts.admin')

@section('title', 'New User')
@section('subtitle', 'Create a back-office or customer account')

@section('content')
    <form method="POST" action="{{ route('admin.users.store') }}" class="neon-card max-w-2xl space-y-5 p-6">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="neon-label">Full name</label>
                <input name="name" value="{{ old('name') }}" class="input-neon" required>
            </div>
            <div>
                <label class="neon-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="input-neon" required>
            </div>
        </div>

        <div>
            <label class="neon-label">Password</label>
            <input type="password" name="password" class="input-neon" required>
            <p class="mt-1 text-xs text-zinc-500">Minimum 8 characters.</p>
        </div>

        <div>
            <label class="neon-label">Roles</label>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($roles as $role)
                    <label class="flex cursor-pointer items-start gap-2.5 rounded-lg border border-white/[0.06] px-3 py-2 transition hover:border-neon-500/40">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                               class="mt-0.5 rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500"
                               @checked(in_array($role->id, old('roles', [])))>
                        <span>
                            <span class="block text-sm text-zinc-200">{{ $role->label() }}</span>
                            <span class="block text-xs text-zinc-500">{{ $role->description }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <p class="mt-2 text-xs text-zinc-500">Leave all unchecked for a standard customer account.</p>
        </div>

        <label class="flex items-center gap-2 text-sm text-zinc-300">
            <input type="checkbox" name="is_active" value="1" class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500" checked>
            Account active
        </label>

        <div class="flex gap-2">
            <button class="btn-neon">Create user</button>
            <a href="{{ route('admin.users.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
@endsection
