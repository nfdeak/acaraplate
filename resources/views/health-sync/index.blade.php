@section('title', 'Acara Health Sync — Apple Health to Your Plate Dashboard | Now on the App Store')
@section('meta_description', 'Now on the App Store. Acara Health Sync pipes your Apple Health data into Plate with AES-256-GCM end-to-end encryption — glucose, weight, sleep, activity, and 100+ other types, all automatic.')
@section('meta_keywords', 'apple health sync, ios health companion, encrypted health data, health data sync, glucose sync, acara plate')

@section('head')
    <x-json-ld.health-sync />
@endsection

<x-default-layout>
    {{-- Sticky Header --}}
    <header class="sticky top-0 z-50 w-full border-b border-slate-200 bg-white/80 backdrop-blur-md dark:border-slate-700 dark:bg-slate-950/80">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="/" class="flex items-center gap-2 text-xl font-bold text-slate-900 transition-opacity hover:opacity-80 dark:text-white">
                <span class="text-2xl" role="img" aria-label="strawberry">🍓</span>
                <span>Acara Plate</span>
            </a>
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="hidden items-center px-4 py-2 text-sm font-medium text-slate-600 transition-all duration-200 hover:text-slate-900 sm:inline-flex dark:text-slate-400 dark:hover:text-white">
                        Sign in
                    </a>
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                        Start for Free
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <div class="mx-auto my-8 max-w-6xl px-4 sm:px-6 lg:px-8">
        {{-- Hero Section --}}
        <header class="mb-16 text-center">
            <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                <span class="relative flex h-2 w-2" aria-hidden="true">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                Now on the App Store
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl speakable-intro dark:text-white">
                Your Health Data. Your Dashboard.<br class="hidden sm:block"> Zero Manual Entry.
            </h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600 speakable-intro dark:text-slate-400">
                Acara Health Sync reads your Apple Health data, encrypts it on your phone, and sends it straight to your Plate instance. No middleman. No cloud. No hassle.
            </p>
            <div class="mt-6 flex flex-wrap justify-center gap-4 text-sm text-slate-600 dark:text-slate-400">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    End-to-End Encrypted
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    100+ Health Types
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    Open Source
                </span>
            </div>
            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <x-app-store-badge size="lg" />
                <a href="{{ route('health-sync.setup') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-7 py-4 text-base font-semibold text-slate-700 shadow-sm transition-all duration-300 hover:border-slate-300 hover:bg-slate-50 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    See the 5-minute setup
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-500">Free. Requires iPhone on iOS {{ config('plate.health_sync.minimum_ios_version') }} or later.</p>
        </header>

        {{-- Data Flow Visualization --}}
        <section class="mb-16">
            <div class="flex flex-col items-center gap-3 md:flex-row md:justify-center md:gap-0">
                {{-- Apple Health Node --}}
                <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <svg class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">Apple Health</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Your health data</p>
                    </div>
                </div>

                {{-- Arrow 1 --}}
                <div class="flex flex-col items-center md:flex-row">
                    <div class="h-6 w-px bg-slate-300 md:h-px md:w-8 dark:bg-slate-600"></div>
                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">reads</span>
                    <div class="h-6 w-px bg-slate-300 md:h-px md:w-8 dark:bg-slate-600"></div>
                </div>

                {{-- Health Sync Node --}}
                <div class="flex items-center gap-3 rounded-xl border-2 border-emerald-200 bg-emerald-50/50 px-5 py-4 shadow-sm dark:border-emerald-800 dark:bg-emerald-950/20">
                    <svg class="h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">Health Sync</p>
                        <p class="text-xs text-emerald-700 dark:text-emerald-400">Encrypts with AES-256-GCM</p>
                    </div>
                </div>

                {{-- Arrow 2 --}}
                <div class="flex flex-col items-center md:flex-row">
                    <div class="h-6 w-px bg-slate-300 md:h-px md:w-8 dark:bg-slate-600"></div>
                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">sends</span>
                    <div class="h-6 w-px bg-slate-300 md:h-px md:w-8 dark:bg-slate-600"></div>
                </div>

                {{-- Plate Node --}}
                <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <span class="text-2xl" role="img" aria-label="strawberry">🍓</span>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">Acara Plate</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Decrypts & stores securely</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Why Does This Exist? --}}
        <section class="mb-16">
            <h2 class="mb-8 text-center text-2xl font-bold text-slate-900 dark:text-white">Why Does This Exist?</h2>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="relative rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="absolute -top-3 left-6 flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">1</div>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">Apple Health Has No API</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Web apps can't talk to HealthKit. It's an iOS-only sandbox. That's a problem if your nutrition platform lives in a browser.</p>
                </div>
                <div class="relative rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="absolute -top-3 left-6 flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">2</div>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">So We Built a Bridge</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Health Sync is a lightweight iOS app that reads your HealthKit data and securely relays it to Plate. Think of it as a one-way encrypted tunnel.</p>
                </div>
                <div class="relative rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="absolute -top-3 left-6 flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">3</div>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">Smarter Meal Plans, Automatically</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">When Plate's AI has your real activity, sleep, glucose, and vitals, it stops guessing and starts personalizing. Better data means better plans.</p>
                </div>
            </div>
        </section>

        {{-- What Can You Sync? --}}
        <section class="mb-16">
            <div class="mb-8 text-center">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">What Can You Sync?</h2>
                <p class="mt-2 text-slate-600 dark:text-slate-400">Eleven categories of health data, pulled straight from Apple Health. You pick exactly what to share.</p>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                {{-- Glucose --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                    <svg class="mb-2 h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 2h8l-4 7-4-7z" />
                    </svg>
                    <h3 class="font-semibold text-slate-900 dark:text-white">Glucose</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Blood Glucose</p>
                </div>

                {{-- Vitals --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                    <svg class="mb-2 h-6 w-6 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <h3 class="font-semibold text-slate-900 dark:text-white">Vitals</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Heart Rate, HRV, Blood Pressure, SpO2</p>
                </div>

                {{-- Body --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                    <svg class="mb-2 h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <h3 class="font-semibold text-slate-900 dark:text-white">Body</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Weight, BMI, Body Fat %, Height</p>
                </div>

                {{-- Activity --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                    <svg class="mb-2 h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <h3 class="font-semibold text-slate-900 dark:text-white">Activity</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Steps, Active Energy, Exercise, Workouts</p>
                </div>

                {{-- Mobility --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                    <svg class="mb-2 h-6 w-6 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <h3 class="font-semibold text-slate-900 dark:text-white">Mobility</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">VO2 Max, Walking Speed, 6-Min Walk</p>
                </div>

                {{-- Sleep --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                    <svg class="mb-2 h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <h3 class="font-semibold text-slate-900 dark:text-white">Sleep</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Time in Bed, REM, Deep, Core Sleep</p>
                </div>

                {{-- Nutrition --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                    <svg class="mb-2 h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                    </svg>
                    <h3 class="font-semibold text-slate-900 dark:text-white">Nutrition</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Calories, Carbs, Protein, Fat, Fiber</p>
                </div>

                {{-- Reproductive Health --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                    <svg class="mb-2 h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="font-semibold text-slate-900 dark:text-white">Reproductive Health</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Menstrual Flow, Basal Temp, Ovulation</p>
                </div>

                {{-- Hearing --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                    <svg class="mb-2 h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    </svg>
                    <h3 class="font-semibold text-slate-900 dark:text-white">Hearing</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Environmental Audio, Headphone Levels</p>
                </div>

                {{-- Mindfulness --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                    <svg class="mb-2 h-6 w-6 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <h3 class="font-semibold text-slate-900 dark:text-white">Mindfulness</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Mindful Minutes, Time in Daylight</p>
                </div>

                {{-- Medications --}}
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 dark:border-slate-600 dark:bg-slate-800/50">
                    <svg class="mb-2 h-6 w-6 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 2h8l-4 7-4-7z" />
                    </svg>
                    <h3 class="font-semibold text-slate-500 dark:text-slate-400">Medications</h3>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Medication Logs — coming soon</p>
                </div>
            </div>
            <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
                You pick exactly what to share. Nothing syncs without your say-so.
            </p>
        </section>

        {{-- Security Section --}}
        <section class="mb-16 rounded-2xl border border-emerald-200 bg-emerald-50/50 p-8 sm:p-10 dark:border-emerald-800/50 dark:bg-emerald-950/10">
            <div class="mb-8 text-center">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">We Took the Paranoid Approach to Security</h2>
                <p class="mt-2 text-slate-600 dark:text-slate-400">Your health data is encrypted before it ever leaves your phone. Here's how.</p>
            </div>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                {{-- E2E Encryption --}}
                <div class="rounded-xl border border-emerald-200 bg-white p-6 dark:border-emerald-800/50 dark:bg-slate-900">
                    <svg class="mb-3 h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">End-to-End Encryption</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Every piece of health data is encrypted on your iPhone using <strong class="text-slate-800 dark:text-slate-200">AES-256-GCM</strong> before it leaves the device. The server only sees ciphertext in transit.
                    </p>
                </div>

                {{-- Keychain Storage --}}
                <div class="rounded-xl border border-emerald-200 bg-white p-6 dark:border-emerald-800/50 dark:bg-slate-900">
                    <svg class="mb-3 h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Hardware-Level Key Storage</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Your API token and encryption key live in the <strong class="text-slate-800 dark:text-slate-200">iOS Keychain</strong>, backed by the Secure Enclave. That's the same hardware vault that protects Face ID.
                    </p>
                </div>

                {{-- Token Auth --}}
                <div class="rounded-xl border border-emerald-200 bg-white p-6 dark:border-emerald-800/50 dark:bg-slate-900">
                    <svg class="mb-3 h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">One Token, One Device</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Each device gets its own <strong class="text-slate-800 dark:text-slate-200">Sanctum API token</strong> with a single permission: push health data. Revoke it anytime from your Settings.
                    </p>
                </div>
            </div>

            <div class="mt-8 space-y-3">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-sm text-slate-700 dark:text-slate-300">No third-party servers. No cloud relay. No analytics. Data goes straight from your phone to your Plate instance.</p>
                </div>
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-sm text-slate-700 dark:text-slate-300">App-scoped Keychain. Other apps on your phone can't access your credentials.</p>
                </div>
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-sm text-slate-700 dark:text-slate-300">Disconnect from the app or the web. Your call, your data.</p>
                </div>
            </div>
        </section>

        {{-- Open Source Section --}}
        <section class="mb-16 text-center">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Open Source. Because Trust Should Be Verifiable.</h2>
            <p class="mx-auto mt-3 max-w-2xl text-slate-600 dark:text-slate-400">
                Both the iOS app and the Plate backend are fully open source. You can read every line of encryption code, audit the data handling, and self-host the whole thing. No black boxes.
            </p>
        </section>

        {{-- CTA Section --}}
        <section class="mb-16 text-center">
            <div class="rounded-2xl border border-slate-200 bg-white p-10 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Ready to stop typing in your glucose readings?</h2>
                <p class="mt-2 text-slate-600 dark:text-slate-400">Install the app, scan one QR code, and your data flows automatically from that point on.</p>
                <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <x-app-store-badge size="lg" />
                    <a href="{{ route('health-sync.setup') }}"
                        class="inline-flex items-center px-6 py-4 text-sm font-medium text-slate-600 transition-all duration-200 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                        Read the 5-minute setup guide →
                    </a>
                </div>
            </div>
        </section>
    </div>

    <x-footer />
</x-default-layout>
