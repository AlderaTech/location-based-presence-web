<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\JadwalKerja;
use App\Models\LogAuditPresensi;
use App\Models\LokasiSekolah;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Support\SortirAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PresensiController extends Controller
{
    public function index(Request $request): View
    {
        $query = Presensi::query()
            ->with(['pengguna', 'guru', 'jadwalKerja', 'lokasiSekolah'])
            ->when($request->query('q'), function ($query, string $q): void {
                $query->where(function ($query) use ($q): void {
                    $query->whereHas('pengguna', fn ($pengguna) => $pengguna
                        ->where('nama', 'like', '%'.$q.'%')
                        ->orWhere('email', 'like', '%'.$q.'%')
                        ->orWhere('nomor_pegawai', 'like', '%'.$q.'%'))
                        ->orWhereHas('guru', fn ($guru) => $guru->where('nip', 'like', '%'.$q.'%'));
                });
            })
            ->when($request->query('id_guru'), fn ($query, $id) => $query->where('id_guru', $id))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('id_jadwal_kerja'), fn ($query, $id) => $query->where('id_jadwal_kerja', $id))
            ->when($request->query('id_lokasi_sekolah'), fn ($query, $id) => $query->where('id_lokasi_sekolah', $id))
            ->when($request->has('lokasi_palsu') && $request->query('lokasi_palsu') !== '', fn ($query) => $query->where('terdeteksi_lokasi_palsu', $request->boolean('lokasi_palsu')))
            ->when($request->query('tanggal_mulai'), fn ($query, $tanggal) => $query->whereDate('tanggal_presensi', '>=', $tanggal))
            ->when($request->query('tanggal_selesai'), fn ($query, $tanggal) => $query->whereDate('tanggal_presensi', '<=', $tanggal));

        SortirAdmin::terapkan($query, $request, [
            'tanggal_presensi' => 'presensi.tanggal_presensi',
            'nama' => fn ($query, string $arah) => $query->orderBy(
                Pengguna::query()->select('nama')->whereColumn('pengguna.id', 'presensi.id_pengguna'),
                $arah
            ),
            'status' => 'presensi.status',
            'jam_masuk_pada' => 'presensi.jam_masuk_pada',
            'jam_pulang_pada' => 'presensi.jam_pulang_pada',
            'jadwal' => fn ($query, string $arah) => $query->orderBy(
                JadwalKerja::query()->select('nama')->whereColumn('jadwal_kerja.id', 'presensi.id_jadwal_kerja'),
                $arah
            ),
            'lokasi' => fn ($query, string $arah) => $query->orderBy(
                LokasiSekolah::query()->select('nama')->whereColumn('lokasi_sekolah.id', 'presensi.id_lokasi_sekolah'),
                $arah
            ),
        ], 'tanggal_presensi', 'desc');

        $presensi = $query
            ->orderByDesc('presensi.id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.presensi.index', [
            'presensi' => $presensi,
            'guru' => Guru::query()->with('pengguna')->where('aktif', true)->orderBy('id')->get(),
            'jadwalKerja' => JadwalKerja::query()->where('aktif', true)->orderBy('nama')->get(),
            'lokasiSekolah' => LokasiSekolah::query()->orderBy('nama')->get(),
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
            'id_pengoreksi' => $request->user()->id,
            'alasan_koreksi' => $data['alasan_koreksi'],
        ]);

        LogAuditPresensi::create([
            'id_presensi' => $presensi->id,
            'id_pengguna' => $presensi->id_pengguna,
            'id_aktor' => $request->user()->id,
            'aksi' => 'koreksi_manual',
            'payload' => ['sebelum' => $sebelum, 'sesudah' => $presensi->fresh()->only(['status', 'jam_masuk_pada', 'jam_pulang_pada', 'catatan'])],
            'dibuat_pada' => now(),
        ]);

        return redirect()->route('admin.presensi.index')->with('status', 'Koreksi presensi berhasil disimpan.');
    }
}
