<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected function logActivity(string $aksi, string $objek, ?string $ringkasan = null): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'objek' => $objek,
            'ringkasan' => $ringkasan,
            'waktu' => now(),
        ]);
    }
}
