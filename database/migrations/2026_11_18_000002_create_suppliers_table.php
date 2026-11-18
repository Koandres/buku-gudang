<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 32)->unique();
            $table->string('nama');
            $table->string('kontak')->nullable();
            $table->string('telepon', 32)->nullable();
            $table->text('alamat')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->index(['aktif', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
