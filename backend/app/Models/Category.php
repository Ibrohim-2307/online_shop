<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Professional high-resolution category photography visual (Uzum / Amazon marketplace grade).
     */
    public function getDisplayImageAttribute(): string
    {
        $slug = strtolower(trim((string)$this->slug));

        if (str_contains($slug, 'qurilish') || str_contains($slug, 'sement') || str_contains($slug, 'gisht')) {
            return '/images/products/cement.jpg';
        }
        if (str_contains($slug, 'sanoat') || str_contains($slug, 'uskuna') || str_contains($slug, 'generator')) {
            return '/images/products/generator.jpg';
        }
        if (str_contains($slug, 'oziq-ovqat') || str_contains($slug, 'ichimlik') || str_contains($slug, 'don')) {
            return '/images/products/yog.jpg';
        }
        if (str_contains($slug, 'elektr') || str_contains($slug, 'yoritish') || str_contains($slug, 'kabel')) {
            return '/images/products/proyektor.jpg';
        }
        if (str_contains($slug, 'boyoq') || str_contains($slug, 'kimyo')) {
            return '/images/products/boyoq.jpg';
        }
        if (str_contains($slug, 'santexnika') || str_contains($slug, 'quvur') || str_contains($slug, 'suv')) {
            return '/images/products/quvur.jpg';
        }
        if (str_contains($slug, 'asbob') || str_contains($slug, 'instrument')) {
            return '/images/products/nivelir.jpg';
        }

        return '/images/products/cement.jpg';
    }

    /**
     * Get handsome emoji or icon for display across storefront and dashboards.
     */
    public function getDisplayIconAttribute(): string
    {
        if (!empty($this->icon)) {
            return $this->icon;
        }

        return match ($this->slug) {
            'qurilish-materiallari', 'qurilish-material' => '🧱',
            'sanoat-uskunalari', 'sanoat' => '🏭',
            'oziq-ovqat-va-ichimliklar', 'oziq-ovqat' => '🌾',
            'elektr-jihozlari', 'elektr-va-yoritish', 'elektr' => '💡',
            'boyoqlar-va-kimyo', 'boyoq' => '🎨',
            'santexnika-va-quvurlar', 'santexnika' => '🚰',
            'asbob-uskunalar', 'qurilish-asbob-uskunalari', 'asboblar' => '🛠️',
            'avto-ehtiyot-qismlar', 'avto' => '🚗',
            'mebel-va-interyer', 'mebel' => '🛋️',
            'maxsus-kiyimlar', 'forma' => '🦺',
            default => '📦',
        };
    }

    /**
     * Subtle thematic background color badge per category.
     */
    public function getBgColorAttribute(): string
    {
        return match ($this->slug) {
            'qurilish-materiallari' => 'bg-amber-50 group-hover:bg-[#C9F46B]',
            'sanoat-uskunalari' => 'bg-blue-50 group-hover:bg-[#C9F46B]',
            'oziq-ovqat-va-ichimliklar' => 'bg-emerald-50 group-hover:bg-[#C9F46B]',
            'elektr-jihozlari', 'elektr-va-yoritish' => 'bg-yellow-50 group-hover:bg-[#C9F46B]',
            'boyoqlar-va-kimyo' => 'bg-purple-50 group-hover:bg-[#C9F46B]',
            'santexnika-va-quvurlar' => 'bg-cyan-50 group-hover:bg-[#C9F46B]',
            'asbob-uskunalar', 'qurilish-asbob-uskunalari' => 'bg-orange-50 group-hover:bg-[#C9F46B]',
            default => 'bg-[#FAFBF7] group-hover:bg-[#C9F46B]',
        };
    }
}
