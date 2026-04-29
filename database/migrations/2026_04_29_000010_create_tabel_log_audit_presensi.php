<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_audit_presensi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_presensi')->nullable()->constrained('presensi')->nullOnDelete();
            $table->foreignId('id_pengguna')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->foreignId('id_aktor')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->string('aksi');
            $table->json('payload')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_audit_presensi');
    }
};
