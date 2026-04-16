<?php

declare(strict_types=1);

use App\Actions\BuildAssistantAgentAction;
use App\Ai\AgentBuilder;
use App\Ai\AgentPayload;
use App\Contracts\Ai\Memory\DispatchesMemoryExtraction;
use App\Contracts\Ai\Memory\ManagesMemoryContext;
use App\Contracts\Billing\GatesPremiumFeatures;
use App\Enums\AgentMode;
use App\Enums\ModelName;
use App\Http\Requests\StreamChatRequest;
use App\Jobs\Memory\ExtractUserMemoriesJob;
use App\Models\Conversation;
use App\Models\History;
use App\Models\Memory as MemoryModel;
use App\Models\User;
use App\Services\Null\NullMemoryContext;
use App\Services\Null\NullMemoryExtractionDispatcher;
use App\Services\Null\NullPremiumGate;
use Illuminate\Support\Facades\Queue;

covers(AgentBuilder::class);
covers(BuildAssistantAgentAction::class);
covers(User::class);

beforeEach(function (): void {
    config()->set('memory.embeddings.dimensions', 8);
});

it('renders no memory block when ManagesMemoryContext is bound to the null-object', function (): void {
    app()->bind(ManagesMemoryContext::class, NullMemoryContext::class);

    $user = User::factory()->create();
    MemoryModel::factory()->for($user)->create([
        'content' => 'User is lactose intolerant',
        'is_pinned' => true,
    ]);

    $payload = new AgentPayload(
        userId: $user->id,
        message: 'hi',
        mode: AgentMode::Ask,
    );

    $instructions = resolve(AgentBuilder::class)->buildInstructions($payload, $user);

    expect($instructions)
        ->not->toContain('# CORE TRUTHS')
        ->and($instructions)->not->toContain('# RECALLED MEMORIES')
        ->and($instructions)->not->toContain('User is lactose intolerant');
});

it('does not dispatch ExtractUserMemoriesJob when DispatchesMemoryExtraction is bound to the null-object', function (): void {
    app()->bind(DispatchesMemoryExtraction::class, NullMemoryExtractionDispatcher::class);

    config()->set('memory.extraction.threshold', 1);

    Queue::fake();

    $user = User::factory()->create();
    $conversation = Conversation::factory()->forUser($user)->create();
    History::factory()->forConversation($conversation)->count(10)->create();

    $request = StreamChatRequest::create(
        route('chat.stream', $conversation->id),
        'POST',
        [
            'model' => ModelName::GPT_5_MINI->value,
            'mode' => AgentMode::Ask->value,
            'messages' => [['role' => 'user', 'parts' => [['type' => 'text', 'text' => 'Hello']]]],
        ],
    );
    $request->setContainer(app());
    $request->validateResolved();

    resolve(BuildAssistantAgentAction::class)->handle($request, $user, $conversation->id);

    Queue::assertNotPushed(ExtractUserMemoriesJob::class);
});

it('treats every user as premium when GatesPremiumFeatures is bound to the null-object', function (): void {
    app()->bind(GatesPremiumFeatures::class, NullPremiumGate::class);

    $user = User::factory()->create(['is_verified' => false]);

    expect($user->is_verified)->toBeTrue();
});
