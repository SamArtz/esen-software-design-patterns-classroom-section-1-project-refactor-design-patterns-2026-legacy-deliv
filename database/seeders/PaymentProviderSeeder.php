<?php

namespace Database\Seeders;

use App\Models\PaymentProvider;
use Illuminate\Database\Seeder;

class PaymentProviderSeeder extends Seeder
{
    public function run(): void
    {
        // api_key se guarda vía el mutator del modelo (base64 "falso")
        PaymentProvider::create([
            'name'         => 'Wompi',
            'api_endpoint' => 'https://sandbox.wompi.co/v1',
            'api_key'      => 'wompi_test_key_abc123',
            'enabled'      => true,
            'priority'     => 1,
        ]);

        PaymentProvider::create([
            'name'         => 'N1co',
            'api_endpoint' => 'https://sandbox.n1co.com/v2',
            'api_key'      => 'n1co_test_key_def456',
            'enabled'      => true,
            'priority'     => 2,
        ]);

        PaymentProvider::create([
            'name'         => 'BAC Transfer',
            'api_endpoint' => 'https://sandbox.bac.com/v1',
            'api_key'      => 'bac_test_key_ghi789',
            'enabled'      => true,
            'priority'     => 3,
        ]);

        PaymentProvider::create([
            'name'         => 'Visa Direct',
            'api_endpoint' => 'https://sandbox.visa.com/v1',
            'api_key'      => 'visa_test_key_jkl012',
            'enabled'      => false, // disabled
            'priority'     => 4,
        ]);
    }
}
