<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('presensi');
        Schema::create('presensi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->cascadeOnDelete();
            $table->foreignId('id_murid')->constrained('murid')->cascadeOnDelete();
            $table->foreignId('id_jadwal_pelajaran')->constrained('jadwal_pelajaran')->cascadeOnDelete();
            $table->foreignId('id_sesi_presensi')->nullable()->constrained('sesi_presensi')->nullOnDelete();
            $table->foreignId('id_lokasi_sekolah')->nullable()->constrained('lokasi_sekolah')->nullOnDelete();
            $table->date('tanggal_presensi');
            $table->timestamp('waktu_presensi')->nullable();
            $table->string('status')->default('hadir');
            $table->string('metode')->default('qr_lokasi');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('akurasi')->nullable();
            $table->unsignedInteger('jarak_meter')->nullable();
            $table->boolean('terdeteksi_lokasi_palsu')->default(false);
            $table->string('id_perangkat')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('id_pengoreksi')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->text('alasan_koreksi')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();

            $table->unique(['id_murid', 'id_jadwal_pelajaran', 'tanggal_presensi'], 'murid_jadwal_tanggal_unik');
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('presensi');
        Schema::enableForeignKeyConstraints();
    }
};
