<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Models\StockMovement;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    use LogsActivity;

    public function __construct(private DocumentNumberService $numbers) {}

    public function saldo(int $itemId, int $warehouseId): int
    {
        return (int) StockMovement::query()
            ->where(['item_id' => $itemId, 'warehouse_id' => $warehouseId])
            ->get()
            ->sum(fn (StockMovement $movement): int => $movement->quantity * $movement->jenis->tanda());
    }

    public function catatMasuk(
        int $itemId,
        int $warehouseId,
        int $jumlah,
        string $reference,
        ?string $catatan,
        User $user,
    ): StockMovement {
        return StockMovement::create([
            'item_id' => $itemId,
            'warehouse_id' => $warehouseId,
            'jenis' => MovementType::Masuk,
            'quantity' => $jumlah,
            'reference' => $reference,
            'catatan' => $catatan,
            'user_id' => $user->id,
            'waktu' => now(),
        ]);
    }

    /** @param array<string, mixed> $data */
    public function sesuaikan(array $data, User $user): StockMovement
    {
        $itemId = (int) $data['item_id'];
        $warehouseId = (int) $data['warehouse_id'];
        $quantity = (int) $data['quantity'];

        if ($quantity === 0) {
            throw ValidationException::withMessages(['quantity' => 'Jumlah penyesuaian tidak boleh nol.']);
        }

        return DB::transaction(function () use ($itemId, $warehouseId, $quantity, $data, $user): StockMovement {
            $saldo = StockMovement::query()
                ->where(['item_id' => $itemId, 'warehouse_id' => $warehouseId])
                ->lockForUpdate()
                ->get()
                ->sum(fn (StockMovement $movement): int => $movement->quantity * $movement->jenis->tanda());

            if ($saldo + $quantity < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok tidak mencukupi. Saldo saat ini: {$saldo}.",
                ]);
            }

            $movement = StockMovement::create([
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'jenis' => MovementType::Penyesuaian,
                'quantity' => $quantity,
                'reference' => $this->numbers->next('ADJ'),
                'catatan' => $data['catatan'],
                'user_id' => $user->id,
                'waktu' => now(),
            ]);

            $this->logActivity('Penyesuaian stok', "ADJ: {$movement->reference}", $data['catatan']);

            return $movement->load(['item', 'warehouse']);
        });
    }
}
