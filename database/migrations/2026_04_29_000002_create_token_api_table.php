<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('token_api', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->cascadeOnDelete();
            $table->string('nama')->default('mobile');
            $table->string('hash_token', 64)->unique();
            $table->string('id_perangkat')->nullable();
            $table->timestamp('terakhir_dipakai_pada')->nullable();
            $table->timestamp('kedaluwarsa_pada')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('token_api');
    }
};
