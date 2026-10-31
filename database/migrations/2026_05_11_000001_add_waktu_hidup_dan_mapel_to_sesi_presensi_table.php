<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sesi_presensi', function (Blueprint $table): void {
            $table->foreignId('id_mata_pelajaran')
                ->nullable()
                ->after('id_jadwal_pelajaran')
                ->constrained('mata_pelajaran')
                ->cascadeOnDelete();
            $table->timestamp('waktu_mulai')->nullable()->after('waktu_buka');
            $table->timestamp('waktu_selesai')->nullable()->after('waktu_mulai');

            $table->index(['id_mata_pelajaran', 'status', 'waktu_selesai'], 'idx_sesi_mapel_status_selesai');
        });
    }

    public function down(): void
    {
        Schema::table('sesi_presensi', function (Blueprint $table): void {
            $table->dropIndex('idx_sesi_mapel_status_selesai');
            $table->dropForeign(['id_mata_pelajaran']);
            $table->dropColumn(['id_mata_pelajaran', 'waktu_mulai', 'waktu_selesai']);
        });
    }
};
