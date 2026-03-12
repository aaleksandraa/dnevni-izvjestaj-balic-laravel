<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $entityType = trim((string) $request->input('entity_type', ''));
        $action = trim((string) $request->input('action', ''));
        $userId = (int) $request->input('user_id', 0);
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));

        $logs = AuditLog::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('description', 'like', "%{$search}%")
                        ->orWhere('entity_type', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $innerQuery
                            ->orWhere('entity_id', (int) $search)
                            ->orWhere('id', (int) $search);
                    }
                });
            })
            ->when($entityType !== '', fn ($query) => $query->where('entity_type', $entityType))
            ->when($action !== '', fn ($query) => $query->where('action', $action))
            ->when($userId > 0, fn ($query) => $query->where('user_id', $userId))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $entityTypes = AuditLog::query()
            ->select('entity_type')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type');

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $users = User::query()
            ->orderBy('name')
            ->orderBy('email')
            ->get(['id', 'name', 'email']);

        return view('audit-logs.index', [
            'logs' => $logs,
            'entityTypes' => $entityTypes,
            'actions' => $actions,
            'users' => $users,
            'search' => $search,
            'entityType' => $entityType,
            'action' => $action,
            'userId' => $userId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
