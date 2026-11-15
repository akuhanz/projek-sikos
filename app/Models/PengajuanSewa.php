<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PengajuanSewa extends Model
{
    public const STATUS = [
        'menunggu'   => 'Menunggu respons',
        'diterima'   => 'Diterima',
        'ditolak'    => 'Ditolak',
        'dibatalkan' => 'Dibatalkan',
        'selesai'    => 'Selesai',
    ];

    public const DURASI = [
        1  => '1 bulan',
        3  => '3 bulan',
        6  => '6 bulan',
        12 => '12 bulan',
    ];

    protected $table = 'pengajuan_sewa';
    protected $primaryKey = 'id_pengajuan';

    protected $fillable = [
        'id_kos',
        'id_user',
        'tanggal_masuk',
        'durasi_bulan',
        'jumlah_penghuni',
        'catatan',
        'status',
        'catatan_pemilik',
        'direspons_pada',
        'diakhiri_pada',
        'diakhiri_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk'  => 'date',
            'direspons_pada' => 'datetime',
            'diakhiri_pada'  => 'datetime',
        ];
    }

    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class, 'id_kos', 'id_kos');
    }

    public function pencari(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Pengajuan untuk kos-kos milik satu pemilik
    public function scopeUntukPemilik(Builder $query, int $idUser): Builder
    {
        return $query->whereHas('kos', fn ($k) => $k->where('id_user', $idUser));
    }

    public function labelDurasi(): string
    {
        return self::DURASI[$this->durasi_bulan] ?? $this->durasi_bulan . ' bulan';
    }

    public function tanggalMasukIndo(): string
    {
        return Carbon::parse($this->tanggal_masuk)->locale('id')->translatedFormat('j F Y');
    }

    // Nomor pemilik (format 628xxx). Hanya tersedia setelah pengajuan DITERIMA.
    public function nomorPemilik(): ?string
    {
        if ($this->status !== 'diterima') {
            return null;
        }

        $telp = preg_replace('/\D+/', '', (string) $this->kos?->pemilik?->no_telp);

        if ($telp === '') {
            return null;
        }

        return str_starts_with($telp, '0') ? '62' . substr($telp, 1) : $telp;
    }

    public function linkWa(): ?string
    {
        $nomor = $this->nomorPemilik();

        if (! $nomor) {
            return null;
        }

        $pesan = 'Halo, pengajuan sewa saya di ' . $this->kos->nama_kos
               . ' sudah diterima. Boleh kita lanjut membahas survei dan pembayarannya?';

        return 'https://wa.me/' . $nomor . '?text=' . rawurlencode($pesan);
    }
}