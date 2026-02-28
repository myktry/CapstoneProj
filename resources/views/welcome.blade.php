<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Black Ember Barbershop</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-zinc-950 text-zinc-100 antialiased">
        @php
            $styles = \App\Models\GalleryItem::query()
                ->active()
                ->get()
                ->map(fn ($item) => [
                    'name' => $item->name,
                    'image' => $item->image
                        ? (str_starts_with($item->image, 'http') ? $item->image : \Illuminate\Support\Facades\Storage::disk('public')->url($item->image))
                        : 'https://images.unsplash.com/photo-1503951458645-643d53bfd90f?q=80&w=1200&auto=format&fit=crop',
                    'description' => $item->description ?: 'Premium grooming style showcase.',
                    'time' => 'Gallery',
                ])->values();
        @endphp

        <livewire:booking-panel />
        
        <div class="min-h-screen">
            <header class="sticky top-0 z-40 border-b border-white/10 bg-zinc-950/80 backdrop-blur">
                <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-amber-300">Barbershop</p>
                        <h1 class="text-lg font-semibold">Black Ember</h1>
                    </div>
                    <nav class="flex items-center gap-8 text-sm uppercase tracking-widest text-zinc-400">
                        <a href="#home" class="transition hover:text-white">Home</a>
                        <a href="#gallery" class="transition hover:text-white">Gallery</a>
                        <a href="#book" class="transition hover:text-white">Book Now</a>
                        <a href="#contact" class="transition hover:text-white">Contact</a>

                        @auth
                            @php
                                $recentBookings = auth()->user()->appointments()
                                    ->with('service')
                                    ->latest()
                                    ->take(5)
                                    ->get();
                                $newCount = auth()->user()->appointments()
                                    ->where('created_at', '>=', now()->subDay())
                                    ->count();
                            @endphp

                            {{-- Notification Bell --}}
                            <details class="relative" id="notif-details">
                                <summary class="list-none cursor-pointer relative flex items-center justify-center w-9 h-9 rounded-full border border-white/10 bg-zinc-900 text-zinc-400 hover:border-amber-400/40 hover:text-amber-300 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    @if($newCount > 0)
                                        <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[9px] font-bold text-black leading-none">
                                            {{ $newCount > 9 ? '9+' : $newCount }}
                                        </span>
                                    @endif
                                </summary>

                                <div class="absolute right-0 mt-2 w-72 rounded-xl border border-white/10 bg-zinc-900 shadow-2xl shadow-black/50 overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                                        <span class="text-xs font-semibold text-zinc-300 uppercase tracking-widest">My Bookings</span>
                                        @if($newCount > 0)
                                            <span class="text-[10px] bg-amber-500/15 text-amber-400 border border-amber-500/30 rounded-full px-2 py-0.5 font-medium">
                                                {{ $newCount }} new
                                            </span>
                                        @endif
                                    </div>

                                    @if($recentBookings->isEmpty())
                                        <div class="px-4 py-6 text-center">
                                            <svg class="w-8 h-8 text-zinc-700 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5"/>
                                            </svg>
                                            <p class="text-xs text-zinc-500 normal-case tracking-normal">No bookings yet</p>
                                        </div>
                                    @else
                                        <ul class="divide-y divide-white/5 max-h-72 overflow-y-auto">
                                            @foreach($recentBookings as $booking)
                                                @php
                                                    $isNew = $booking->created_at >= now()->subDay();
                                                @endphp
                                                <li class="px-4 py-3 flex items-start gap-3 {{ $isNew ? 'bg-amber-500/5' : '' }}">
                                                    <div class="flex-shrink-0 mt-0.5 w-7 h-7 rounded-full flex items-center justify-center
                                                        {{ $booking->status === 'paid' ? 'bg-emerald-500/10 text-emerald-400' : ($booking->status === 'cancelled' ? 'bg-red-500/10 text-red-400' : 'bg-zinc-800 text-zinc-400') }}">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            @if($booking->status === 'paid')
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                            @elseif($booking->status === 'cancelled')
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                            @else
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/>
                                                            @endif
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs font-medium text-zinc-200 normal-case tracking-normal truncate">
                                                            {{ optional($booking->service)->name ?? 'Service' }}
                                                        </p>
                                                        <p class="text-[11px] text-zinc-500 normal-case tracking-normal mt-0.5">
                                                            {{ \Carbon\Carbon::parse($booking->appointment_date)->format('M j, Y') }}
                                                            &middot; {{ \Carbon\Carbon::createFromTimeString($booking->appointment_time)->format('g:i A') }}
                                                        </p>
                                                    </div>
                                                    <span class="flex-shrink-0 text-[10px] font-medium rounded-full px-2 py-0.5 normal-case tracking-normal
                                                        {{ $booking->status === 'paid' ? 'bg-emerald-500/10 text-emerald-400' : ($booking->status === 'cancelled' ? 'bg-red-500/10 text-red-400' : 'bg-zinc-700 text-zinc-400') }}">
                                                        {{ ucfirst($booking->status) }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </details>

                            <details class="relative">
                                <summary class="list-none cursor-pointer rounded-full border border-amber-400/40 bg-zinc-900 px-3 py-2 text-xs text-amber-300 hover:border-amber-300/70 hover:text-amber-200">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </summary>

                                <div class="absolute right-0 mt-2 w-52 rounded-xl border border-white/10 bg-zinc-900 p-2 shadow-2xl shadow-black/40">
                                    <div class="px-3 py-2 text-xs normal-case tracking-normal text-zinc-500">
                                        {{ auth()->user()->email }}
                                    </div>

                                    <a href="{{ route('profile') }}" class="block rounded-lg px-3 py-2 text-xs normal-case tracking-normal text-zinc-200 hover:bg-zinc-800">
                                        View / Edit Profile
                                    </a>

                                    <form method="POST" action="{{ route('logout') }}" class="mt-1">
                                        @csrf
                                        <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-xs normal-case tracking-normal text-zinc-200 hover:bg-zinc-800">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </details>
                        @else
                            <a href="{{ route('login') }}" class="transition hover:text-white">Login</a>
                        @endauth
                    </nav>
                </div>
            </header>

            <section
                id="home"
                class="relative overflow-hidden bg-cover bg-center"
                style="background-image: url('https://images.unsplash.com/photo-1489515217757-5fd1be406fef?q=80&w=1800&auto=format&fit=crop');"
            >
                <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/70 to-zinc-950"></div>
                <div class="relative mx-auto grid w-full max-w-6xl items-center gap-10 px-6 py-20 lg:grid-cols-[1.1fr_0.9fr]">
                    <div data-animate class="space-y-6">
                        <p class="text-xs uppercase tracking-[0.4em] text-amber-300">Precision &amp; Craft</p>
                        <h2 class="text-4xl font-semibold leading-tight text-white sm:text-5xl">Premium grooming for the modern gentleman.</h2>
                        <p class="max-w-xl text-lg text-zinc-400">
                            Elevated fades, tailored beard work, and calm barbershop energy. Walk in for confidence, leave sharper.
                        </p>
                    </div>
                    <div></div>
                </div>
            </section>

            <section id="gallery" class="bg-zinc-950 py-20" data-animate>
                <div class="mx-auto w-full max-w-6xl px-6">
                    <div class="flex flex-wrap items-end justify-between gap-6">
                        <div>
                            <p class="text-xs uppercase tracking-[0.4em] text-amber-300">Gallery</p>
                            <h3 class="mt-3 text-3xl font-semibold text-white">Featured styles</h3>
                        </div>
                        <p class="max-w-xl text-zinc-400">
                            A lookbook of sharp cuts, textured tops, and tailored beards.
                        </p>
                    </div>

                    <div x-data="{
                            page: 0,
                            perPage: 3,
                            items: {{ Js::from($styles) }},
                            sliding: false,
                            direction: 'next',
                            get totalPages() { return Math.ceil(this.items.length / this.perPage); },
                            get currentItems() { return this.items.slice(this.page * this.perPage, (this.page + 1) * this.perPage); },
                            go(dir) {
                                if (this.sliding) return;
                                this.direction = dir;
                                this.sliding = true;
                                setTimeout(() => {
                                    if (dir === 'next') this.page++;
                                    else this.page--;
                                    this.sliding = false;
                                }, 300);
                            }
                        }">

                        {{-- Slide wrapper --}}
                        <div class="mt-10 overflow-hidden">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3 transition-all duration-300"
                                 :style="sliding
                                    ? (direction === 'next'
                                        ? 'opacity:0; transform:translateX(-40px)'
                                        : 'opacity:0; transform:translateX(40px)')
                                    : 'opacity:1; transform:translateX(0)'">
                                <template x-for="(style, index) in currentItems" :key="page + '-' + index">
                                    <div class="group flex flex-col overflow-hidden rounded-2xl border border-white/10 bg-zinc-900 transition hover:border-amber-400/30">
                                        <div class="aspect-[4/3] w-full overflow-hidden bg-zinc-800 flex-shrink-0">
                                            <img :src="style.image" :alt="style.name"
                                                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                        </div>
                                        <div class="flex flex-col flex-1 p-5 min-h-[80px]">
                                            <h4 class="font-semibold text-white leading-snug" x-text="style.name"></h4>
                                            <p class="mt-1 text-sm text-zinc-400 line-clamp-2" x-text="style.description"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Pagination controls --}}
                        <div x-show="totalPages > 1" class="mt-8 flex items-center justify-center gap-4">
                            <button @click="go('prev')" :disabled="page === 0 || sliding"
                                :class="page === 0 ? 'opacity-30 cursor-not-allowed' : 'hover:border-amber-400/40 hover:text-amber-300'"
                                class="flex items-center gap-2 rounded-full border border-white/10 bg-zinc-900 px-4 py-2 text-sm text-zinc-300 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                                Prev
                            </button>

                            <div class="flex items-center gap-2">
                                <template x-for="i in totalPages" :key="i">
                                    <button @click="if(!sliding){ direction = i-1 > page ? 'next' : 'prev'; sliding=true; setTimeout(()=>{ page=i-1; sliding=false; }, 300); }"
                                        :class="page === i - 1
                                            ? 'w-3 h-3 rounded-full bg-amber-400 scale-110'
                                            : 'w-2 h-2 rounded-full bg-zinc-600 hover:bg-zinc-400'"
                                        class="transition-all duration-200">
                                    </button>
                                </template>
                            </div>

                            <button @click="go('next')" :disabled="page >= totalPages - 1 || sliding"
                                :class="page >= totalPages - 1 ? 'opacity-30 cursor-not-allowed' : 'hover:border-amber-400/40 hover:text-amber-300'"
                                class="flex items-center gap-2 rounded-full border border-white/10 bg-zinc-900 px-4 py-2 text-sm text-zinc-300 transition">
                                Next
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section id="book" class="bg-zinc-950 pb-20" data-animate>
                <div class="mx-auto w-full max-w-6xl px-6">
                    <div class="rounded-3xl border border-white/10 bg-gradient-to-br from-zinc-900/80 to-zinc-950 p-10 shadow-2xl">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.4em] text-amber-300">Book Now</p>
                                <h3 class="mt-3 text-3xl font-semibold text-white">Ready for a fresh look?</h3>
                                <p class="mt-4 max-w-xl text-zinc-400">
                                    Walk-ins are welcome, but reserving a seat keeps your schedule smooth. Choose your style and we will handle the rest.
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-4">
                                @auth
                                    <button type="button" onclick="window.Livewire.dispatch('open-booking')" class="rounded-full bg-amber-400 px-6 py-3 text-sm font-semibold uppercase tracking-widest text-zinc-900 transition hover:bg-amber-300 focus:bg-amber-300">
                                        Schedule Appointment
                                    </button>
                                @else
                                    <a href="{{ route('book.appointment') }}" class="rounded-full bg-amber-400 px-6 py-3 text-sm font-semibold uppercase tracking-widest text-zinc-900 transition hover:bg-amber-300 focus:bg-amber-300">
                                        Schedule Appointment
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="contact" class="bg-zinc-950 pb-20" data-animate>
                <div class="mx-auto w-full max-w-6xl px-6">
                    <div class="flex flex-wrap items-end justify-between gap-6">
                        <div>
                            <p class="text-xs uppercase tracking-[0.4em] text-amber-300">Contact</p>
                            <h3 class="mt-3 text-3xl font-semibold text-white">Visit the shop</h3>
                        </div>
                        <p class="max-w-xl text-zinc-400">
                            We are ready to sharpen your style. Reach out anytime.
                        </p>
                    </div>

                    <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-zinc-900/70 p-6">
                            <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Location</p>
                            <p class="mt-3 text-lg text-white">123 Ember Street</p>
                            <p class="text-zinc-400">Downtown, PH 1000</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-zinc-900/70 p-6">
                            <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Hours</p>
                            <p class="mt-3 text-lg text-white">Mon - Sat: 10 AM - 8 PM</p>
                            <p class="text-zinc-400">Sun: 12 PM - 6 PM</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-zinc-900/70 p-6">
                            <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Get in Touch</p>
                            <p class="mt-3 text-lg text-white">+63 900 000 0000</p>
                            <p class="text-zinc-400">hello@blackember.com</p>
                        </div>
                    </div>
                </div>
            </section>

            <footer class="border-t border-white/10 py-10 text-center text-sm text-zinc-500">
                © 2026 Black Ember Barbershop. All rights reserved.
            </footer>
        </div>

        @livewireScripts

        @if (request()->boolean('booking'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    window.Livewire.dispatch('open-booking');
                });
            </script>
        @endif

        {{-- Close other <details> dropdowns when one opens --}}
        <script>
            document.querySelectorAll('details').forEach(details => {
                details.addEventListener('toggle', () => {
                    if (details.open) {
                        document.querySelectorAll('details').forEach(other => {
                            if (other !== details) other.removeAttribute('open');
                        });
                    }
                });
            });
        </script>
    </body>
</html>
