<?php

namespace App\Http\Controllers;
use App\Models\Product;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\QueryException;

class ProductController extends Controller
{
    //
    public function getProducts(Request $request){
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

        // Obtener los productos con paginación
        $products = Product::offset($offset)->limit($limit)->get();
        $totalProducts = Product::count();
        $totalPages = (int) ceil($totalProducts / $limit);

        // Construir la respuesta
        return response()->json([
            'total_products' => $totalProducts,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'limit' => $limit,
            'data' => $products,
            'has_more_pages' => $page < $totalPages
        ]);
    }
    
    public function registerProduct(Request $request){
        try{
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'creator' => 'required|string|max:255',
                'ISBN' => 'nullable|string|unique:products|max:255|min:10',
                'publisher' => 'nullable|string|max:255|min:10',
                'release_date' => 'nullable|date',
                'rental_price' => 'required|numeric|min:0',
                'initial_stock' => 'required|integer|min:1',
                'type' => 'required|string|in:libro,pelicula',
            ]);
            $product = new Product();
            $product->title = $validatedData['title'];
            $product->creator = $validatedData['creator'];
            $product->ISBN = $validatedData['ISBN'];
            $product->publisher = $validatedData['publisher'] ?? null;
            $product->release_date = $validatedData['release_date'] ?? null;
            $product->rental_price = $validatedData['rental_price'];
            $product->initial_stock = $validatedData['initial_stock'];
            $product->available_stock = $validatedData['initial_stock'];
            $product->type = $validatedData['type'];
            $product->is_enabled = true;

            $product->save();

            return response()->json([
                'message' => 'Producto registrado correctamente',
                'data' => $product,
            ], 201);

        } catch (QueryException $e){
            return response()->json([
                'error' => 'Error al registrar el producto',
                'details' => $e->getMessage()],
                500);
        } catch (Exception $e){
            return response()->json([
                'error' => 'Ocurrio un error inesperado',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // metodo para editar el precio de un producto obteniendo su isbn
    public function updateProductPrice(Request $request){
    try {
        // Validar los datos de la solicitud, incluyendo el ISBN
        $validatedData = $request->validate([
            'ISBN' => 'required|string|max:255',
            'new_price' => 'required|numeric|min:0'
        ], [
            'ISBN.required' => 'El campo ISBN es obligatorio',
            'new_price.required' => 'El campo precio es obligatorio',
            'new_price.numeric' => 'El campo precio debe ser numérico',
            'new_price.min' => 'El campo precio debe ser mayor o igual a 0'
        ]);

        // Buscar el producto por su ISBN
        $product = Product::where('ISBN', $validatedData['ISBN'])->first();
        if ($product == null) {
            return response()->json([
                'error' => 'Producto no encontrado'
            ], 404);
        }

        $product->rental_price = $validatedData['new_price'];
        $product->save();

        return response()->json([
            'message' => 'Precio actualizado correctamente',
            'data' => $product
        ]);

        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Error al actualizar el precio del producto',
                'details' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Ocurrió un error inesperado',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function replenishStock(Request $request) {
        try {
            // Validar los datos de la solicitud, incluyendo el ISBN y la cantidad a agregar
            $validatedData = $request->validate([
                'ISBN' => 'required|string|max:255',
                'quantity' => 'required|integer|min:1'
            ], [
                'ISBN.required' => 'El campo ISBN es obligatorio',
                'quantity.required' => 'El campo cantidad es obligatorio',
                'quantity.integer' => 'El campo cantidad debe ser un número entero',
                'quantity.min' => 'El campo cantidad debe ser mayor o igual a 1'
            ]);

            // Buscar el producto por su ISBN
            $product = Product::where('ISBN', $validatedData['ISBN'])->first();
            if ($product == null) {
                return response()->json([
                    'error' => 'Producto no encontrado'
                ], 404);
            }

            // Incrementar el stock disponible
            $product->available_stock += $validatedData['quantity'];
            $product->save();

            return response()->json([
                'message' => 'Stock actualizado correctamente',
                'data' => $product
            ]);

        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Error al actualizar el stock del producto',
                'details' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Ocurrió un error inesperado',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
