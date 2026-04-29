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
            $table->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete();
            $table->string('nama')->default('mobile');
            $table->string('hash_token', 64)->unique();
            $table->string('id_perangkat')->nullable();
            $table->timestamp('terakhir_dipakai_pada')->nullable();
            $table->timestamp('kedaluwarsa_pada')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });

        Schema::create('jadwal_kerja', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->unsignedInteger('toleransi_terlambat_menit')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });

        Schema::create('guru', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengguna_id')->unique()->constrained('pengguna')->cascadeOnDelete();
            $table->foreignId('jadwal_kerja_bawaan_id')->nullable()->constrained('jadwal_kerja')->nullOnDelete();
            $table->string('nip')->nullable()->unique();
            $table->string('mata_pelajaran')->nullable();
            $table->string('jabatan')->nullable();
            $table->text('alamat')->nullable();
            $table->text('jadwal_kerja')->nullable();
            $table->boolean('notifikasi_aktif')->default(true);
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });

        Schema::create('perangkat_pengguna', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete();
            $table->string('id_perangkat');
            $table->string('nama_perangkat')->nullable();
            $table->string('platform')->nullable();
            $table->timestamp('pertama_terlihat_pada')->nullable();
            $table->timestamp('terakhir_terlihat_pada')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
            $table->unique(['pengguna_id', 'id_perangkat']);
        });

        Schema::create('lokasi_sekolah', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('radius_meter')->default(100);
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });

        Schema::create('hari_libur', function (Blueprint $table): void {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('nama');
            $table->string('jenis')->default('sekolah');
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });

        Schema::create('presensi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('lokasi_sekolah_id')->nullable()->constrained('lokasi_sekolah')->nullOnDelete();
            $table->foreignId('jadwal_kerja_id')->nullable()->constrained('jadwal_kerja')->nullOnDelete();
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
            $table->foreignId('dikoreksi_oleh')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->text('alasan_koreksi')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
            $table->unique(['guru_id', 'tanggal_presensi']);
        });

        Schema::create('pengajuan_izin', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->string('jenis');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('alasan');
            $table->string('status')->default('menunggu');
            $table->foreignId('ditinjau_oleh')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamp('ditinjau_pada')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });

        Schema::create('dokumen_izin', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengajuan_izin_id')->constrained('pengajuan_izin')->cascadeOnDelete();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('nama_asli');
            $table->string('tipe_mime')->nullable();
            $table->unsignedBigInteger('ukuran')->default(0);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });

        Schema::create('log_audit_presensi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('presensi_id')->nullable()->constrained('presensi')->nullOnDelete();
            $table->foreignId('pengguna_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->foreignId('aktor_id')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->string('aksi');
            $table->json('payload')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_audit_presensi');
        Schema::dropIfExists('dokumen_izin');
        Schema::dropIfExists('pengajuan_izin');
        Schema::dropIfExists('presensi');
        Schema::dropIfExists('hari_libur');
        Schema::dropIfExists('lokasi_sekolah');
        Schema::dropIfExists('perangkat_pengguna');
        Schema::dropIfExists('guru');
        Schema::dropIfExists('jadwal_kerja');
        Schema::dropIfExists('token_api');
    }
};
