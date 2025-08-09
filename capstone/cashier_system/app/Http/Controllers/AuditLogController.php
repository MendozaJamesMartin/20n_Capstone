<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditLogController extends Controller
{
    public function index(Request $request) {
        $query = \OwenIt\Auditing\Models\Audit::with('user')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($uq) => $uq->where('name', 'like', "%$search%"))
                ->orWhere('event', 'like', "%$search%")
                ->orWhere('auditable_type', 'like', "%$search%");
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('model')) {
            $query->where('auditable_type', 'like', '%'.$request->model);
        }

        if ($request->filled('user')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', $request->user);
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $audits = $query->paginate(10);

        return view('common.audit-logs', compact('audits'));
    }
}
