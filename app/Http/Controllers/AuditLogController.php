<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('view audit logs');

        $query = ActivityLog::with('causer', 'subject')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%')
                ->orWhereHas('causer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->input('search') . '%');
                });
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->input('log_name'));
        }

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        $activityLogs = $query->paginate(10);

        return view('audit-logs.index', compact('activityLogs'));
    }

    public function show(ActivityLog $activityLog): View
    {
        Gate::authorize('view audit logs');

        $activityLog->load('causer', 'subject');

        return view('audit-logs.show', compact('activityLog'));
    }
}