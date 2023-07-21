<?php

namespace Database\Seeders;

use App\Models\Album;
use Illuminate\Database\Seeder;

class AlbumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $albums = [
            'PAISAJES',
            'MONUMENTOS HISTORICOS',
            'RETRATOS COSTUMBRISTAS',
            'CALLEJEROS',
            'BUENAS PRACTICAS DE SERVICIOS AL CIUDADANO'
        ];

        foreach($albums as $album)
        {
            Album::create([
                'nombre' => $album,
            ]);
        }
    }
}
