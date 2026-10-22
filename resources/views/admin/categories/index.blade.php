@extends('layouts.admin')

@section('title', 'Categories')
@section('subtitle', 'Group activities for pricing and the customer catalogue')

@section('header')
    <a href="{{ route('admin.categories.create') }}" class="btn-neon">
        <x-icon name="plus" class="h-4 w-4" /> Add category
    </a>
@endsection

@section('content')
    <div class="neon-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-neon">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Slug</th>
                        <th>Business</th>
                        <th>Activities</th>
                        <th>Colour</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $c)
                        <tr>
                            <td class="font-medium text-white">{{ $c->name }}</td>
                            <td class="font-mono text-xs text-zinc-500">{{ $c->slug }}</td>
                            <td class="text-sm text-zinc-400">{{ $c->business->name }}</td>
                            <td class="text-sm">{{ $c->resources_count }}</td>
                            <td>
                                <span class="inline-flex items-center gap-2">
                                    <span class="h-4 w-4 rounded" style="background: {{ $c->color }}"></span>
                                    <code class="text-xs text-zinc-500">{{ $c->color }}</code>
                                </span>
                            </td>
                            <td>
                                @if($c->is_active)
                                    <span class="badge border-emerald-500/30 bg-emerald-500/10 text-emerald-300">Active</span>
                                @else
                                    <span class="badge border-white/10 bg-white/[0.03] text-zinc-400">Hidden</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $c) }}" class="btn-mini">
                                        <x-icon name="pencil" class="h-3.5 w-3.5" /> Edit
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" class="inline"
                                              data-confirm="Delete the “{{ $c->name }}” category? Activities must be moved first.">
                                            @csrf @method('DELETE')
                                            <button class="btn-danger">
                                                <x-icon name="trash" class="h-3.5 w-3.5" /> Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-sm text-zinc-500">
                                No categories yet.
                                <a href="{{ route('admin.categories.create') }}" class="ml-1 text-neon-400 hover:text-neon-300">Create one →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $categories->links() }}</div>
@endsection
