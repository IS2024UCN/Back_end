<?php

namespace App\Http\Controllers;

use App\Models\Rent;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Responce;
use App\Http\Resources\RentResource;
use Illuminate\Support\Facades\DB;

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
}
