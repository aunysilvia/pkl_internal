<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'discount_price',
        'stock',
        'weight',
        'is_active',
        'is_featured',
    ];

    // Casts: Konversi tipe data otomatis
    // decimal:2 -> Angka decimal dengan 2 digit di belakang koma (string di PHP agar akurat)
    // boolean   -> tinyint(1) di DB dikonversi jadi true/false di PHP
    protected $casts = [
        'price'          => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_active'      => 'boolean',
        'is_featured'    => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Relasi Inverse One-to-Many: Produk milik SATU Kategori.
     *
     * Laravel mendeteksi foreign key 'category_id' dari nama method 'category'.
     *
     * $product->category->name
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi One-to-Many: Produk punya BANYAK gambar.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Relasi One-to-One: Mengambil gambar UTAMA saja.
     * Menggunakan where('is_primary', true) untuk filter.
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Fallback Image: Jika tidak ada image primary, ambil yang paling tua/pertama diupload.
     */
    public function firstImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->oldestOfMany('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlistedBy(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    // ==================== ACCESSORS ====================

    /**
     * Accessor: Display Price
     * Logika: Jika ada diskon valid, tampilkan harga diskon. Jika tidak, harga normal.
     *
     * $product->display_price (returns float)
     */
    public function getDisplayPriceAttribute(): float
    {
        if ($this->discount_price !== null && $this->discount_price < $this->price) {
            return (float) $this->discount_price;
        }
        return (float) $this->price;
    }

    /**
     * Accessor: Formatted Price
     * Format Rupiah: Rp 1.500.000
     *
     * $product->formatted_price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->display_price, 0, ',', '.');
    }

    /**
     * Accessor: Formatted Original Price (Coret)
     * Hanya digunakan jika produk diskon, untuk menampilkan harga asli yang dicoret.
     */
    public function getFormattedOriginalPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Accessor: Cek apakah produk diskon?
     * Return: true/false
     */
    public function getHasDiscountAttribute(): bool
    {
        return $this->discount_price !== null
        && $this->discount_price > 0
        && $this->discount_price < $this->price;
    }

    /**
     * Accessor: Hitung % Diskon
     * Rumus: (Diskon / Harga Asli) * 100
     */
    public function getDiscountPercentageAttribute(): int
    {
        if (! $this->has_discount) {
            return 0;
        }

        $discount = $this->price - $this->discount_price;
        return (int) round(($discount / $this->price) * 100);
    }

    /**
     * Accessor: Get Image URL (Smart)
     * Strategi:
     * 1. Cek Primary Image
     * 2. Kalau null, cek First Image
     * 3. Kalau null, cek Collection Images ambil yang pertama
     * 4. Kalau semua null (gak punya gambar), return Placeholder
     */
    public function getImageUrlAttribute(): string
    {
        $image = $this->primaryImage ?? $this->firstImage ?? $this->images->first();

        if ($image) {
            return $image->image_url;
        }

        return asset('images/no-product-image.jpg');
    }

    /**
     * Cek ketersediaan untuk tombol "Beli"
     */
    public function getIsAvailableAttribute(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    public function getStockLabelAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'Habis';
        } elseif ($this->stock <= 5) {
            return 'Sisa ' . $this->stock;
        }
        return 'Tersedia';
    }

    public function getStockBadgeColorAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'danger';
        } elseif ($this->stock <= 5) {
            return 'warning';
        }
        return 'success';
    }

    public function getFormattedWeightAttribute(): string
    {
        if ($this->weight >= 1000) {
            return number_format($this->weight / 1000, 1) . ' kg';
        }
        return $this->weight . ' gram';
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Scope: Pencarian Produk
     * Menerima keyword, mencari di nama ATAU deskripsi.
     *
     * Product::search('samsung')->get();
     */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%");
        });
    }

    // ... Scopes lainnya sama seperti sebelumnya ...
    public function scopeActive($query)
    {return $query->where('is_active', true);}
    public function scopeFeatured($query)
    {return $query->where('is_featured', true);}
    public function scopeInStock($query)
    {return $query->where('stock', '>', 0);}

    public function scopeAvailable($query)
    {
        return $query->active()->inStock();
    }

    public function scopeByCategory($query, string $categorySlug)
    {
        return $query->whereHas('category', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    public function scopeInCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopePriceRange($query, float $min, float $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function scopeMinPrice($query, float $min)
    {
        return $query->where('price', '>=', $min);
    }

    public function scopeMaxPrice($query, float $max)
    {
        return $query->where('price', '<=', $max);
    }

    public function scopeOnSale($query)
    {
        return $query->whereNotNull('discount_price')
            ->whereColumn('discount_price', '<', 'price');
    }

    public function scopeSortBy($query, ?string $sort)
    {
        return match ($sort) {
            'newest'     => $query->latest(),
            'oldest'     => $query->oldest(),
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc'   => $query->orderBy('name', 'asc'),
            'name_desc'  => $query->orderBy('name', 'desc'),
            'popular'    => $query->withCount('orderItems')->orderByDesc('order_items_count'),
            default      => $query->latest(),
        };
    }

    // ==================== BOOT METHOD ====================

    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug yang UNIK saat creating
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $baseSlug = Str::slug($product->name);
                $slug     = $baseSlug;
                $counter  = 1;

                // Loop cek apakah slug sudah dipakai?
                // Jika ya, tambahkan angka (contoh: produk-1, produk-2)
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                $product->slug = $slug;
            }
        });
    }

    // ==================== HELPER METHODS ====================

    /**
     * Kurangi stok atomik (thread-safe).
     */
    public function decrementStock(int $quantity): bool
    {
        if ($this->stock < $quantity) {
            return false;
        }

        $this->decrement('stock', $quantity); // Query langsung: UPDATE products SET stock = stock - X
        return true;
    }

    public function incrementStock(int $quantity): void
    {
        $this->increment('stock', $quantity);
    }

    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }
}
