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
            'title' => 'Harry Potter',
            'creator' => 'J.K. Rowling',
            'ISBN' => '1234567890',
            'publisher' => 'Editorial 1',
            'release_date' => '2023-01-01',
            'rental_price' => 999,
            'initial_stock' => 100,
            'available_stock' => 100,
            'type' => 'Libro',
            'is_enabled' => true,
            
        ]);

        Product::create([
            'title' => 'Harry Potter 2',
            'creator' => 'J.K. Rowling',
            'ISBN' => '1234567891',
            'publisher' => 'Editorial 2',
            'release_date' => '2023-02-01',
            'rental_price' => 1999,
            'initial_stock' => 50,
            'available_stock' => 50,
            'type' => 'Pelicula',
            'is_enabled' => true,
            
        ]);
    }
}
