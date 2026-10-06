<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NvidiaChatService
{
    protected string $apiKey;

    protected string $baseUrl;

    protected string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.nvidia.api_key', '');
        $this->baseUrl = rtrim((string) config('services.nvidia.base_url', 'https://integrate.api.nvidia.com/v1'), '/');
        $this->model = (string) config('services.nvidia.model', 'z-ai/glm-5.3');
    }

    /**
     * Send messages to NVIDIA API and get completion response.
     *
     * @param  array<int, array{role: string, content: string|array}>  $messages
     * @param  array<string, mixed>  $options
     * @return array{success: bool, content?: string, model?: string, error?: string, raw?: array}
     */
    public function chat(array $messages, array $options = []): array
    {
        $endpoint = "{$this->baseUrl}/chat/completions";
        $model = ! empty($options['model']) ? (string) $options['model'] : $this->model;
        $apiKey = ! empty($options['api_key']) ? (string) $options['api_key'] : $this->apiKey;

        // Ensure messages are properly formatted
        $formattedMessages = array_map(function ($msg) {
            return [
                'role' => $msg['role'] ?? 'user',
                'content' => $msg['content'] ?? '',
            ];
        }, $messages);

        $payload = [
            'model' => $model,
            'messages' => $formattedMessages,
            'temperature' => (float) ($options['temperature'] ?? 0.5),
            'top_p' => (float) ($options['top_p'] ?? 1.0),
            'max_tokens' => (int) ($options['max_tokens'] ?? 1024),
            'stream' => false,
        ];

        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout((int) ($options['timeout'] ?? 30))
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';

                return [
                    'success' => true,
                    'content' => $content,
                    'model' => $data['model'] ?? $model,
                    'raw' => $data,
                ];
            }

            $errorMessage = $response->json('error.message')
                ?? $response->json('message')
                ?? "NVIDIA API Error ({$response->status()}): {$response->body()}";

            Log::error('NVIDIA NIM API Error', [
                'status' => $response->status(),
                'model' => $model,
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        } catch (ConnectionException $e) {
            Log::warning('NVIDIA API Timeout/Connection issue', [
                'model' => $model,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => "Waktu tunggu (timeout) habis saat menghubungi model '{$model}'. Server free endpoint NVIDIA mungkin sedang antre tinggi. Silakan coba kirim ulang atau pilih model lain dari menu.",
            ];
        } catch (\Throwable $e) {
            Log::error('NVIDIA API Exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Gagal menghubungi server AI: '.$e->getMessage(),
            ];
        }
    }
}
