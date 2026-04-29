<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perangkat_pengguna', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->cascadeOnDelete();
            $table->string('id_perangkat');
            $table->string('nama_perangkat')->nullable();
            $table->string('platform')->nullable();
            $table->timestamp('pertama_terlihat_pada')->nullable();
            $table->timestamp('terakhir_terlihat_pada')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
            $table->unique(['id_pengguna', 'id_perangkat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perangkat_pengguna');
    }
};
