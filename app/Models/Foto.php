<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_participante',
        'titulo',
        'lugar',
        'resenia',
        'motivacion',
        'archivo',
        'activo',
        'publicado',
        'fecha_carga',
        'usuario_id',
        'album_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
