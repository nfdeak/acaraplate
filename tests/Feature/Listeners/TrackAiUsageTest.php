<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners;

use App\Ai\Agents\AssistantAgent;
use App\Enums\AgentMode;
use App\Enums\ModelName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('tracks usage for AgentStreamed events via chat stream', function (): void {
    $user = User::factory()->create();

    AssistantAgent::fake([
        'Test response',
    ]);

    $this->actingAs($user)
        ->postJson(route('chat.stream'), [
            'mode' => AgentMode::Ask->value,
            'model' => ModelName::GEMINI_3_FLASH->value,
            'messages' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['type' => 'text', 'text' => 'Hello AI'],
                    ],
                ],
            ],
        ])
        ->assertSuccessful();
});
