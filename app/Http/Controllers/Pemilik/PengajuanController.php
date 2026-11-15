<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\PengajuanSewa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->user()->id_user;

        $filter = $request->query('status', 'menunggu');
        $filter = in_array($filter, ['menunggu', 'diterima', 'ditolak', 'selesai', 'semua'], true) ? $filter : 'menunggu';

        $hitung = PengajuanSewa::untukPemilik($id)
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $daftar = PengajuanSewa::untukPemilik($id)
            ->with(['kos:id_kos,nama_kos', 'pencari:id_user,nama_lengkap'])
            ->when($filter !== 'semua', fn ($q) => $q->where('status', $filter))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pemilik.pengajuan.index', compact('daftar', 'hitung', 'filter'));
    }

    public function respons(Request $request, PengajuanSewa $pengajuan)
    {
        $user = $request->user();

        $pengajuan->load(['kos:id_kos,id_user,nama_kos', 'pencari:id_user,nama_lengkap']);

        // Hanya pemilik kos ini yang boleh merespons
        abort_unless((int) $pengajuan->kos->id_user === (int) $user->id_user, 404);

        $data = $request->validate([
            'aksi'            => ['required', Rule::in(['terima', 'tolak'])],
            'catatan_pemilik' => ['nullable', 'string'],
        ]);

        $diterima = $data['aksi'] === 'terima';

        if ($diterima && blank($user->no_telp)) {
            return back()->with('galat', 'Isi nomor teleponmu dulu di halaman Ringkasan. Tanpa nomor, pencari tidak punya cara menghubungimu setelah pengajuan diterima.');
        }

        $catatan = mb_substr(trim((string) ($data['catatan_pemilik'] ?? '')), 0, 300);

        // Ubah hanya kalau masih menunggu, jadi tidak bentrok dengan pembatalan oleh pencari
        $diubah = PengajuanSewa::whereKey($pengajuan->getKey())
            ->where('status', 'menunggu')
            ->update([
                'status'          => $diterima ? 'diterima' : 'ditolak',
                'catatan_pemilik' => $catatan !== '' ? $catatan : null,
                'direspons_pada'  => now(),
            ]);

        if (! $diubah) {
            return back()->with('galat', 'Pengajuan ini sudah tidak menunggu respons, mungkin dibatalkan pencari.');
        }

        $nama = $pengajuan->pencari?->nama_lengkap ?? 'pencari';

        return back()->with('status', 'Pengajuan dari ' . $nama . ($diterima
            ? ' diterima. Nomor WhatsApp-mu sekarang tampil di akun mereka.'
            : ' ditolak.'));
    }

    // Pemilik menandai penghuni sudah keluar
    public function akhiri(Request $request, PengajuanSewa $pengajuan)
    {
        $user = $request->user();

        $pengajuan->load(['kos:id_kos,id_user,nama_kos', 'pencari:id_user,nama_lengkap']);

        abort_unless((int) $pengajuan->kos->id_user === (int) $user->id_user, 404);

        $diubah = PengajuanSewa::whereKey($pengajuan->getKey())
            ->where('status', 'diterima')
            ->update([
                'status'        => 'selesai',
                'diakhiri_pada' => now(),
                'diakhiri_oleh' => 'pemilik',
            ]);

        if (! $diubah) {
            return back()->with('galat', 'Sewa ini sudah tidak berstatus diterima.');
        }

        return back()->with('status', ($pengajuan->pencari?->nama_lengkap ?? 'Penghuni')
            . ' ditandai sudah keluar. Dia sekarang bisa mengajukan ke kos lain.');
    }
}