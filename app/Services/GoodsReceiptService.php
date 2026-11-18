<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptService
{
    use LogsActivity;

    public function __construct(
        private DocumentNumberService $numbers,
        private StockService $stock,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(PurchaseOrder $order, array $data, User $user): GoodsReceipt
    {
        if (! $order->status?->bisaDiterima()) {
            throw ValidationException::withMessages([
                'purchase_order_id' => 'PO harus berstatus terbit atau sebagian diterima.',
            ]);
        }

        return DB::transaction(function () use ($order, $data, $user): GoodsReceipt {
            $orderItems = $order->items()->lockForUpdate()->get()->keyBy('id');

            foreach ($data['items'] as $line) {
                $orderItem = $orderItems->get((int) $line['purchase_order_item_id']);

                if (! $orderItem) {
                    throw ValidationException::withMessages(['items' => 'Baris PO tidak ditemukan pada pesanan ini.']);
                }

                if ((int) $line['jumlah'] > $orderItem->sisa()) {
                    throw ValidationException::withMessages([
                        'items' => "Jumlah diterima melebihi sisa PO ({$orderItem->sisa()}).",
                    ]);
                }
            }

            $receipt = GoodsReceipt::create([
                'nomor' => $this->numbers->next('GR'),
                'tanggal' => $data['tanggal'],
                'purchase_order_id' => $order->id,
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => $user->id,
                'catatan' => $data['catatan'] ?? null,
            ]);

            foreach ($data['items'] as $line) {
                /** @var PurchaseOrderItem $orderItem */
                $orderItem = $orderItems->get((int) $line['purchase_order_item_id']);

                $receipt->items()->create([
                    'purchase_order_item_id' => $orderItem->id,
                    'jumlah' => (int) $line['jumlah'],
                ]);

                $orderItem->increment('jumlah_diterima', (int) $line['jumlah']);

                $this->stock->catatMasuk(
                    $orderItem->item_id,
                    (int) $data['warehouse_id'],
                    (int) $line['jumlah'],
                    $receipt->nomor,
                    $data['catatan'] ?? null,
                    $user,
                );
            }

            $order->load('items');
            $status = $order->items->every(fn ($item): bool => $item->jumlah_diterima >= $item->jumlah)
                ? OrderStatus::Selesai
                : OrderStatus::SebagianDiterima;
            $order->update(['status' => $status]);

            $this->logActivity('Terima barang', "GR: {$receipt->nomor}", "Dari PO: {$order->nomor}");

            return $receipt->load(['order', 'warehouse', 'user', 'items.orderItem.item']);
        });
    }
}
