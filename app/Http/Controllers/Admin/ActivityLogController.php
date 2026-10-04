<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        try {
            $query = ActivityLog::query()->with('user:id,name,email')->latest('created_at');
            if ($request->filled('action')) {
                $query->where('action', 'like', '%'.$request->string('action').'%');
            }

            return Inertia::render('Admin/ActivityLogs', [
                'logs' => $query->paginate(20)->withQueryString(),
                'filters' => ['action' => $request->string('action')->toString()],
            ]);
        } catch (Throwable $exception) {
            Log::error('Activity logs could not be loaded', ['user_id' => auth()->id(), 'exception' => $exception->getMessage()]);
            return Inertia::render('Admin/ActivityLogs', ['logs' => ['data' => [], 'total' => 0], 'filters' => [], 'error' => 'Activity logs load nahi ho paaye.']);
        }
    }
}
