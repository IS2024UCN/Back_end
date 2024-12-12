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
        $limit = $request->query('limit', 10);
        $page = $request->query('page', 1);

        // validaciones de limit y page numericos
        if (!is_numeric($limit) || $limit <= 0) {
            $limit = 10;
        }

        if (!is_numeric($page) || $page <= 0) {
            $page = 1;
        }

        // Calcular el offset
        $offset = ($page - 1) * $limit;

        // Obtener los productos con paginación
        $products = Product::offset($offset)->limit($limit)->get();
        $totalProducts = Product::count();
        $totalPages = ceil($totalProducts / $limit);

        // Construir la respuesta
        return response()->json([
            'total_products' => $totalProducts,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'limit' => $limit,
            'data' => $products
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
    public function updateProductPrice(Request $request, $isbn){
        try{
            $product = Product::where('ISBN', $isbn)->first();
            if ($product == null){
                return response()->json([
                    'error' => 'Producto no encontrado'
                ], 404);
            }

            $validatedData = $request->validate([
                'new_price' => 'required|numeric|min:0'
            ], [
                'new_price.required' => 'El campo precio es obligatorio',
                'new_price.numeric' => 'El campo precio debe ser numérico',
                'new_price.min' => 'El campo precio debe ser mayor o igual a 0'
            ]);

            $product->rental_price = $validatedData['new_price'];
            $product->save();

            return response()->json([
                'message' => 'Precio actualizado correctamente',
                'data' => $product
            ]);

        } catch (QueryException $e){
            return response()->json([
                'error' => 'Error al actualizar el precio del producto',
                'details' => $e->getMessage()
            ], 500);
        } catch (Exception $e){
            return response()->json([
                'error' => 'Ocurrio un error inesperado',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
