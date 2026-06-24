@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md border border-white/10 bg-zinc-900/80 px-3 py-2 text-zinc-100 shadow-sm placeholder:text-zinc-500 focus:border-amber-400 focus:ring-amber-400']) }}>
