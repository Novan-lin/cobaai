<?php

namespace Tests\Feature;

use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_chat_homepage(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeText('Gemini & AI Chat');
    }

    public function test_can_create_new_conversation(): void
    {
        $response = $this->post(route('chat.new'), [], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'conversation' => [
                'title' => 'Obrolan Baru',
            ],
        ]);

        $this->assertDatabaseHas('conversations', [
            'title' => 'Obrolan Baru',
        ]);
    }

    public function test_can_send_message_and_receive_gemini_response(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => 'Halo! Saya Google Gemini AI siap membantu Anda.',
                                ],
                            ],
                            'role' => 'model',
                        ],
                        'finishReason' => 'STOP',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('chat.send'), [
            'message' => 'Halo Gemini, apa kabar?',
            'model' => 'gemini-flash-latest',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'assistant_message' => [
                'role' => 'assistant',
                'content' => 'Halo! Saya Google Gemini AI siap membantu Anda.',
            ],
        ]);

        $this->assertDatabaseHas('messages', [
            'role' => 'user',
            'content' => 'Halo Gemini, apa kabar?',
        ]);

        $this->assertDatabaseHas('messages', [
            'role' => 'assistant',
            'content' => 'Halo! Saya Google Gemini AI siap membantu Anda.',
        ]);
    }

    public function test_can_send_message_in_existing_conversation(): void
    {
        $conversation = Conversation::create(['title' => 'Diskusi PHP']);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => 'PHP 8.4 memperkenalkan fitur baru yang menarik.',
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('chat.send'), [
            'conversation_id' => $conversation->id,
            'message' => 'Ceritakan tentang PHP 8.4',
            'model' => 'gemini-flash-latest',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'conversation_id' => $conversation->id,
        ]);

        $this->assertCount(2, $conversation->fresh()->messages);
    }

    public function test_handles_gemini_api_error_gracefully(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'error' => [
                    'code' => 503,
                    'message' => 'Model is currently overloaded.',
                ],
            ], 503),
        ]);

        $response = $this->postJson(route('chat.send'), [
            'message' => 'Test error handling',
            'model' => 'gemini-flash-latest',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_can_delete_conversation(): void
    {
        $conversation = Conversation::create(['title' => 'Obrolan untuk dihapus']);
        $conversation->messages()->create(['role' => 'user', 'content' => 'Pesan 1']);

        $response = $this->deleteJson(route('chat.destroy', $conversation));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('conversations', ['id' => $conversation->id]);
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_can_clear_conversation_messages(): void
    {
        $conversation = Conversation::create(['title' => 'Obrolan yang ingin dibersihkan']);
        $conversation->messages()->create(['role' => 'user', 'content' => 'Pesan']);
        $conversation->messages()->create(['role' => 'assistant', 'content' => 'Balasan']);

        $response = $this->postJson(route('chat.clear', $conversation));

        $response->assertStatus(200);
        $this->assertCount(0, $conversation->fresh()->messages);
    }
}
