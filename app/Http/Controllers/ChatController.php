<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\GeminiChatService;
use App\Services\NvidiaChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        protected GeminiChatService $geminiChatService,
        protected NvidiaChatService $nvidiaChatService
    ) {}

    /**
     * Display the main chat interface.
     */
    public function index(): View
    {
        $conversations = Conversation::with(['messages' => function ($query) {
            $query->latest()->limit(1);
        }])->latest()->get();

        $activeConversation = $conversations->first();
        if ($activeConversation) {
            $activeConversation->load('messages');
        }

        return view('chat', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
        ]);
    }

    /**
     * Show a specific conversation.
     */
    public function show(Conversation $conversation, Request $request): View|JsonResponse
    {
        $conversation->load('messages');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'conversation' => $conversation,
                'messages' => $conversation->messages,
            ]);
        }

        $conversations = Conversation::with(['messages' => function ($query) {
            $query->latest()->limit(1);
        }])->latest()->get();

        return view('chat', [
            'conversations' => $conversations,
            'activeConversation' => $conversation,
        ]);
    }

    /**
     * Send a message and receive response from AI API.
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => ['nullable', 'integer', 'exists:conversations,id'],
            'message' => ['required', 'string', 'max:20000'],
            'model' => ['nullable', 'string', 'max:100'],
        ]);

        $conversation = null;
        if (! empty($validated['conversation_id'])) {
            $conversation = Conversation::find($validated['conversation_id']);
        }

        if (! $conversation) {
            $title = Str::limit(trim($validated['message']), 35, '...');
            $conversation = Conversation::create([
                'title' => $title ?: 'Obrolan Baru',
            ]);
        } elseif ($conversation->title === 'Obrolan Baru' && $conversation->messages()->count() === 0) {
            $conversation->update([
                'title' => Str::limit(trim($validated['message']), 35, '...'),
            ]);
        }

        // Store user message
        $userMessage = $conversation->messages()->create([
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        // Get conversation history for context
        $history = $conversation->messages()
            ->select('role', 'content')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn (Message $msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();

        $model = $validated['model'] ?? env('GEMINI_MODEL', 'gemini-flash-latest');
        $isGemini = str_contains(strtolower($model), 'gemini') || env('AI_PROVIDER') === 'gemini';

        // Choose appropriate AI provider service
        if ($isGemini && (str_contains(strtolower($model), 'gemini') || ! str_contains($model, '/'))) {
            $result = $this->geminiChatService->chat($history, ['model' => $model]);
        } else {
            $result = $this->nvidiaChatService->chat($history, ['model' => $model]);
        }

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Terjadi kesalahan saat memproses jawaban AI.',
                'conversation_id' => $conversation->id,
            ], 500);
        }

        // Store assistant response
        $assistantMessage = $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $result['content'],
        ]);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'conversation_title' => $conversation->title,
            'user_message' => $userMessage,
            'assistant_message' => $assistantMessage,
        ]);
    }

    /**
     * Create a new conversation.
     */
    public function newChat(): RedirectResponse|JsonResponse
    {
        $conversation = Conversation::create([
            'title' => 'Obrolan Baru',
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'conversation' => $conversation,
            ]);
        }

        return redirect()->route('chat.show', $conversation);
    }

    /**
     * Delete a conversation.
     */
    public function destroy(Conversation $conversation): JsonResponse|RedirectResponse
    {
        $conversation->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Obrolan berhasil dihapus.',
            ]);
        }

        return redirect()->route('chat.index');
    }

    /**
     * Clear all messages in a conversation.
     */
    public function clear(Conversation $conversation): JsonResponse
    {
        $conversation->messages()->delete();
        $conversation->update(['title' => 'Obrolan Baru']);

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dibersihkan.',
        ]);
    }
}
