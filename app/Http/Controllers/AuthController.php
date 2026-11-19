<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau kata sandi salah.']);
        }

        if (! Auth::user()->active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun ini tidak aktif.']);
        }

        $request->session()->regenerate();

        return redirect()->intended($this->halamanAwal());
    }

    private function halamanAwal(): string
    {
        return match (Auth::user()->role) {
            UserRole::Pemohon, UserRole::Penyetuju => route('purchase-requests.index'),
            UserRole::Pembelian, UserRole::Admin => route('dashboard'),
            UserRole::Gudang => route('stock.index'),
        };
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
