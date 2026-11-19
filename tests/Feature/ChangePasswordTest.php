<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function penggunaUntukSandi(): User
{
    return User::factory()->create(['password' => 'sandi-lama-123']);
}

it('menampilkan halaman ubah kata sandi', function () {
    $this->actingAs(penggunaUntukSandi())
        ->get(route('profile.password.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Profile/Password'));
});

it('mengubah kata sandi dengan sandi saat ini yang benar', function () {
    $user = penggunaUntukSandi();

    $this->actingAs($user)
        ->put(route('profile.password.update'), [
            'current_password' => 'sandi-lama-123',
            'password' => 'sandi-baru-123',
            'password_confirmation' => 'sandi-baru-123',
        ])
        ->assertRedirect();

    expect(Hash::check('sandi-baru-123', $user->fresh()->password))->toBeTrue();
});

it('menolak perubahan bila sandi saat ini salah', function () {
    $user = penggunaUntukSandi();

    $this->actingAs($user)
        ->put(route('profile.password.update'), [
            'current_password' => 'sandi-salah',
            'password' => 'sandi-baru-123',
            'password_confirmation' => 'sandi-baru-123',
        ])
        ->assertSessionHasErrors(['current_password' => 'Kata sandi saat ini tidak benar.']);

    expect(Hash::check('sandi-lama-123', $user->fresh()->password))->toBeTrue();
});

it('menolak perubahan bila konfirmasi sandi tidak cocok', function () {
    $user = penggunaUntukSandi();

    $this->actingAs($user)
        ->put(route('profile.password.update'), [
            'current_password' => 'sandi-lama-123',
            'password' => 'sandi-baru-123',
            'password_confirmation' => 'sandi-beda-123',
        ])
        ->assertSessionHasErrors(['password' => 'Konfirmasi kata sandi baru tidak cocok.']);

    expect(Hash::check('sandi-lama-123', $user->fresh()->password))->toBeTrue();
});

it('menampilkan pesan validasi berbahasa Indonesia', function (array $overrides, string $field, string $message) {
    $this->actingAs(penggunaUntukSandi())
        ->put(route('profile.password.update'), array_merge([
            'current_password' => 'sandi-lama-123',
            'password' => 'sandi-baru-123',
            'password_confirmation' => 'sandi-baru-123',
        ], $overrides))
        ->assertSessionHasErrors([$field => $message]);
})->with([
    'sandi saat ini kosong' => [
        ['current_password' => ''],
        'current_password',
        'Kata sandi saat ini wajib diisi.',
    ],
    'sandi baru kosong' => [
        ['password' => '', 'password_confirmation' => ''],
        'password',
        'Kata sandi baru wajib diisi.',
    ],
    'sandi baru terlalu pendek' => [
        ['password' => 'pendek', 'password_confirmation' => 'pendek'],
        'password',
        'Kata sandi baru minimal 8 karakter.',
    ],
    'sandi baru bukan teks' => [
        ['password' => ['sandi-baru-123'], 'password_confirmation' => ['sandi-baru-123']],
        'password',
        'Kata sandi baru harus berupa teks.',
    ],
]);
