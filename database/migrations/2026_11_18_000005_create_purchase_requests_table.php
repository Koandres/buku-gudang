<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('nomor', 24)->unique();
            $table->date('tanggal');
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->text('keperluan');
            $table->string('status', 20)->default('draft');
            $table->text('alasan_ditolak')->nullable();
            $table->timestamps();
            $table->index(['status', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
