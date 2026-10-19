<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'legal_name',
        'tax_id',
        'status',
        'phone',
        'email',
        'address',
        'commission_rate',
        'logo_path',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'rating' => 'decimal:2',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(SellerMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(SellerMember::class)->where('is_active', true);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'seller_members')
            ->withPivot(['role', 'permissions', 'is_active'])
            ->withTimestamps();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SellerDocument::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function owner(): ?SellerMember
    {
        return $this->members()->where('role', 'owner')->first();
    }
}
