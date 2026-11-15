<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'id_user';
    protected ?array $idKosFavoritCache = null;

    protected $fillable = [
        'username',
        'email',
        'password',
        'nama_lengkap',
        'no_telp',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function favoriteKos(): BelongsToMany
    {
        return $this->belongsToMany(Kos::class, 'favorites', 'id_user', 'id_kos', 'id_user', 'id_kos')
                    ->withTimestamps();
    }

    // Hasilnya disimpan sementara selama satu request, jadi 20 kartu tidak memicu 20 query
    public function sudahFavorit(int $idKos): bool
    {
        $this->idKosFavoritCache ??= $this->favoriteKos()
            ->pluck('kos.id_kos')
            ->map(fn ($i) => (int) $i)
            ->all();

        return in_array($idKos, $this->idKosFavoritCache, true);
    }
}