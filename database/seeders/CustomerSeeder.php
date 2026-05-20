<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customerUsers = User::where('role', 'customer')->get();
        $cities = ['San Salvador', 'Santa Ana', 'San Miguel', 'Soyapango', 'Mejicanos'];
        $addresses = [
            'Colonia Escalón, 75 Av. Norte #120',
            'Colonia San Benito, Calle La Reforma #45',
            'Colonia Flor Blanca, 3a Calle Ote #67',
            'Residencial Santa Elena, Blvd. Los Héroes #234',
            'Barrio San Jacinto, Calle Principal #89',
        ];

        foreach ($customerUsers as $index => $user) {
            Customer::create([
                'user_id'                  => $user->id,
                'address'                  => $addresses[$index % count($addresses)],
                'city'                     => $cities[$index % count($cities)],
                'latitude'                 => 13.65 + (($index * 7) % 100) / 1000,
                'longitude'                => -89.30 + (($index * 13) % 200) / 1000,
                // 30 verified, 10 unverified (index 30+ => false for last ~10)
                'verified'                 => $index < 30,
                'preferred_payment_method' => ['card', 'cash', 'wallet', null][$index % 4],
                'loyalty_points'           => ($index * 37) % 501,
            ]);
        }
    }
}
