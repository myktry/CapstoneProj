// In-repo fork of `stegano-kit` with optional randomized payload embedding.
// Baseline algorithm: LSB encoding with optional AES-256-GCM (PBKDF2) encryption.
//
// Additions (opt-in):
// - scatterPayload: keep MAGIC+LEN linear, but shuffle payload bit positions
// - randomizeBitSlots: when scattering, shuffle across individual LSB slots (pixel+channel+bitSlot)

const HEADER_BITS = 32;
const MAGIC = 1398031687;

function getChannelIndices(channels) {
  const map = { r: 0, g: 1, b: 2, a: 3 };
  return channels.map((c) => map[c]);
}

function resolveEncodeOpts(opts) {
  return {
    bitsPerChannel: opts?.bitsPerChannel ?? 1,
    channels: opts?.channels ?? ['r', 'g', 'b'],
    password: opts?.password,
    scatterPayload: opts?.scatterPayload ?? false,
    randomizeBitSlots: opts?.randomizeBitSlots ?? false,
  };
}

function resolveDecodeOpts(opts) {
  return {
    bitsPerChannel: opts?.bitsPerChannel ?? 1,
    channels: opts?.channels ?? ['r', 'g', 'b'],
    password: opts?.password,
    scatterPayload: opts?.scatterPayload ?? false,
    randomizeBitSlots: opts?.randomizeBitSlots ?? false,
  };
}

function textToBytes(text) {
  return new TextEncoder().encode(text);
}

function bytesToText(bytes) {
  return new TextDecoder().decode(bytes);
}

function buildBitStream(payloadBytes) {
  const totalBits = HEADER_BITS + HEADER_BITS + payloadBytes.length * 8;
  const bits = new Uint8Array(totalBits);
  let idx = 0;
  for (let i = 31; i >= 0; i--) bits[idx++] = (MAGIC >>> i) & 1;
  for (let i = 31; i >= 0; i--) bits[idx++] = (payloadBytes.length >>> i) & 1;
  for (const byte of payloadBytes) {
    for (let i = 7; i >= 0; i--) bits[idx++] = (byte >>> i) & 1;
  }
  return bits;
}

function readUint32(bits, offset) {
  let value = 0;
  for (let i = 0; i < 32; i++) value = (value << 1) | bits[offset + i];
  return value >>> 0;
}

function maxPayloadBytes(imageData, bitsPerChannel, channelCount) {
  const totalUsableBits = imageData.width * imageData.height * channelCount * bitsPerChannel;
  const headerBits = HEADER_BITS * 2;
  return Math.floor((totalUsableBits - headerBits) / 8);
}

async function deriveKey(password, salt) {
  const enc = new TextEncoder();
  const keyMaterial = await crypto.subtle.importKey('raw', enc.encode(password), 'PBKDF2', false, ['deriveKey']);
  return crypto.subtle.deriveKey(
    { name: 'PBKDF2', salt, iterations: 100000, hash: 'SHA-256' },
    keyMaterial,
    { name: 'AES-GCM', length: 256 },
    false,
    ['encrypt', 'decrypt'],
  );
}

async function encrypt(data, password) {
  const salt = crypto.getRandomValues(new Uint8Array(16));
  const iv = crypto.getRandomValues(new Uint8Array(12));
  const key = await deriveKey(password, salt);
  const cipher = await crypto.subtle.encrypt({ name: 'AES-GCM', iv }, key, data);
  const out = new Uint8Array(salt.length + iv.length + cipher.byteLength);
  out.set(salt, 0);
  out.set(iv, salt.length);
  out.set(new Uint8Array(cipher), salt.length + iv.length);
  return out;
}

async function decrypt(data, password) {
  const salt = data.slice(0, 16);
  const iv = data.slice(16, 28);
  const cipher = data.slice(28);
  const key = await deriveKey(password, salt);
  const plain = await crypto.subtle.decrypt({ name: 'AES-GCM', iv }, key, cipher);
  return new Uint8Array(plain);
}

// --- PRNG / shuffle (deterministic, password-derived seed) ---

async function sha256Bytes(text) {
  const bytes = new TextEncoder().encode(text);
  const digest = await crypto.subtle.digest('SHA-256', bytes);
  return new Uint8Array(digest);
}

