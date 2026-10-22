@extends('layouts.admin')

@section('title', 'Edit Customer')
@section('subtitle', $customer->name)

@section('header')
    <div class="flex gap-2">
        <a href="{{ route('admin.customers.show', $customer) }}" class="btn-ghost">View profile</a>
        <a href="{{ route('admin.customers.index') }}" class="btn-ghost">← All customers</a>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="neon-card mx-auto max-w-2xl space-y-6 p-6">
        @csrf @method('PATCH')
        @include('admin.customers._form', ['customer' => $customer])

        <div class="flex justify-end gap-2 border-t border-white/[0.06] pt-5">
            <a href="{{ route('admin.customers.index') }}" class="btn-ghost">Cancel</a>
            <button class="btn-neon">Save changes</button>
        </div>
    </form>
@endsection
