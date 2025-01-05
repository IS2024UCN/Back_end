<?php

namespace App\Http\Controllers;

use App\Models\Rent;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Responce;
use App\Http\Resources\RentResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RentController extends Controller
{
    
    //
    public function getRents(Request $request){
        // Determinar valores predeterminados en caso de no ingresar limit y page
        $limit = $request->query('limit', 5);
        $page = $request->query('page', 1);

        // validaciones de limit y page numericos
        if (!is_numeric($limit) || $limit <= 0) {
            $limit = 5;
        }

        if (!is_numeric($page) || $page <= 0) {
            $page = 1;
        }

        // Calcular el offset
        $offset = ($page - 1) * $limit;

        // Obtener las rentas con estado 0 (pendiente) con paginación
        $rents = Rent::where('state', 0)->offset($offset)->limit($limit)->get();
        $totalRents = Rent::where('state', 0)->count();
        $totalPages = (int) ceil($totalRents / $limit);

        // Agregar el título del producto a cada renta y eliminar el objeto producto
        $rents->each(function ($rent) {
            $rent->product_title = $rent->product->title;
            unset($rent->product); // Eliminar el objeto producto
        });

        // Construir la respuesta
        return response()->json([
            'total_rents' => $totalRents,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'limit' => $limit,
            'data' => $rents,
            'has_more_pages' => $page < $totalPages
        ]);
    }

    public function updateRentStatus(Request $request)
    {
        // Validar los datos de la solicitud
        $validator = Validator::make($request->all(), [
            'rent_id' => 'required|exists:rents,id',
            'state' => 'required|in:1,2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        // Buscar la solicitud de arriendo por su ID
        $rent = Rent::find($request->rent_id);
        if (!$rent || $rent->state != 'pendiente') {
            return response()->json([
                'error' => 'Solicitud de arriendo no encontrada o no está pendiente'
            ], 404);
        }

        // Actualizar el estado de la solicitud
        $rent->state = $request->state;

        if ($request->state == 1) {
            // Confirmar la solicitud de arriendo
            $rent->startDate = now();
            $rent->endDate = now()->addDays($rent->daysRent);
        }

        $rent->save();

        return response()->json([
            'message' => 'Estado de la solicitud de arriendo actualizado correctamente',
            'data' => $rent
        ], 200);
    }

    /**
     * Método para registrar una solicitud de arriendo de un producto.
     *
     * El metodo recibe un JSON (en el postman) con los siguientes campos:
     * - product_id: ID del producto a arrendar.
     * - startDate: Fecha de inicio del arriendo ingresadas por el cliente.
     * - endDate: Fecha de fin del arriendo ingresadas por el cliente.
     * - confirm: (Opcional) Confirmar la solicitud de arriendo (si el cliente confirma el arriendo).
     * 
     * Este método realiza las siguientes acciones:
     * 1. Verifica si el usuario está autenticado.
     * 2. Valida los datos de la solicitud recibida en el $request.
     * 3. Busca el producto por su ID y verifica su disponibilidad.
     * 4. Calcula el costo total del arriendo basado en las fechas de inicio y fin.
     * 5. Si no se ha confirmado la solicitud, devuelve un mensaje con el costo total.
     * 6. Si se confirma la solicitud, crea un nuevo registro de arriendo en la base de datos
     *    en estado pendiente para la revision de un trabajador.
     * 7. Reduce el stock disponible del producto en la base de datos.
     */
    
    public function rentProduct(Request $request)
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Debe estar autenticado para realizar un arriendo'
            ], 401);
        }

        // Validar los datos de la solicitud
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'daysRent' => 'required|integer|min:1|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        // Buscar el producto por su ID
        $product = Product::find($request->product_id);
        if (!$product || !$product->is_enabled || $product->available_stock <= 0) {
            return response()->json([
                'error' => 'No se encuentran existencias, intente en otra ocasión'
            ], 404);
        }

        // Calcular el costo total del arriendo
        $days = $request->daysRent;
        $totalCost = $days * $product->rental_price;

        // Confirmar la solicitud de arriendo
        if (!$request->has('confirm') || $request->confirm !== true) {
            return response()->json([
                'message' => '¿Está seguro de proceder con la solicitud de arriendo? El costo total de arriendo es de $' . $totalCost,
                'totalCost' => $totalCost
            ], 200);
        }

        // Crear la solicitud de arriendo
        $rent = new Rent();
        $rent->state = '0';
        $rent->totalCost = $totalCost;
        $rent->requestDate = now();
        $rent->daysRent = $days;
        $rent->startDate = null;
        $rent->endDate = null;
        $rent->product_id = $product->id;
        $rent->user_id = Auth::id();
        $rent->save();

        // Reducir el stock disponible del producto
        $product->available_stock -= 1;
        $product->save();

        return response()->json([
            'message' => 'Solicitud de arriendo registrada correctamente',
            'data' => $rent
        ], 201);
    }
}