async function deriveScatterSeed32({ password, width, height, channels, bitsPerChannel }) {
  const ctx = `${password}|${width}x${height}|${channels.join('')}|${bitsPerChannel}|payloadScatterV1`;
  const digest = await sha256Bytes(ctx);
  // First 32 bits as little-endian uint32
  return (digest[0] | (digest[1] << 8) | (digest[2] << 16) | (digest[3] << 24)) >>> 0;
}

function xorshift32(seed) {
  let x = seed >>> 0;
  return function next() {
    x ^= x << 13;
    x ^= x >>> 17;
    x ^= x << 5;
    // convert to [0,1)
    return ((x >>> 0) / 4294967296);
  };
}

function makePermutation(n, seed32) {
  const perm = new Uint32Array(n);
  for (let i = 0; i < n; i++) perm[i] = i;
  const rnd = xorshift32(seed32 || 1);
  for (let i = n - 1; i > 0; i--) {
    const j = Math.floor(rnd() * (i + 1));
    const tmp = perm[i];
    perm[i] = perm[j];
    perm[j] = tmp;
  }
  return perm;
}

// --- bit addressing helpers (matches original decode extractBits math) ---

function bitPosToLocation(bitPos, bitsPerChannel, channelCount) {
  const px = Math.floor(bitPos / (channelCount * bitsPerChannel));
  const withinPx = bitPos % (channelCount * bitsPerChannel);
  const channelOffset = Math.floor(withinPx / bitsPerChannel);
  const bitInChannel = withinPx % bitsPerChannel;
  return { px, channelOffset, bitInChannel };
}

function setBitInChannelByte(byteValue, bitsPerChannel, bitInChannel, bit) {
  const shift = bitsPerChannel - 1 - bitInChannel;
  const mask = 1 << shift;
  const cleared = byteValue & ~mask;
  return cleared | ((bit & 1) << shift);
}

function getBitFromChannelByte(byteValue, bitsPerChannel, bitInChannel) {
  const shift = bitsPerChannel - 1 - bitInChannel;
  return (byteValue >>> shift) & 1;
}

function writeBitsLinear(out, imageData, channelIdx, bitsPerChannel, startBitPos, bitsArray) {
  const channelCount = channelIdx.length;
  let bitPos = startBitPos;
  for (let i = 0; i < bitsArray.length; i++, bitPos++) {
    const { px, channelOffset, bitInChannel } = bitPosToLocation(bitPos, bitsPerChannel, channelCount);
    const base = px * 4;
    const ci = channelIdx[channelOffset];
    const idx = base + ci;
    out[idx] = setBitInChannelByte(out[idx], bitsPerChannel, bitInChannel, bitsArray[i]);
  }
}

function readBitsLinear(imageData, channelIdx, bitsPerChannel, startBitPos, count) {
  const channelCount = channelIdx.length;
  const bits = new Uint8Array(count);
  let bitPos = startBitPos;
  for (let written = 0; written < count; written++, bitPos++) {
    const { px, channelOffset, bitInChannel } = bitPosToLocation(bitPos, bitsPerChannel, channelCount);
    const base = px * 4;
    const ci = channelIdx[channelOffset];
    bits[written] = getBitFromChannelByte(imageData.data[base + ci], bitsPerChannel, bitInChannel);
  }
  return bits;
}

// --- public API ---

