<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\HariLiburController;
use App\Http\Controllers\Admin\JadwalPelajaranController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LokasiSekolahController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\MuridController;
use App\Http\Controllers\Admin\PenggunaAdminController;
use App\Http\Controllers\Admin\PersetujuanIzinController;
use App\Http\Controllers\Admin\PresensiController;
use App\Http\Controllers\Guru\SesiPresensiController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/admin/masuk', [AuthController::class, 'showLogin'])->name('admin.masuk');
Route::post('/admin/masuk', [AuthController::class, 'login'])->name('admin.masuk.store');
Route::post('/admin/keluar', [AuthController::class, 'logout'])->name('admin.keluar');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::get('pengajuan-izin', [PersetujuanIzinController::class, 'index'])->name('pengajuan-izin.index');
    Route::post('pengajuan-izin/{pengajuanIzin}/setujui', [PersetujuanIzinController::class, 'setujui'])->name('pengajuan-izin.setujui');
    Route::post('pengajuan-izin/{pengajuanIzin}/tolak', [PersetujuanIzinController::class, 'tolak'])->name('pengajuan-izin.tolak');

    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/ekspor/{format}', [LaporanController::class, 'ekspor'])->whereIn('format', ['xlsx', 'pdf'])->name('laporan.ekspor');

    // Sesi QR Presensi
    Route::get('sesi-presensi', [SesiPresensiController::class, 'index'])->name('sesi-presensi.index');
    Route::post('sesi-presensi', [SesiPresensiController::class, 'store'])->name('sesi-presensi.store');
    Route::get('sesi-presensi/{sesiPresensi}', [SesiPresensiController::class, 'show'])->name('sesi-presensi.show');
    Route::post('sesi-presensi/{sesiPresensi}/tutup', [SesiPresensiController::class, 'tutup'])->name('sesi-presensi.tutup');
    Route::post('sesi-presensi/{sesiPresensi}/perpanjang', [SesiPresensiController::class, 'perpanjang'])->name('sesi-presensi.perpanjang');
    Route::get('sesi-presensi/{sesiPresensi}/live', [SesiPresensiController::class, 'live'])->name('sesi-presensi.live');

    // Master Data Manajemen khusus Admin
    Route::middleware('peran:admin')->group(function (): void {
        Route::resource('kelas', KelasController::class)->parameters(['kelas' => 'kela'])->except('show');
        Route::resource('mata-pelajaran', MataPelajaranController::class)->parameters(['mata-pelajaran' => 'mataPelajaran'])->except('show');
        Route::resource('guru', GuruController::class)->parameters(['guru' => 'guru'])->except('show');
        Route::post('guru/{guru}/reset-perangkat', [GuruController::class, 'resetPerangkat'])->name('guru.reset-perangkat');

        Route::resource('murid', MuridController::class)->parameters(['murid' => 'murid'])->except('show');
        Route::post('murid/{murid}/reset-perangkat', [MuridController::class, 'resetPerangkat'])->name('murid.reset-perangkat');

        Route::resource('jadwal-pelajaran', JadwalPelajaranController::class)->parameters(['jadwal-pelajaran' => 'jadwalPelajaran'])->except('show');
        Route::resource('lokasi-sekolah', LokasiSekolahController::class)->parameters(['lokasi-sekolah' => 'lokasiSekolah'])->except('show');
        Route::resource('hari-libur', HariLiburController::class)->parameters(['hari-libur' => 'hariLibur'])->except('show');
        Route::resource('pengguna-admin', PenggunaAdminController::class)->parameters(['pengguna-admin' => 'penggunaAdmin'])->except('show');

        Route::get('presensi/{presensi}/edit', [PresensiController::class, 'edit'])->name('presensi.edit');
        Route::put('presensi/{presensi}', [PresensiController::class, 'update'])->name('presensi.update');
    });
});
