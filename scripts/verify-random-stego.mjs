import { encode, decode, capacity } from '../resources/js/vendor/stegano-kit/index.js';

function makeImageLike(width, height) {
  const data = new Uint8ClampedArray(width * height * 4);
  // deterministic-ish carrier so we can run in Node without canvas
  for (let i = 0; i < data.length; i += 4) {
    data[i + 0] = (i * 31) & 255;
    data[i + 1] = (i * 17) & 255;
    data[i + 2] = (i * 7) & 255;
    data[i + 3] = 255;
  }
  return { width, height, data };
}

async function main() {
  const password = process.env.VITE_STEGO_SECRET || 'dev-only-secret-key';
  const msg = JSON.stringify({ v: 1, kind: 'smoke', t: Date.now(), note: 'randomized payload scatter' });

  for (const bitsPerChannel of [1, 2, 3]) {
    const img = makeImageLike(256, 256);
    const cap = capacity(img, { bitsPerChannel, channels: ['r', 'g', 'b'] });
    console.log(`bpc=${bitsPerChannel} capacity=${cap.readable}`);

    const encoded = await encode(img, msg, {
      password,
      bitsPerChannel,
      channels: ['r', 'g', 'b'],
      scatterPayload: true,
      randomizeBitSlots: true,
    });

    const out = await decode(encoded, {
      password,
      bitsPerChannel,
      channels: ['r', 'g', 'b'],
      scatterPayload: true,
      randomizeBitSlots: true,
    });

    if (out !== msg) {
      throw new Error(`Mismatch for bpc=${bitsPerChannel}`);
    }
  }

  console.log('OK: randomized encode/decode round-trip passed for bpc=1..3');
}

main().catch((e) => {
  console.error(e);
  process.exitCode = 1;
});

