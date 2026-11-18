<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 32)->unique();
            $table->string('nama');
            $table->string('satuan', 16)->default('pcs');
            $table->integer('harga_acuan')->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->index(['aktif', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
