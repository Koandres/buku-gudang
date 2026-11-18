<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseRequestService
{
    use LogsActivity;

    public function __construct(private DocumentNumberService $numbers) {}

    /** @param array<string, mixed> $data */
    public function create(array $data, User $user): PurchaseRequest
    {
        return DB::transaction(function () use ($data, $user): PurchaseRequest {
            $request = PurchaseRequest::create([
                'nomor' => $this->numbers->next('PR'),
                'tanggal' => $data['tanggal'],
                'user_id' => $user->id,
                'keperluan' => $data['keperluan'],
                'status' => RequestStatus::Draft,
            ]);

            $this->saveItems($request, $data['items']);
            $this->logActivity('Buat PR', "PR: {$request->nomor}", $request->keperluan);

            return $request->load('items.item');
        });
    }

    /** @param array<string, mixed> $data */
    public function update(PurchaseRequest $request, array $data): PurchaseRequest
    {
        $this->pastikanDraft($request);

        return DB::transaction(function () use ($request, $data): PurchaseRequest {
            $request->update([
                'tanggal' => $data['tanggal'],
                'keperluan' => $data['keperluan'],
            ]);
            $request->items()->delete();
            $this->saveItems($request, $data['items']);
            $this->logActivity('Ubah PR', "PR: {$request->nomor}", $request->keperluan);

            return $request->load('items.item');
        });
    }

    public function submit(PurchaseRequest $request): void
    {
        $this->pastikanDraft($request);
        DB::transaction(function () use ($request): void {
            $request->update(['status' => RequestStatus::Diajukan]);
            $this->logActivity('Ajukan PR', "PR: {$request->nomor}", 'Status menjadi diajukan');
        });
    }

    public function approve(PurchaseRequest $request, User $approver): void
    {
        $this->pastikanStatus($request, RequestStatus::Diajukan);
        if ($request->user_id === $approver->id) {
            throw ValidationException::withMessages(['status' => 'Penyetuju tidak boleh menyetujui permintaan yang dibuatnya sendiri.']);
        }

        DB::transaction(function () use ($request): void {
            $request->update(['status' => RequestStatus::Disetujui]);
            $this->logActivity('Setujui PR', "PR: {$request->nomor}", 'Status menjadi disetujui');
        });
    }

    public function reject(PurchaseRequest $request, string $alasan): void
    {
        $this->pastikanStatus($request, RequestStatus::Diajukan);
        DB::transaction(function () use ($request, $alasan): void {
            $request->update([
                'status' => RequestStatus::Ditolak,
                'alasan_ditolak' => $alasan,
            ]);
            $this->logActivity('Tolak PR', "PR: {$request->nomor}", $alasan);
        });
    }

    public function destroy(PurchaseRequest $request): void
    {
        $this->pastikanDraft($request);
        DB::transaction(function () use ($request): void {
            $nomor = $request->nomor;
            $request->delete();
            $this->logActivity('Hapus PR', "PR: {$nomor}", 'Draft dihapus');
        });
    }

    public function tandaiDiproses(PurchaseRequest $request): void
    {
        if (! in_array($request->status, [RequestStatus::Disetujui, RequestStatus::Diproses], true)) {
            return;
        }

        $request->load('items');
        if ($request->items->isEmpty() || ! $request->items->every(fn ($item): bool => $item->jumlah_dipesan >= $item->jumlah)) {
            return;
        }

        if ($request->status === RequestStatus::Diproses) {
            return;
        }

        DB::transaction(function () use ($request): void {
            $request->update(['status' => RequestStatus::Diproses]);
            $this->logActivity('PR diproses', "PR: {$request->nomor}", 'Semua baris telah masuk PO');
        });
    }

    public function tandaiBelumDiproses(PurchaseRequest $request): void
    {
        if ($request->status !== RequestStatus::Diproses) {
            return;
        }

        $request->load('items');
        if (! $request->items->contains(fn ($item): bool => $item->jumlah_dipesan < $item->jumlah)) {
            return;
        }

        DB::transaction(function () use ($request): void {
            $request->update(['status' => RequestStatus::Disetujui]);
            $this->logActivity('PR dibuka kembali', "PR: {$request->nomor}", 'Sebagian PO dibatalkan');
        });
    }

    /** @param array<int, array<string, mixed>> $items */
    private function saveItems(PurchaseRequest $request, array $items): void
    {
        foreach ($items as $item) {
            $request->items()->create([
                'item_id' => $item['item_id'],
                'jumlah' => $item['jumlah'],
            ]);
        }
    }

    private function pastikanDraft(PurchaseRequest $request): void
    {
        if (! $request->isDraft()) {
            throw ValidationException::withMessages(['status' => 'Permintaan yang sudah diajukan tidak dapat diubah.']);
        }
    }

    private function pastikanStatus(PurchaseRequest $request, RequestStatus $status): void
    {
        if ($request->status !== $status) {
            throw ValidationException::withMessages(['status' => "Status PR harus {$status->label()}."]);
        }
    }
}
