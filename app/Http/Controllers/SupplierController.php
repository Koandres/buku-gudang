<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    use LogsActivity;

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Supplier::class);
        $q = trim((string) $request->input('q', ''));

        $suppliers = Supplier::query()
            ->when($q, fn ($query) => $query->where(
                fn ($query) => $query->where('kode', 'like', "%$q%")->orWhere('nama', 'like', "%$q%")
            ))
            ->orderBy('kode')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Master/Suppliers/Index', [
            'suppliers' => $suppliers,
            'q' => $q,
            'canManage' => Auth::user()->can('create', Supplier::class),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Supplier::class);

        return Inertia::render('Master/Suppliers/Form', ['supplier' => null]);
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        Gate::authorize('create', Supplier::class);
        $supplier = Supplier::create($request->validated());
        $this->logActivity('Tambah supplier', "Supplier: {$supplier->kode}", $supplier->nama);

        return redirect()->route('suppliers.index')->with('success', 'Supplier ditambahkan.');
    }

    public function edit(Supplier $supplier): Response
    {
        Gate::authorize('update', $supplier);

        return Inertia::render('Master/Suppliers/Form', ['supplier' => $supplier]);
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        Gate::authorize('update', $supplier);
        $supplier->update($request->validated());
        $this->logActivity('Ubah supplier', "Supplier: {$supplier->kode}", $supplier->nama);

        return redirect()->route('suppliers.index')->with('success', 'Supplier diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        Gate::authorize('delete', $supplier);
        $supplier->update(['aktif' => ! $supplier->aktif]);
        $this->logActivity($supplier->aktif ? 'Aktifkan supplier' : 'Nonaktifkan supplier', "Supplier: {$supplier->kode}");

        return back()->with('success', $supplier->aktif ? 'Supplier diaktifkan.' : 'Supplier dinonaktifkan.');
    }
}
