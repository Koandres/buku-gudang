<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'menunggu' => 0,
                'poTerbuka' => 0,
                'stokMinimum' => 0,
            ],
            'chart' => [],
        ]);
    }
}
