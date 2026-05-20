<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Models\Product;
use App\Models\ProductBundle;
use Illuminate\Database\Seeder;

class ProductBundleSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::with('products')->get();

        foreach ($vendors as $vendorIndex => $vendor) {
            $products = $vendor->products;
            if ($products->count() < 3) continue;

            // Bundle 1: Combo Bebida (solo productos directos)
            $comboBebida = ProductBundle::create([
                'vendor_id'           => $vendor->id,
                'name'                => 'Combo Bebida',
                'description'         => 'Incluye bebida y postre',
                'base_price'          => 8.00,
                'discount_percentage' => 0,
                'available'           => true,
            ]);
            $comboBebida->products()->attach([
                $products->get(8)->id ?? $products->last()->id => ['quantity' => 1],
                $products->get(9)->id ?? $products->first()->id => ['quantity' => 1],
            ]);

            // Bundle 2: Combo Individual (solo productos)
            $comboIndividual = ProductBundle::create([
                'vendor_id'           => $vendor->id,
                'name'                => 'Combo Individual',
                'description'         => 'Plato principal + bebida',
                'base_price'          => 12.00,
                'discount_percentage' => 5.0,
                'available'           => true,
            ]);
            $comboIndividual->products()->attach([
                $products->get(0)->id => ['quantity' => 1],
                $products->get(8)->id ?? $products->last()->id => ['quantity' => 1],
            ]);

            // Bundle 3: Combo Familiar (anidamiento real - expone el BUG de getTotalPrice())
            // Contiene: 1x Combo Individual + 1x Combo Bebida + producto adicional
            // El precio correcto debería considerar el descuento del Combo Individual (5%)
            // Pero getTotalPrice() usa base_price del hijo (12.00) en lugar de getTotalPrice() recursivo
            // Bug: precio calculado = 12.00 + 8.00 + product.price (ignora el 5% de descuento del hijo)
            // Precio correcto = 12.00*(1-0.05) + 8.00 + product.price = 11.40 + 8.00 + price
            $comboFamiliar = ProductBundle::create([
                'vendor_id'           => $vendor->id,
                'name'                => 'Combo Familiar',
                'description'         => 'Para toda la familia. Incluye combos individuales y bebidas.',
                'base_price'          => 35.00,
                'discount_percentage' => 10.0,
                'available'           => true,
            ]);
            // Agregar producto adicional directamente
            $comboFamiliar->products()->attach([
                $products->get(1)->id => ['quantity' => 2],
            ]);
            // Agregar bundles anidados (expone el BUG)
            $comboFamiliar->childBundles()->attach([
                $comboIndividual->id => ['quantity' => 1],
                $comboBebida->id     => ['quantity' => 2],
            ]);
        }
    }
}
