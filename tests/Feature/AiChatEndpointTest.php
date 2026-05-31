<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;

it('requires authentication for ai chat endpoint', function () {
    $this->post(route('ai-chat.chat'), [
        'message' => 'Oi',
    ])->assertRedirect(route('login'));
});

it('validates required message in ai chat endpoint', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('ai-chat.chat'), [
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

    $response = $this->actingAs($user)->postJson(route('ai-chat.chat'), [
        'message' => 'Qual meu estoque?',
        'history' => [],
    ]);

    $response->assertOk()
        ->assertJsonPath('reply', 'Resposta simulada da IA')
        ->assertJsonCount(2, 'history')
        ->assertJsonPath('history.0.role', 'user')
        ->assertJsonPath('history.0.text', 'Qual meu estoque?')
        ->assertJsonPath('history.1.role', 'assistant')
        ->assertJsonPath('history.1.text', 'Resposta simulada da IA');
});
