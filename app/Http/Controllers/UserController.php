<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    private function validateRut($rut){
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
                    'max:64',
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
            log::error('Error al actualizar la contraseña: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno del servidor',
                'details' => env('APP_DEBUG') ? $e->getMessage() : 'Contacte con administracion'],
                500);
        } catch (ValidationException $e){
            return response()->json([
                'error' => 'Error al actualizar la contraseña',
                'details' => $e->errors()],
                422);
        }
    }

    public function registerWorker(Request $request){
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
    public function getWorkers(Request $request){
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
        $users = User::whereIn('role_id', [2, 3])->offset($offset)->limit($limit)->get();
        $totalUsers = User::whereIn('role_id', [2, 3])->count();
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
    public function toggleWorkerStatus(Request $request){
        // Validar los datos de la solicitud, incluyendo el ID
        $validatedData = $request->validate([
            'id' => 'required|integer|exists:users,id',
        ]);

        // Buscar el usuario por su ID
        $user = User::find($validatedData['id']);

        if (!$user) {
            return response([
                'message' => 'Trabajador no encontrado',
                'data' => [],
                'error' => true
            ], 404);
        }

        // Alternar el estado del trabajador
        $user->active = !$user->active;
        $user->save();

        $status = $user->active ? 'habilitado' : 'deshabilitado';

        return response([
            'message' => "Estado del trabajador actualizado exitosamente. El trabajador ha sido $status.",
            'data' => $user
        ], 200); 
    }
    
    // Método para actualizar la información de un trabajador
    public function updateWorker(Request $request){
        // Verificar si el usuario autenticado es un administrador
        if ($request->user()->role_id != 2) {
            return response([
                'message' => 'No autorizado',
                'data' => [],
                'error' => true
            ], 403);
        }
        
        // de momento el metodo funciona solo cuando cambias un email,
        // la parte del new_email se cae cuando no editas el correo (incluso si no lo quieres editar)
        // Validar los datos de la solicitud, incluyendo el RUT y el estado activo
        $validatedData = $request->validate([
            'rut' => [
            'required',
            'string',
            'max:255',
            'regex:/^[0-9]+[Kk0-9]$/',
            function($attribute, $value, $fail) {
                if (!$this->validateRut($value)) {
                $fail('El RUT no es válido.');
                }
            }
            ],
            'new_name' => [
            'required',
            'string',
            'min:3',
            'max:255',
            'regex:/^[a-zA-Z\s]+$/',
            function($attribute, $value, $fail) {
                if (preg_match('/[0-9]/', $value)) {
                $fail('El nombre no puede contener números.');
                }
            }
            ],
            'new_phone' => [
            'required',
            'string',
            'regex:/^[0-9]{9}$/',
            function($attribute, $value, $fail) {
                if (!preg_match('/^[0-9]{9}$/', $value)) {
                $fail('El teléfono móvil ingresado no es válido.');
                }
            }
            ],
            'new_email' => [
            'required',
            'string',
            'email',
            'max:255',
            'unique:users,email,' . $request->user()->id,
            function($attribute, $value, $fail) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $fail('Formato incorrecto de correo.');
                }
            }
            ],
            'string_active' => [
            'required',
            'string',
            'in:activo,inactivo',
            function($attribute, $value, $fail) {
                if (!in_array($value, ['activo', 'inactivo'])) {
                $fail('El estado debe ser "activo" o "inactivo".');
                }
            }
            ],
        ], [
            'rut.required' => 'RUT requerido.',
            'rut.regex' => 'El RUT ingresado no es válido.',
            'new_name.required' => 'Nombre requerido.',
            'new_name.min' => 'El nombre debe tener más de 2 caracteres.',
            'new_name.regex' => 'El nombre no puede contener números.',
            'new_phone.required' => 'Teléfono requerido.',
            'new_phone.regex' => 'El teléfono móvil ingresado no es válido.',
            'new_email.required' => 'Correo requerido.',
            'new_email.email' => 'Este correo electrónico no es válido.',
            'new_email.unique' => 'Este correo electrónico ya está registrado en el sistema.',
            'string_active.required' => 'Estado requerido.',
            'string_active.in' => 'El estado debe ser "activo" o "inactivo".',
        ]);
        
        // Convertir el estado activo a booleano
        $validatedData['new_active'] = $validatedData['string_active'] === 'activo' ? 1 : 0;
        // Buscar el usuario por su RUT
        $users = User::where('rut', $validatedData['rut'])->first();

        if (!$users) {
            return response([
                'message' => 'Trabajador no encontrado. El rut no existe en el sistema.',
                'data' => [],
                'error' => true
            ], 404);
        }
        
        // no esta funcionando
        // Verificar si el nuevo correo electrónico ya está registrado en otro usuario
        if ($validatedData['new_email'] && User::where('email', $validatedData['new_email'])->where('rut', '!=', $validatedData['rut'])->exists()) {
            return response([
                'message' => 'El correo electrónico ya está registrado en otro usuario.',
                'data' => [],
                'error' => true
            ], 422);
        }
        
        $users->name = strtolower($validatedData['new_name']);
        $users->phone = '+56' . $validatedData['new_phone'];
        // tampoco esta funcionando esta validacion
        if ($validatedData['new_email'] != $users->email) {
            $users->email = $validatedData['new_email'];
        }
        $users->active = $validatedData['new_active'];
        $users->save();
    
        return response([
            'message' => 'Trabajador actualizado exitosamente',
            'data' => $users
        ], 200);
    }
}