async function encode(imageData, message, options) {
  const { bitsPerChannel, channels, password, scatterPayload, randomizeBitSlots } = resolveEncodeOpts(options);
  if (bitsPerChannel < 1 || bitsPerChannel > 4) {
    throw new RangeError('bitsPerChannel must be between 1 and 4.');
  }

  let payload = textToBytes(message);
  if (password) payload = await encrypt(payload, password);

  const channelIdx = getChannelIndices(channels);
  const cap = maxPayloadBytes(imageData, bitsPerChannel, channelIdx.length);
  if (payload.length > cap) {
    throw new RangeError(
      `Message too large: ${payload.length} bytes, but image can hold at most ${cap} bytes with current settings (${bitsPerChannel} bits/channel, channels: ${channels.join(',')}).`,
    );
  }

  const fullBits = buildBitStream(payload);
  const out = new Uint8ClampedArray(imageData.data);

  // 1) Write header (MAGIC + LEN) linearly.
  const headerBitCount = HEADER_BITS * 2;
  writeBitsLinear(out, imageData, channelIdx, bitsPerChannel, 0, fullBits.slice(0, headerBitCount));

  // 2) Write payload bits either linearly (default) or scattered (opt-in).
  const payloadBits = fullBits.slice(headerBitCount);
  const totalUsableBits = imageData.width * imageData.height * channelIdx.length * bitsPerChannel;
  const payloadStartBitPos = headerBitCount;
  const availableBits = totalUsableBits - payloadStartBitPos;

  if (!scatterPayload) {
    writeBitsLinear(out, imageData, channelIdx, bitsPerChannel, payloadStartBitPos, payloadBits);
    return { width: imageData.width, height: imageData.height, data: out };
  }

  if (!password) {
    throw new Error('scatterPayload requires `password` so encode/decode can derive a deterministic seed.');
  }

  // Scatter only the payload region, leaving header linear.
  // If randomizeBitSlots=false, we scatter by (pixel+channel) cells, and keep bit slots sequential within the cell.
  if (!randomizeBitSlots) {
    const cells = imageData.width * imageData.height * channelIdx.length;
    const seed32 = await deriveScatterSeed32({
      password,
      width: imageData.width,
      height: imageData.height,
      channels,
      bitsPerChannel,
    });
    const perm = makePermutation(cells, seed32);
    const bitsNeeded = payloadBits.length;
    const capacityBits = cells * bitsPerChannel;
    if (bitsNeeded > capacityBits) {
      throw new RangeError('Payload bits exceed capacity in scatter-by-cell mode.');
    }
    let bitPtr = 0;
    for (let i = 0; i < cells && bitPtr < bitsNeeded; i++) {
      const cell = perm[i];
      const px = Math.floor(cell / channelIdx.length);
      const channelOffset = cell % channelIdx.length;
      const base = px * 4;
      const ci = channelIdx[channelOffset];
      const idx = base + ci;
      // write bitsPerChannel bits in order into this channel's LSB group
      for (let bitInChannel = 0; bitInChannel < bitsPerChannel && bitPtr < bitsNeeded; bitInChannel++) {
        out[idx] = setBitInChannelByte(out[idx], bitsPerChannel, bitInChannel, payloadBits[bitPtr++]);
      }
    }
    return { width: imageData.width, height: imageData.height, data: out };
  }

  // randomizeBitSlots=true: scatter across individual bit slots in the payload region.
  if (payloadBits.length > availableBits) {
    throw new RangeError('Payload bits exceed available bit slots.');
  }

  const seed32 = await deriveScatterSeed32({
    password,
    width: imageData.width,
    height: imageData.height,
    channels,
    bitsPerChannel,
  });
  const perm = makePermutation(availableBits, seed32);
  const channelCount = channelIdx.length;
  for (let i = 0; i < payloadBits.length; i++) {
    const rel = perm[i]; // relative bit index within payload region
    const bitPos = payloadStartBitPos + rel;
    const { px, channelOffset, bitInChannel } = bitPosToLocation(bitPos, bitsPerChannel, channelCount);
    const base = px * 4;
    const ci = channelIdx[channelOffset];
    const idx = base + ci;
    out[idx] = setBitInChannelByte(out[idx], bitsPerChannel, bitInChannel, payloadBits[i]);
  }

  return { width: imageData.width, height: imageData.height, data: out };
}

