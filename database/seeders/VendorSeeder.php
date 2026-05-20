<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendorUsers = User::where('role', 'vendor')->get();

        $vendorData = [
            ['business_name' => 'Pizza Napoli', 'description' => 'Auténtica pizza italiana al horno de leña'],
            ['business_name' => 'Burger Palace', 'description' => 'Las mejores hamburguesas artesanales de El Salvador'],
            ['business_name' => 'Sushi Zen', 'description' => 'Sushi fresco preparado por chefs japoneses'],
            ['business_name' => 'La Pupusa Feliz', 'description' => 'Comida típica salvadoreña, pupusas y más'],
            ['business_name' => 'Pizza Roma', 'description' => 'Pizza al estilo romano con ingredientes importados'],
            ['business_name' => 'Burger House', 'description' => 'Burgers gourmet y papas fritas caseras'],
            ['business_name' => 'Sushi Tokyo', 'description' => 'Rolls creativos y sashimi premium'],
            ['business_name' => 'Comida Típica Don Carlos', 'description' => 'Sabores auténticos de El Salvador'],
            ['business_name' => 'Dulce Tentación', 'description' => 'Postres y pasteles artesanales'],
            ['business_name' => 'Refresquería El Volcán', 'description' => 'Bebidas naturales y jugos frescos'],
        ];

        $openingHours = [
            'monday'    => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
            'tuesday'   => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
            'wednesday' => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
            'thursday'  => ['open' => '10:00', 'close' => '23:00', 'closed' => false],
            'friday'    => ['open' => '10:00', 'close' => '23:00', 'closed' => false],
            'saturday'  => ['open' => '11:00', 'close' => '23:00', 'closed' => false],
            'sunday'    => ['open' => '11:00', 'close' => '21:00', 'closed' => false],
        ];

        $cities = ['San Salvador', 'Santa Tecla', 'Antiguo Cuscatlán', 'San Salvador', 'Santa Ana',
                   'San Salvador', 'San Salvador', 'Soyapango', 'San Salvador', 'San Salvador'];
        $addresses = [
            'Colonia Escalón, 75 Av. Norte #120',
            'Santa Tecla, Calle El Progreso #45',
            'Antiguo Cuscatlán, Blvd. Orden de Malta',
            'Centro Histórico, Calle Arce #78',
            'Santa Ana, Av. Independencia #234',
            'Colonia San Benito, Calle La Reforma #89',
            'Colonia Flor Blanca, Paseo Escalón #156',
            'Soyapango, Blvd. del Ejército #67',
            'Colonia Médica, Av. Dr. Emilio Álvarez #90',
            'Centro Comercial Galerías #12',
        ];

        foreach ($vendorUsers as $index => $user) {
            Vendor::create([
                'user_id'         => $user->id,
                'business_name'   => $vendorData[$index]['business_name'],
                'description'     => $vendorData[$index]['description'],
                'address'         => $addresses[$index],
                'city'            => $cities[$index],
                'latitude'        => 13.65 + (($index * 11) % 100) / 1000,
                'longitude'       => -89.30 + (($index * 17) % 200) / 1000,
                'opening_hours'   => $openingHours,
                'commission_rate' => 15.00 + ($index % 3) * 2.5,
                // Vendor #9 (index 9) suspended para probar el check
                'status'          => $index === 9 ? 'suspended' : 'active',
            ]);
        }
    }
}
