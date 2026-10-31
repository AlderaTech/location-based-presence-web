<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hari_libur', function (Blueprint $table): void {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('nama');
            $table->string('jenis')->default('sekolah');
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hari_libur');
    }
};
