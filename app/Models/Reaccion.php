<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaccion extends Model
{
    use HasFactory;

    protected $fillable = [
        'foto_id',
        'tipo_reaccion',
        'fecha',
        'terminal_ip',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
