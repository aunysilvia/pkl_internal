<?php
// database/migrations/xxxx_create_wishlists_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('wishlists', function (Blueprint $table) {
    $table->id();
    // Foreign Key ke User
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    // Foreign Key ke Product
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->timestamps();

    // Mencegah duplikasi: User yang sama tidak bisa wishlist produk yang sama 2x
    $table->unique(['user_id', 'product_id']);
});

    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
