<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_izin', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_pengajuan_izin')->constrained('pengajuan_izin')->cascadeOnDelete();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('nama_asli');
            $table->string('tipe_mime')->nullable();
            $table->unsignedBigInteger('ukuran')->default(0);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_izin');
    }
};
