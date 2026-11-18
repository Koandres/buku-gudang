<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);
        $q = trim((string) $request->input('q', ''));

        $logs = ActivityLog::query()
            ->with('user:id,name')
            ->when($q, fn ($query) => $query->where(
                fn ($query) => $query->where('aksi', 'like', "%$q%")
                    ->orWhere('objek', 'like', "%$q%")
                    ->orWhere('ringkasan', 'like', "%$q%"),
            ))
            ->orderByDesc('waktu')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Activities/Index', [
            'logs' => $logs,
            'q' => $q,
        ]);
    }
}
