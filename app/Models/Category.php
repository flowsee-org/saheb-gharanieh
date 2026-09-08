<?php

namespace App\Models;

use App\Enums\CategoryLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'short_name',
        'latin_name',
        'subtitle',
        'description',
        'kind',
        'layout',
        'glyph',
        'image_path',
        'price',
        'price_note',
        'card_order',
        'card_title',
        'card_subtitle',
        'card_latin',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'layout' => CategoryLayout::class,
            'price' => 'integer',
            'card_order' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $category) {
            if (blank($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @return HasMany<Product, $this> */
    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    /** @return HasMany<CategoryFeature, $this> */
    public function features(): HasMany
    {
        return $this->hasMany(CategoryFeature::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @param  Builder<$this>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @param  Builder<$this>  $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /** Categories promoted to the landing page as a big card. */
    /** @param  Builder<$this>  $query */
    public function scopeOnLanding(Builder $query): void
    {
        $query->whereNotNull('card_order')->orderBy('card_order');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Title used on the landing card (falls back to the section name). */
    public function cardTitle(): string
    {
        return $this->card_title ?: $this->name;
    }

    /** Compact label for the chip nav (falls back to the full name). */
    public function shortName(): string
    {
        return $this->short_name ?: $this->name;
    }

    public function isHookah(): bool
    {
        return $this->kind === 'hookah';
    }

    public function usesGrid(): bool
    {
        return $this->layout === CategoryLayout::Grid;
    }
}
