<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pengajuan_izin');
        Schema::create('pengajuan_izin', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->cascadeOnDelete();
            $table->foreignId('id_murid')->constrained('murid')->cascadeOnDelete();
            $table->foreignId('id_jadwal_pelajaran')->nullable()->constrained('jadwal_pelajaran')->nullOnDelete();
            $table->string('jenis');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('alasan');
            $table->string('status')->default('menunggu');
            $table->foreignId('id_peninjau')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamp('ditinjau_pada')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pengajuan_izin');
        Schema::enableForeignKeyConstraints();
    }
};
