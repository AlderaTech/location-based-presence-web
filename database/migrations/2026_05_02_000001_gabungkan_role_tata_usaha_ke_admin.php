<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pengguna')
            ->where('peran', 'tata_usaha')
            ->update(['peran' => 'admin']);
    }

    public function down(): void
    {
        //
    }
};
