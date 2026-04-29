<?php

use App\Http\Controllers\Api\Mobile\PresensiController;
use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\PengajuanIzinController;
use App\Http\Controllers\Api\Mobile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->name('mobile.')->group(function (): void {
    Route::post('autentikasi/masuk', [AuthController::class, 'masuk'])->name('autentikasi.masuk');
    Route::post('autentikasi/aktivasi', [AuthController::class, 'aktivasi'])->name('autentikasi.aktivasi');
    Route::post('autentikasi/reset-kata-sandi', [AuthController::class, 'resetKataSandi'])->name('autentikasi.reset-kata-sandi');

    Route::middleware('mobile.auth')->group(function (): void {
        Route::post('autentikasi/keluar', [AuthController::class, 'keluar'])->name('autentikasi.keluar');
        Route::get('profil', [ProfileController::class, 'tampil'])->name('profil.tampil');
        Route::put('profil/pengaturan-notifikasi', [ProfileController::class, 'ubahPengaturanNotifikasi'])->name('profil.notifikasi');
        Route::get('jadwal-kerja', [ProfileController::class, 'jadwalKerja'])->name('profil.jadwal-kerja');
        Route::get('hari-libur', [ProfileController::class, 'hariLibur'])->name('profil.hari-libur');

        Route::get('presensi/hari-ini', [PresensiController::class, 'hariIni'])->name('presensi.hari-ini');
        Route::post('presensi/catat', [PresensiController::class, 'catat'])->name('presensi.catat');
        Route::get('presensi/riwayat', [PresensiController::class, 'riwayat'])->name('presensi.riwayat');

        Route::get('pengajuan-izin', [PengajuanIzinController::class, 'index'])->name('pengajuan-izin.index');
        Route::post('pengajuan-izin', [PengajuanIzinController::class, 'store'])->name('pengajuan-izin.store');
    });
});
