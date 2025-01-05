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
    public function index()
    {
        $rents = Rent::with(['product', 'user'])
            ->where('state', 'pendiente')
            ->get();

        if($rents->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No hay solicitudes pendientes'
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'data' => RentResource::collection($rents)
        ]);
    }

    public function show(Rent $rent)
    {
        try{

            $rent->load(['product', 'user']);

            if(!$rent->product){
                return response()->json([
                    'status' => 'error',
                    'message' => 'La solicitud no tiene un producto asociado'
                ], 400);
            }

            if(!$rent->product->is_enabled) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El producto no está disponible actualmente'
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'data' => new RentResource($rent)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar la solicitud'
            ], 500);
        }
    }

    public function confirm(Rent $rent)
    {
        try{

            if($rent->state !== 'pendiente') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Solo se pueden confirmar solicitudes pendientes'
                ], 400);
            }

            if($rent->product->available_stock <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No hay stock disponible para este producto'
                ], 400);
            }

            DB::transaction(function() use ($rent) {
                $rent->product->decrement('available_stock');

                $rent->state = 'confirmado';
                $rent->save(); 
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Solicitud confirmada exitosamente',
                'data' => new RentResource($rent)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar la solicitud'
            ], 500);
        }
    }

    public function reject(Rent $rent)
    {
        try{
    
            if($rent->state !== 'pendiente'){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Solo se pueden rechazar solicitudes pendientes'
                ], 400);
            }

            $rent->state = 'rechazado';
            $rent->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Solicitud rechazada exitosamente',
                'data' => new RentResource($rent)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar la solicitud'
            ], 500);
        }
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
        $rent->state = 'pendiente';
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
