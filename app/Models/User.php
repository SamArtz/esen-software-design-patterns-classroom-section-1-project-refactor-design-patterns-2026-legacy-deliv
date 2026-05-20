<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'phone', 'role'];

    protected $hidden = ['password', 'remember_token'];

    public function setPasswordAttribute(string $value): void
    {
        if (strlen($value) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters.');
        }
        if (!preg_match('/[A-Z]/', $value)) {
            throw new \InvalidArgumentException('Password must contain at least one uppercase letter.');
        }
        $this->attributes['password'] = bcrypt($value);
    }

    public function isCustomer(): bool { return $this->role === 'customer'; }
    public function isVendor(): bool { return $this->role === 'vendor'; }
    public function isCourier(): bool { return $this->role === 'courier'; }
    public function isAdmin(): bool { return $this->role === 'admin'; }

    public function customer() { return $this->hasOne(Customer::class); }
    public function vendor() { return $this->hasOne(Vendor::class); }
    public function courier() { return $this->hasOne(Courier::class); }
}
