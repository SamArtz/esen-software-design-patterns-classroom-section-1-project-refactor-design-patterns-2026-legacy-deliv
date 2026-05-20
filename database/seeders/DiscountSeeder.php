<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        $validFrom = Carbon::now()->subDays(30);
        $validTo   = Carbon::now()->addDays(30);

        // 5 percentage
        foreach ([10, 15, 20, 25, 30] as $i => $value) {
            Discount::create([
                'code'               => 'DESCUENTO' . $value,
                'type'               => 'percentage',
                'value'              => $value,
                'min_order_amount'   => $i > 2 ? 15.00 : null,
                'max_discount_amount'=> $i > 1 ? 10.00 : null,
                'valid_from'         => $validFrom,
                'valid_to'           => $validTo,
                'max_uses'           => 100,
                'current_uses'       => $i * 5,
            ]);
        }

        // 3 fixed_amount
        foreach ([2.00, 5.00, 10.00] as $i => $value) {
            Discount::create([
                'code'             => 'FIJO' . ($value * 100),
                'type'             => 'fixed_amount',
                'value'            => $value,
                'min_order_amount' => $value * 2,
                'valid_from'       => $validFrom,
                'valid_to'         => $validTo,
                'max_uses'         => 50,
                'current_uses'     => $i * 3,
            ]);
        }

        // 3 bogo
        foreach ([1, 2, 3] as $i) {
            Discount::create([
                'code'        => 'BOGO' . $i,
                'type'        => 'bogo',
                'value'       => 0,
                'valid_from'  => $validFrom,
                'valid_to'    => $validTo,
                'max_uses'    => 30,
                'current_uses'=> $i,
            ]);
        }

        // 2 first_purchase
        foreach ([1, 2] as $i) {
            Discount::create([
                'code'               => 'PRIMERACOMPRA' . $i,
                'type'               => 'first_purchase',
                'value'              => 20,
                'max_discount_amount'=> 8.00,
                'valid_from'         => $validFrom,
                'valid_to'           => $validTo,
                'max_uses'           => 200,
                'current_uses'       => $i * 2,
            ]);
        }

        // 2 free_delivery
        foreach ([1, 2] as $i) {
            Discount::create([
                'code'         => 'ENVIOGRATIS' . $i,
                'type'         => 'free_delivery',
                'value'        => 0,
                'valid_from'   => $validFrom,
                'valid_to'     => $validTo,
                'max_uses'     => 100,
                'current_uses' => $i,
            ]);
        }
    }
}
