@extends('layouts.admin')

@section('title', 'New Role')
@section('subtitle', 'Define a custom access level')

@section('content')
    <form method="POST" action="{{ route('admin.roles.store') }}" class="grid gap-6 lg:grid-cols-3">
        @csrf

        <div class="neon-card h-fit space-y-4 p-5">
            <div>
                <label class="neon-label">Role name</label>
                <input name="name" value="{{ old('name') }}" class="input-neon" placeholder="Tournament Manager" required>
            </div>
            <div>
                <label class="neon-label">Slug</label>
                <input name="slug" value="{{ old('slug') }}" class="input-neon" placeholder="tournament_manager" required>
                <p class="mt-1 text-xs text-zinc-500">Lowercase letters, numbers and underscores.</p>
            </div>
            <div>
                <label class="neon-label">Description</label>
                <textarea name="description" rows="3" class="input-neon">{{ old('description') }}</textarea>
            </div>
            <div class="flex gap-2">
                <button class="btn-neon">Create role</button>
                <a href="{{ route('admin.roles.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </div>

        <div class="lg:col-span-2">
            <h2 class="mb-3 text-sm font-semibold text-white">Permissions</h2>
            @include('admin.roles._matrix', ['groups' => $groups, 'granted' => old('permissions', [])])
        </div>
    </form>
@endsection
