<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ConversationSummary;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ConversationSummary>
 */
final class ConversationSummaryFactory extends Factory
{
    protected $model = ConversationSummary::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid7(),
            'conversation_id' => Conversation::factory(),
            'sequence_number' => 1,
            'summary' => fake()->paragraph(),
            'topics' => [],
            'key_facts' => [],
            'unresolved_threads' => [],
            'resolved_threads' => [],
            'start_message_id' => (string) Str::uuid7(),
            'end_message_id' => (string) Str::uuid7(),
            'message_count' => 0,
        ];
    }
}
