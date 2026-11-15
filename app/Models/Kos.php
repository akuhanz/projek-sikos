<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kos extends Model
{
    public const FASILITAS_KAMAR = [
        'kasur'   => 'Kasur',
        'lemari'  => 'Lemari pakaian',
        'meja'    => 'Meja belajar',
        'kursi'   => 'Kursi',
        'ac'      => 'AC',
        'kipas'   => 'Kipas angin',
        'jendela' => 'Jendela',
        'tv'      => 'TV',
    ];

    public const FASILITAS_KAMAR_MANDI = [
        'kamar_mandi_dalam' => 'Kamar mandi dalam',
        'kamar_mandi_luar'  => 'Kamar mandi luar',
        'air_panas'         => 'Air panas',
        'kloset_duduk'      => 'Kloset duduk',
        'shower'            => 'Shower',
    ];

    public const FASILITAS_UMUM = [
        'wifi'        => 'WiFi',
        'dapur'       => 'Dapur bersama',
        'parkir'      => 'Parkir',
        'parkir_mobil' => 'Parkir mobil',
        'laundry'     => 'Laundry',
        'cctv'        => 'CCTV',
        'ruang_tamu'  => 'Ruang tamu',
        'mushola'     => 'Mushola',
        'jemuran'     => 'Tempat jemur',
        'dispenser'   => 'Dispenser air',
        'kulkas'      => 'Kulkas bersama',
        'satpam'      => 'Penjaga / satpam',
    ];

    // Gabungan semua, dipakai untuk validasi
    public const FASILITAS = self::FASILITAS_KAMAR + self::FASILITAS_KAMAR_MANDI + self::FASILITAS_UMUM;

    public const LISTRIK = [
        'termasuk' => 'Termasuk biaya sewa',
        'token'    => 'Token / meteran sendiri',
        'terpisah' => 'Dibayar terpisah',
    ];

    protected $table = 'kos';
    protected $primaryKey = 'id_kos';

    protected $fillable = [
        'id_user',
        'nama_kos',
        'alamat',
        'jenis_kos',
        'harga',
        'ukuran_kamar',
        'jumlah_kamar',
        'kamar_tersedia',
        'listrik',
        'deskripsi',
        'peraturan',
        'fasilitas',
        'foto',
        'latitude',
        'longitude',
        'status_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'fasilitas' => 'array',
        ];
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function difavoritkanOleh(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'id_kos', 'id_user', 'id_kos', 'id_user')
                    ->withTimestamps();
    }

    public function galeri(): HasMany
    {
        return $this->hasMany(FotoKos::class, 'id_kos', 'id_kos')->orderBy('urutan');
    }

    // Pemakaian: $kos->foto_url (null kalau belum ada foto utama)
    protected function fotoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->foto ? asset('storage/' . $this->foto) : null);
    }

    // Ambil teks fasilitas milik kos ini yang termasuk dalam satu kelompok
    public function fasilitasDi(array $kelompok): array
    {
        return collect($this->fasilitas ?? [])
            ->filter(fn ($kunci) => isset($kelompok[$kunci]))
            ->map(fn ($kunci) => $kelompok[$kunci])
            ->values()
            ->all();
    }
}