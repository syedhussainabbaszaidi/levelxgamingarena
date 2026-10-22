@extends('layouts.admin')

@section('title', 'Users')
@section('subtitle', 'Accounts, roles and access')

@section('header')
    <a href="{{ route('admin.users.create') }}" class="btn-neon"><x-icon name="plus" class="h-4 w-4" /> New user</a>
@endsection

@section('content')
    <div class="neon-card mb-6 p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input name="q" value="{{ $filters['q'] }}" placeholder="Search name or email…" class="input-neon max-w-xs">
            <select name="role" class="select-neon !w-auto">
                <option value="">All roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->slug }}" @selected($filters['role'] === $role->slug)>{{ $role->label() }}</option>
                @endforeach
            </select>
            <button class="btn-neon">Filter</button>
            <a href="{{ route('admin.users.index') }}" class="btn-ghost">Reset</a>
        </form>
    </div>

    <div class="neon-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-neon">
                <thead>
                    <tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-full bg-ink-500 text-sm font-bold text-white">{{ substr($user->name, 0, 1) }}</span>
                                    <div>
                                        <p class="font-medium text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-zinc-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge {{ $user->roleBadge() }}">{{ $user->primaryRoleLabel() }}</span></td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge border-emerald-500/30 bg-emerald-500/10 text-emerald-300">Active</span>
                                @else
                                    <span class="badge border-rose-500/30 bg-rose-500/10 text-rose-300">Disabled</span>
                                @endif
                            </td>
                            <td class="text-xs text-zinc-500">{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn-mini">Edit</a>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                        @csrf @method('DELETE')
                                        <button class="btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-sm text-zinc-500">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection
