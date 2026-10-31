<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sesi_presensi', function (Blueprint $table): void {
            $table->string('kode_unik', 64)->nullable()->after('kode_qr')->index();
            $table->text('payload_qr')->nullable()->after('kode_unik');
        });
    }

    public function down(): void
    {
        Schema::table('sesi_presensi', function (Blueprint $table): void {
            $table->dropIndex(['kode_unik']);
            $table->dropColumn(['kode_unik', 'payload_qr']);
        });
    }
};
