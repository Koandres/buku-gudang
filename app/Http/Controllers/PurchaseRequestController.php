<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Http\Requests\PurchaseRequestRequest;
use App\Http\Requests\RejectRequest;
use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseRequestController extends Controller
{
    public function __construct(private PurchaseRequestService $service) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', PurchaseRequest::class);
        $q = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');

        $requests = PurchaseRequest::query()
            ->with(['user:id,name', 'items.item:id,kode,nama,satuan'])
            ->when($q, fn ($query) => $query->where(
                fn ($query) => $query->where('nomor', 'like', "%$q%")->orWhere('keperluan', 'like', "%$q%")
            ))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('tanggal')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('PurchaseRequests/Index', [
            'requests' => $requests,
            'q' => $q,
            'status' => $status,
            'statuses' => array_map(fn (RequestStatus $item) => [
                'value' => $item->value,
                'label' => $item->label(),
            ], RequestStatus::cases()),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', PurchaseRequest::class);

        return Inertia::render('PurchaseRequests/Form', [
            'request' => null,
            'items' => Item::active()->orderBy('nama')->get(['id', 'kode', 'nama', 'satuan', 'harga_acuan']),
            'tanggal' => now()->toDateString(),
        ]);
    }

    public function store(PurchaseRequestRequest $request): RedirectResponse
    {
        Gate::authorize('create', PurchaseRequest::class);
        $pr = $this->service->create($request->validated(), $request->user());

        return redirect()->route('purchase-requests.show', $pr)->with('success', 'PR dibuat.');
    }

    public function show(PurchaseRequest $purchaseRequest): Response
    {
        Gate::authorize('view', $purchaseRequest);
        $purchaseRequest->load([
            'user:id,name',
            'items.item:id,kode,nama,satuan',
            'purchaseOrders:id,purchase_request_id,nomor,status',
        ]);

        return Inertia::render('PurchaseRequests/Show', [
            'request' => $purchaseRequest,
            'timeline' => ActivityLog::where('objek', "PR: {$purchaseRequest->nomor}")
                ->orderByDesc('waktu')->get(['aksi', 'ringkasan', 'waktu']),
            'can' => [
                'edit' => Gate::allows('update', $purchaseRequest),
                'submit' => Gate::allows('submit', $purchaseRequest),
                'approve' => Gate::allows('approve', $purchaseRequest),
                'reject' => Gate::allows('approve', $purchaseRequest),
                'createOrder' => Gate::allows('create', PurchaseOrder::class)
                    && $purchaseRequest->status === RequestStatus::Disetujui,
            ],
        ]);
    }

    public function edit(PurchaseRequest $purchaseRequest): Response
    {
        Gate::authorize('update', $purchaseRequest);

        return Inertia::render('PurchaseRequests/Form', [
            'request' => $purchaseRequest->load('items'),
            'items' => Item::active()->orderBy('nama')->get(['id', 'kode', 'nama', 'satuan', 'harga_acuan']),
            'tanggal' => now()->toDateString(),
        ]);
    }

    public function update(PurchaseRequestRequest $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        Gate::authorize('update', $purchaseRequest);
        $this->service->update($purchaseRequest, $request->validated());

        return redirect()->route('purchase-requests.show', $purchaseRequest)->with('success', 'PR diperbarui.');
    }

    public function destroy(PurchaseRequest $purchaseRequest): RedirectResponse
    {
        Gate::authorize('delete', $purchaseRequest);
        $this->service->destroy($purchaseRequest);

        return redirect()->route('purchase-requests.index')->with('success', 'Draft PR dihapus.');
    }

    public function submit(PurchaseRequest $purchaseRequest): RedirectResponse
    {
        Gate::authorize('submit', $purchaseRequest);
        $this->service->submit($purchaseRequest);

        return back()->with('success', 'PR diajukan untuk persetujuan.');
    }

    public function approve(PurchaseRequest $purchaseRequest): RedirectResponse
    {
        Gate::authorize('approve', $purchaseRequest);
        $this->service->approve($purchaseRequest, request()->user());

        return back()->with('success', 'PR disetujui.');
    }

    public function reject(RejectRequest $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        Gate::authorize('approve', $purchaseRequest);
        $this->service->reject($purchaseRequest, $request->validated('alasan'));

        return back()->with('success', 'PR ditolak.');
    }
}
