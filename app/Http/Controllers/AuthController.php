<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
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
                'message' => 'Sesion Cerrada',
                'data' => [],
                'error' => false
            ]);
    }
        catch (\Exception $e) {
            return response([
              'message' => 'Error al cerrar sesion',
               'data' => [],
              'error' => $e->getMessage()
         ], 500);
      }
    }

    public function UserLogged(){
        return response([
            'message' => 'Usuario Autenticado',
            'data' => [
                'user' => Auth::user()
            ],
            'error' => false
        ], 200);
    }

    /**public function register(Request $request)
    {
        try {
            // Validar los datos de entrada
            $request->validate([
                'rut' => ['required', 'string', 'unique:users', 'regex:/^[0-9]+[Kk0-9]$/', function($attribute, $value, $fail){
                }],
                'name' => ['required', 'string', 'min:3', 'regex:/^[a-zA-Z\s]+$/', function($attribute, $value, $fail){
                    if (preg_match('/[0-9]/', $value)) {
                        $fail('El nombre no puede contener números');
                    }
                }],
                'last_name' => ['required', 'string', 'min:3', 'regex:/^[a-zA-Z\s]+$/', function($attribute, $value, $fail){
                    if (preg_match('/[0-9]/', $value)) {
                        $fail('El apellido no puede contener números');
                    }
                }],
                'phone' => ['required', 'string', 'regex:/^[0-9]{9}$/', function($attribute, $value, $fail){
                }],
                
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users', function($attribute, $value, $fail){
                    if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('Formato incorrecto de correo');
                    }
                }],
            ], [
                'rut.unique' => 'Este RUT ya esta registrado en el sistema. Intente iniciar sesión.',
                'rut.regex' => 'El RUT ingresado no es válido.',
                'rut.required' => 'RUT requerido',
                'email.unique' => 'Este correo electrónico ya esta registrado en el sistema. Intente iniciar sesión.',
                'email.email' => 'Este correo electrónico no es válido.',
                'email.required' => 'Correo requerido',
                'phone.regex' => 'El teléfono móvil ingresado no es válido.',
                'phone.required' => 'Telefono requrido',
                'name.min' => 'Los nombres o apellidos deben tener más de 2 caracteres.',
                'name.required' => 'Nombre requerido',
                'name.regex' => 'El nombre no puede contener números.',
                'last_name.min' => 'Los nombres o apellidos deben tener más de 2 caracteres.',
                'last_name.required' => 'Apellido requerido',
                'last_name.regex' => 'El apellido no puede contener números.'
                
                
            ]); 

            // Validar el RUT chileno
            $rut = strtoupper($request->input('rut'));
            if (!$this->validateRut($rut)) {
                return response([
                    'message' => 'El RUT no es válido',
                    'data' => [],
                    'error' => true
                ], 422);
            }
            // Convertir el RUT a mayúsculas
            $rut = strtoupper($rut);
            // Agregar el prefijo +56 al teléfono
            $phone = '+56' . $request->input('phone');
            $name = strtolower($request->input('name'));
            $last_name = strtolower($request->input('last_name'));
            // Crear el usuario
            $user = User::create([
                'rut' => $rut,
                'name' => $name . ' ' . $last_name,
                'phone' => $phone,
                'email' => $request->input('email'),
                'password' => bcrypt($rut)
            ]);
            // Generar un token de acceso para el usuario
            $token = JWTAuth::fromUser($user);         

            return response([
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ],
                'error' => false
            ], 201);
        } catch (\Exception $e) {
            return response([
                'message' => 'Error al registrar el usuario',
                'data' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }*/

    private function validateRut($rut)
    {
        // Eliminar puntos y guiones
        $rut = str_replace(['.', '-'], '', strtoupper($rut));
        $number = substr($rut, 0, -1);
        $dv = substr($rut, -1);

        // Validar que el RUT tenga el formato correcto
        if (!preg_match('/^[0-9]+[K0-9]$/', $rut)) {
            return false;
        }

        // Calcular el dígito verificador
        $sum = 0;
        $factor = 2;
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $sum += $number[$i] * $factor;
            $factor = $factor == 7 ? 2 : $factor + 1;
        }
        $dv_calculated = 11 - ($sum % 11);
        if ($dv_calculated == 11) {
            $dv_calculated = '0';
        } elseif ($dv_calculated == 10) {
            $dv_calculated = 'K';
        } else {
            $dv_calculated = (string) $dv_calculated;
        }

        // Comparar el dígito verificador calculado con el proporcionado
        return $dv_calculated === $dv;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);
        if ($validator->fails()) {
            return response([
                'message' => 'Error al registrar el usuario',
                'data' => $validator->errors(),
                'error' => true
            ], 422);
        }
        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
        return response([
            'message' => 'Usuario registrado exitosamente',
            'data' => $user,
            'error' => false
        ], 201);

    }
}
