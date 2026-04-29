<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_pengguna')->unique()->constrained('pengguna')->cascadeOnDelete();
            $table->foreignId('id_jadwal_kerja_bawaan')->nullable()->constrained('jadwal_kerja')->nullOnDelete();
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
    }

    public function down(): void
    {
        Schema::dropIfExists('guru');
    }
};
