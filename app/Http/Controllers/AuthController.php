<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    public function login(Request $request)
    {
        $credenciales = $request->only('username', 'password');

        try {
            if (!$token = JWTAuth::attempt($credenciales)) {
                return response()->json(['error' => 'Credenciales Inconrrectas'], 400);
            }
        } catch (Exception $ex) {
            return response()->json(['error' => "No se pudo generar el token $ex"], 500);
        }

        return response()->json(['token'=>$token,'rol'=>Auth::user()->rol, 'name'=>Auth::user()->name], 200);
    }

    public function register(Request $request)
    {
        // valida campos
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:25|unique:users',
            'password' => 'required|string|min:6'
        ]);
        // confirma existencia de errores
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        // de no haber errores crea usuario
        $usuario = User::create([
            'rol' => 'Colaborador',
            'name' => $request->name,
            'username' => $request->username,
            'password' => bcrypt($request->password)
        ]);
        // genera token para el nuevo usuario
        // $token = JWTAuth::fromUser($usuario);
        // retorna objeto nuevo usuario creado y el token generado
        return response()->json(['rol' => $usuario->rol, 'name' => $usuario->name, 'success' => true]);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'success' => true,
            'message' => 'Cerraste tu sesion, hasta próxima!'
        ]);
    }

    public function resetpass(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user != null) {
                DB::beginTransaction();

                DB::table('users')->where('id',$user->id)->update([
                    'password' => bcrypt($request->password),
                ]);

                DB::commit();

                return response()->json(['message' => 'Contraseña actualizada satisfactoriamente!']);
            }
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['message' => $ex->getMessage()]);
        }
    }

    // public function me()
    // {
    //     return response()->json(auth()->user());
    // }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
