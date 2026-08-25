<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Administrador
        User::create([
            'name'     => 'Admin Onigiri',
            'email'    => 'admin@onigiri-pos.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'phone'    => '+51 999 000 001',
        ]);

        // Cajero del local
        User::create([
            'name'     => 'Cajero Principal',
            'email'    => 'cajero@onigiri-pos.com',
            'password' => Hash::make('password'),
            'role'     => 'cashier',
            'phone'    => '+51 999 000 002',
        ]);

        // Cliente de prueba
        User::create([
            'name'     => 'Cliente Demo',
            'email'    => 'cliente@example.com',
            'password' => Hash::make('password'),
            'role'     => 'customer',
            'phone'    => '+51 999 000 003',
        ]);
    }
}
