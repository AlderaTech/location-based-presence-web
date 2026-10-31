<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pengguna')
            ->whereIn('peran', ['tata_usaha', 'kepala_sekolah'])
            ->update(['peran' => 'admin']);

        DB::table('pengguna')
            ->whereIn('peran', ['siswa', 'mahasiswa'])
            ->update(['peran' => 'murid']);
    }

    public function down(): void
    {
        //
    }
};
