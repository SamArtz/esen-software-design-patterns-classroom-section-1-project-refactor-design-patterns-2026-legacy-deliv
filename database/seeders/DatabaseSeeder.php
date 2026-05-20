<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CustomerSeeder::class,
            VendorSeeder::class,
            CourierSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ProductBundleSeeder::class,
            DiscountSeeder::class,
            PaymentProviderSeeder::class,
            OrderSeeder::class,
            PaymentSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