async function decode(imageData, options) {
  const { bitsPerChannel, channels, password, scatterPayload, randomizeBitSlots } = resolveDecodeOpts(options);
  const channelIdx = getChannelIndices(channels);

  // Read header linearly to get MAGIC and LEN.
  const magicBits = readBitsLinear(imageData, channelIdx, bitsPerChannel, 0, HEADER_BITS);
  const magic = readUint32(magicBits, 0);
  if (magic !== MAGIC) {
    throw new Error('No hidden message found (magic header mismatch). Make sure decode options match the ones used during encoding.');
  }

  const lenBits = readBitsLinear(imageData, channelIdx, bitsPerChannel, HEADER_BITS, HEADER_BITS);
  const payloadLen = readUint32(lenBits, 0);
  if (payloadLen < 0 || payloadLen > imageData.width * imageData.height) {
    throw new Error('Invalid payload length detected. The image may not contain a hidden message.');
  }

  const payloadBitCount = payloadLen * 8;
  const headerBitCount = HEADER_BITS * 2;
  const payloadStartBitPos = headerBitCount;

  let payloadBytes;

  if (!scatterPayload) {
    const payloadBits = readBitsLinear(imageData, channelIdx, bitsPerChannel, payloadStartBitPos, payloadBitCount);
    payloadBytes = bitsToBytes(payloadBits);
  } else {
    if (!password) {
      throw new Error('scatterPayload requires `password` so decode can derive a deterministic seed.');
    }

    const totalUsableBits = imageData.width * imageData.height * channelIdx.length * bitsPerChannel;
    const availableBits = totalUsableBits - payloadStartBitPos;
    if (payloadBitCount > availableBits) {
      throw new Error('Invalid payload length detected (exceeds capacity for this image/options).');
    }

    if (!randomizeBitSlots) {
      const cells = imageData.width * imageData.height * channelIdx.length;
      const seed32 = await deriveScatterSeed32({
        password,
        width: imageData.width,
        height: imageData.height,
        channels,
        bitsPerChannel,
      });
      const perm = makePermutation(cells, seed32);
      const bitsOut = new Uint8Array(payloadBitCount);
      let bitPtr = 0;
      for (let i = 0; i < cells && bitPtr < payloadBitCount; i++) {
        const cell = perm[i];
        const px = Math.floor(cell / channelIdx.length);
        const channelOffset = cell % channelIdx.length;
        const base = px * 4;
        const ci = channelIdx[channelOffset];
        const idx = base + ci;
        for (let bitInChannel = 0; bitInChannel < bitsPerChannel && bitPtr < payloadBitCount; bitInChannel++) {
          bitsOut[bitPtr++] = getBitFromChannelByte(imageData.data[idx], bitsPerChannel, bitInChannel);
        }
      }
      payloadBytes = bitsToBytes(bitsOut);
    } else {
      const seed32 = await deriveScatterSeed32({
        password,
        width: imageData.width,
        height: imageData.height,
        channels,
        bitsPerChannel,
      });
      const perm = makePermutation(availableBits, seed32);
      const channelCount = channelIdx.length;
      const bitsOut = new Uint8Array(payloadBitCount);
      for (let i = 0; i < payloadBitCount; i++) {
        const rel = perm[i];
        const bitPos = payloadStartBitPos + rel;
        const { px, channelOffset, bitInChannel } = bitPosToLocation(bitPos, bitsPerChannel, channelCount);
        const base = px * 4;
        const ci = channelIdx[channelOffset];
        bitsOut[i] = getBitFromChannelByte(imageData.data[base + ci], bitsPerChannel, bitInChannel);
      }
      payloadBytes = bitsToBytes(bitsOut);
    }
  }

  if (password) {
    const decrypted = await decrypt(payloadBytes, password);
    return bytesToText(decrypted);
  }
  return bytesToText(payloadBytes);
}

function bitsToBytes(payloadBits) {
  const payloadLen = Math.floor(payloadBits.length / 8);
  const bytes = new Uint8Array(payloadLen);
  for (let i = 0; i < payloadLen; i++) {
    let byte = 0;
    for (let b = 7; b >= 0; b--) {
      byte |= payloadBits[i * 8 + (7 - b)] << b;
    }
    bytes[i] = byte;
  }
  return bytes;
}

function humanReadable(bytes) {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function capacity(imageData, options) {
  const { bitsPerChannel, channels } = resolveEncodeOpts(options);
  const totalBytes = maxPayloadBytes(imageData, bitsPerChannel, channels.length);
  return { totalBytes, readable: humanReadable(totalBytes), width: imageData.width, height: imageData.height };
}

// Keep detect() consistent with upstream: header scan / chi-square on linear LSB positions.
// This fork does not modify the header, so detect remains useful.
function detect(imageData) {
  try {
    const channels = ['r', 'g', 'b'];
    const channelIdx = getChannelIndices(channels);
    const bitsPerChannel = 1;
    const magicBits = readBitsLinear(imageData, channelIdx, bitsPerChannel, 0, HEADER_BITS);
    const magic = readUint32(magicBits, 0);
    if (magic === MAGIC) {
      return { hasHiddenData: true, confidence: 0.99, method: 'lsb', details: 'Magic header detected in LSB positions (RGB, 1 bit/channel).' };
    }
  } catch {
    // ignore
  }
  return { hasHiddenData: false, confidence: 0.5, method: 'lsb', details: 'No magic header detected.' };
}

export { capacity, decode, detect, encode };

