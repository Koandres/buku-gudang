<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Profile/Password');
    }

    public function update(ChangePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }
}
