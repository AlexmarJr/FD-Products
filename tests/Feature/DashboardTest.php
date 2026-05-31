<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns dashboard stats json for authenticated user', function () {
    $user = User::factory()->create();

    Product::create([
        'user_id' => $user->id,
        'name' => 'Produto 1',
        'description' => 'Produto de teste',
        'status' => 'ativo',
        'quantity' => 2,
        'cost_price' => 10.00,
        'sale_price' => 15.00,
        'supplier' => 'Fornecedor A',
    ]);

    Product::create([
        'user_id' => $user->id,
        'name' => 'Produto 2',
        'description' => 'Produto de teste',
        'status' => 'pausado',
        'quantity' => 3,
        'cost_price' => 20.00,
        'sale_price' => 30.00,
        'supplier' => 'Fornecedor A',
    ]);

    Product::create([
        'user_id' => $user->id,
        'name' => 'Produto 3',
        'description' => 'Produto de teste',
        'status' => 'ativo',
        'quantity' => 1,
        'cost_price' => 5.00,
        'sale_price' => 8.00,
        'supplier' => 'Fornecedor B',
    ]);

    $response = $this->actingAs($user)->getJson('/dashboard/stats');

    $response->assertStatus(200)
        ->assertJson([
            'total_products' => 3,
            'status_counts' => [
                'ativo' => 2,
                'pausado' => 1,
            ],
            'total_cost_value' => 85,
            'total_sale_value' => 128,
            'estimated_profit' => 43,
        ])
        ->assertJsonStructure([
            'total_products',
            'status_counts',
            'total_cost_value',
            'total_sale_value',
            'estimated_profit',
            'top_suppliers',
        ])
        ->assertJsonCount(2, 'top_suppliers')
        ->assertJsonPath('top_suppliers.0.name', 'Fornecedor A')
        ->assertJsonPath('top_suppliers.0.count', 2);
});