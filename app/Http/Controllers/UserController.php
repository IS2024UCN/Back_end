<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function register(Request $request)
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
                'password' => bcrypt($rut),
                'role_id' => 1
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
    }

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

    public function updatePassword(Request $request){
        try{
            $request->validate([
                'current_password' => 'required',
                'new_password' => [
                    'required',
                    'min:8',
                    'confirmed',
                    'regex:/[A-Z]/',
                    'regex:/[a-z]/',
                    'regex:/[0-9]/',
                    'regex:/[@$!%*#?&]/'
                ],
            ]);

            $user = Auth::user();

            if(!Hash::check($request->current_password, $user->password)){
                return response()->json([
                    'error' => 'Contraseña actual incorrecta'],
                    400);
            }
            if($request->current_password === $request->new_password){
                return response()->json([
                    'error' => 'La nueva contraseña no puede ser igual a la actual'],
                    400);
            }

            User::where('id', $user->id)->update(['password' => Hash::make($request->new_password)]);

            return response()->json([
                'message' => 'Contraseña actualizada correctamente'],
            200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar la contraseña',
                'details' => $e->getMessage()],
                500);
        }
    }

    public function registerWorker(Request $request)
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
                'password' => bcrypt($rut),
                'role_id' => 3
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
    }

    // Método para listar todos los usuarios con paginación
    public function getWorkers(Request $request)
    {
        // Determinar valores predeterminados en caso de no ingresar limit y page
        $limit = $request->query('limit', 10);
        $page = $request->query('page', 1);

        // Validaciones de limit y page numéricos
        if (!is_numeric($limit) || $limit <= 0) {
            $limit = 10;
        }

        if (!is_numeric($page) || $page <= 0) {
            $page = 1;
        }

        // Calcular el offset
        $offset = ($page - 1) * $limit;

        // Obtener los usuarios con paginación
        $users = User::offset($offset)->limit($limit)->get();
        $totalUsers = User::count();
        $totalPages = ceil($totalUsers / $limit);

        // Verificar si hay trabajadores
        if ($totalUsers == 0) {
            return response()->json([
                'message' => 'No hay trabajadores para mostrar',
                'data' => []
            ], 200);
        }

        // Construir la respuesta
        return response()->json([
            'total_users' => $totalUsers,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'limit' => $limit,
            'data' => $users
        ]);
    }

    // Método para habilitar o deshabilitar un trabajador
    public function toggleWorkerStatus(Request $request, $id)
    {
        // Verificar si el usuario autenticado es un administrador
        if ($request->user()->role_id != 2) {
            return response([
                'message' => 'No autorizado',
                'data' => [],
                'error' => true
            ], 403);
        }

        $users = User::find($id);

        if (!$users) {
            return response([
                'message' => 'Trabajador no encontrado',
                'data' => [],
                'error' => true
            ], 404);
        }

        // Cambiar el estado de is_enabled
        $users->active = !$users->active;
        $users->save();

        $status = $users->active ? 'habilitado' : 'deshabilitado';

        return response([
            'message' => "Trabajador $status exitosamente",
            'data' => $users
        ], 200);
    }

    // Método para actualizar la información de un trabajador
    public function updateWorker(Request $request, $id)
    {
        $users = User::find($id);

        if (!$users) {
            return response([
                'message' => 'Trabajador no encontrado',
                'data' => [],
                'error' => true
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255|regex:/^[a-zA-Z]+$/',
            'phone' => 'required|string|max:15',
            'email' => 'required|string|email|max:255|unique:users,email,' . $users->id,
        ], [
            'last_name.required' => 'Apellido requerido',
            'last_name.regex' => 'El apellido no puede contener números.'
        ]);

        $users->name = strtolower($request->input('name'));
        $users->last_name = strtolower($request->input('last_name'));
        $users->phone = '+56' . $request->input('phone');
        $users->email = $request->input('email');
        $users->save();

        return response([
            'message' => 'Trabajador actualizado exitosamente',
            'data' => $users
        ], 200);
    }
}
