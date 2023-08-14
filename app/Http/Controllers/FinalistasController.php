<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinalistasController extends Controller
{
    public function finalists()
    {
        try {
            DB::beginTransaction();

            $top = DB::select('select f.nombre_participante , f.titulo, f.archivo ,f.fecha_carga , a.nombre as "ALBÚM",
            (select count(*) from reaccions r2 where  r2.foto_id = f.id and r2.tipo_reaccion = true) as total
            from fotos f
            inner join albums a
            on f.album_id = a.id
            order by total desc
            limit 3');

            DB::commit();

            return response()->json([
                'success'=>true,
                'content'=>$top
            ],200);

        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'content' => $ex->getMessage()
            ], 500);
        }
    }
}
