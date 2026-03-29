<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }} | Todo</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
        <div class="relative isolate flex min-h-screen items-center justify-center overflow-hidden px-6 py-12">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top,_rgba(14,165,233,0.16),transparent_55%),radial-gradient(circle_at_bottom,_rgba(34,197,94,0.14),transparent_45%)]"></div>

            <main class="w-full max-w-3xl rounded-2xl border border-zinc-800/80 bg-zinc-900/70 p-8 shadow-2xl shadow-black/30 backdrop-blur sm:p-12">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Todo App</p>
                <h1 class="mt-4 text-3xl font-semibold leading-tight text-white sm:text-5xl">
                    Organize your day with less noise.
                </h1>
                <p class="mt-5 max-w-xl text-sm leading-7 text-zinc-300 sm:text-base">
                    Capture tasks, track what matters, and finish work without clutter.
                    A focused workspace built for daily planning and quick progress.
                </p>

                <div class="mt-10 flex flex-wrap items-center gap-4">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-sky-500 px-5 py-2.5 text-sm font-semibold text-zinc-950 transition hover:bg-sky-400"
                        >
                            Go to Dashboard
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a
                                href="{{ route('login') }}"
                                class="inline-flex items-center justify-center rounded-lg bg-sky-500 px-5 py-2.5 text-sm font-semibold text-zinc-950 transition hover:bg-sky-400"
                            >
                                Log In
                            </a>
                        @endif

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex items-center justify-center rounded-lg border border-zinc-700 px-5 py-2.5 text-sm font-semibold text-zinc-100 transition hover:border-zinc-500 hover:bg-zinc-800"
                            >
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            </main>
        </div>
    </body>
</html>
