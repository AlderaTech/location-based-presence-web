<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->cascadeOnDelete();
            $table->foreignId('id_guru')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('id_lokasi_sekolah')->nullable()->constrained('lokasi_sekolah')->nullOnDelete();
            $table->foreignId('id_jadwal_kerja')->nullable()->constrained('jadwal_kerja')->nullOnDelete();
            $table->date('tanggal_presensi');
            $table->timestamp('jam_masuk_pada')->nullable();
            $table->timestamp('jam_pulang_pada')->nullable();
            $table->decimal('latitude_masuk', 10, 7)->nullable();
            $table->decimal('longitude_masuk', 10, 7)->nullable();
            $table->unsignedInteger('akurasi_masuk')->nullable();
            $table->unsignedInteger('jarak_masuk_meter')->nullable();
            $table->decimal('latitude_pulang', 10, 7)->nullable();
            $table->decimal('longitude_pulang', 10, 7)->nullable();
            $table->unsignedInteger('akurasi_pulang')->nullable();
            $table->unsignedInteger('jarak_pulang_meter')->nullable();
            $table->string('status')->default('hadir');
            $table->boolean('terdeteksi_lokasi_palsu')->default(false);
            $table->string('id_perangkat')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('id_pengoreksi')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->text('alasan_koreksi')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
            $table->unique(['id_guru', 'tanggal_presensi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
