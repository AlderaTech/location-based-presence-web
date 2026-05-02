<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\HariLiburController;
use App\Http\Controllers\Admin\JadwalKerjaController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LokasiSekolahController;
use App\Http\Controllers\Admin\PenggunaAdminController;
use App\Http\Controllers\Admin\PersetujuanIzinController;
use App\Http\Controllers\Admin\PresensiController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/admin/masuk', [AuthController::class, 'showLogin'])->name('admin.masuk');
Route::post('/admin/masuk', [AuthController::class, 'login'])->name('admin.masuk.store');
Route::post('/admin/keluar', [AuthController::class, 'logout'])->name('admin.keluar');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::get('pengajuan-izin', [PersetujuanIzinController::class, 'index'])->name('pengajuan-izin.index');
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/ekspor/{format}', [LaporanController::class, 'ekspor'])->whereIn('format', ['xlsx', 'pdf'])->name('laporan.ekspor');

    Route::middleware('peran:admin')->group(function (): void {
        Route::resource('guru', GuruController::class)->parameters(['guru' => 'guru'])->except('show');
        Route::post('guru/{guru}/reset-perangkat', [GuruController::class, 'resetPerangkat'])->name('guru.reset-perangkat');

        Route::resource('lokasi-sekolah', LokasiSekolahController::class)->parameters(['lokasi-sekolah' => 'lokasiSekolah'])->except('show');
        Route::resource('jadwal-kerja', JadwalKerjaController::class)->parameters(['jadwal-kerja' => 'jadwalKerja'])->except('show');
        Route::resource('hari-libur', HariLiburController::class)->parameters(['hari-libur' => 'hariLibur'])->except('show');
        Route::resource('pengguna-admin', PenggunaAdminController::class)->parameters(['pengguna-admin' => 'penggunaAdmin'])->except('show');

        Route::get('presensi/{presensi}/edit', [PresensiController::class, 'edit'])->name('presensi.edit');
        Route::put('presensi/{presensi}', [PresensiController::class, 'update'])->name('presensi.update');

        Route::post('pengajuan-izin/{pengajuanIzin}/setujui', [PersetujuanIzinController::class, 'setujui'])->name('pengajuan-izin.setujui');
        Route::post('pengajuan-izin/{pengajuanIzin}/tolak', [PersetujuanIzinController::class, 'tolak'])->name('pengajuan-izin.tolak');
    });
});
