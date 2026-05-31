<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists products for authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        Product::create([
            'user_id' => $user->id,
            'name' => "Produto {$i} - {$user->id}",
            'description' => 'Produto teste',
            'status' => 'ativo',
            'quantity' => 10,
            'cost_price' => 10.0,
            'sale_price' => 15.0,
        ]);
    }

    Product::create([
        'user_id' => $otherUser->id,
        'name' => 'Produto de outro usuario',
        'description' => 'Nao deve aparecer',
        'status' => 'ativo',
        'quantity' => 1,
        'cost_price' => 1.0,
        'sale_price' => 2.0,
    ]);

    $response = $this->actingAs($user)->get(route('products.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Products')
            ->has('products.data', 5)
            ->where('products.data.0.user_id', $user->id)
        );
});

it('creates a product via store endpoint', function () {
    $user = User::factory()->create();

    $payload = [
        'name' => 'Produto Teste',
        'description' => 'Descrição teste',
        'status' => 'ativo',
        'quantity' => 10,
        'cost_price' => 10.5,
        'sale_price' => 15.0,
        'supplier' => 'Fornecedor X',
    ];

    $response = $this->actingAs($user)->post(route('products.store'), $payload);

    $response->assertRedirect(route('products.index'));
    $this->assertDatabaseHas('products', [
        'name' => 'Produto Teste',
        'user_id' => $user->id,
        'supplier' => 'Fornecedor X',
        'quantity' => 10,
    ]);
});

it('redirects guest users from all product endpoints', function () {
    $product = Product::create([
        'user_id' => User::factory()->create()->id,
        'name' => 'Produto Restrito',
        'description' => 'Teste',
        'status' => 'ativo',
        'quantity' => 5,
        'cost_price' => 10,
        'sale_price' => 20,
    ]);

    $this->get(route('products.index'))->assertRedirect(route('login'));
    $this->post(route('products.store'), [])->assertRedirect(route('login'));
    $this->put(route('products.update', $product), [])->assertRedirect(route('login'));
    $this->delete(route('products.destroy', $product))->assertRedirect(route('login'));
});

it('validates required fields on store endpoint', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('products.index'))
        ->post(route('products.store'), [
            'name' => '',
            'status' => '',
            'quantity' => -1,
            'cost_price' => -10,
            'sale_price' => -10,
        ]);

    $response->assertRedirect(route('products.index'));
    $response->assertSessionHasErrors([
        'name',
        'status',
        'quantity',
        'cost_price',
        'sale_price',
    ]);
});

it('updates a product via update endpoint', function () {
    $user = User::factory()->create();

    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Produto Antigo',
        'description' => 'Descricao antiga',
        'status' => 'ativo',
        'quantity' => 3,
        'cost_price' => 20,
        'sale_price' => 30,
        'supplier' => 'Fornecedor A',
    ]);

    $payload = [
        'name' => 'Produto Atualizado',
        'description' => 'Descricao nova',
        'status' => 'pausado',
        'quantity' => 8,
        'cost_price' => 25,
        'sale_price' => 40,
        'supplier' => 'Fornecedor B',
    ];

    $response = $this->actingAs($user)->put(route('products.update', $product), $payload);

    $response->assertRedirect(route('products.index'));
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Produto Atualizado',
        'status' => 'pausado',
        'quantity' => 8,
        'supplier' => 'Fornecedor B',
    ]);
});

it('deletes a product via destroy endpoint', function () {
    $user = User::factory()->create();

    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Produto para remover',
        'description' => 'Descricao',
        'status' => 'ativo',
        'quantity' => 2,
        'cost_price' => 5,
        'sale_price' => 8,
    ]);

    $response = $this->actingAs($user)->delete(route('products.destroy', $product));

    $response->assertRedirect(route('products.index'));
    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});
