<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role');
        $role = in_array($role, ['pencari_kos', 'pemilik_kos', 'admin'], true) ? $role : null;

        $q    = trim((string) $request->query('q', ''));
        $like = '%' . addcslashes($q, '%_\\') . '%';

        $hitung = User::selectRaw('role, count(*) as jumlah')->groupBy('role')->pluck('jumlah', 'role');

        $daftar = User::withCount(['kos', 'pengajuan'])
            ->when($role, fn ($w) => $w->where('role', $role))
            ->when($q !== '', fn ($w) => $w->where(fn ($s) => $s
                ->where('nama_lengkap', 'like', $like)
                ->orWhere('username', 'like', $like)
                ->orWhere('email', 'like', $like)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.pengguna.index', compact('daftar', 'hitung', 'role', 'q'));
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->role === 'admin' || (int) $user->id_user === (int) $request->user()->id_user) {
            return back()->with('galat', 'Akun admin tidak bisa dihapus dari sini.');
        }

        $nama = $user->nama_lengkap;

        // Kumpulkan file foto semua kos milik pengguna ini sebelum datanya hilang
        $berkas = $user->kos()->get()->flatMap(fn ($k) => $k->daftarBerkas())->all();

        DB::transaction(fn () => $user->delete());

        Storage::disk('public')->delete($berkas);

        return back()->with('status', 'Akun ' . $nama . ' dan semua datanya dihapus.');
    }
}