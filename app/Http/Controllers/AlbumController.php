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
    public function getAll()
    {
        try {
            $albums = Album::where('activo', true)->get();
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
            if ($image = $request->file('portada')) {
                $rutaGaurdada = 'storage/archivos/';
                $imgRegis = date('YmdHis') . "." . $image->getClientOriginalExtension();
                $image->move($rutaGaurdada, $imgRegis);
                $album->portada = "$imgRegis";
            } else {
                $album->portada = null;
            }
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
        $actAlbum = Album::find($id)->where('activo', true);
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
                    'activo' => $request->activo,
                ]);
            } else return response()->json(
                [
                    'success' => false,
                    'message' => 'Albúm no encontrado'
                ],
                404
            );
            DB::commit();
            return response()->json(
                [
                    'success' => true,
                    'album' => $actAlbum
                ],
                200
            );
        } catch (Exception $ex) {
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

            $fotosAlbum = Foto::join('albums', 'albums.id', '=', 'fotos.album_id')
                ->where('albums.id', '=', $id)
                ->get();

            if (count($fotosAlbum) > 0) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el albúm por que cuenta con fotos'
                ]);
            } else {

                $actAlbum->update([
                    'activo' => false,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Se elimino el albúm"
                ]);
            }
        } catch (Exception $ex) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        $response = array();

        if ($request->hasFile('portada')) {
            $foto = $request->portada;
            $path = $foto->store('storage/archivos/');
            $response = [
                'filename' => basename($path)
            ];
            return response()->json([
                'success' => true,
                'archivo' => $response
            ]);
        } else {
            return response()->json([
                'success'=>false,
                'message'=>'Imagen no procesada'
            ]);
        }
    }
}
