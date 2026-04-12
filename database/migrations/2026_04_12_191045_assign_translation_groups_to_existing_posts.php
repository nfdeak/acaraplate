<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $posts = DB::table('contents')
            ->where('type', 'post')
            ->orderBy('slug')
            ->orderBy('locale')
            ->get(['id', 'slug', 'locale', 'translation_group']);

        $grouped = $posts->groupBy('slug');

        foreach ($grouped as $translations) {
            $uuid = (string) Str::uuid();

            $ids = $translations->pluck('id')->toArray();

            DB::table('contents')
                ->whereIn('id', $ids)
                ->update(['translation_group' => $uuid]);
        }
    }

    public function down(): void
    {
        DB::table('contents')
            ->where('type', 'post')
            ->update(['translation_group' => null]);
    }
};
