<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
    public function getAll()
    {
        try {
            $resp = Album::all()->where('activo',true);
            return response()->json(['resp' => $resp], 200);
        } catch (Exception $ex) {
            DB::Rollback();
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }

    public function createAlbum(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            if ($imgportada = $request->file('imagen')) {
                $rutaaguardar = 'archivos/';
                $nombreregistrar = date('YmdHis') . "." . $imgportada->getClientOriginalExtension();
                $imgportada->move($rutaaguardar, $nombreregistrar);
            } else {
                $imgportada = 'default.png';
            }

            $album = Album::create([
                'nombre' => Str::upper($request->nombre),
                'portada' => $imgportada,
                'publicado' => true,
                'activo' => true,
            ]);

            DB::commit();

            return response()->json(['album' => $album], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }

    public function showAlbum($id)
    {
        $album = Album::where('id',$id)
        ->where('activo',true)->get();

        try {
            if (is_null($album)) return response()->json(['album' => 'album no encontrado'], 404);
            else return response()->json(['album' => $album], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['messaget' => $ex->getMessage()], 500);
        }
    }

    public function updatedAlbum(Request $request)
    {
        $actAlbum = Album::find($request->id)->where('activo',true);
        try {
            if (!empty($actAlbum))
            {
                $validator = Validator::make($request->all(),[
                    'nombre' => 'required|string|max:100',
                ]);

                if($validator->fails()) return response()->json($validator->errors()->toJson(), 401);

                $actAlbum->update([
                    'nombre' => Str::upper($request->nombre),
                    'portada' => $request->portada,
                    'publicado' => $request->publicado,
                    'activo' => $request->activo,
                ]);
            }
            else return response()->json(['message' => 'Albúm no encontrado'], 404);

            return response()->json(['album' => $actAlbum], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }

    public function delete(Request $request)
    {
        $elimAlbum = Album::find($request->id);
        try {
            if(!empty($elimAlbum))
            {
                if($elimAlbum->activo == true)
                {
                    $elimAlbum->update([
                        'activo' => false
                    ]);
                    return response()->json(['message'=>'Albúm fue eliminado.'], 200);
                }else response()->json(['message' => 'El albúm se encuentra eliminado actualmente.'], 300);
            }else response()->json(['message' => 'No se encontro el albúm que quieres eliminar'], 404);
        } catch (Exception $ex) {
            return response()->json(['messaget'=>$ex->getMessage()], 500);
        }
    }
}
