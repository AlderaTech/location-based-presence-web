<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesi_presensi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_jadwal_pelajaran')->constrained('jadwal_pelajaran')->cascadeOnDelete();
            $table->foreignId('id_guru')->constrained('guru')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('kode_qr')->unique();
            $table->timestamp('waktu_buka')->nullable();
            $table->timestamp('waktu_tutup')->nullable();
            $table->string('status')->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_presensi');
    }
};
