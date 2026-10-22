@extends('layouts.admin')

@section('title', 'Edit Category')
@section('subtitle', $category->name)

@section('header')
    <a href="{{ route('admin.categories.index') }}" class="btn-ghost">← All categories</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="neon-card mx-auto max-w-2xl space-y-6 p-6">
        @csrf @method('PATCH')
        @include('admin.categories._form', ['category' => $category])

        <div class="flex justify-end gap-2 border-t border-white/[0.06] pt-5">
            <a href="{{ route('admin.categories.index') }}" class="btn-ghost">Cancel</a>
            <button class="btn-neon">Save changes</button>
        </div>
    </form>
@endsection
