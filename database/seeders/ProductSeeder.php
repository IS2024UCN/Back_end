<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Product::create([
            'title' => 'El Señor de los Anillos La Comunidad del Anillo',
            'creator' => 'J.R.R. Tolkien',
            'ISBN' => '9780261103573',
            'publisher' => 'HarperCollins',
            'release_date' => '1954-07-29',
            'rental_price' => 1500,
            'initial_stock' => 50,
            'available_stock' => 50,
            'type' => 'libro',
            'is_enabled' => true,
        ]);
        
        Product::create([
            'title' => 'Harry Potter y la Piedra Filosofal',
            'creator' => 'J.K. Rowling',
            'ISBN' => '9780747532699',
            'publisher' => 'Bloomsbury',
            'release_date' => '1997-06-26',
            'rental_price' => 1200,
            'initial_stock' => 40,
            'available_stock' => 40,
            'type' => 'libro',
            'is_enabled' => true,
        ]);
        
        Product::create([
            'title' => 'Inception',
            'creator' => 'Christopher Nolan',
            'ISBN' => '1234567890123',
            'publisher' => 'Warner Bros.',
            'release_date' => '2010-07-16',
            'rental_price' => 3000,
            'initial_stock' => 30,
            'available_stock' => 30,
            'type' => 'pelicula',
            'is_enabled' => true,
        ]);
        
        Product::create([
            'title' => 'Interstellar',
            'creator' => 'Christopher Nolan',
            'ISBN' => '1234567890124',
            'publisher' => 'Paramount Pictures',
            'release_date' => '2014-11-07',
            'rental_price' => 3500,
            'initial_stock' => 20,
            'available_stock' => 20,
            'type' => 'pelicula',
            'is_enabled' => true,
        ]);

        // Nuevo libro
        Product::create([
            'title' => 'Cien Años de Soledad',
            'creator' => 'Gabriel García Márquez',
            'ISBN' => '9780307474728',
            'publisher' => 'Penguin Books',
            'release_date' => '1967-05-30',
            'rental_price' => 1800,
            'initial_stock' => 60,
            'available_stock' => 60,
            'type' => 'libro',
            'is_enabled' => true,
        ]);

        // Nueva película
        Product::create([
            'title' => 'The Matrix',
            'creator' => 'Lana Wachowski, Lilly Wachowski',
            'ISBN' => '1234567890125',
            'publisher' => 'Warner Bros.',
            'release_date' => '1999-03-31',
            'rental_price' => 2500,
            'initial_stock' => 25,
            'available_stock' => 25,
            'type' => 'pelicula',
            'is_enabled' => true,
        ]);
    }
}
