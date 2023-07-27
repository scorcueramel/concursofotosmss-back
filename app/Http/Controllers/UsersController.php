<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function getAllUser()
    {
        try {
            DB::beginTransaction();
            $listaUsuario = DB::select('SELECT u.id, u.rol, u.name, u.username FROM users AS u');

            DB::commit();
            return response()->json($listaUsuario, 200);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $ex->getMessage()], 500);
        }
    }

    public function getOneUser($id)
    {
        DB::beginTransaction();
        $getUser = DB::select('SELECT u.id, u.rol, u.name, u.username FROM users AS u WHERE id = ?', [$id]);
        try {
            if (count($getUser) <= 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No se encuentra el usuario que buscas.'
                ], 401);
            } else {
                return response()->json([
                    'success' => true,
                    'usuario' => $getUser,
                ], 200);
            }
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function updatedUser(Request $request, $id)
    {
        $usuarioEditar = DB::table('users')->where('id', $id)->get();

        try {
            if (count($usuarioEditar) > 0) {
                DB::beginTransaction();

                $inputs = $request->all();

                if(empty($inputs['name']) || empty($inputs['username'])){

                    return response()->json([
                        'success' => false,
                        'message' => 'Los campos NOMBRES Y APELLIDOS, USERNAME son obligatorios'
                    ],500);
                }

                if (!empty($inputs['password'])) {
                    $inputs['password'] = Hash::make($inputs['password']);
                }
                else {
                    $inputs = Arr::except($inputs, array('password'));
                }

                $actualizar = User::find($id);
                $actualizar->update($inputs);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Usuario actualizado satisfactoriamente'
                ],200);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario que intentas actualizar, no existe o fue eliminado'
                ],401);
            }
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function deleteUser($id)
    {
        $eliminar = DB::table('users')->where('id', $id)->get();

        if (count($eliminar) > 0) {
            User::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => "El usuario fue eliminado satisfactoriamente!"
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "El usuario no existe o ya fue eliminado anteriormente"
            ], 404);
        }
    }
}
