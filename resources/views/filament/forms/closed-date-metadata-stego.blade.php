@props([
    'heheCarrierUrl' => '',
])

<div
    class="rounded-xl border border-amber-500/20 bg-zinc-950/40 p-4"
    wire:key="closed-date-stego-wrap"
    id="closed-date-metadata-stego-ui"
    data-hehe-url="{{ e($heheCarrierUrl) }}"
>
    <p class="text-sm font-semibold text-amber-200">Metadata steganography</p>
    <p class="mt-1 text-xs text-zinc-500">
        Embeds date, type, and note into the mascot carrier image (hehe.png) with stegano-kit AES-256-GCM. Generate,
        then save the form.
    </p>
    <button
        type="button"
        class="mt-3 inline-flex items-center rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-zinc-900 shadow hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 disabled:opacity-50"
        x-data="{ busy: false }"
        x-bind:disabled="busy"
        x-on:click="async () => {
            if (busy || !window.StegoDemo?.regenClosedDateMetadataStego) return;
            busy = true;
            try {
                await window.StegoDemo.regenClosedDateMetadataStego($wire);
            } finally {
                busy = false;
            }
        }"
    >
        <span x-show="!busy">Regenerate stego PNG</span>
        <span x-show="busy" x-cloak>Working…</span>
    </button>
</div>
