<?php

declare(strict_types=1);

use App\Ai\SafeConversationStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\ToolResultMessage;

beforeEach(function () {
    $this->store = new SafeConversationStore;
    $this->conversationId = (string) Str::uuid7();

    DB::table('agent_conversations')->insert([
        'id' => $this->conversationId,
        'user_id' => 1,
        'title' => 'Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

it('provides a fallback resultId for tool calls with null result_id', function () {
    DB::table('agent_conversation_messages')->insert([
        'id' => (string) Str::uuid7(),
        'conversation_id' => $this->conversationId,
        'user_id' => 1,
        'agent' => 'App\\Ai\\Agents\\AgentRunner',
        'role' => 'assistant',
        'content' => '',
        'attachments' => '[]',
        'tool_calls' => json_encode([
            ['id' => 'get_user_profile', 'name' => 'get_user_profile', 'arguments' => ['section' => 'all'], 'result_id' => null],
        ]),
        'tool_results' => json_encode([
            ['id' => 'get_user_profile', 'name' => 'get_user_profile', 'arguments' => ['section' => 'all'], 'result' => '{"name":"Test"}', 'result_id' => null],
        ]),
        'usage' => '[]',
        'meta' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $messages = $this->store->getLatestConversationMessages($this->conversationId, 10);

    $assistant = $messages->first(fn ($m) => $m instanceof AssistantMessage);
    expect($assistant->toolCalls->first()->resultId)->toBe('call_get_user_profile');

    $toolResult = $messages->first(fn ($m) => $m instanceof ToolResultMessage);
    expect($toolResult->toolResults->first()->resultId)->toBe('call_get_user_profile');
});

it('preserves existing resultId values', function () {
    DB::table('agent_conversation_messages')->insert([
        'id' => (string) Str::uuid7(),
        'conversation_id' => $this->conversationId,
        'user_id' => 1,
        'agent' => 'App\\Ai\\Agents\\AgentRunner',
        'role' => 'assistant',
        'content' => 'Done',
        'attachments' => '[]',
        'tool_calls' => json_encode([
            ['id' => 'fc_abc123', 'name' => 'CreateMealPlan', 'arguments' => [], 'result_id' => 'call_xyz789'],
        ]),
        'tool_results' => json_encode([
            ['id' => 'fc_abc123', 'name' => 'CreateMealPlan', 'arguments' => [], 'result' => '{"success":true}', 'result_id' => 'call_xyz789'],
        ]),
        'usage' => '[]',
        'meta' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $messages = $this->store->getLatestConversationMessages($this->conversationId, 10);

    $assistant = $messages->first(fn ($m) => $m instanceof AssistantMessage);
    expect($assistant->toolCalls->first()->resultId)->toBe('call_xyz789');

    $toolResult = $messages->first(fn ($m) => $m instanceof ToolResultMessage);
    expect($toolResult->toolResults->first()->resultId)->toBe('call_xyz789');
});
