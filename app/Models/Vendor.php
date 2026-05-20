<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'business_name', 'description', 'logo', 'address',
                           'city', 'latitude', 'longitude', 'opening_hours', 'commission_rate', 'status'];

    protected $casts = ['opening_hours' => 'array'];

    public function user()           { return $this->belongsTo(User::class); }
    public function products()       { return $this->hasMany(Product::class); }
    public function productBundles() { return $this->hasMany(ProductBundle::class); }
    public function categories()     { return $this->hasMany(Category::class); }
    public function orders()         { return $this->hasMany(Order::class); }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
