<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('action', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs       = $query->latest()->paginate(20)->withQueryString();
        $totalLogs  = AuditLog::count();
        $users      = User::orderBy('name')->get();
        $actions    = AuditLog::distinct()->pluck('action')->sort()->values();

        return view('audit-logs.index', compact('logs', 'totalLogs', 'users', 'actions'));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        return view('audit-logs.show', compact('auditLog'));
    }

    public function destroy(AuditLog $auditLog)
    {
        $auditLog->delete(); // soft delete = archive
        return back()->with('success', 'Audit log archived.');
    }

    public function archived(Request $request)
    {
        $logs = AuditLog::onlyTrashed()->with('user')->latest('deleted_at')->paginate(20);
        return view('audit-logs.archived', compact('logs'));
    }

    public function restore(AuditLog $auditLog)
    {
        AuditLog::onlyTrashed()->findOrFail($auditLog->id)->restore();
        return back()->with('success', 'Audit log restored.');
    }

    public function forceDelete(AuditLog $auditLog)
    {
        AuditLog::onlyTrashed()->findOrFail($auditLog->id)->forceDelete();
        return back()->with('success', 'Audit log permanently deleted.');
    }

    public function clear()
    {
        AuditLog::truncate();
        return back()->with('success', 'All audit logs cleared.');
    }
}
