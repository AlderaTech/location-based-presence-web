<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->index(['id_jadwal_pelajaran', 'tanggal_presensi'], 'idx_presensi_jadwal_tanggal');
            $table->index(['id_murid', 'tanggal_presensi'], 'idx_presensi_murid_tanggal');
        });

        Schema::table('murid', function (Blueprint $table) {
            $table->boolean('notifikasi_aktif')->default(true)->after('aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->dropIndex('idx_presensi_jadwal_tanggal');
            $table->dropIndex('idx_presensi_murid_tanggal');
        });

        Schema::table('murid', function (Blueprint $table) {
            $table->dropColumn('notifikasi_aktif');
        });
    }
};
