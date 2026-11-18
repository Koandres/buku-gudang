<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('aksi', 48);
            $table->string('objek', 96);
            $table->text('ringkasan')->nullable();
            $table->timestamp('waktu');
            $table->index(['waktu', 'aksi']);
            $table->index('objek');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
