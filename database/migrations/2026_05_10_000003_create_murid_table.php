<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('murid', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_pengguna')->unique()->constrained('pengguna')->cascadeOnDelete();
            $table->foreignId('id_kelas')->nullable()->constrained('kelas')->nullOnDelete();
            $table->string('nis')->unique();
            $table->string('nisn')->nullable()->unique();
            $table->string('jenis_kelamin')->default('L');
            $table->string('telepon_wali')->nullable();
            $table->text('alamat')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('murid');
    }
};
