<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 24)->default('pemohon')->after('password');
            $table->boolean('active')->default(true)->after('role');
            $table->index(['role', 'active']);
        });

        Schema::create('document_sequences', function (Blueprint $table): void {
            $table->id();
            $table->string('prefix', 8);
            $table->string('period', 6);
            $table->unsignedInteger('last_number')->default(0);
            $table->unique(['prefix', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['role', 'active']);
            $table->dropColumn(['role', 'active']);
        });
    }
};
