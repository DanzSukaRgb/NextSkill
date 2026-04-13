<?php

namespace App\Services\AI;

use App\Services\AI\Context\NextSkillContext;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService implements AiServiceInterface
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->model = config('services.groq.model');
    }

    public function chat(string $prompt, array $history = [], ?string $systemPrompt = null, ?int $maxTokens = null): string
    {
        try {
            $messages = [];
            
            // Base Platform Context (Grounding)
            $baseContext = NextSkillContext::getPrompt();
            
            // Combine with custom system prompt if any
            $finalSystemPrompt = $baseContext;
            if ($systemPrompt) {
                $finalSystemPrompt .= "\n\nInstruksi Tambahan:\n" . $systemPrompt;
            }

            $messages[] = ['role' => 'system', 'content' => $finalSystemPrompt];

            // Add history
            foreach ($history as $message) {
                $messages[] = $message;
            }

            // Add current prompt
            $messages[] = ['role' => 'user', 'content' => $prompt];

            $payload = [
                'model' => $this->model,
                'messages' => $messages,
            ];

            // Add max tokens if provided
            if ($maxTokens) {
                $payload['max_tokens'] = $maxTokens;
            }

            $response = Http::withToken($this->apiKey)
                ->post($this->baseUrl, $payload);

            if ($response->failed()) {
                Log::error('Groq AI Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return 'Sorry, I am having trouble connecting to the AI service right now.';
            }

            return $response->json('choices.0.message.content') ?? 'No response from AI.';
        } catch (\Exception $e) {
            Log::error('Groq AI Exception', [
                'message' => $e->getMessage(),
            ]);
            return 'An unexpected error occurred while communicating with the AI.';
        }
    }
}
