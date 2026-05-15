import { randomBytes } from 'node:crypto';
import { encode } from '../resources/js/vendor/stegano-kit/index.js';
import {
  hideUserDataInImageLike,
  revealUserDataFromImageLike,
  encryptPayload,
  decryptPayload,
  stegCapacity,
} from '../resources/js/stego/index.js';

function makeImageLike(width, height) {
  const size = width * height * 4;
  const buf = randomBytes(size);
  const data = new Uint8ClampedArray(size);
  for (let i = 0; i < size; i += 4) {
    data[i + 0] = buf[i + 0];
    data[i + 1] = buf[i + 1];
    data[i + 2] = buf[i + 2];
    data[i + 3] = 255;
  }
  return { width, height, data };
}

async function main() {
  const key = 'dev-only-secret-key';
  const payload = { name: 'Ada Lovelace', email: 'ada@example.com' };

  const cipher = encryptPayload(payload, key);
  const plain = decryptPayload(cipher, key);
  if (plain.name !== payload.name || plain.email !== payload.email) {
    throw new Error(`Crypto roundtrip failed: ${JSON.stringify(plain)}`);
  }

  // Pipeline B-style embed (scatter + stegano-kit AES); 512px avoids canvas upscale in Node
  const cover = makeImageLike(512, 512);
  const cap = stegCapacity(cover);
  if (!cap?.totalBytes || cap.totalBytes <= 0) {
    throw new Error(`Unexpected capacity: ${JSON.stringify(cap)}`);
  }

  const encoded = await hideUserDataInImageLike(cover, payload, key);
  const decoded = await revealUserDataFromImageLike(encoded, key);

  if (decoded.name !== payload.name || decoded.email !== payload.email) {
    throw new Error(`Stego roundtrip failed: ${JSON.stringify(decoded)}`);
  }

  console.log('OK: Pipeline B-style user stego roundtrip succeeded.');
  console.log('Capacity:', cap);

  // Legacy Pipeline A: CryptoJS + linear LSB; reveal must fall back when scatter decode fails
  const legacyCover = makeImageLike(300, 300);
  const legacyCipher = encryptPayload({ name: 'Legacy User' }, key);
  const legacyEncoded = await encode(legacyCover, legacyCipher, { bitsPerChannel: 1, channels: ['r', 'g', 'b'] });
  const legacyDecoded = await revealUserDataFromImageLike(legacyEncoded, key);

  if (legacyDecoded.name !== 'Legacy User') {
    throw new Error(`Legacy fallback decode failed: ${JSON.stringify(legacyDecoded)}`);
  }

  console.log('OK: legacy CryptoJS + linear LSB fallback decode succeeded.');
}

main().catch((err) => {
  console.error(err);
  process.exitCode = 1;
});
