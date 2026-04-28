@php
    /** @var \App\Models\Appointment $appointment */
    $pngBase64 = (string) ($appointment->customer_stego_png_base64 ?? '');
    $dataUrl = $pngBase64 !== '' ? ('data:image/png;base64,'.$pngBase64) : '';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stego Test — Appointment #{{ $appointment->id }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100">
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8 space-y-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Steganography Test</p>
                <h1 class="mt-2 text-2xl font-semibold text-white">Appointment #{{ $appointment->id }}</h1>
                <p class="mt-1 text-sm text-zinc-400">
                    This page renders the stego PNG from the database and decodes it in-browser.
                </p>
            </div>
            <a href="{{ route('stego.test-latest') }}"
               class="rounded-full border border-white/10 bg-zinc-900 px-4 py-2 text-sm text-zinc-200 hover:bg-zinc-800 transition">
                Open latest
            </a>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-2xl border border-white/10 bg-zinc-900/60 p-5 space-y-4">
                <h2 class="text-sm font-semibold text-white">Stored stego image (DB)</h2>

                @if ($dataUrl === '')
                    <div class="rounded-xl border border-white/10 bg-zinc-950 p-4 text-sm text-zinc-400">
                        No `customer_stego_png_base64` found on this appointment.
                    </div>
                @else
                    <img
                        id="stegoImg"
                        src="{{ $dataUrl }}"
                        alt="Stego PNG"
                        class="w-full rounded-xl border border-white/10 bg-black"
                    />

                    <div class="flex items-center gap-3">
                        <button
                            id="copyDataUrlBtn"
                            type="button"
                            class="rounded-full bg-amber-400 px-4 py-2 text-sm font-semibold text-zinc-900 hover:bg-amber-300 transition"
                        >
                            Copy data URL
                        </button>
                        <p id="copyStatus" class="text-xs text-zinc-500"></p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-white/10 bg-zinc-900/60 p-5 space-y-4">
                <h2 class="text-sm font-semibold text-white">Decoded payload (client-side)</h2>

                <div class="rounded-xl border border-white/10 bg-zinc-950 p-4">
                    <p class="text-xs text-zinc-500">Status</p>
                    <p id="decodeStatus" class="mt-1 text-sm text-zinc-200">Waiting…</p>
                </div>

                <div class="rounded-xl border border-white/10 bg-zinc-950 p-4">
                    <p class="text-xs text-zinc-500">Decoded JSON</p>
                    <pre id="decodedJson" class="mt-2 text-xs text-zinc-200 whitespace-pre-wrap break-words"></pre>
                </div>

                <p class="text-xs text-zinc-500">
                    Note: this uses the current dev key in `resources/js/stego/index.js`.
                </p>
            </div>
        </div>

        <div class="text-sm text-zinc-400">
            <a class="underline underline-offset-4 hover:text-amber-300" href="{{ route('home') }}">Back to home</a>
        </div>
    </div>

    <script>
        (function () {
            const decodeStatus = document.getElementById('decodeStatus');
            const decodedJson = document.getElementById('decodedJson');
            const img = document.getElementById('stegoImg');
            const copyBtn = document.getElementById('copyDataUrlBtn');
            const copyStatus = document.getElementById('copyStatus');

            const dataUrl = @json($dataUrl);

            async function decodeNow() {
                if (!dataUrl) {
                    decodeStatus.textContent = 'No stego image stored for this appointment.';
                    return;
                }
                if (!window.StegoDemo) {
                    decodeStatus.textContent = 'StegoDemo not found. Ensure Vite assets are loading.';
                    return;
                }

                try {
                    decodeStatus.textContent = 'Decoding…';
                    const pngBase64 = dataUrl.replace(/^data:image\/png;base64,/, '');
                    const imageLike = await window.StegoDemo.pngBase64ToImageLike(pngBase64);
                    const payload = await window.StegoDemo.revealUserDataFromImageLike(imageLike);
                    decodedJson.textContent = JSON.stringify(payload, null, 2);
                    decodeStatus.textContent = 'OK (decoded + decrypted).';
                } catch (err) {
                    decodedJson.textContent = '';
                    decodeStatus.textContent = 'Failed: ' + (err && err.message ? err.message : String(err));
                }
            }

            if (copyBtn) {
                copyBtn.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(dataUrl);
                        copyStatus.textContent = 'Copied.';
                    } catch (e) {
                        copyStatus.textContent = 'Copy failed (clipboard permission).';
                    }
                    setTimeout(() => { copyStatus.textContent = ''; }, 1200);
                });
            }

            if (img && img.complete) decodeNow();
            else if (img) img.addEventListener('load', decodeNow);
            else decodeNow();
        })();
    </script>
</body>
</html>

