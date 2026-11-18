<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\GoodsReceiptRequest;
use App\Models\ActivityLog;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GoodsReceiptController extends Controller
{
    public function __construct(private GoodsReceiptService $service) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', GoodsReceipt::class);
        $q = trim((string) $request->input('q', ''));
        $warehouseId = $request->integer('warehouse_id') ?: null;

        $receipts = GoodsReceipt::query()
            ->with([
                'order:id,nomor,supplier_id',
                'order.supplier:id,nama',
                'warehouse:id,kode,nama',
                'user:id,name',
                'items',
            ])
            ->when($q, fn ($query) => $query->where(
                fn ($query) => $query->where('nomor', 'like', "%$q%")
                    ->orWhereHas('order', fn ($order) => $order->where('nomor', 'like', "%$q%"))
                    ->orWhereHas('warehouse', fn ($warehouse) => $warehouse->where('nama', 'like', "%$q%"))
            ))
            ->when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
            ->latest('tanggal')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('GoodsReceipts/Index', [
            'receipts' => $receipts,
            'q' => $q,
            'warehouseId' => $warehouseId,
            'warehouses' => Warehouse::orderBy('nama')->get(['id', 'kode', 'nama']),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', GoodsReceipt::class);

        $orders = PurchaseOrder::query()
            ->whereIn('status', [OrderStatus::Terbit, OrderStatus::SebagianDiterima])
            ->whereHas('items', fn ($query) => $query->whereColumn('jumlah_diterima', '<', 'jumlah'))
            ->with(['supplier:id,nama', 'items.item:id,kode,nama,satuan'])
            ->orderBy('nomor')
            ->get()
            ->map(fn (PurchaseOrder $order): array => [
                'id' => $order->id,
                'nomor' => $order->nomor,
                'supplier' => $order->supplier?->nama,
                'items' => $order->items->map(fn ($line): array => [
                    'id' => $line->id,
                    'kode' => $line->item?->kode,
                    'nama' => $line->nama ?? $line->item?->nama,
                    'satuan' => $line->item?->satuan,
                    'sisa' => $line->sisa(),
                ])->all(),
            ])->all();

        return Inertia::render('GoodsReceipts/Form', [
            'orders' => $orders,
            'selectedOrder' => $request->integer('purchase_order_id') ?: null,
            'warehouses' => Warehouse::orderBy('nama')->get(['id', 'kode', 'nama']),
            'tanggal' => now()->toDateString(),
        ]);
    }

    public function store(GoodsReceiptRequest $request): RedirectResponse
    {
        Gate::authorize('create', GoodsReceipt::class);
        $data = $request->validated();
        $order = PurchaseOrder::findOrFail($data['purchase_order_id']);
        $receipt = $this->service->create($order, $data, $request->user());

        return redirect()->route('goods-receipts.show', $receipt)->with('success', 'Penerimaan barang disimpan.');
    }

    public function show(GoodsReceipt $goodsReceipt): Response
    {
        Gate::authorize('view', $goodsReceipt);
        $goodsReceipt->load([
            'order:id,nomor,supplier_id',
            'order.supplier:id,nama',
            'warehouse:id,kode,nama',
            'user:id,name',
            'items.orderItem:id,purchase_order_id,item_id,jumlah,jumlah_diterima',
            'items.orderItem.item:id,kode,nama,satuan',
        ]);

        return Inertia::render('GoodsReceipts/Show', [
            'receipt' => $goodsReceipt,
            'timeline' => ActivityLog::where('objek', "GR: {$goodsReceipt->nomor}")
                ->orderByDesc('waktu')->get(['aksi', 'ringkasan', 'waktu']),
        ]);
    }
}
