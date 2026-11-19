<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private ReportService $reports) {}

    public function index(): Response
    {
        Gate::authorize('viewFinancialData');

        return Inertia::render('Dashboard', [
            'stats' => $this->reports->statistik(),
            'chart' => $this->reports->chartPembelian(),
        ]);
    }
}
