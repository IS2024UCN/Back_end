<?php

namespace App\Http\Controllers;
use App\Models\Product;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    //
    public function getProducts(Request $request)
    {
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
}
