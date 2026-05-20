<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductBundle extends Model
{
    use HasFactory;

    protected $fillable = ['vendor_id', 'name', 'description', 'base_price',
                           'discount_percentage', 'image', 'available'];

    public function vendor()      { return $this->belongsTo(Vendor::class); }
    public function products()    { return $this->belongsToMany(Product::class, 'bundle_products', 'bundle_id', 'product_id')
        ->withPivot('quantity'); }
    // Auto-referencia para anidamiento
    public function childBundles()  { return $this->belongsToMany(self::class, 'bundle_bundles', 'parent_bundle_id', 'child_bundle_id')
        ->withPivot('quantity'); }
    public function parentBundles() { return $this->belongsToMany(self::class, 'bundle_bundles', 'child_bundle_id', 'parent_bundle_id')
        ->withPivot('quantity'); }

    public function getTotalPrice(): float
    {
        $total = 0.0;

        // Suma productos directos
        $products = $this->products()->withPivot('quantity')->get();
        foreach ($products as $product) {
            $total += $product->price * $product->pivot->quantity;
        }

        $childBundles = $this->childBundles()->withPivot('quantity')->get();
        foreach ($childBundles as $bundle) {
            $total += $bundle->base_price * $bundle->pivot->quantity;        }

        // Aplica descuento del bundle
        if ($this->discount_percentage > 0) {
            $total = $total * (1 - $this->discount_percentage / 100);
        }

        return $total;
    }
}
