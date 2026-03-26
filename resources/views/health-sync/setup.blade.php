@section('title', 'Setup Guide | Acara Health Sync — Connect in 5 Minutes')
@section('meta_description', 'Step-by-step guide to set up Acara Health Sync. Generate a pairing token, install the iOS app, and start syncing your Apple Health data to Acara Plate.')
@section('meta_keywords', 'health sync setup, acara plate setup guide, apple health pairing, ios health app setup')

@section('head')
    <x-json-ld.health-sync-setup />
@endsection

<x-default-layout>
    <div class="mx-auto my-16 max-w-7xl px-6 lg:px-8">
        <a
            href="{{ route('health-sync') }}"
            class="-mt-10 mb-12 flex items-center text-slate-600 hover:underline dark:text-slate-400"
        >
            <x-icons.chevron-left class="size-4" />
            <span>Back to Health Sync</span>
        </a>

        <div class="mx-auto max-w-3xl">
            {{-- Header --}}
            <header class="mb-12 speakable-intro">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">Set Up Health Sync</h1>
                <p class="mt-3 text-lg text-slate-600 dark:text-slate-400">
                    Five steps, five minutes, and your Apple Health data flows into Plate automatically.
                </p>
            </header>

            {{-- Requirements --}}
            <section class="mb-12">
                <h2 class="mb-4 text-xl font-bold text-slate-900 dark:text-white">Before You Start</h2>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <p class="text-slate-700 dark:text-slate-300"><strong class="text-slate-900 dark:text-white">iPhone running iOS 18.0 or later</strong></p>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <p class="text-slate-700 dark:text-slate-300">
                            <strong class="text-slate-900 dark:text-white">An Acara Plate account</strong> —
                            <a href="{{ route('register') }}" class="text-emerald-600 hover:underline dark:text-emerald-400">create one for free</a> if you haven't yet
                        </p>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <p class="text-slate-700 dark:text-slate-300"><strong class="text-slate-900 dark:text-white">Apple Health with some data</strong> — Apple Watch helps, but isn't required</p>
                    </div>
                </div>
            </section>

            {{-- Steps --}}
            <section class="mb-12 space-y-6">
                {{-- Step 1 --}}
                <div class="relative rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="absolute -top-3 left-6 flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">1</div>
                    <h3 class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">Generate a Pairing Token</h3>
                    <div class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                        <p>Head to <strong class="text-slate-800 dark:text-slate-200">Settings &gt; Mobile Sync</strong> in your Plate dashboard.</p>
                        <p>Hit <strong class="text-slate-800 dark:text-slate-200">"Generate Pairing Token"</strong>. You'll get an 8-character code — it's good for 24 hours.</p>
                        <p>Keep this tab open. You'll need the code (and the QR code) in a moment.</p>
                    </div>
                    @auth
                        <a href="{{ route('mobile-sync.edit') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-emerald-600 hover:underline dark:text-emerald-400">
                            Go to Mobile Sync Settings
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    @endauth
                </div>

                {{-- Step 2 --}}
                <div class="relative rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="absolute -top-3 left-6 flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">2</div>
                    <h3 class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">Get the App</h3>
                    <div class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-900/50 dark:bg-amber-950/30">
                            <p class="text-sm text-amber-800 dark:text-amber-200">
                                <strong>Coming Soon</strong> — Acara Health Sync is currently in development and will be available on the App Store soon. Stay tuned!
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Step 3 --}}
                <div class="relative rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="absolute -top-3 left-6 flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">3</div>
                    <h3 class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">Connect Your Account</h3>
                    <div class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                        <p>Open <strong class="text-slate-800 dark:text-slate-200">Acara Health Sync</strong> on your iPhone.</p>
                        <p>Two ways to connect:</p>
                        <ul class="ml-4 list-disc space-y-1">
                            <li><strong class="text-slate-800 dark:text-slate-200">QR Code</strong> — Scan the code shown on your Mobile Sync page. Easiest way.</li>
                            <li><strong class="text-slate-800 dark:text-slate-200">Manual</strong> — Type in your Plate instance URL and the 8-character token.</li>
                        </ul>
                        <p>Tap <strong class="text-slate-800 dark:text-slate-200">"Connect"</strong>. The app gets a secure API token and encryption key, both locked away in your iOS Keychain.</p>
                    </div>
                </div>

                {{-- Step 4 --}}
                <div class="relative rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="absolute -top-3 left-6 flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">4</div>
                    <h3 class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">Pick Your Data</h3>
                    <div class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                        <p>Choose which health categories to share. Toggle individual types on or off — glucose but not reproductive health, exercise but not hearing. Your call.</p>
                        <p>Tap <strong class="text-slate-800 dark:text-slate-200">"Continue"</strong> and approve the Apple Health permissions prompt.</p>
                        <p>Only the types you selected get read. Everything else stays private.</p>
                    </div>
                </div>

                {{-- Step 5 --}}
                <div class="relative rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="absolute -top-3 left-6 flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">5</div>
                    <h3 class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">You're Syncing</h3>
                    <div class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                        <p>That's it. Your dashboard shows the connection status:</p>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-xs dark:border-slate-600 dark:bg-slate-800">
                            <p class="text-emerald-600 dark:text-emerald-400">&#10003; HealthKit connected</p>
                            <p class="text-emerald-600 dark:text-emerald-400">&#10003; Acara Plate connected</p>
                            <p class="text-slate-500 dark:text-slate-400">&#128336; Last synced: just now</p>
                        </div>
                        <p>Data syncs automatically when you open the app. Want it now? Tap <strong class="text-slate-800 dark:text-slate-200">"Sync Now"</strong> or pull to refresh on the Health Data screen.</p>
                    </div>
                </div>
            </section>

            {{-- Managing Your Connection --}}
            <section class="mb-12">
                <h2 class="mb-4 text-xl font-bold text-slate-900 dark:text-white">Managing Your Connection</h2>
                <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <div class="prose prose-sm prose-slate max-w-none dark:prose-invert">
                        <ul>
                            <li><strong>Change data types:</strong> Settings &gt; Manage Health Data Permissions</li>
                            <li><strong>View sync history:</strong> Logs tab in the app</li>
                            <li><strong>Manual sync:</strong> Dashboard &gt; Sync Now, or Settings &gt; Sync Now</li>
                            <li><strong>View health data:</strong> Settings &gt; Health Data</li>
                            <li><strong>Disconnect from the app:</strong> Settings &gt; Disconnect Account (clears all credentials, keeps Apple Health data intact)</li>
                            <li><strong>Revoke from the web:</strong> Plate Settings &gt; Mobile Sync &gt; delete the device</li>
                        </ul>
                    </div>
                </div>
            </section>

            {{-- Troubleshooting --}}
            <section class="mb-12">
                <h2 class="mb-4 text-xl font-bold text-slate-900 dark:text-white">Troubleshooting</h2>
                <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-slate-900 dark:text-white">Problem</th>
                                <th class="px-4 py-3 font-semibold text-slate-900 dark:text-white">What to Do</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-900">
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">Token expired</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">Generate a fresh one from Settings &gt; Mobile Sync. Tokens last 24 hours.</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">Pairing fails</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">Double-check the instance URL and make sure the token hasn't expired. Your Plate instance needs to be reachable from your phone.</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">No data showing</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">Open Apple Health and verify data is being recorded. Then check your HealthKit permissions in the app settings.</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">Sync fails</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">Make sure your Plate instance is online and the API is responding. Try a manual sync from Settings.</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">Want to re-pair</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">Disconnect from Settings first, then pair again with a new token. Your Apple Health data stays intact.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Back to Landing --}}
            <div class="text-center">
                <a href="{{ route('health-sync') }}" class="text-sm font-medium text-emerald-600 hover:underline dark:text-emerald-400">
                    &larr; Back to Acara Health Sync
                </a>
            </div>
        </div>
    </div>

    <x-footer />
</x-default-layout>
