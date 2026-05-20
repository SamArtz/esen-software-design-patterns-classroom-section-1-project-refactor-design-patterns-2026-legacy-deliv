<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['vendor_id', 'name', 'slug', 'parent_category_id', 'display_order'];

    public function vendor()   { return $this->belongsTo(Vendor::class); }
    public function products() { return $this->hasMany(Product::class); }
    public function parent()   { return $this->belongsTo(self::class, 'parent_category_id'); }
    public function children() { return $this->hasMany(self::class, 'parent_category_id'); }
}
