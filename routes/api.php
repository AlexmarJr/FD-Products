<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AiChatApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Versioned RESTful API. Protected by session auth.
| Base prefix: /api  (applied automatically by bootstrap/app.php)
*/

Route::middleware(['web', 'auth'])->prefix('v1')->group(function () {
    Route::post('ai-chat', [AiChatApiController::class, 'chat'])->name('api.v1.ai-chat');
});

//Detalhe, nao ta sendo usado o Sanctum, que seria o mais correto, com o web/auth é necessario usar o CRFT, e nao um token. mas é so a nivel de demonstração