<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::with('categories')->get();

        $productTemplates = [
            // Pizza vendors
            [
                'Pizza Margarita', 'Pizza Pepperoni', 'Pizza Hawaiana', 'Pizza Quattro Formaggi',
                'Pizza Vegetariana', 'Pizza BBQ', 'Pizza Prosciutto', 'Calzone de Carne',
                'Bruschetta', 'Tiramisú',
            ],
            // Burger vendors
            [
                'Burger Clásica', 'Burger Doble', 'Burger BBQ', 'Burger Mushroom',
                'Burger Crispy', 'Burger Vegana', 'Wrap de Pollo', 'Papas Fritas',
                'Aros de Cebolla', 'Refresco',
            ],
        ];

        $globalIndex = 0;
        foreach ($vendors as $vendorIndex => $vendor) {
            $category = $vendor->categories->first();
            $templates = $productTemplates[$vendorIndex % count($productTemplates)];

            for ($i = 0; $i < 10; $i++) {
                $productName = $templates[$i] ?? "Producto {$i} Vendor {$vendor->id}";
                $price = round(2.50 + (($globalIndex * 1.37 + $i * 2.5) % 22.50), 2);

                // 5 productos con stock=0 y available=true (bug intencional de desincronización)
                $isBugProduct = $globalIndex < 5;
                $stock = $isBugProduct ? 0 : (10 + ($globalIndex * 7) % 91);
                $available = true; // bug: available=true aunque stock=0

                Product::create([
                    'vendor_id'                => $vendor->id,
                    'category_id'              => $category?->id,
                    'name'                     => $productName,
                    'description'              => "Delicioso {$productName} preparado con ingredientes frescos.",
                    'price'                    => $price, // float intencional
                    'available'                => $available,
                    'stock'                    => $stock,
                    'preparation_time_minutes' => 10 + ($i * 3) % 25,
                ]);
                $globalIndex++;
            }
        }
    }
}
