<?php

declare(strict_types=1);

namespace App\Actions;

use App\Ai\Agents\AssistantAgent;
use App\Http\Requests\StoreAgentConversationRequest;
use App\Models\User;

final class BuildAssistantAgentAction
{
    /**
     * Resolve and fully configure an AssistantAgent for the given request and user.
     *
     * Responsibilities:
     *  - Resolve the agent from the container
     *  - Wire the agent mode and attachments from the request
     *  - Conditionally enable web search based on the selected model
     */
    public function handle(StoreAgentConversationRequest $request, User $user): AssistantAgent
    {
        $model = $request->modelName();
        $attachments = $request->userAttachments();

        $agent = resolve(AssistantAgent::class)
            ->withUser($user)
            ->withMode($request->mode())
            ->withAttachments($attachments);

        if ($model->supportsWebSearch()) {
            $agent->withWebSearch();
        }

        return $agent;
    }
}
