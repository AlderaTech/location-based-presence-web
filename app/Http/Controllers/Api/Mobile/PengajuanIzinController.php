<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengajuanIzinController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = PengajuanIzin::query()
            ->with('dokumen')
            ->where('id_guru', $request->user()->guru?->id)
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
                'dokumen' => $pengajuan->dokumen->map(fn ($dokumen) => [
                    'id' => $dokumen->id,
                    'nama_asli' => $dokumen->nama_asli,
                    'path' => $dokumen->path,
                ]),
            ]);

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'jenis' => ['required', 'in:izin,sakit,cuti'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan' => ['required', 'string', 'max:2000'],
            'dokumen' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        $guru = $request->user()->guru;
        $pengajuan = PengajuanIzin::create([
            'id_pengguna' => $request->user()->id,
            'id_guru' => $guru->id,
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
            'pesan' => 'Pengajuan berhasil dikirim.',
            'pengajuan_izin' => $pengajuan->load('dokumen'),
        ], 201);
    }
}
