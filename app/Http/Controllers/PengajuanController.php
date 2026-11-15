<?php

namespace App\Http\Controllers;

use App\Models\Kos;
use App\Models\PengajuanSewa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->user()->id_user;

        $filter = $request->query('status');
        $filter = in_array($filter, array_keys(PengajuanSewa::STATUS), true) ? $filter : null;

        $hitung = PengajuanSewa::where('id_user', $id)
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $daftar = PengajuanSewa::with('kos.pemilik')
            ->where('id_user', $id)
            ->when($filter, fn ($q) => $q->where('status', $filter))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pengajuan.index', compact('daftar', 'hitung', 'filter'));
    }

    public function store(Request $request, Kos $kos)
    {
        abort_unless($kos->status_verifikasi === 'terverifikasi', 404);

        $data = $request->validate([
            'tanggal_masuk'   => ['required', 'date', 'after_or_equal:today', 'before_or_equal:+1 year'],
            'durasi_bulan'    => ['required', Rule::in(array_keys(PengajuanSewa::DURASI))],
            'jumlah_penghuni' => ['required', 'integer', 'between:1,10'],
        ], [
            'tanggal_masuk.required'        => 'Pilih rencana tanggal masuk.',
            'tanggal_masuk.date'            => 'Tanggal masuk tidak valid.',
            'tanggal_masuk.after_or_equal'  => 'Tanggal masuk tidak boleh sebelum hari ini.',
            'tanggal_masuk.before_or_equal' => 'Tanggal masuk maksimal 1 tahun dari sekarang.',
            'durasi_bulan.required'         => 'Pilih lama sewa.',
            'durasi_bulan.in'               => 'Lama sewa tidak valid.',
            'jumlah_penghuni.between'       => 'Jumlah penghuni antara 1 sampai 10.',
        ]);

        if ($kos->kamar_tersedia !== null && (int) $kos->kamar_tersedia === 0) {
            return back()->withInput()->with('galat', 'Kamar di kos ini sedang penuh.');
        }

        $user = $request->user();

        $diblokir = DB::transaction(function () use ($user, $kos, $data) {
            // Kunci baris pengguna supaya klik ganda atau dua tab tidak lolos bersamaan
            User::whereKey($user->getKey())->lockForUpdate()->first();

            // Satu pengajuan aktif per pencari, di kos mana pun
            $aktif = PengajuanSewa::with('kos:id_kos,nama_kos')
                ->where('id_user', $user->id_user)
                ->whereIn('status', ['menunggu', 'diterima'])
                ->orderByDesc('id_pengajuan')
                ->first();

            if ($aktif) {
                return $aktif;
            }

            PengajuanSewa::create([
                'id_kos'          => $kos->id_kos,
                'id_user'         => $user->id_user,
                'tanggal_masuk'   => $data['tanggal_masuk'],
                'durasi_bulan'    => (int) $data['durasi_bulan'],
                'jumlah_penghuni' => (int) $data['jumlah_penghuni'],
                'status'          => 'menunggu',
            ]);

            return null;
        });

        if ($diblokir) {
            return back()->withInput()->with('galat', $this->pesanBlokir($diblokir, $kos));
        }

        return redirect()->route('pengajuan.index')
            ->with('status', 'Pengajuan sewa terkirim. Kontak pemilik tampil setelah pengajuanmu diterima.');
    }

    public function batalkan(Request $request, PengajuanSewa $pengajuan)
    {
        abort_unless((int) $pengajuan->id_user === (int) $request->user()->id_user, 404);

        // Hanya berhasil kalau masih menunggu, jadi tidak bentrok dengan respons pemilik
        $diubah = PengajuanSewa::whereKey($pengajuan->getKey())
            ->where('status', 'menunggu')
            ->update(['status' => 'dibatalkan']);

        return back()->with(
            $diubah ? 'status' : 'galat',
            $diubah ? 'Pengajuan dibatalkan. Kamu bisa mengajukan ke kos lain.' : 'Pengajuan ini sudah direspons pemilik, jadi tidak bisa dibatalkan.'
        );
    }

    // Pencari mengakhiri sewa: "saya sudah tidak ngekos di sini"
    public function akhiri(Request $request, PengajuanSewa $pengajuan)
    {
        abort_unless((int) $pengajuan->id_user === (int) $request->user()->id_user, 404);

        $diubah = PengajuanSewa::whereKey($pengajuan->getKey())
            ->where('status', 'diterima')
            ->update([
                'status'        => 'selesai',
                'diakhiri_pada' => now(),
                'diakhiri_oleh' => 'pencari',
            ]);

        return back()->with(
            $diubah ? 'status' : 'galat',
            $diubah ? 'Sewa diakhiri. Kamu sekarang bisa mengajukan ke kos lain.' : 'Sewa ini sudah tidak berstatus diterima.'
        );
    }

    private function pesanBlokir(PengajuanSewa $aktif, Kos $kos): string
    {
        $sama = (int) $aktif->id_kos === (int) $kos->id_kos;
        $nama = $aktif->kos?->nama_kos ?? 'kos lain';

        if ($aktif->status === 'menunggu') {
            return $sama
                ? 'Kamu sudah mengajukan sewa di kos ini dan masih menunggu respons pemilik.'
                : 'Pengajuanmu di ' . $nama . ' masih menunggu respons. Batalkan dulu kalau mau mengajukan ke kos lain.';
        }

        return $sama
            ? 'Pengajuanmu di kos ini sudah diterima.'
            : 'Kamu masih tercatat menyewa di ' . $nama . '. Akhiri sewa di sana dulu sebelum mengajukan ke kos lain.';
    }
}