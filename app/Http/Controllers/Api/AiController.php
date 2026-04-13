<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AI\AiServiceInterface;
use App\Http\Requests\AI\AiChatRequest;
use App\Helpers\BaseResponse;
use Illuminate\Http\JsonResponse;

class AiController extends Controller
{
    private AiServiceInterface $aiService;

    public function __construct(AiServiceInterface $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle AI chat request.
     *
     * @param AiChatRequest $request
     * @return JsonResponse
     */
    public function chat(AiChatRequest $request): JsonResponse
    {
        $prompt = $request->input('message');
        $history = $request->input('history', []);
        $systemPrompt = $request->input('system_prompt');
        $maxTokens = $request->input('max_tokens');

        $response = $this->aiService->chat($prompt, $history, $systemPrompt, $maxTokens);

        return BaseResponse::Success('AI response generated', [
            'response' => $response,
        ]);
    }
}
