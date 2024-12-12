<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'rut' => '12345678-9',
            'name' => 'Ernes Fuenzalida',
            'email' => 'vicho@vicho.com',
            'password' => '123',
            'phone' => '123456789',
            'role_id' => Role::where('name', 'Cliente')->first()->id,
            'active' => true
        ]);

    }
}