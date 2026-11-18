<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\RequestStatus;
use App\Http\Requests\PurchaseOrderRequest;
use App\Models\ActivityLog;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseOrderController extends Controller
{
    public function __construct(private PurchaseOrderService $service) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', PurchaseOrder::class);
        $q = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');
        $supplierId = $request->integer('supplier_id') ?: null;

        $orders = PurchaseOrder::query()
            ->with(['supplier:id,kode,nama', 'request:id,nomor', 'items'])
            ->when($q, fn ($query) => $query->where(
                fn ($query) => $query->where('nomor', 'like', "%$q%")
                    ->orWhere('catatan', 'like', "%$q%")
                    ->orWhereHas('supplier', fn ($supplier) => $supplier->where('nama', 'like', "%$q%"))
            ))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId))
            ->latest('tanggal')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('PurchaseOrders/Index', [
            'orders' => $orders,
            'q' => $q,
            'status' => $status,
            'supplierId' => $supplierId,
            'suppliers' => Supplier::orderBy('nama')->get(['id', 'kode', 'nama']),
            'statuses' => array_map(fn (OrderStatus $item): array => [
                'value' => $item->value,
                'label' => $item->label(),
            ], OrderStatus::cases()),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', PurchaseOrder::class);

        $requests = PurchaseRequest::query()
            ->where('status', RequestStatus::Disetujui)
            ->whereHas('items', fn ($query) => $query->whereColumn('jumlah_dipesan', '<', 'jumlah'))
            ->with(['user:id,name', 'items.item:id,kode,nama,satuan,harga_acuan'])
            ->orderBy('nomor')
            ->get()
            ->map(fn (PurchaseRequest $pr): array => [
                'id' => $pr->id,
                'nomor' => $pr->nomor,
                'tanggal' => $pr->tanggal?->toDateString(),
                'keperluan' => $pr->keperluan,
                'pemohon' => $pr->user?->name,
                'items' => $pr->items->map(fn ($line): array => [
                    'id' => $line->id,
                    'kode' => $line->item?->kode,
                    'nama' => $line->item?->nama ?? (string) $line->item_id,
                    'satuan' => $line->item?->satuan,
                    'harga_acuan' => (int) ($line->item?->harga_acuan ?? 0),
                    'sisa' => $line->sisa(),
                ])->all(),
            ])->all();

        return Inertia::render('PurchaseOrders/Form', [
            'order' => null,
            'requests' => $requests,
            'selectedRequest' => $request->integer('purchase_request_id') ?: null,
            'suppliers' => Supplier::active()->orderBy('nama')->get(['id', 'kode', 'nama']),
            'tanggal' => now()->toDateString(),
        ]);
    }

    public function store(PurchaseOrderRequest $request): RedirectResponse
    {
        Gate::authorize('create', PurchaseOrder::class);
        $data = $request->validated();
        $pr = PurchaseRequest::findOrFail($data['purchase_request_id']);
        $order = $this->service->createFromRequest($pr, $data, $request->user());

        return redirect()->route('purchase-orders.show', $order)->with('success', 'PO dibuat.');
    }

    public function show(PurchaseOrder $purchaseOrder): Response
    {
        Gate::authorize('view', $purchaseOrder);
        $purchaseOrder->load([
            'supplier:id,kode,nama',
            'request:id,nomor',
            'user:id,name',
            'items.item:id,kode,nama,satuan',
            'receipts:id,purchase_order_id,nomor,status',
        ]);

        return Inertia::render('PurchaseOrders/Show', [
            'order' => $purchaseOrder,
            'total' => $purchaseOrder->items->sum(fn ($item): int => $item->harga * $item->jumlah),
            'timeline' => ActivityLog::where('objek', "PO: {$purchaseOrder->nomor}")
                ->orderByDesc('waktu')->get(['aksi', 'ringkasan', 'waktu']),
            'can' => [
                'issue' => Gate::allows('issue', $purchaseOrder),
                'cancel' => Gate::allows('cancel', $purchaseOrder),
            ],
        ]);
    }

    public function issue(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        Gate::authorize('issue', $purchaseOrder);
        $this->service->issue($purchaseOrder);

        return back()->with('success', 'PO diterbitkan.');
    }

    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        Gate::authorize('cancel', $purchaseOrder);
        $this->service->cancel($purchaseOrder);

        return back()->with('success', 'PO dibatalkan.');
    }
}
