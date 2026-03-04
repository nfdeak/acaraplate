<?php

declare(strict_types=1);

namespace App\Actions;

use App\Ai\Agents\AssistantAgent;
use App\Contracts\ProcessesAdvisorMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\ConversationStore;
use Laravel\Ai\Files\Base64Image;

final readonly class ProcessAdvisorMessageAction implements ProcessesAdvisorMessage
{
    public function __construct(
        private AssistantAgent $advisor,
        private ConversationStore $conversationStore,
    ) {}

    /**
     * @param  array<int, Base64Image>  $attachments
     * @return array{response: string, conversation_id: string}
     */
    public function handle(User $user, string $message, ?string $conversationId = null, array $attachments = []): array
    {
        // Ensure the user is set in the auth guard so AI tools can access it
        // via Auth::user() (Telegram requests bypass web auth middleware).
        Auth::login($user);

        $conversationId ??= $this->conversationStore->latestConversationId($user->id)
            ?? $this->conversationStore->storeConversation($user->id, 'Telegram Chat');

        $agent = $this->advisor
            ->withAttachments($attachments)
            ->continue($conversationId, $user);

        $response = $agent->prompt($message, attachments: $attachments);

        return [
            'response' => $response->text,
            'conversation_id' => $conversationId,
        ];
    }

    public function resetConversation(User $user): string
    {
        return $this->conversationStore->storeConversation($user->id, 'Telegram Chat');
    }
}
