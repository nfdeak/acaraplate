@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('canonical_url', $canonicalUrl)

@section('head')
    <script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "CollectionPage",
    "name": "{{ $pageTitle }}",
    "description": "{{ $pageDescription }}",
    "url": "{{ $canonicalUrl }}",
    "isPartOf": {
        "@@type": "WebSite",
        "name": "Acara Plate",
        "url": "{{ url('/') }}"
    }
}
</script>
    <script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "ItemList",
    "name": "{{ $pageTitle }}",
    "numberOfItems": {{ $posts->total() }},
    "itemListElement": [
        @foreach($posts as $post)
        {
            "@@type": "ListItem",
            "position": {{ $loop->iteration + (($posts->currentPage() - 1) * $posts->perPage()) }},
            "url": "{{ $post->locale === 'en' ? route('blog.show', $post->slug) : route('blog.show.locale', ['locale' => $post->locale, 'slug' => $post->slug]) }}",
            "name": "{{ $post->display_name }}"
        }@unless ($loop->last),@endunless
        @endforeach
    ]
}
</script>
@endsection

<x-mini-app-layout>
    <div class="mx-auto my-16 max-w-7xl px-6 lg:px-8">
        <div class="mt-6">
            <h1 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-4">
                {{ $pageTitle }}
            </h1>
            <p class="text-lg text-slate-600 dark:text-slate-300 mb-10 max-w-3xl">
                {{ $pageDescription }}
            </p>

            {{-- Blog Grid --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                @forelse($posts as $post)
                    @include('blog._card', ['post' => $post])
                @empty
                    <div class="col-span-full text-center py-16">
                        <svg class="mx-auto size-16 text-slate-300 dark:text-slate-600 mb-4" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">No Articles Yet</h3>
                        <p class="text-slate-500 dark:text-slate-400">
                            Check back soon — we're always adding new content.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($posts->hasPages())
                <div class="mt-8">
                    {{ $posts->links() }}
                </div>
            @endif

            {{-- CTA Section --}}
            <div
                class="mt-16 bg-linear-to-r from-primary/10 to-primary/5 dark:from-primary/20 dark:to-primary/10 rounded-2xl p-8">
                <div class="max-w-2xl">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">
                        Want Personalized Nutrition Advice?
                    </h2>
                    <p class="text-slate-600 dark:text-slate-300 mb-6">
                        Our AI nutritionist can analyze your meals, predict blood sugar impact, and create a personalized
                        plan — tailored to your health goals.
                    </p>
                    <a href="{{ route('meet-altani') }}"
                        class="inline-flex items-center px-6 py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-semibold shadow-lg hover:shadow-xl">
                        <svg class="size-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Ask Our AI Nutritionist
                    </a>
                </div>
            </div>
        </div>
    </div>

    <x-footer />
</x-mini-app-layout>
