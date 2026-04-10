@props([
    'post',
])

@php
    $excerpt = $post->body['excerpt'] ?? '';
    $readingTime = $post->body['reading_time'] ?? null;
    $categoryLabel = $post->category?->label() ?? '';
@endphp

<a
    href="{{ $post->locale === 'en' ? route('blog.show', $post->slug) : route('blog.show.locale', ['locale' => $post->locale, 'slug' => $post->slug]) }}"
    class="group flex flex-col bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-primary dark:hover:border-primary"
>
    @if($post->image_url)
        <div class="aspect-video overflow-hidden bg-slate-100 dark:bg-slate-700">
            <img
                src="{{ $post->image_url }}"
                alt="{{ $post->display_name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                loading="lazy"
            />
        </div>
    @else
        <div class="aspect-video bg-linear-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center">
            <svg class="size-12 text-slate-300 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
        </div>
    @endif

    <div class="flex flex-col flex-1 p-5">
        @if($categoryLabel)
            <span class="inline-flex self-start items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary mb-3">
                {{ $categoryLabel }}
            </span>
        @endif

        <h3 class="font-semibold text-slate-900 dark:text-white mb-2 group-hover:text-primary transition-colors line-clamp-2 text-lg">
            {{ $post->display_name }}
        </h3>

        @if($excerpt)
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 line-clamp-2 flex-1">
                {{ $excerpt }}
            </p>
        @endif

        <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-500 mt-auto pt-3 border-t border-slate-100 dark:border-slate-700">
            @if($readingTime)
                <span class="flex items-center gap-1">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $readingTime }} min read
                </span>
            @endif
            <span>{{ $post->created_at->format('M j, Y') }}</span>
        </div>
    </div>
</a>