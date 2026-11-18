<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class StockController extends Controller
{
    public function __construct(private StockService $service) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', StockMovement::class);
        $itemId = $request->integer('item_id') ?: null;
        $warehouseId = $request->integer('warehouse_id') ?: null;

        $movements = StockMovement::query()
            ->with([
                'item:id,kode,nama,satuan',
                'warehouse:id,kode,nama',
                'user:id,name',
            ])
            ->when($itemId, fn ($query) => $query->where('item_id', $itemId))
            ->when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
            ->orderBy('waktu')
            ->orderBy('id')
            ->get();

        $saldo = [];
        $rows = $movements->map(function (StockMovement $movement) use (&$saldo): array {
            $kunci = $movement->item_id.':'.$movement->warehouse_id;
            $saldo[$kunci] = ($saldo[$kunci] ?? 0) + $movement->quantity * $movement->jenis->tanda();

            return [
                'id' => $movement->id,
                'waktu' => $movement->waktu,
                'item' => $movement->item,
                'warehouse' => $movement->warehouse,
                'jenis' => $movement->jenis->value,
                'quantity' => $movement->quantity,
                'saldo' => $saldo[$kunci],
                'reference' => $movement->reference,
                'catatan' => $movement->catatan,
                'user' => $movement->user,
            ];
        })->reverse()->values()->all();

        return Inertia::render('Stock/Index', [
            'rows' => $rows,
            'saldoAkhir' => array_sum($saldo),
            'itemId' => $itemId,
            'warehouseId' => $warehouseId,
            'items' => Item::orderBy('nama')->get(['id', 'kode', 'nama', 'satuan']),
            'warehouses' => Warehouse::orderBy('nama')->get(['id', 'kode', 'nama']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('adjust', StockMovement::class);

        $items = Item::active()->orderBy('nama')->get(['id', 'kode', 'nama', 'satuan']);
        $warehouses = Warehouse::orderBy('nama')->get(['id', 'kode', 'nama']);
        $saldo = [];
        foreach ($items as $item) {
            foreach ($warehouses as $warehouse) {
                $saldo[$item->id][$warehouse->id] = $this->service->saldo($item->id, $warehouse->id);
            }
        }

        return Inertia::render('Stock/Adjust', [
            'items' => $items,
            'warehouses' => $warehouses,
            'saldo' => $saldo,
        ]);
    }

    public function store(StockAdjustmentRequest $request): RedirectResponse
    {
        Gate::authorize('adjust', StockMovement::class);
        $this->service->sesuaikan($request->validated(), $request->user());

        return redirect()->route('stock.index')->with('success', 'Penyesuaian stok disimpan.');
    }
}
