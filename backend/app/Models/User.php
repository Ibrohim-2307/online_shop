<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getSellerIdAttribute(): ?int
    {
        if (isset($this->attributes['seller_id']) && $this->attributes['seller_id'] !== null) {
            return (int) $this->attributes['seller_id'];
        }
        return $this->sellerMemberships()->where('is_active', true)->value('seller_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withTimestamps();
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    public function sellerMemberships(): HasMany
    {
        return $this->hasMany(SellerMember::class);
    }

    public function sellers(): BelongsToMany
    {
        return $this->belongsToMany(Seller::class, 'seller_members')
            ->withPivot(['role', 'permissions', 'is_active'])
            ->withTimestamps();
    }

    public function hasRole(string|array $roles): bool
    {
        $roleList = is_array($roles) ? $roles : func_get_args();
        return $this->roles->pluck('name')->intersect($roleList)->isNotEmpty();
    }

    public function hasPermission(string $permission): bool
    {
        // Admin has all permissions
        if ($this->hasRole('admin')) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->permissions->contains('name', $permission)) {
                return true;
            }
        }

        return false;
    }

    public function belongsToSeller(int $sellerId): bool
    {
        return $this->sellerMemberships()
            ->where('seller_id', $sellerId)
            ->where('is_active', true)
            ->exists();
    }

    public function isSellerOwner(int $sellerId): bool
    {
        return $this->sellerMemberships()
            ->where('seller_id', $sellerId)
            ->where('role', 'owner')
            ->where('is_active', true)
            ->exists();
    }

    public function sellerMember(int $sellerId): ?SellerMember
    {
        return $this->sellerMemberships()
            ->where('seller_id', $sellerId)
            ->where('is_active', true)
            ->first();
    }

    public function activeSeller(): ?Seller
    {
        $membership = $this->sellerMemberships()
            ->where('is_active', true)
            ->with('seller')
            ->first();

        return $membership?->seller;
    }

    public function assignRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role && !$this->roles->contains('id', $role->id)) {
            $this->roles()->attach($role->id);
            $this->load('roles');
            \App\Services\Audit\AuditService::log('user.role_assigned', $this, null, ['role' => $roleName]);
        }
    }

    public function removeRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role->id);
            $this->load('roles');
            \App\Services\Audit\AuditService::log('user.role_removed', $this, ['role' => $roleName], null);
        }
    }
}

