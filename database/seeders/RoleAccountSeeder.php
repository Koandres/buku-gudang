<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleAccountSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        foreach (UserRole::cases() as $role) {
            User::updateOrCreate(
                ['email' => $role->value.'@bukugudang.test'],
                [
                    'name' => 'Akun '.$role->label(),
                    'password' => 'password',
                    'role' => $role,
                    'active' => true,
                ],
            );
        }
    }
}
