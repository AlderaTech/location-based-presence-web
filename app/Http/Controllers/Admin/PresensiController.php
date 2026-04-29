<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\LogAuditPresensi;
use App\Models\Presensi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PresensiController extends Controller
{
    public function index(Request $request): View
    {
        $presensi = Presensi::query()
            ->with(['pengguna', 'guru', 'jadwalKerja', 'lokasiSekolah'])
            ->when($request->query('guru_id'), fn ($query, $id) => $query->where('guru_id', $id))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('tanggal_mulai'), fn ($query, $tanggal) => $query->whereDate('tanggal_presensi', '>=', $tanggal))
            ->when($request->query('tanggal_selesai'), fn ($query, $tanggal) => $query->whereDate('tanggal_presensi', '<=', $tanggal))
            ->orderByDesc('tanggal_presensi')
            ->paginate(20)
            ->withQueryString();

        return view('admin.presensi.index', [
            'presensi' => $presensi,
            'guru' => Guru::query()->with('pengguna')->where('aktif', true)->orderBy('id')->get(),
        ]);
    }

    public function edit(Presensi $presensi): View
    {
        return view('admin.presensi.edit', ['presensi' => $presensi->load('pengguna', 'jadwalKerja', 'lokasiSekolah')]);
    }

    public function update(Request $request, Presensi $presensi): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:hadir,terlambat,izin,sakit,cuti,alpha'],
            'jam_masuk_pada' => ['nullable', 'date'],
            'jam_pulang_pada' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'alasan_koreksi' => ['required', 'string', 'max:2000'],
        ]);

        $sebelum = $presensi->only(['status', 'jam_masuk_pada', 'jam_pulang_pada', 'catatan']);
        $presensi->update([
            'status' => $data['status'],
            'jam_masuk_pada' => filled($data['jam_masuk_pada'] ?? null) ? Carbon::parse($data['jam_masuk_pada']) : null,
            'jam_pulang_pada' => filled($data['jam_pulang_pada'] ?? null) ? Carbon::parse($data['jam_pulang_pada']) : null,
            'catatan' => $data['catatan'] ?? null,
            'dikoreksi_oleh' => $request->user()->id,
            'alasan_koreksi' => $data['alasan_koreksi'],
        ]);

        LogAuditPresensi::create([
            'presensi_id' => $presensi->id,
            'pengguna_id' => $presensi->pengguna_id,
            'aktor_id' => $request->user()->id,
            'aksi' => 'koreksi_manual',
            'payload' => ['sebelum' => $sebelum, 'sesudah' => $presensi->fresh()->only(['status', 'jam_masuk_pada', 'jam_pulang_pada', 'catatan'])],
            'dibuat_pada' => now(),
        ]);

        return redirect()->route('admin.presensi.index')->with('status', 'Koreksi presensi berhasil disimpan.');
    }
}
