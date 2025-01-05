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
            'rut' => '101007928',
            'name' => 'Ernes Fuenzalida',
            'email' => 'cliente1@cliente1.com',
            'password' => '123',
            'phone' => '123456789',
            'role_id' => Role::where('name', 'Cliente')->first()->id,
            'active' => true
        ]);

        User::create([
            'rut' => '214808919',
            'name' => 'el admin',
            'email' => 'admin@admin.com',
            'password' => '123',
            'phone' => '123456789',
            'role_id' => Role::where('name', 'Administrador')->first()->id,
            'active' => true
        ]);

        User::create([
            'rut' => '199283715',
            'name' => 'el trabajador',
            'email' => 'worker@worker.com',
            'password' => '123',
            'phone' => '123456789',
            'role_id' => Role::where('name', 'Trabajador')->first()->id,
            'active' => true
        ]);

        User::create([
            'rut' => '199283510',
            'name' => 'el pepe',
            'email' => 'cliente@cliente.com',
            'password' => '123',
            'phone' => '123456789',
            'role_id' => Role::where('name', 'Cliente')->first()->id,
            'active' => true
        ]);
    }
}