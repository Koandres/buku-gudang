<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemRequest;
use App\Models\Item;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    use LogsActivity;

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Item::class);
        $q = trim((string) $request->input('q', ''));

        $items = Item::query()
            ->when($q, fn ($query) => $query->where(
                fn ($query) => $query->where('kode', 'like', "%$q%")->orWhere('nama', 'like', "%$q%")
            ))
            ->orderBy('kode')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Master/Items/Index', [
            'items' => $items,
            'q' => $q,
            'canManage' => Auth::user()->can('create', Item::class),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Item::class);

        return Inertia::render('Master/Items/Form', [
            'item' => null,
            'units' => config('erp.units'),
        ]);
    }

    public function store(ItemRequest $request): RedirectResponse
    {
        Gate::authorize('create', Item::class);
        $item = Item::create($request->validated());
        $this->logActivity('Tambah barang', "Barang: {$item->kode}", $item->nama);

        return redirect()->route('items.index')->with('success', 'Barang ditambahkan.');
    }

    public function edit(Item $item): Response
    {
        Gate::authorize('update', $item);

        return Inertia::render('Master/Items/Form', [
            'item' => $item,
            'units' => config('erp.units'),
        ]);
    }

    public function update(ItemRequest $request, Item $item): RedirectResponse
    {
        Gate::authorize('update', $item);
        $item->update($request->validated());
        $this->logActivity('Ubah barang', "Barang: {$item->kode}", $item->nama);

        return redirect()->route('items.index')->with('success', 'Barang diperbarui.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        Gate::authorize('delete', $item);
        $item->update(['aktif' => ! $item->aktif]);
        $this->logActivity($item->aktif ? 'Aktifkan barang' : 'Nonaktifkan barang', "Barang: {$item->kode}");

        return back()->with('success', $item->aktif ? 'Barang diaktifkan.' : 'Barang dinonaktifkan.');
    }
}
