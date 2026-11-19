<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use LogsActivity;

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);
        $q = trim((string) $request->input('q', ''));

        $users = User::query()
            ->when($q, fn ($query) => $query->where(
                fn ($query) => $query->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%")
            ))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Master/Users/Index', ['users' => $users, 'q' => $q]);
    }

    public function create(): Response
    {
        Gate::authorize('create', User::class);

        return Inertia::render('Master/Users/Form', [
            'user' => null,
            'roles' => array_map(fn (UserRole $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ], UserRole::cases()),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);
        $data = $request->validated();
        $user = User::create($data);
        $this->logActivity('Tambah pengguna', "Pengguna: {$user->email}", $user->role->label());

        return redirect()->route('users.index')->with('success', 'Pengguna ditambahkan.');
    }

    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        return Inertia::render('Master/Users/Form', [
            'user' => $user,
            'roles' => array_map(fn (UserRole $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ], UserRole::cases()),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);
        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);
        $this->logActivity('Ubah pengguna', "Pengguna: {$user->email}", $user->role->label());

        return redirect()->route('users.index')->with('success', 'Pengguna diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);
        $user->update(['active' => ! $user->active]);
        $this->logActivity($user->active ? 'Aktifkan pengguna' : 'Nonaktifkan pengguna', "Pengguna: {$user->email}");

        return back()->with('success', $user->active ? 'Pengguna diaktifkan.' : 'Pengguna dinonaktifkan.');
    }
}
