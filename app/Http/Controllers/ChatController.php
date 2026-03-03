<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Ai\Agents\AssistantAgent;
use App\Ai\Tools\AnalyzePhoto;
use App\Enums\AgentMode;
use App\Http\Requests\StoreAgentConversationRequest;
use App\Models\Conversation;
use App\Models\History;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Ai\Providers\Tools\WebSearch;
use Laravel\Ai\Responses\StreamableAgentResponse;

final readonly class ChatController
{
    public function __construct(
        #[CurrentUser] private User $user,
    ) {}

    public function create(
        Request $request,
        string $conversationId = ''
    ): Response {
        $conversation = $conversationId !== ''
            ? Conversation::query()->with('messages')->find($conversationId)
            : null;

        $messages = $conversation?->messages->map(fn (History $message): array => [
            'id' => $message->id,
            'role' => $message->role->value,
            'parts' => [
                ['type' => 'text', 'text' => $message->content],
            ],
        ])->all() ?? [];

        return Inertia::render('chat/create-chat', [
            'conversationId' => $conversation?->id,
            'messages' => $messages,
            'mode' => $request->enum('mode', AgentMode::class),
        ]);
    }

    public function stream(
        StoreAgentConversationRequest $request
    ): StreamableAgentResponse {
        $model = $request->modelName();
        $attachments = $request->userAttachments();

        $agent = resolve(AssistantAgent::class, ['user' => $this->user])
            ->withMode($request->mode())
            ->forUser($this->user);

        if ($model->supportsWebSearch()) {
            $agent->addTool(new WebSearch);
        }

        if ($attachments !== []) {
            $agent->addTool(new AnalyzePhoto($attachments));
        }

        return $agent
            ->stream(
                prompt: $request->userMessage(),
                attachments: $attachments,
                provider: $model->labProvider(),
                model: $model->value,
            )
            ->usingVercelDataProtocol();
    }
}
