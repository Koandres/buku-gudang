<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Services\CsvExportService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reports,
        private CsvExportService $csv,
    ) {}

    public function index(): Response
    {
        $this->authorizeKeuangan();

        return Inertia::render('Reports/Index', [
            'ringkasan' => $this->reports->ringkasan(),
            'chart' => $this->reports->chartPembelian(),
        ]);
    }

    public function outstanding(): Response
    {
        $this->authorizeKeuangan();

        return Inertia::render('Reports/Outstanding', [
            'rows' => $this->reports->outstanding()->all(),
        ]);
    }

    public function suppliers(): Response
    {
        $this->authorizeKeuangan();

        return Inertia::render('Reports/Suppliers', [
            'rows' => $this->reports->perSupplier()->all(),
        ]);
    }

    public function stock(): Response
    {
        $this->authorizeLaporan();

        return Inertia::render('Reports/Stock', [
            'rows' => $this->reports->stokPerGudang()->all(),
        ]);
    }

    public function reorder(): Response
    {
        $this->authorizeKeuangan();

        return Inertia::render('Reports/Reorder', [
            'rows' => $this->reports->perluDipesan()->all(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $jenis = (string) $request->query('jenis', '');
        if ($jenis === 'stock') {
            $this->authorizeLaporan();
        } else {
            $this->authorizeKeuangan();
        }

        $data = $this->reports->dataEkspor($jenis);

        return $this->csv->download($data['headers'], $data['rows'], 'laporan-'.$jenis);
    }

    private function authorizeLaporan(): void
    {
        Gate::authorize('viewAny', PurchaseRequest::class);
    }

    private function authorizeKeuangan(): void
    {
        Gate::authorize('viewFinancialData');
    }
}
