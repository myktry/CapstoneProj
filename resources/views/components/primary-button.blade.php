<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-md border border-amber-400/20 bg-amber-400 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-zinc-950 transition hover:bg-amber-300 focus:bg-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 focus:ring-offset-zinc-950 active:bg-amber-500']) }}>
    {{ $slot }}
</button>
