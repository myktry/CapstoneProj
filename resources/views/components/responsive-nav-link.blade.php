@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-amber-400 text-start text-base font-medium text-amber-300 bg-zinc-900 focus:outline-none focus:text-amber-200 focus:bg-zinc-900 focus:border-amber-300 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900 hover:border-white/20 focus:outline-none focus:text-zinc-200 focus:bg-zinc-900 focus:border-white/20 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
