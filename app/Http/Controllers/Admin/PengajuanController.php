<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanSewa;
use App\Models\User;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $status = in_array($status, array_keys(PengajuanSewa::STATUS), true) ? $status : null;

        $idPencari = ctype_digit((string) $request->query('pencari')) ? (int) $request->query('pencari') : null;

        $q    = trim((string) $request->query('q', ''));
        $like = '%' . addcslashes($q, '%_\\') . '%';

        $hitung = PengajuanSewa::when($idPencari, fn ($w) => $w->where('id_user', $idPencari))
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $daftar = PengajuanSewa::with(['kos:id_kos,nama_kos,id_user', 'kos.pemilik:id_user,nama_lengkap', 'pencari:id_user,nama_lengkap'])
            ->when($status, fn ($w) => $w->where('status', $status))
            ->when($q !== '', fn ($w) => $w->where(fn ($s) => $s
                ->whereHas('pencari', fn ($p) => $p->where('nama_lengkap', 'like', $like))
                ->orWhereHas('kos', fn ($k) => $k->where('nama_kos', 'like', $like))))
            ->when($idPencari, fn ($w) => $w->where('id_user', $idPencari))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $namaPencari = $idPencari ? User::whereKey($idPencari)->value('nama_lengkap') : null;

        return view('admin.pengajuan.index', compact('daftar', 'hitung', 'status', 'q', 'idPencari', 'namaPencari'));
    }
}