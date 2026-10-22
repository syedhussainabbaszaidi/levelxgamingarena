@extends('layouts.admin')

@section('title', 'Profile')
@section('subtitle', 'Your account details and security')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        @include('profile.partials.account-summary')
        @include('profile.partials.update-profile-information-form')
        @include('profile.partials.update-password-form')
        @include('profile.partials.delete-user-form')
    </div>
@endsection
