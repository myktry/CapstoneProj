<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Black Ember Auth</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Livewire Styles -->
        @livewireStyles

        <!-- CSS -->
        @vite(['resources/css/app.css'])
    </head>
    <body class="font-sans antialiased bg-zinc-950 text-zinc-100">
        <div class="relative min-h-screen overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(251,191,36,0.18),_transparent_45%)]"></div>

            <div class="relative z-10 min-h-screen flex items-center justify-center px-6 py-10">
                <div class="w-full max-w-md">
                    <div class="text-center mb-6">
                        <a href="/" wire:navigate class="inline-flex flex-col items-center gap-2">
                            <span class="text-xs uppercase tracking-[0.35em] text-amber-300">Barbershop</span>
                            <span class="text-2xl font-semibold text-white">Black Ember</span>
                        </a>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-zinc-900/80 p-6 shadow-2xl shadow-black/40 backdrop-blur">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
        @livewireScripts

        <!-- Scripts -->
        @vite(['resources/js/app.js'])
    </body>
</html>
