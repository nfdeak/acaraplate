<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Actions\ProcessAdvisorMessageAction;
use App\Models\User;
use Illuminate\Container\Attributes\Bind;
use Laravel\Ai\Files\Base64Image;

#[Bind(ProcessAdvisorMessageAction::class)]
interface ProcessesAdvisorMessage
{
    /**
     * @param  array<int, Base64Image>  $attachments
     * @return array{response: string, conversation_id: string}
     */
    public function handle(User $user, string $message, ?string $conversationId = null, array $attachments = []): array;

    public function resetConversation(User $user): string;
}
