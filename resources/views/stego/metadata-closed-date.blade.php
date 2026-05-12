@php
    /** @var \App\Models\ClosedDate $closedDate */
    $pngBase64 = (string) ($closedDate->metadata_stego_png_base64 ?? '');
    $dataUrl = $pngBase64 !== '' ? ('data:image/png;base64,'.$pngBase64) : '';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stego Metadata — Closed date #{{ $closedDate->id }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100">
<div class="mx-auto max-w-4xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
    <div>
        <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Admin — Steganography</p>
        <h1 class="mt-2 text-2xl font-semibold text-white">Closed date #{{ $closedDate->id }}</h1>
        <p class="mt-1 text-sm text-zinc-400">
            Decodes `metadata_stego_png_base64` using stegano-kit AES-256-GCM and <code class="text-zinc-300">VITE_STEGO_SECRET</code>.
        </p>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-4 rounded-2xl border border-white/10 bg-zinc-900/60 p-5">
            <h2 class="text-sm font-semibold text-white">Stored stego image</h2>
            @if ($dataUrl === '')
                <p class="rounded-xl border border-white/10 bg-zinc-950 p-4 text-sm text-zinc-400">No metadata PNG stored yet.</p>
            @else
                <img id="stegoImg2" src="{{ $dataUrl }}" alt="" class="w-full rounded-xl border border-white/10 bg-black"/>
            @endif
        </div>
        <div class="space-y-4 rounded-2xl border border-white/10 bg-zinc-900/60 p-5">
            <h2 class="text-sm font-semibold text-white">Decoded JSON</h2>
            <p id="decodeStatus2" class="text-sm text-zinc-400">Waiting…</p>
            <pre id="decodedJson2" class="whitespace-pre-wrap break-words rounded-xl border border-white/10 bg-zinc-950 p-4 text-xs text-zinc-200"></pre>
        </div>
    </div>
</div>

<script>
    (function () {
        const decodeStatus = document.getElementById('decodeStatus2');
        const decodedJson = document.getElementById('decodedJson2');
        const img = document.getElementById('stegoImg2');
        const dataUrl = @json($dataUrl);

        async function decodeNow() {
            if (!dataUrl) {
                decodeStatus.textContent = 'No stego image.';
                return;
            }
            if (!window.StegoDemo?.revealMetadataJsonFromSteganoPngBase64) {
                decodeStatus.textContent = 'StegoDemo bundle missing.';
                return;
            }
            try {
                decodeStatus.textContent = 'Decoding…';
                const pngBase64 = dataUrl.replace(/^data:image\/png;base64,/, '');
                const payload = await window.StegoDemo.revealMetadataJsonFromSteganoPngBase64(pngBase64);
                decodedJson.textContent = JSON.stringify(payload, null, 2);
                decodeStatus.textContent = 'OK.';
            } catch (err) {
                decodedJson.textContent = '';
                decodeStatus.textContent = err?.message ? String(err.message) : String(err);
            }
        }

        if (img && img.complete) decodeNow();
        else if (img) img.addEventListener('load', decodeNow);
        else decodeNow();
    })();
</script>
</body>
</html>
