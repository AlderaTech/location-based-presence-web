<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengajuanIzinController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $murid = $request->user()->murid;

        $items = PengajuanIzin::query()
            ->with(['dokumen', 'jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.guru.pengguna'])
            ->where('id_murid', $murid?->id)
            ->latest('dibuat_pada')
            ->get()
            ->map(fn (PengajuanIzin $pengajuan) => [
                'id' => $pengajuan->id,
                'jenis' => $pengajuan->jenis,
                'tanggal_mulai' => $pengajuan->tanggal_mulai->toDateString(),
                'tanggal_selesai' => $pengajuan->tanggal_selesai->toDateString(),
                'alasan' => $pengajuan->alasan,
                'status' => $pengajuan->status,
                'alasan_penolakan' => $pengajuan->alasan_penolakan,
                'mata_pelajaran' => $pengajuan->jadwalPelajaran?->mataPelajaran ? [
                    'id' => $pengajuan->jadwalPelajaran->mataPelajaran->id,
                    'nama' => $pengajuan->jadwalPelajaran->mataPelajaran->nama_mapel,
                ] : null,
                'dokumen' => $pengajuan->dokumen->map(fn ($dokumen) => [
                    'id' => $dokumen->id,
                    'nama_asli' => $dokumen->nama_asli,
                    'path' => $dokumen->path,
                    'url' => Storage::disk($dokumen->disk ?? 'public')->url($dokumen->path),
                ]),
            ]);

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_jadwal_pelajaran' => ['nullable', 'integer'],
            'jenis' => ['required', 'in:izin,sakit'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan' => ['required', 'string', 'max:2000'],
            'dokumen' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        $murid = $request->user()->murid;

        if (! $murid) {
            return response()->json(['pesan' => 'Akun bukan murid.'], 403);
        }

        $pengajuan = PengajuanIzin::create([
            'id_pengguna' => $request->user()->id,
            'id_murid' => $murid->id,
            'id_jadwal_pelajaran' => $data['id_jadwal_pelajaran'] ?? null,
            'jenis' => $data['jenis'],
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'alasan' => $data['alasan'],
            'status' => 'menunggu',
        ]);

        if ($request->hasFile('dokumen')) {
            $file = $request->file('dokumen');
            $path = $file->store('dokumen-izin', 'public');
            $pengajuan->dokumen()->create([
                'disk' => 'public',
                'path' => $path,
                'nama_asli' => $file->getClientOriginalName(),
                'tipe_mime' => $file->getMimeType(),
                'ukuran' => $file->getSize() ?: 0,
            ]);
        }

        return response()->json([
            'pesan' => 'Pengajuan izin/sakit berhasil dikirim.',
            'pengajuan_izin' => $pengajuan->load(['dokumen', 'jadwalPelajaran.mataPelajaran']),
        ], 201);
    }
}
