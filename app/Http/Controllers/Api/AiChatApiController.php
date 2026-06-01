<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;


//Essa aqui seria a versao para uma API REST, basciamente é uma copia do Controller que estava usando, mas com retornos em Json.
class AiChatApiController extends Controller
{
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
        ]);

        $userMessage = $request->input('message');
        $history = $request->input('history', []);

        $geminiApiKey = env('GEMINI_API');

        if (!$geminiApiKey) {
            return response()->json(['error' => 'Gemini API key not configured.'], 500);
        }

        try {
            $products = Product::where('user_id', Auth::id())
                ->select(['name', 'sale_price', 'cost_price', 'quantity', 'supplier'])
                ->get();
            $productContext = $products
                ->map(function ($product) {

                    $parts = [
                        $product->name,
                        "venda: R$ {$product->sale_price}",
                        "custo: R$ {$product->cost_price}",
                    ];

                    if (!is_null($product->quantity)) {
                        $parts[] = "estoque: {$product->quantity}";
                    }

                    if (!empty($product->supplier)) {
                        $parts[] = "fornecedor: {$product->supplier}";
                    }

                    return '- ' . implode(' | ', $parts);
                })
                ->implode("\n");


            $totalProducts = $products->count();
            $totalCost = $products->sum(function($p) { return $p->cost_price * $p->quantity; });
            $totalSale = $products->sum(function($p) { return $p->sale_price * $p->quantity; });
            $estimatedProfit = $totalSale - $totalCost;

            $systemPrompt = "
                Você é um assistente especialista em vendas, margem de lucro e estoque.
                Responda de forma objetiva, clara e estratégica.
                Considere sempre o horário de Brasília (UTC-3).

                REGRAS DE COMPORTAMENTO:
                - Somente quando o usuário perguntar sobre o estoque de forma geral, forneça IMEDIATAMENTE
                um resumo completo: valor total em estoque, produtos críticos (baixa quantidade),
                destaques de margem e total de itens. Não peça mais detalhes.
                - Só peça esclarecimentos se a pergunta for genuinamente ambígua e impossível de responder.
                - Use tabelas ou listas quando facilitar a leitura.

                Usuário: " . Auth::user()->name . " .
                Foco do usuário: " . (string) (Auth::user()->business_type[0] ?? '') . " .
                Produtos em estoque:
                {$productContext} 

                Com um total de {$totalProducts} produtos, valor total em estoque de R$ {$totalCost}, valor total de venda de R$ {$totalSale} e lucro estimado de R$ {$estimatedProfit}
            ";

            $contents = [];
            if (is_array($history) && count($history) > 0) {
                $slice = array_slice($history, -12);
                foreach ($slice as $h) {
                    $isUser = (isset($h['sender']) && $h['sender'] === 'user')
                        || (isset($h['role']) && $h['role'] === 'user');
                    $role = $isUser ? 'user' : 'model';
                    $text = isset($h['text']) ? $h['text'] : '';
                    $contents[] = [
                        'role' => $role,
                        'parts' => [['text' => $text]],
                    ];
                }
            }

            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $userMessage]],
            ];

            $payload = [
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt]],
                ],
                'tools' => [
                    ['google_search' => (object)[]],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 8192,
                    'temperature'     => 0.2,
                ],
                'contents' => $contents,
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(60)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=' . $geminiApiKey,
                //Deixei a Chave da API do gemini aqui, pra facilitar pra voces, mas obviamente o correto é usar uma varaivel de ambiente que no caso buscaria assim $geminiApiKey = env('GEMINI_API');
                //Chave foi criada especifica pra isso, e ta limitada a 10 Reais de uso, oq da bastante pra um chat.

                //A ideia era essa, ai subi com a chave hardcoded, e o google explodiu minha chave e meu projeto na IA pq foi "Vazado" kkk
                $payload
            );

            if (!$response->successful()) {
                return response()->json([
                    'data'    => null,
                    'message' => 'Erro ao se comunicar com a API Gemini.',
                    'errors'  => ['gemini' => $response->body()],
                ], 500);
            }

            $responseData = $response->json();

            $reply = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Desculpe, não consegui obter uma resposta.';

            $updatedHistory = $history;
            $updatedHistory[] = [
                'role' => 'user',
                'text' => $userMessage,
            ];
            $updatedHistory[] = [
                'role' => 'assistant',
                'text' => $reply,
            ];

            return response()->json([
                'data' => [
                    'reply'   => $reply,
                    'history' => array_slice($updatedHistory, -40),
                ],
                'message' => 'OK',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'data'    => null,
                'message' => 'Failed to communicate with Gemini API.',
                'errors'  => ['exception' => $e->getMessage()],
            ], 500);
        }
    }
}
