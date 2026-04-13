<?php

namespace App\Services\AI;

interface AiServiceInterface
{
    /**
     * Send a message to the AI and get a response.
     *
     * @param string $prompt
     * @param array $history
     * @param string|null $systemPrompt
     * @param int|null $maxTokens
     * @return string
     */
    public function chat(string $prompt, array $history = [], ?string $systemPrompt = null, ?int $maxTokens = null): string;
}
