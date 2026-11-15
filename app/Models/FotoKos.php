<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class FotoKos extends Model
{
    protected $table = 'foto_kos';
    protected $primaryKey = 'id_foto';

    protected $fillable = ['id_kos', 'path', 'kategori', 'keterangan', 'urutan'];

    // Pemakaian: $foto->url
    protected function url(): Attribute
    {
        return Attribute::get(fn () => asset('storage/' . $this->path));
    }
}