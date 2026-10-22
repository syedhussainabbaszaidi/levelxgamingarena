@extends('layouts.admin')

@section('title', 'Add Category')
@section('subtitle', 'Group related activities together')

@section('header')
    <a href="{{ route('admin.categories.index') }}" class="btn-ghost">← All categories</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.categories.store') }}" class="neon-card mx-auto max-w-2xl space-y-6 p-6">
        @csrf
        @include('admin.categories._form')

        <div class="flex justify-end gap-2 border-t border-white/[0.06] pt-5">
            <a href="{{ route('admin.categories.index') }}" class="btn-ghost">Cancel</a>
            <button class="btn-neon">Create category</button>
        </div>
    </form>
@endsection
