@extends('layouts.admin')

@section('title', 'Edit Gaming Activity')
@section('subtitle', $resource->name)

@section('content')
    <form method="POST" action="{{ route('admin.activities.update', $resource) }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PATCH')
        @include('admin.resources._form', ['resource' => $resource])

        <div class="flex gap-2">
            <button class="btn-neon">Save changes</button>
            <a href="{{ route('admin.activities.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
@endsection
