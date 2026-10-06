<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiChatService
{
    protected string $apiKey;

    protected string $baseUrl;

    protected string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.api_key', '');
        $this->baseUrl = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $this->model = (string) config('services.gemini.model', 'gemini-flash-latest');
    }

    /**
     * Send messages to Google Gemini API and get response.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     * @return array{success: bool, content?: string, model?: string, error?: string, raw?: array}
     */
    public function chat(array $messages, array $options = []): array
    {
        $model = ! empty($options['model']) ? (string) $options['model'] : $this->model;
        $apiKey = ! empty($options['api_key']) ? (string) $options['api_key'] : $this->apiKey;

        // Convert standard messages to Gemini contents format
        $contents = [];
        foreach ($messages as $msg) {
            $role = ($msg['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
            $text = is_array($msg['content'] ?? '') ? json_encode($msg['content']) : (string) ($msg['content'] ?? '');

            if (trim($text) === '') {
                continue;
            }

            $contents[] = [
                'role' => $role,
                'parts' => [
                    [
                        'text' => $text,
                    ],
                ],
            ];
        }

        if (empty($contents)) {
            return [
                'success' => false,
                'error' => 'Pesan tidak boleh kosong.',
            ];
        }

        $endpoint = "{$this->baseUrl}/models/{$model}:generateContent";

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => (float) ($options['temperature'] ?? 0.7),
                'maxOutputTokens' => (int) ($options['max_tokens'] ?? 2048),
            ],
        ];

        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'X-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout((int) ($options['timeout'] ?? 30))
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

                return [
                    'success' => true,
                    'content' => $text,
                    'model' => $model,
                    'raw' => $data,
                ];
            }

            $errorStatus = $response->status();
            $errorMessage = $response->json('error.message') ?? "Gemini API Error ({$errorStatus})";

            if ($errorStatus === 503) {
                $errorMessage = 'Model Gemini saat ini sedang mengalami lonjakan antrean tinggi (503 High Demand). Silakan coba klik Kirim kembali dalam beberapa saat.';
            }

            Log::error('Gemini API Error', [
                'status' => $errorStatus,
                'model' => $model,
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        } catch (ConnectionException $e) {
            Log::warning('Gemini API Connection Timeout', [
                'model' => $model,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Koneksi ke Gemini API timeout. Silakan periksa jaringan internet Anda dan coba lagi.',
            ];
        } catch (\Throwable $e) {
            Log::error('Gemini API Exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Terjadi kesalahan saat menghubungi server Gemini: '.$e->getMessage(),
            ];
        }
    }
}
