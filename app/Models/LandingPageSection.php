<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LandingPageSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'section_type',
        'source_type',
        'source_value',
        'product_count',
        'display_style',
        'is_active',
        'display_order',
        'description',
        'config',
        'status',
        'published_at',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'product_count' => 'integer',
        'display_order' => 'integer',
        'config' => 'array',
        'published_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get products for this section from the collection
     */
    public function getProducts()
    {
        $collection = ProductCollection::where('slug', $this->source_value)
            ->where('is_active', true)
            ->first();

        if (!$collection) {
            return collect([]);
        }

        return $collection->products()
            ->where('is_active', true)
            ->limit($this->product_count)
            ->get();
    }
}
