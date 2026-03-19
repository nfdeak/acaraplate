<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_summaries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('conversation_id', 36);
            $table->unsignedInteger('sequence_number');
            $table->uuid('previous_summary_id')->nullable();
            $table->text('summary');
            $table->json('topics');
            $table->json('key_facts');
            $table->json('unresolved_threads');
            $table->json('resolved_threads');
            $table->string('start_message_id', 36);
            $table->string('end_message_id', 36);
            $table->unsignedInteger('message_count');
            $table->timestamps();

            $table->index('conversation_id');
            $table->unique(['conversation_id', 'sequence_number']);
        });

        Schema::table('agent_conversation_messages', function (Blueprint $table): void {
            $table->string('summary_id', 36)->nullable()->after('meta')->index();
        });

        Schema::table('agent_conversations', function (Blueprint $table): void {
            $table->timestamp('summarization_dispatched_at')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('agent_conversations', function (Blueprint $table): void {
            $table->dropColumn('summarization_dispatched_at');
        });

        Schema::table('agent_conversation_messages', function (Blueprint $table): void {
            $table->dropIndex(['summary_id']);
            $table->dropColumn('summary_id');
        });

        Schema::dropIfExists('conversation_summaries');
    }
};
