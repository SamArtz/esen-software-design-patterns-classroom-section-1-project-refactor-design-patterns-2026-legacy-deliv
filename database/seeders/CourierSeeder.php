<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Courier;
use Illuminate\Database\Seeder;

class CourierSeeder extends Seeder
{
    public function run(): void
    {
        $courierUsers = User::where('role', 'courier')->get();

        // 5 motorcycle, 3 bicycle, 2 car
        $vehicles = ['motorcycle', 'motorcycle', 'motorcycle', 'motorcycle', 'motorcycle',
                     'bicycle', 'bicycle', 'bicycle', 'car', 'car'];
        $plates   = ['P-123456', 'P-234567', 'P-345678', null, null, null, null, null, 'N-456789', 'N-567890'];

        foreach ($courierUsers as $index => $user) {
            Courier::create([
                'user_id'           => $user->id,
                'vehicle_type'      => $vehicles[$index],
                'license_plate'     => $plates[$index],
                'current_latitude'  => 13.69 + (($index * 9) % 60) / 1000,
                'current_longitude' => -89.22 + (($index * 13) % 100) / 1000,
                // 7 available, 3 not
                'available'         => $index < 7,
                'rating'            => $index < 7 ? round(3.5 + ($index * 0.2), 2) : null,
            ]);
        }
    }
}
