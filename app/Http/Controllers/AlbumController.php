<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Foto;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
    public function getAlbumsActives()
    {
        try {
            DB::beginTransaction();
            $albums = Album::where('activo', true)
                ->where('publicado', '=', true)
                ->get();
            // $albums = Album::where('activo', true)->paginate(5);
            return response()->json(
                [
                    'success' => true,
                    'albums' => $albums
                ],
                200
            );
            DB::commit();
        } catch (Exception $ex) {
            DB::Rollback();
            return response()->json(
                [
                    'success' => false,
                    'message' => $ex->getMessage()
                ],
                500
            );
        }
    }

    public function getAlbumsIanctives()
    {
        try {
            DB::beginTransaction();
            $albums = Album::where('activo', true)
                ->where('publicado', '=', false)
                ->get();
            // $albums = Album::where('activo', true)->paginate(5);
            return response()->json(
                [
                    'success' => true,
                    'albums' => $albums
                ],
                200
            );
            DB::commit();
        } catch (Exception $ex) {
            DB::Rollback();
            return response()->json(
                [
                    'success' => false,
                    'message' => $ex->getMessage()
                ],
                500
            );
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

            $album = new Album();
            $album->nombre = Str::upper($request->nombre);
            $album->portada = $request->portada;
            $album->publicado = $request->publicado;
            $album->activo = true;
            $album->save();

            DB::commit();

            return response()->json(
                [
                    'success' => true,
                    'album' => $album
                ],
                200
            );
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function getOneAlbum($id)
    {
        DB::beginTransaction();
        $album = Album::where('id', $id)
            ->where('activo', true)->get();

        try {
            if (is_null($album)) return response()->json(
                [
                    'success' => false,
                    'message' => 'album no encontrado'
                ],
                404
            );
            else return response()->json(['album' => $album], 200);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['messaget' => $ex->getMessage()], 500);
        }
    }

    public function updatedAlbum(Request $request, $id)
    {
        DB::beginTransaction();
        $actAlbum = Album::where('id', $id)->where('activo', true);
        try {
            if (!empty($actAlbum)) {
                $validator = Validator::make($request->all(), [
                    'nombre' => 'required|string|max:100',
                ]);

                if ($validator->fails()) return response()->json($validator->errors()->toJson(), 401);

                $actAlbum->update([
                    'nombre' => Str::upper($request->nombre),
                    'portada' => $request->portada,
                    'publicado' => $request->publicado,
                ]);

                DB::commit();

                return response()->json(
                    [
                        'success' => true,
                        'message' => 'Albúm actualizado exitosamente'
                    ],
                    200
                );
            } else {
                DB::rollBack();

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Albúm no encontrado'
                    ],
                    404
                );
            }
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function deleteAlbum($id)
    {
        DB::beginTransaction();

        $actAlbum = Album::find($id);

        try {

            $fotosAlbum = Album::join('fotos', 'albums.id', '=', 'fotos.album_id')
                ->where('albums.id', '=', $id)
                ->select('albums.id')
                ->get();

            if (count($fotosAlbum) > 0) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'severity' => 'error',
                    'summary' => 'No Eliminado',
                    'message' => 'No se puede eliminar el albúm por que cuenta con fotos'
                ], 200);
            } else {

                $actAlbum->update([
                    'activo' => false,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'severity' => 'success',
                    'summary' =>  'Eliminado',
                    'message' => "Se elimino el albúm"
                ], 200);
            }
        } catch (Exception $ex) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'severity' => 'error',
                'summary' =>  'Error al eliminar',
                'message' => $ex->getMessage()
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

    public function publicateAlbum($id)
    {
        DB::beginTransaction();
        $album = Album::find($id);

        try {
            if (!empty($album)) {
                $album->update([
                    'publicado' => true
                ]);
                DB::commit();
            }
            return response()->json([
                'success' => true,
                'severity' => 'success',
                'summary' => 'PUBLICADO',
                'message' => 'Albúm publicado'
            ], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'severity' => 'error',
                'summary' => 'Llama a soporte',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function DePublicateAlbum($id)
    {
        DB::beginTransaction();
        $album = Album::find($id);

        try {
            if (!empty($album)) {
                $fotosAlbum = Foto::join('albums', 'albums.id', '=', 'fotos.album_id')
                    ->where('albums.id', '=', $id)
                    ->get();

                if (count($fotosAlbum) > 0) {

                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'severity' => 'error',
                        'summary' => 'No OCULTO',
                        'message' => 'No se puede OCULTAR el albúm por que cuenta con fotos'
                    ],200);
                } else {

                    $album->update([
                        'publicado' => false
                    ]);
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'severity' => 'success',
                        'summary' => 'OCULTO',
                        'message' => "Se OCULTO el albúm"
                    ],200);
                }
            }
            return response()->json(['success' => true, 'message' => 'Albúm oculto (despublicado)'], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }
}
