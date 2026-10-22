<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'action', 'user', 'group', 'from', 'to'])
            + ['q' => null, 'action' => null, 'user' => null, 'group' => null, 'from' => null, 'to' => null];

        $logs = ActivityLog::with('user')
            ->filter($filters)
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'actions' => ActivityLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
