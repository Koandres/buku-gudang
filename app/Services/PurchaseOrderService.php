<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\RequestStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    use LogsActivity;

    public function __construct(
        private DocumentNumberService $numbers,
        private PurchaseRequestService $requests,
    ) {}

    /** @param array<string, mixed> $data */
    public function createFromRequest(PurchaseRequest $request, array $data, User $user): PurchaseOrder
    {
        if ($request->status !== RequestStatus::Disetujui) {
            throw ValidationException::withMessages([
                'purchase_request_id' => 'PR harus berstatus disetujui sebelum dibuat PO.',
            ]);
        }

        return DB::transaction(function () use ($request, $data, $user): PurchaseOrder {
            $prItems = $request->items()->with('item')->lockForUpdate()->get()->keyBy('id');

            foreach ($data['items'] as $line) {
                $prItem = $prItems->get((int) $line['purchase_request_item_id']);

                if (! $prItem) {
                    throw ValidationException::withMessages([
                        'items' => 'Baris PR tidak ditemukan pada permintaan ini.',
                    ]);
                }

                if ((int) $line['jumlah'] > $prItem->sisa()) {
                    throw ValidationException::withMessages([
                        'items' => "Jumlah {$prItem->item?->nama} melebihi sisa PR ({$prItem->sisa()}).",
                    ]);
                }
            }

            $order = PurchaseOrder::create([
                'nomor' => $this->numbers->next('PO'),
                'tanggal' => $data['tanggal'],
                'supplier_id' => $data['supplier_id'],
                'purchase_request_id' => $request->id,
                'user_id' => $user->id,
                'status' => OrderStatus::Draft,
                'catatan' => $data['catatan'] ?? null,
            ]);

            foreach ($data['items'] as $line) {
                /** @var PurchaseRequestItem $prItem */
                $prItem = $prItems->get((int) $line['purchase_request_item_id']);

                $order->items()->create([
                    'purchase_request_item_id' => $prItem->id,
                    'item_id' => $prItem->item_id,
                    'nama' => $prItem->item?->nama ?? (string) $prItem->item_id,
                    'harga' => (int) $line['harga'],
                    'jumlah' => (int) $line['jumlah'],
                ]);

                $prItem->increment('jumlah_dipesan', (int) $line['jumlah']);
            }

            $this->requests->tandaiDiproses($request->load('items'));
            $this->logActivity('Buat PO', "PO: {$order->nomor}", "Dari PR: {$request->nomor}");

            return $order->load(['supplier', 'request', 'items.item']);
        });
    }

    public function issue(PurchaseOrder $order): void
    {
        if ($order->status !== OrderStatus::Draft) {
            throw ValidationException::withMessages(['status' => 'Hanya PO draft yang dapat diterbitkan.']);
        }

        DB::transaction(function () use ($order): void {
            $order->update(['status' => OrderStatus::Terbit]);
            $this->logActivity('Terbitkan PO', "PO: {$order->nomor}", 'Status menjadi terbit');
        });
    }

    public function cancel(PurchaseOrder $order): void
    {
        if (! in_array($order->status, [OrderStatus::Draft, OrderStatus::Terbit], true)) {
            throw ValidationException::withMessages(['status' => 'PO ini tidak dapat dibatalkan.']);
        }

        $order->load('items');
        if ($order->items->contains(fn ($item): bool => $item->jumlah_diterima > 0)) {
            throw ValidationException::withMessages(['status' => 'PO yang sudah sebagian diterima tidak dapat dibatalkan.']);
        }

        DB::transaction(function () use ($order): void {
            foreach ($order->items as $line) {
                if ($line->purchase_request_item_id) {
                    PurchaseRequestItem::whereKey($line->purchase_request_item_id)
                        ->decrement('jumlah_dipesan', $line->jumlah);
                }
            }

            $order->update(['status' => OrderStatus::Dibatalkan]);

            if ($order->purchase_request_id) {
                $this->requests->tandaiBelumDiproses($order->request()->firstOrFail());
            }

            $this->logActivity('Batalkan PO', "PO: {$order->nomor}", 'Sisa PR dikembalikan');
        });
    }
}
