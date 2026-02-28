@props([
    'name',
    'price',
    'image',
    'description',
    'time',
])

<div data-animate class="group relative overflow-hidden rounded-2xl border border-white/10 bg-zinc-900/70 shadow-xl shadow-black/30">
    <div class="relative">
        <img
            src="{{ $image }}"
            alt="{{ $name }}"
            class="h-56 w-full object-cover transition duration-500 ease-out group-hover:scale-105"
        />
    </div>

    <div class="flex h-full flex-col gap-4 p-6">
        <div>
            <h3 class="text-xl font-semibold text-white">{{ $name }}</h3>
            <p class="mt-2 text-sm text-zinc-400">{{ $description }}</p>
        </div>

        <div class="mt-auto flex flex-wrap items-center gap-3">
            <x-primary-button class="bg-amber-400 text-zinc-900 hover:bg-amber-300 focus:bg-amber-300">
                Book Now
            </x-primary-button>
        </div>
    </div>
</div>
