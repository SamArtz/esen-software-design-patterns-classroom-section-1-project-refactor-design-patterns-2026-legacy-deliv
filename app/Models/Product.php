<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['vendor_id', 'category_id', 'name', 'description', 'price',
                           'image', 'available', 'stock', 'preparation_time_minutes'];

    protected $casts = ['available' => 'boolean'];

    public function vendor()     { return $this->belongsTo(Vendor::class); }
    public function category()   { return $this->belongsTo(Category::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class, 'item_id')
        ->where('item_type', 'product'); }
    public function bundles()    { return $this->belongsToMany(ProductBundle::class, 'bundle_products', 'product_id', 'bundle_id')
        ->withPivot('quantity'); }
}
