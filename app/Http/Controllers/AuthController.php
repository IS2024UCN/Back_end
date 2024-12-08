<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;

class AuthController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login (Request $request)
    {   
        try{
        // Validacion de campos con mensaje personalizado para correo y formato incorrecto
        $request->validate([
            'email' => ['required','email', function($attribute, $value, $fail){
                if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $fail('Formato incorrecto de correo');
                }
            }],
            'password' => 'required' 
        ], [
            'email.required' => 'Correo es obligatorio',
            'email.email' => 'Formato incorrecto de correo',
            'password.required' => 'Contraseña es obligatoria'
        ]);
        // Intento de auteticacion
        $token = Auth::attempt($request->only('email','password'));

        // Verificacion de credenciales
        if(!$token){
            return response([
                'message'=>'Credenciales Incorrectas o correo no encontrado',
                'data'=>[],
                'error' => true
            ], 401);
        }

        // Usuario Autenticado
        $user = Auth::user();
        return response([
            'message' => 'Inicio de sesion exitoso',
            'data' => [
                'token' => $token,
                'user' => $user,
                'user_role' => $user->role_id
            ],
            'error' => false
        ]);}
        catch (\Exception $e) {
            // Manejo de excepciones generales
            return response([
                'message' => 'Error al iniciar sesion',
                'data' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout (){
        try{
            Auth::logout();
            return response([
                // Mensaje de cierre de sesion
                'message' => 'Sesion Cerrada',
                'data' => [],
                'error' => false
            ]);
    }
        catch (\Exception $e) {
            return response([
            // Mensaje de error al cerrar sesion
              'message' => 'Error al cerrar sesion',
               'data' => [],
              'error' => $e->getMessage()
         ], 500);
      }
    }

    public function UserLogged(){
        return response([
            // Mensaje de usuario autenticado
            'message' => 'Usuario Autenticado',
            'data' => [
                'user' => Auth::user()
            ],
            'error' => false
        ], 200);
    }

    
    

}
