<?php

namespace App\Http\Controllers;

use App\Models\Foto;
use App\Models\Reaccion;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FotoController extends Controller
{
    public function getFotosAll($id)
    {
        try {
            DB::beginTransaction();

            $fotos = Foto::join('albums', 'albums.id', '=', 'fotos.album_id')
                ->where('fotos.album_id', $id)
                ->where(function ($query) {
                    return $query
                        ->where('fotos.activo', true);
                })
                ->select('fotos.id', 'fotos.nombre_participante', 'fotos.titulo', 'fotos.lugar', 'fotos.resenia', 'fotos.motivacion', 'fotos.archivo', 'fotos.activo', 'fotos.publicado', 'fotos.album_id')
                ->get();

            if (count($fotos) <= 0) {

                return response()->json([
                    'success' => true,
                    'content' => 'No se encontraron fotos en este albúm',
                    'code' => 201
                ], 200);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'content' => $fotos,
            ], 200);
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'content' => $ex->getMessage()
            ], 500);
        }
    }

    public function createFoto(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'nombre_participante' => 'required|string|max:150',
                'titulo' => 'required|string|max:100',
                'lugar' => 'required|string|max:150',
                'resenia' => 'required|string|max:600',
                'motivacion' => 'required|string|max:200',
                'archivo' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $foto = new Foto();
            $foto->nombre_participante = $request->nombre_participante;
            $foto->titulo = $request->titulo;
            $foto->lugar = $request->lugar;
            $foto->resenia = $request->resenia;
            $foto->motivacion = $request->motivacion;
            $foto->archivo = $request->archivo;
            $foto->activo = true;
            $foto->publicado = $request->publicado;
            $foto->fecha_carga = Date(now());
            $foto->usuario_id = Auth::user()->id;
            $foto->album_id = $request->album_id;
            $foto->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'content' => "Se agrago la nueva foto $foto->titulo"
            ], 200);
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'content' => $ex->getMessage()
            ], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        $response = array();

        if ($request->hasFile('portada')) {
            $portada = $request->portada;
            $path = $portada->store('public/archivos/');

            $response = [
                'filename' => basename($path)
            ];
            return response()->json([
                'success' => true,
                'archivo' => $response
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Imagen no procesada'
            ]);
        }
    }

    public function getOnePhoto($id)
    {
        DB::beginTransaction();

        $foto = Foto::where('id', $id)->where('activo', true)->get();

        try {
            if (is_null($foto)) {
                return response()->json(['success' => false, 'content' => 'Foto no encontrada.'], 404);
            } else {
                DB::commit();
                return response()->json(['success' => true, 'content' => $foto], 200);
            }
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'content' => $ex->getMessage()
            ], 500);
        }
    }

    public function updatedPhoto(Request $request, $id)
    {
        DB::beginTransaction();
        $actFoto = Foto::where('id', $id)->where('activo', true);
        try {
            if (!empty($actFoto)) {
                $validator = Validator::make($request->all(), [
                    'nombre_participante' => 'required|string|max:150',
                    'titulo' => 'required|string|max:100',
                    'lugar' => 'required|string|max:150',
                    'resenia' => 'required|string|max:600',
                    'motivacion' => 'required|string|max:200',
                    'archivo' => 'required',
                ]);

                if ($validator->failed()) return response()->json($validator->errors()->toJson(), 401);

                $actFoto->update([
                    'nombre_participante' => $request->nombre_participante,
                    'titulo' => $request->titulo,
                    'lugar' => $request->lugar,
                    'resenia' => $request->resenia,
                    'motivacion' => $request->motivacion,
                    'archivo' => $request->archivo,
                    'publicado' => $request->publicado
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'content' => 'Foto Cargada exitosamente'
                ]);
            } else {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'content' => 'Foto no encontrada'
                ], 404);
            }
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'content' => $ex->getMessage()
            ], 500);
        }
    }

    public function deleteFoto($id)
    {
        DB::beginTransaction();
        $delFoto = Foto::find($id);
        try {
            $reactionFoto = DB::select('select * from fotos f
            inner join
            reaccions r on
            r.foto_id = f.id
            where f.id = ? and f.activo = 1', [$id]);

            if (count($reactionFoto) > 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'severity' => 'error',
                    'summary' => 'No Eliminado',
                    'content' => 'No se puede eliminar la foto por que ya cuenta con reacciones'
                ]);
            } else {
                $delFoto->update([
                    'activo' => false
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'severity' => 'success',
                    'summary' => 'Foto Eliminada',
                    'content' => 'Se elimino la foto'
                ]);
            }
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'severity' => 'error',
                'summary' => 'No Eliminado',
                'content' => $ex->getMessage()
            ]);
        }
    }

    public function publicateFoto($id)
    {
        DB::beginTransaction();
        $foto = Foto::find($id);
        try {
            if (!empty($foto)) {
                $foto->update([
                    'publicado' => true
                ]);
                DB::commit();
            }
            return response()->json([
                'success' => true,
                'severity' => 'success',
                'summary' => 'PUBLICADO',
                'content' => 'Foto Publicada'
            ], 200);
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'severity' => 'error',
                'summary' => 'NO OCULTO',
                'content' => $ex->getMessage()
            ], 500);
        }
    }

    public function dePublicateFoto($id)
    {
        DB::beginTransaction();

        try {
            $reactionFoto = DB::table('fotos')
                ->join('reaccions', 'fotos.id', '=', 'reaccions.foto_id')
                ->where('fotos.id', $id)
                ->where('fotos.activo', true)
                ->get();


            if (count($reactionFoto) > 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'severity' => 'error',
                    'summary' => 'NO OCULTO',
                    'content' => 'No se puede OCULTAR la foto por que ya cuenta con reacciones'
                ]);
            } else {
                $foto = Foto::find($id);
                $foto->update([
                    'publicado' => false
                ]);
                DB::commit();

                return response()->json([
                    'success' => true,
                    'severity' => 'success',
                    'summary' => 'OCULTO',
                    'content' => 'Se OCULTO la foto de la vista pública'
                ]);
            }
        } catch (Exception $ex) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'severity' => 'error',
                'summary' => 'OCULTO',
                'content' => $ex->getMessage()
            ], 500);
        }
    }

    public function getFotosAllStatePublic($id)
    {
        try {
            DB::beginTransaction();

            $fotos = Foto::join('albums', 'albums.id', '=', 'fotos.album_id')
                ->where('fotos.album_id', $id)
                ->where(function ($query) {
                    return $query
                        ->where('fotos.activo', true)
                        ->where('fotos.publicado', true);
                })
                ->select('fotos.id', 'fotos.nombre_participante', 'fotos.titulo', 'fotos.lugar', 'fotos.resenia', 'fotos.motivacion', 'fotos.archivo', 'fotos.activo', 'fotos.publicado', 'fotos.album_id')
                ->get();

            if (count($fotos) <= 0) {

                return response()->json([
                    'success' => true,
                    'content' => []
                ], 200);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'content' => $fotos
            ], 200);
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'content' => $ex->getMessage()
            ], 500);
        }
    }

    public function getOnePhotoPublic($id)
    {
        DB::beginTransaction();

        $foto = Foto::where('fotos.id','=',$id)
        ->where('fotos.activo','=',true)
        ->get();

        $ipReaccion = Reaccion::where('foto_id','=',$id)->get();

        try {
            if (is_null($foto)) {
                return response()->json(['success' => false, 'content' => 'Foto no encontrada.'], 404);
            } else {
                DB::commit();
                return response()->json(['success' => true, 'content' => $foto, 'ip' => $ipReaccion], 200);
            }
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'content' => $ex->getMessage()
            ], 500);
        }
    }
}
