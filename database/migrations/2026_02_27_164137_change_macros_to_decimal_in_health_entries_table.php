<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::table('health_entries', function (Blueprint $table): void {
            $table->decimal('carbs_grams', 5, 2)->nullable()->change();
            $table->decimal('protein_grams', 5, 2)->nullable();
            $table->decimal('fat_grams', 5, 2)->nullable();
            $table->smallInteger('calories')->nullable();

        });

        Schema::table('user_telegram_chats', function (Blueprint $table): void {
            $table->dropColumn('pending_health_log');
        });
    }
};
