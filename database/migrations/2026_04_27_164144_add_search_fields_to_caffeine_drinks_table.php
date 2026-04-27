<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caffeine_drinks', function (Blueprint $table): void {
            $table->json('aliases')->nullable()->after('category');
            $table->text('search_text')->nullable()->after('aliases');

            if (DB::getDriverName() === 'pgsql') {
                $table->vector('embedding', 1536)->nullable()->after('search_text');
            } else {
                $table->json('embedding')->nullable()->after('search_text');
            }

            $table->index('search_text');
        });
    }

    public function down(): void
    {
        Schema::table('caffeine_drinks', function (Blueprint $table): void {
            $table->dropIndex(['search_text']);
            $table->dropColumn(['aliases', 'search_text', 'embedding']);
        });
    }
};
