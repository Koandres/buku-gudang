<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseRequest;
use App\Models\Warehouse;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseController extends Controller
{
    use LogsActivity;

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Warehouse::class);
        $q = trim((string) $request->input('q', ''));

        $warehouses = Warehouse::query()
            ->when($q, fn ($query) => $query->where(
                fn ($query) => $query->where('kode', 'like', "%$q%")->orWhere('nama', 'like', "%$q%")
            ))
            ->orderBy('kode')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Master/Warehouses/Index', [
            'warehouses' => $warehouses,
            'q' => $q,
            'canManage' => Auth::user()->can('create', Warehouse::class),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Warehouse::class);

        return Inertia::render('Master/Warehouses/Form', ['warehouse' => null]);
    }

    public function store(WarehouseRequest $request): RedirectResponse
    {
        Gate::authorize('create', Warehouse::class);
        $warehouse = Warehouse::create($request->validated());
        $this->logActivity('Tambah gudang', "Gudang: {$warehouse->kode}", $warehouse->nama);

        return redirect()->route('warehouses.index')->with('success', 'Gudang ditambahkan.');
    }

    public function edit(Warehouse $warehouse): Response
    {
        Gate::authorize('update', $warehouse);

        return Inertia::render('Master/Warehouses/Form', ['warehouse' => $warehouse]);
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        Gate::authorize('update', $warehouse);
        $warehouse->update($request->validated());
        $this->logActivity('Ubah gudang', "Gudang: {$warehouse->kode}", $warehouse->nama);

        return redirect()->route('warehouses.index')->with('success', 'Gudang diperbarui.');
    }
}
