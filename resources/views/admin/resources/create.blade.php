@extends('layouts.admin')

@section('title', 'New Gaming Activity')
@section('subtitle', 'Add a bookable service')

@section('content')
    <form method="POST" action="{{ route('admin.activities.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @include('admin.resources._form', ['resource' => null])

        <div class="flex gap-2">
            <button class="btn-neon">Create activity</button>
            <a href="{{ route('admin.activities.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
@endsection
