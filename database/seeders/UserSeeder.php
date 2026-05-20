<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'     => 'Admin Sistema',
            'email'    => 'admin@legacy.dev',
            'password' => 'Password1',
            'phone'    => '+50322100000',
            'role'     => 'admin',
        ]);

        // Vendors (10)
        $vendorNames = [
            'Pizza Napoli', 'Burger Palace', 'Sushi Zen', 'La Pupusa Feliz',
            'Pizza Roma', 'Burger House', 'Sushi Tokyo', 'Comida Típica Don Carlos',
            'Dulce Tentación', 'Refresquería El Volcán',
        ];
        foreach ($vendorNames as $i => $name) {
            User::create([
                'name'     => $name . ' Owner',
                'email'    => 'vendor' . ($i + 1) . '@legacy.dev',
                'password' => 'Password1',
                'phone'    => '+5032211' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'role'     => 'vendor',
            ]);
        }

        // Couriers (10)
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name'     => 'Courier ' . $i,
                'email'    => 'courier' . $i . '@legacy.dev',
                'password' => 'Password1',
                'phone'    => '+5032221' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'role'     => 'courier',
            ]);
        }

        // Customers (59)
        $firstNames = ['Carlos', 'María', 'José', 'Ana', 'Luis', 'Rosa', 'Juan', 'Laura', 'Pedro', 'Sofia'];
        $lastNames  = ['García', 'López', 'Martínez', 'González', 'Hernández', 'Pérez', 'Rodríguez', 'Sánchez'];
        for ($i = 1; $i <= 59; $i++) {
            $first = $firstNames[($i - 1) % count($firstNames)];
            $last  = $lastNames[($i - 1) % count($lastNames)];
            User::create([
                'name'     => "$first $last $i",
                'email'    => 'customer' . $i . '@legacy.dev',
                'password' => 'Password1',
                'phone'    => '+5032231' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'role'     => 'customer',
            ]);
        }
    }
}
