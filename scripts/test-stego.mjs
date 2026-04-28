import { randomBytes } from 'node:crypto';
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

  // CryptoJS sanity check
  const cipher = encryptPayload(payload, key);
  const plain = decryptPayload(cipher, key);
  if (plain.name !== payload.name || plain.email !== payload.email) {
    throw new Error(`Crypto roundtrip failed: ${JSON.stringify(plain)}`);
  }

  // Stego capacity + roundtrip
  const cover = makeImageLike(300, 300);
  const cap = stegCapacity(cover);
  if (!cap?.totalBytes || cap.totalBytes <= 0) {
    throw new Error(`Unexpected capacity: ${JSON.stringify(cap)}`);
  }

  const encoded = await hideUserDataInImageLike(cover, payload, key);
  const decoded = await revealUserDataFromImageLike(encoded, key);

  if (decoded.name !== payload.name || decoded.email !== payload.email) {
    throw new Error(`Stego roundtrip failed: ${JSON.stringify(decoded)}`);
  }

  console.log('OK: crypto + stego roundtrip succeeded.');
  console.log('Capacity:', cap);
}

main().catch((err) => {
  console.error(err);
  process.exitCode = 1;
});

