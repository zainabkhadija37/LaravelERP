<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('can:activity-log.view');
    }

    public function index(Request $request)
    {
        return Activity::query()
            ->with('causer', 'subject')
            ->when($request->get('log_name'), fn ($q, $log) => $q->where('log_name', $log))
            ->when($request->get('subject_type'), fn ($q, $type) => $q->where('subject_type', $type))
            ->latest()
            ->paginate((int) $request->get('per_page', 25));
    }
}
