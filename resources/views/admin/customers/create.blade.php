@extends('layouts.admin')

@section('title', 'Add Customer')
@section('subtitle', 'Create a customer record for walk-ins and bookings')

@section('header')
    <a href="{{ route('admin.customers.index') }}" class="btn-ghost">← All customers</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.customers.store') }}" class="neon-card mx-auto max-w-2xl space-y-6 p-6">
        @csrf
        @include('admin.customers._form')

        <div class="flex justify-end gap-2 border-t border-white/[0.06] pt-5">
            <a href="{{ route('admin.customers.index') }}" class="btn-ghost">Cancel</a>
            <button class="btn-neon">Create customer</button>
        </div>
    </form>
@endsection
