@extends('layouts.admin')

@section('title', 'Edit Role')
@section('subtitle', $role->label())

@section('content')
    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="grid gap-6 lg:grid-cols-3">
        @csrf
        @method('PATCH')

        <div class="neon-card h-fit space-y-4 p-5">
            <div>
                <label class="neon-label">Role name</label>
                <input name="name" value="{{ old('name', $role->name) }}" class="input-neon" required>
            </div>
            <div>
                <label class="neon-label">Slug</label>
                <input value="{{ $role->slug }}" class="input-neon opacity-60" disabled>
            </div>
            <div>
                <label class="neon-label">Description</label>
                <textarea name="description" rows="3" class="input-neon">{{ old('description', $role->description) }}</textarea>
            </div>

            @if($role->isSuperAdmin())
                <div class="rounded-lg border border-neon-500/30 bg-neon-500/10 px-3 py-2.5 text-xs text-neon-200">
                    The Super Admin role always has every permission and cannot be restricted.
                </div>
            @endif

            <div class="flex gap-2">
                <button class="btn-neon">Save changes</button>
                <a href="{{ route('admin.roles.index') }}" class="btn-ghost">Back</a>
            </div>
        </div>

        <div class="lg:col-span-2">
            <h2 class="mb-3 text-sm font-semibold text-white">Permissions</h2>
            @include('admin.roles._matrix', [
                'groups' => $groups,
                'granted' => old('permissions', $granted),
                'disabled' => $role->isSuperAdmin(),
            ])
        </div>
    </form>
@endsection
