<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;

it('requires authentication for ai chat endpoint', function () {
    $this->post(route('api.v1.ai-chat'), [
        'message' => 'Oi',
    ])->assertRedirect(route('login'));
});

it('validates required message in ai chat endpoint', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('api.v1.ai-chat'), [
        'history' => [],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['message']);
});

it('returns ai reply and history for authenticated user', function () {
    $user = User::factory()->create();

    Product::create([
        'user_id' => $user->id,
        'name' => 'Produto IA',
        'description' => 'Descricao',
        'status' => 'ativo',
        'quantity' => 5,
        'cost_price' => 10,
        'sale_price' => 15,
        'supplier' => 'Fornecedor IA',
    ]);

    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => 'Resposta simulada da IA'],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $response = $this->actingAs($user)->postJson(route('api.v1.ai-chat'), [
        'message' => 'Qual meu estoque?',
        'history' => [],
    ]);

    $response->assertOk()
        ->assertJsonPath('data.reply', 'Resposta simulada da IA')
        ->assertJsonCount(2, 'data.history')
        ->assertJsonPath('data.history.0.role', 'user')
        ->assertJsonPath('data.history.0.text', 'Qual meu estoque?')
        ->assertJsonPath('data.history.1.role', 'assistant')
        ->assertJsonPath('data.history.1.text', 'Resposta simulada da IA');
});
