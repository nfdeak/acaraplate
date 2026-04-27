<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'light') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="canonical" href="{{ strtok(url()->current(), '?') }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "light" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>
        <script>
            window.addEventListener('load', function() {
                const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                const sessionTimezone = '{{ session()->get('timezone', 'UTC') }}';

                if (timezone !== sessionTimezone) {
                    fetch('{{ route('profile.timezone.update') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ timezone: timezone })
                    });
                }
            });
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <x-inertia::head>
            @if (($page['component'] ?? '') === 'caffeine-calculator')
                <title>Coffee Caffeine Calculator: How Much Is Too Much? - {{ config('app.name') }}</title>
                <meta data-inertia="description" name="description" content="Free caffeine calculator: estimate your safe daily caffeine dose and find out when to stop drinking coffee for better sleep.">
                <meta data-inertia="keywords" name="keywords" content="caffeine calculator, safe caffeine dose, caffeine sleep cutoff, coffee calculator, caffeine half life">
                <x-json-ld.caffeine-calculator />
            @else
                <title>{{ config('app.name') }}</title>
            @endif
        </x-inertia::head>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicons/favicon-32x32.png" type="image/png">
        <link rel="apple-touch-icon" href="/apple-touch-icon/apple-touch-icon-180x180.png">
        <link rel="manifest" href="/build//manifest.webmanifest">
        <meta name="theme-color" content="#ffffff">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500" rel="stylesheet" />

        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
