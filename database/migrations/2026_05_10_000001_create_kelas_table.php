<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_kelas')->unique();
            $table->unsignedInteger('tingkat')->default(10);
            $table->string('jurusan')->nullable();
            $table->string('tahun_ajaran')->default('2026/2027');
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
