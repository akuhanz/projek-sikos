<?php

namespace Database\Seeders;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KosSeeder extends Seeder
{
    public function run(): void
    {
        $pemilik = User::firstOrCreate(
            ['email' => 'pemilik@kos.test'],
            [
                'username'     => 'pemilik',
                'nama_lengkap' => 'Pemilik Contoh',
                'no_telp'      => '081234567890',
                'password'     => Hash::make('pemilik12345'),
                'role'         => 'pemilik_kos',
            ]
        );

        // nama, alamat, jenis, latitude, longitude
        $data = [
            ['Kos Melati Putri',    'Jl. Condongcatur No. 12',  'putri',  -7.7600, 110.3950],
            ['Kos Anggrek Putra',   'Jl. Seturan Raya No. 5',   'putra',  -7.7710, 110.4080],
            ['Kos Mawar Campur',    'Jl. Gejayan No. 21',       'campur', -7.7690, 110.3870],
            ['Kos Dahlia Putri',    'Jl. Kaliurang Km 5',       'putri',  -7.7550, 110.3790],
            ['Kos Kenanga Putra',   'Jl. Babarsari No. 8',      'putra',  -7.7790, 110.4150],
            ['Kos Flamboyan',       'Jl. Sagan No. 3',          'campur', -7.7770, 110.3760],
            ['Kos Teratai Putri',   'Jl. Colombo No. 17',       'putri',  -7.7740, 110.3890],
            ['Kos Cempaka Putra',   'Jl. Affandi No. 9',        'putra',  -7.7830, 110.3910],
            ['Kos Seruni Putri',    'Jl. Monjali No. 30',       'putri',  -7.7480, 110.3640],
            ['Kos Bougenville',     'Jl. Kusumanegara No. 14',  'campur', -7.7920, 110.3870],
            ['Kos Kamboja Putra',   'Jl. Parangtritis No. 22',  'putra',  -7.8200, 110.3680],
            ['Kos Sakura Putri',    'Jl. Gamping No. 6',        'putri',  -7.7960, 110.3330],
        ];

        foreach ($data as [$nama, $alamat, $jenis, $lat, $lng]) {
            Kos::updateOrCreate(
                ['nama_kos' => $nama],
                [
                    'id_user'           => $pemilik->id_user,
                    'alamat'            => $alamat,
                    'jenis_kos'         => $jenis,
                    'deskripsi'         => 'Kos nyaman dan strategis, dekat dengan pusat kegiatan.',
                    'latitude'          => $lat,
                    'longitude'         => $lng,
                    'status_verifikasi' => 'terverifikasi',
                ]
            );
        }
    }
}