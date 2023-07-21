<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'portada',
        'publicado',
        'activo',
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
