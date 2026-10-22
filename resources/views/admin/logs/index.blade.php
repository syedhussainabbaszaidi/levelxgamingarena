@extends('layouts.admin')

@section('title', 'Activity Logs')
@section('subtitle', 'Complete audit trail of system actions')

@section('content')
    <div class="neon-card mb-6 p-4">
        <form method="GET" class="grid gap-3 md:grid-cols-6">
            <input name="q" value="{{ $filters['q'] }}" placeholder="Search description, IP…" class="input-neon md:col-span-2">
            <select name="action" class="select-neon">
                <option value="">All actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" @selected($filters['action'] === $action)>{{ $action }}</option>
                @endforeach
            </select>
            <select name="user" class="select-neon">
                <option value="">All users</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected((string) $filters['user'] === (string) $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ $filters['from'] }}" class="input-neon">
            <input type="date" name="to" value="{{ $filters['to'] }}" class="input-neon">
            <div class="flex gap-2 md:col-span-6">
                <button class="btn-neon">Filter</button>
                <a href="{{ route('admin.logs.index') }}" class="btn-ghost">Reset</a>
                <span class="ml-auto self-center text-xs text-zinc-500">{{ $logs->total() }} entries</span>
            </div>
        </form>
    </div>

    <div class="neon-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-neon">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Record</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap text-xs text-zinc-400">
                                {{ $log->created_at->format('d M Y') }}<br>
                                <span class="text-zinc-500">{{ $log->created_at->format('H:i:s') }}</span>
                            </td>
                            <td class="text-sm">
                                @if($log->user)
                                    <span class="text-zinc-200">{{ $log->user->name }}</span>
                                @else
                                    <span class="text-zinc-500">system</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge border-white/10 bg-white/[0.03] {{ $log->tone() }}">{{ $log->action }}</span>
                            </td>
                            <td class="max-w-md text-sm text-zinc-300">{{ $log->description }}</td>
                            <td class="text-xs text-zinc-500">
                                @if($log->subject_type)
                                    {{ $log->subject_type }}#{{ $log->subject_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="font-mono text-xs text-zinc-500">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-sm text-zinc-500">No activity recorded for these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
