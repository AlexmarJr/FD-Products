<?php

namespace App\Services;

use App\Models\Product;

class ProductServices
{
    public function create(array $data, string $userId): Product
    {
        return Product::create([
            ...$data,
            'user_id' => $userId,
        ]);
    }

   public function update(Product $product, array $data, string $userId): Product
    {
        $product->update([
            ...$data,
            'user_id' => $userId,
        ]);

        return $product;
    }
}