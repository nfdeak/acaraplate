@section('title', $content->meta_title)
@section('meta_description', $content->meta_description)

@php
    $displayName = $content->display_name;
    $excerpt = $content->body['excerpt'] ?? '';
    $bodyContent = $content->body['content'] ?? '';
    $readingTime = $content->body['reading_time'] ?? null;
    $postUrl = $locale === 'en' ? route('blog.show', $content->slug) : route('blog.show.locale', ['locale' => $locale, 'slug' => $content->slug]);
@endphp

@section('head')
    {{-- hreflang alternate links for multilingual SEO --}}
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $postUrl }}" />
    @foreach($translations as $translation)
        <link rel="alternate" hreflang="{{ $translation->locale }}" href="{{ $translation->locale === 'en' ? route('blog.show', $translation->slug) : route('blog.show.locale', ['locale' => $translation->locale, 'slug' => $translation->slug]) }}" />
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ route('blog.show', $content->slug) }}" />

    <script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Article",
    "headline": "{{ $content->title }}",
    "description": "{{ $content->meta_description }}",
    "image": "{{ $content->image_url ?? asset('banner-acara-plate.webp') }}",
    "inLanguage": "{{ $locale }}",
    "author": {
        "@@type": "Organization",
        "name": "Acara Plate",
        "url": "{{ url('/') }}"
    },
    "publisher": {
        "@@type": "Organization",
        "name": "Acara Plate",
        "logo": {
            "@@type": "ImageObject",
            "url": "{{ asset('apple-touch-icon/apple-touch-icon-180x180.png') }}"
        }
    },
    "datePublished": "{{ $content->created_at->toIso8601String() }}",
    "dateModified": "{{ $content->updated_at->toIso8601String() }}",
    "mainEntityOfPage": {
        "@@type": "WebPage",
        "@id": "{{ $postUrl }}"
    }
}
</script>

    {{-- Language switcher for search engines --}}
    @if($translations->isNotEmpty())
    <script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebPage",
    "name": "{{ $content->title }}",
    "url": "{{ $postUrl }}",
    "speakable": {
        "@@type": "SpeakableSpecification",
        "cssSelector": [".speakable-intro"]
    }
}
</script>
    @endif
@endsection

<x-default-layout>
    <header class="sticky top-0 z-50 w-full py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-100 dark:border-slate-800">
        <a href="/" class="flex items-center gap-2 text-xl font-bold text-slate-900 dark:text-white">
            <span class="text-2xl" role="img" aria-label="strawberry">🍓</span>
            <span>Acara Plate</span>
        </a>
        <div class="flex items-center gap-4">
            @if($translations->isNotEmpty())
                <div class="flex items-center gap-2">
                    @foreach($translations as $translation)
                        <a href="{{ $translation->locale === 'en' ? route('blog.show', $translation->slug) : route('blog.show.locale', ['locale' => $translation->locale, 'slug' => $translation->slug]) }}"
                           class="px-2.5 py-1 text-xs font-medium rounded-full border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                           lang="{{ $translation->locale }}">
                            {{ strtoupper($translation->locale) }}
                        </a>
                    @endforeach
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-primary/10 text-primary border border-primary/20">
                        {{ strtoupper($locale) }}
                    </span>
                </div>
            @endif
            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white">Log in</a>
            <a href="{{ route('register') }}" class="rounded-full bg-slate-900 dark:bg-white px-5 py-2 text-sm font-semibold text-white dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-slate-100 transition-all">
                Get Started
            </a>
        </div>
    </header>

    <div class="mx-auto my-16 max-w-3xl px-6 lg:px-8">
        <article class="mt-6">
            {{-- Category & Reading Time --}}
            <div class="flex items-center gap-3 mb-4">
                @if($content->category)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary">
                        {{ $content->category->label() }}
                    </span>
                @endif
                @if($readingTime)
                    <span class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $readingTime }} min read
                    </span>
                @endif
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    {{ $content->created_at->format('M j, Y') }}
                </span>
            @if($locale !== 'en')
                <span class="px-2 py-0.5 text-xs font-medium rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                    {{ strtoupper($locale) }}
                </span>
            @endif
            </div>

            {{-- H1 Title --}}
            <h1 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-6 speakable-intro">
                {{ $content->title }}
            </h1>

            {{-- Hero Image --}}
            @if($content->image_url)
            <div class="mb-8 rounded-2xl overflow-hidden shadow-lg">
                <img
                    src="{{ $content->image_url }}"
                    alt="{{ $displayName }}"
                    class="w-full h-auto"
                    loading="eager"
                />
            </div>
            @endif

            {{-- Excerpt --}}
            @if($excerpt)
            <div class="mb-8 text-lg text-slate-600 dark:text-slate-300 leading-relaxed border-l-4 border-primary pl-4">
                {{ $excerpt }}
            </div>
            @endif

            {{-- Article Body --}}
            @if($bodyContent)
            <div class="prose prose-slate dark:prose-invert max-w-none mb-10">
                {!! Str::markdown($bodyContent) !!}
            </div>
            @endif

            {{-- Language Switcher (bottom of article) --}}
            @if($translations->isNotEmpty())
            <div class="mt-10 mb-8 p-6 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.966 18.966 0 016.416 6m0 0a18.966 18.966 0 01-1.14-1.686M6.416 6L5.07 3.95M19.95 16.5A18.966 18.966 0 0115.936 12m0 0a18.966 18.966 0 01-1.14-1.686M15.936 12l1.346-2.05M15.936 12L18 15m-3-3l1.346 2.05" />
                    </svg>
                    Read this article in
                </h3>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('blog.show', $content->slug) }}"
                       class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $locale === 'en' ? 'bg-primary text-white' : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600' }} transition-colors"
                       lang="en">
                        English
                    </a>
                    @foreach($translations as $translation)
                        <a href="{{ $translation->locale === 'en' ? route('blog.show', $translation->slug) : route('blog.show.locale', ['locale' => $translation->locale, 'slug' => $translation->slug]) }}"
                           class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $locale === $translation->locale ? 'bg-primary text-white' : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600' }} transition-colors"
                           lang="{{ $translation->locale }}">
                            {{ strtoupper($translation->locale) }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Related Posts / CTA --}}
            <div class="my-10">
                <x-cta-block
                    title="Have questions about {{ $displayName }}?"
                    description="Our AI nutritionist can help you understand how this topic relates to your personal health goals and blood sugar management."
                    button-text="Ask Our Nutritionist"
                    button-url="{{ route('meet-altani') }}"
                />
            </div>
        </article>
    </div>

    <x-footer />
</x-default-layout>