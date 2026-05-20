<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::all();

        $categoryNames = [
            ['Pizzas Clásicas', 'Pizzas Especiales', 'Bebidas'],
            ['Burgers Clásicas', 'Burgers Especiales', 'Papas y Acompañamientos'],
            ['Rolls', 'Sashimi', 'Bebidas Japonesas'],
            ['Pupusas', 'Bebidas', 'Platos Típicos'],
            ['Pizzas Tradicionales', 'Pizzas Gourmet', 'Entradas'],
            ['Burgers Premium', 'Wraps', 'Bebidas'],
            ['Rolls Creativos', 'Nigiri', 'Sopas'],
            ['Pupusas', 'Desayunos', 'Bebidas'],
            ['Pasteles', 'Galletas', 'Bebidas Dulces'],
            ['Jugos Naturales', 'Licuados', 'Aguas Frescas'],
        ];

        foreach ($vendors as $vendorIndex => $vendor) {
            $names = $categoryNames[$vendorIndex] ?? ['General', 'Especiales', 'Bebidas'];
            $parentId = null;

            foreach ($names as $catIndex => $name) {
                $category = Category::create([
                    'vendor_id'          => $vendor->id,
                    'name'               => $name,
                    'slug'               => Str::slug($name) . '-' . $vendor->id,
                    'parent_category_id' => null,
                    'display_order'      => $catIndex,
                ]);

                // Al menos 5 vendors tienen subcategoría: vendors 0-4 (index)
                // La subcategoría del tercer item apunta al primero del mismo vendor
                if ($vendorIndex < 5 && $catIndex === 2) {
                    // Crear una subcategoría apuntando al parent (primer cat del vendor)
                    $parentCategory = Category::where('vendor_id', $vendor->id)->first();
                    Category::create([
                        'vendor_id'          => $vendor->id,
                        'name'               => 'Sub - ' . $name,
                        'slug'               => Str::slug('sub-' . $name) . '-' . $vendor->id,
                        'parent_category_id' => $parentCategory->id,
                        'display_order'      => 10,
                    ]);
                }
            }
        }
    }
}
