@extends('layouts.public')

@section('title', 'My Profile')

@section('content')
    <div class="mb-6">
        <h1 class="neon-title text-2xl">My profile</h1>
        <p class="text-sm text-zinc-500">Your account details and security.</p>
    </div>

    <div class="space-y-6">
        @include('profile.partials.account-summary')
        @include('profile.partials.update-profile-information-form', ['profileUpdateRoute' => 'customer.profile.update'])
        @include('profile.partials.update-password-form')
        @include('profile.partials.delete-user-form', ['profileDeleteRoute' => 'customer.profile.destroy'])
    </div>
@endsection
