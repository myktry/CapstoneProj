@props([
    'galleryCarrierUrl' => null,
    'heheCarrierUrl' => '',
    'galleryName' => '',
    'galleryImagePath' => '',
])

<div
    class="rounded-xl border border-amber-500/20 bg-zinc-950/40 p-4"
    wire:key="service-stego-wrap"
    id="service-metadata-stego-ui"
    data-gallery-url="{{ e($galleryCarrierUrl ?? '') }}"
    data-hehe-url="{{ e($heheCarrierUrl) }}"
    data-gallery-name="{{ e($galleryName) }}"
    data-gallery-image-path="{{ e($galleryImagePath) }}"
>
    <p class="text-sm font-semibold text-amber-200">Metadata steganography</p>
    <p class="mt-1 text-xs text-zinc-500">
        Embeds service fields (name, description, price, duration, image paths, gallery name) into a PNG using
        stegano-kit with AES-256-GCM. Generate, then save the form.
    </p>
    <button
        type="button"
        class="mt-3 inline-flex items-center rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-zinc-900 shadow hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 disabled:opacity-50"
        x-data="{ busy: false }"
        x-bind:disabled="busy"
        x-on:click="async () => {
            if (busy || !window.StegoDemo?.regenServiceMetadataStego) return;
            busy = true;
            try {
                await window.StegoDemo.regenServiceMetadataStego($wire);
            } finally {
                busy = false;
            }
        }"
    >
        <span x-show="!busy">Regenerate stego PNG</span>
        <span x-show="busy" x-cloak>Working…</span>
    </button>
</div>
