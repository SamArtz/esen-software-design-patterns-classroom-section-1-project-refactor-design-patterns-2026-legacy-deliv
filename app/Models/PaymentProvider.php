<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentProvider extends Model
{
    protected $fillable = ['name', 'api_endpoint', 'api_key', 'enabled', 'priority'];

    protected $casts = ['enabled' => 'boolean'];

    public function payments() { return $this->hasMany(Payment::class, 'provider_id'); }

    public function setApiKeyAttribute(string $value): void
    {
        $this->attributes['api_key'] = base64_encode($value);
    }

    public function getApiKeyAttribute(string $value): string
    {
        return base64_decode($value);
    }
}
