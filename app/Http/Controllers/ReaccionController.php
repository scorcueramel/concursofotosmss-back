<?php

namespace App\Http\Controllers;

use App\Models\Reaccion;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReaccionController extends Controller
{
    public function reaccion($idFoto, $idReaccion, Request $request)
    {
        // 1 = LIKE
        // 2 = DISLIKE
        DB::beginTransaction();
        try {
            $ipid = DB::select('SELECT * FROM reaccions WHERE foto_id = ? AND terminal_ip = ?', [$idFoto, $request->ip()]);
            if ($idReaccion == 1 && count($ipid) <= 0) {
                $reaccion = Reaccion::create([
                    'foto_id' => $idFoto,
                    'tipo_reaccion' => true,
                    'fecha' => new DateTime(now()),
                    'terminal_ip' => $request->ip(),
                ]);
                DB::commit();
                return response()->json([
                    'success' => true,
                    'severity' => 'success',
                    'summary' => 'Le diste me gusta a esta foto',
                    'content' => $reaccion
                ], 200);
            } elseif ($idReaccion == 1 && count($ipid) >= 1 && !$ipid[0]->tipo_reaccion) {
                $reaccion = Reaccion::where('id', $ipid[0]->id)->update([
                    'tipo_reaccion' => true,
                ]);
                DB::commit();
                return response()->json([
                    'success' => true,
                    'severity' => 'success',
                    'summary' => 'Le diste me gusta a esta foto',
                    'content' => $reaccion
                ], 200);
            } elseif ($idReaccion == 2 && count($ipid) >= 1) {
                if(!$ipid[0]->tipo_reaccion){
                    DB::rollBack();
                    return response()->json([
                        'success' => true,
                        'conetnt' => 'Resultado Desconocido',
                    ], 200);
                }else{

                    $reaccion = Reaccion::where('id', $ipid[0]->id)->update([
                        'tipo_reaccion' => false,
                    ]);
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'severity' => 'success',
                        'summary' => 'Le diste no me gusta a esta foto',
                        'content' => $reaccion
                    ], 200);
                }
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => true,
                    'conetnt' => 'Resultado Desconocido',
                ], 200);
            }
        } catch (Exception $ex) {
            db::rollBack();
            return response()->json([
                'success' => false,
                'severity' => 'error',
                'summary' => 'Error, contacte con soporte',
                'content' => $ex->getMessage()
            ], 500);
        }
    }

    public function reaccionesTodas(Request $request){
        try {
            DB::beginTransaction();
            $reacciones = Reaccion::where('terminal_ip',$request->ip())->get();
            DB::commit();

            return response()->json($reacciones);

        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json($ex->getMessage());
        }
    }

    public function conteoReacciones($id){
        try {

            DB::beginTransaction();
            $reacciones = DB::select('select count(*) as conteo from reaccions r
            where r.foto_id = ? and r.tipo_reaccion = ?', [$id,true]);
            DB::commit();

            return response()->json($reacciones,200);

        } catch (Exception $ex) {
            DB::rollback();

            return response()->json([
                'success'=>false,
                'content'=>$ex->getMessage()
            ],500);
        }
    }

    public function getIpClient(Request $request){
        DB::beginTransaction();
        $ipObtn = DB::select('SELECT terminal_ip FROM reaccions WHERE terminal_ip = ?',[$request->ip()]);
        DB::commit();

        return response()->json($ipObtn);
    }
}
