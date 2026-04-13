<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AI\AiServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class AiControllerTest extends TestCase
{
    /**
     * Test the AI chat endpoint.
     */
    public function test_ai_chat_endpoint_returns_success(): void
    {
        // Mock the AI service to avoid real API calls
        $this->mock(AiServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->with('Hello', [])
                ->andReturn('Hello! I am Llama 3 from Groq.');
        });

        $response = $this->postJson('/api/ai/chat', [
            'message' => 'Hello',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'AI response generated',
                'data' => [
                    'response' => 'Hello! I am Llama 3 from Groq.',
                ],
            ]);
    }

    /**
     * Test validation.
     */
    public function test_ai_chat_requires_message(): void
    {
        $response = $this->postJson('/api/ai/chat', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }
}
