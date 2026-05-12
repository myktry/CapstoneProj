import CryptoJS from 'crypto-js';
import { encode, decode, capacity } from '../vendor/stegano-kit/index.js';

const DEFAULT_DEV_KEY = 'dev-only-secret-key';

/** Public branding carrier (same-origin path). */
export const STEGO_PUBLIC_HEHE_IMAGE_PATH = '/img/hehe.png';

const MIN_CARRIER_EDGE_PX = 512;
const MAX_CARRIER_EDGE_PX = 4096;
const DEFAULT_ENCODE_CHANNELS = ['r', 'g', 'b'];

export function getSteganographySecret() {
  return import.meta.env.VITE_STEGO_SECRET ?? DEFAULT_DEV_KEY;
}

export function encryptPayload(payload, key = DEFAULT_DEV_KEY) {
  const json = JSON.stringify(payload);
  return CryptoJS.AES.encrypt(json, key).toString();
}

export function decryptPayload(cipherText, key = DEFAULT_DEV_KEY) {
  const bytes = CryptoJS.AES.decrypt(cipherText, key);
  const json = bytes.toString(CryptoJS.enc.Utf8);
  if (!json) {
    throw new Error('Unable to decrypt payload (empty result).');
  }
  return JSON.parse(json);
}

export function stegCapacity(imageLike, options) {
  return capacity(imageLike, options);
}

export async function embedCiphertext(imageLike, cipherText, options) {
  return encode(imageLike, cipherText, options);
}

export async function extractCiphertext(imageLike, options) {
  return decode(imageLike, options);
}

export async function hideUserDataInImageLike(imageLike, { name, email }, key = DEFAULT_DEV_KEY, stegOptions) {
  const payload = email === undefined ? { name } : { name, email };
  const cipherText = encryptPayload(payload, key);
  return await embedCiphertext(imageLike, cipherText, stegOptions);
}

export async function revealUserDataFromImageLike(imageLike, key = DEFAULT_DEV_KEY, stegOptions) {
  const cipherText = await extractCiphertext(imageLike, stegOptions);
  return decryptPayload(cipherText, key);
}

/**
 * Upscale RGBA pixels by an integer factor. nearest=true keeps pixel-art crisp (hehe.png).
 */
export function upscaleImageLikeInteger(imageLike, factor, nearestNeighbor = false) {
  if (factor < 1 || !Number.isInteger(factor)) {
    throw new Error('scale factor must be a positive integer');
  }
  const sw = imageLike.width;
  const sh = imageLike.height;
  const dw = sw * factor;
  const dh = sh * factor;

  const srcCanvas = document.createElement('canvas');
  srcCanvas.width = sw;
  srcCanvas.height = sh;
  const sctx = srcCanvas.getContext('2d');
  if (!sctx) throw new Error('Canvas 2D context not available.');
  const srcData = imageLike.data instanceof ImageData ? imageLike.data : new ImageData(imageLike.data, sw, sh);
  sctx.putImageData(srcData, 0, 0);

  const dstCanvas = document.createElement('canvas');
  dstCanvas.width = dw;
  dstCanvas.height = dh;
  const dctx = dstCanvas.getContext('2d');
  if (!dctx) throw new Error('Canvas 2D context not available.');
  dctx.imageSmoothingEnabled = !nearestNeighbor;
  dctx.drawImage(srcCanvas, 0, 0, dw, dh);

  return dctx.getImageData(0, 0, dw, dh);
}

export async function loadUrlToImageLike(url) {
  const img = new Image();
  img.crossOrigin = 'anonymous';
  img.decoding = 'async';
  img.src = url;
  await img.decode();

  const canvas = document.createElement('canvas');
  canvas.width = img.naturalWidth || img.width;
  canvas.height = img.naturalHeight || img.height;
  const ctx = canvas.getContext('2d');
  if (!ctx) throw new Error('Canvas 2D context not available.');
  ctx.drawImage(img, 0, 0);
  return ctx.getImageData(0, 0, canvas.width, canvas.height);
}

function integerScaleCandidates(width, height) {
  const m = Math.min(width, height);
  const start = Math.max(1, Math.ceil(MIN_CARRIER_EDGE_PX / m));
  const scales = [];
  let s = start;
  while (s * Math.max(width, height) <= MAX_CARRIER_EDGE_PX) {
    scales.push(s);
    s *= 2;
  }
  if (scales.length === 0) {
    scales.push(start);
  }
  return scales;
}

/**
 * Encode JSON using stegano-kit AES-256-GCM (password); retry upscale + bits/channel.
 */
export async function embedJsonWithSteganoKit(imageLike, jsonString, password, nearestNeighbor) {
  let lastError;
  for (const bitsPerChannel of [1, 2, 3]) {
    const scales = integerScaleCandidates(imageLike.width, imageLike.height);
    for (const scale of scales) {
      const scaled = scale === 1 ? cloneImageData(imageLike) : upscaleImageLikeInteger(imageLike, scale, nearestNeighbor);
      try {
        return await encode(scaled, jsonString, {
          password,
          bitsPerChannel,
          channels: DEFAULT_ENCODE_CHANNELS,
          scatterPayload: true,
          randomizeBitSlots: true,
        });
      } catch (e) {
        if (e instanceof RangeError) {
          lastError = e;
        } else {
          throw e;
        }
      }
    }
  }
  throw lastError ?? new RangeError('Message too large for image even after upscaling.');
}

function cloneImageData(imageLike) {
  const { width, height, data } = imageLike;
  const copy = new Uint8ClampedArray(data);
  return { width, height, data: copy };
}

export function serviceMetadataPayloadFromForm(fields) {
  return {
    v: 1,
    kind: 'service',
    serviceId: fields.serviceId ?? null,
    name: fields.name ?? '',
    description: fields.description ?? '',
    price: fields.price != null ? String(fields.price) : '',
    durationMinutes: Number(fields.durationMinutes) || 0,
    imagePath: fields.imagePath ?? '',
    galleryName: fields.galleryName ?? '',
    galleryImagePath: fields.galleryImagePath ?? '',
  };
}

export function closedDateMetadataPayloadFromForm(fields) {
  return {
    v: 1,
    kind: 'closed_date',
    closedDateId: fields.closedDateId ?? null,
    date: fields.date ?? '',
    type: fields.type ?? '',
    note: fields.note ?? '',
  };
}

export async function embedServiceMetadataPngBase64({ carrierAbsoluteUrl, nearestNeighbor, formSnapshot }) {
  const secret = getSteganographySecret();
  const carrier = await loadUrlToImageLike(carrierAbsoluteUrl);
  const payload = serviceMetadataPayloadFromForm(formSnapshot);
  const encoded = await embedJsonWithSteganoKit(carrier, JSON.stringify(payload), secret, nearestNeighbor);
  return imageLikeToPngBase64(encoded);
}

export async function embedClosedDateMetadataPngBase64({ carrierAbsoluteUrl, nearestNeighbor, formSnapshot }) {
  const secret = getSteganographySecret();
  const carrier = await loadUrlToImageLike(carrierAbsoluteUrl);
  const payload = closedDateMetadataPayloadFromForm(formSnapshot);
  const encoded = await embedJsonWithSteganoKit(carrier, JSON.stringify(payload), secret, nearestNeighbor);
  return imageLikeToPngBase64(encoded);
}

export async function revealMetadataJsonFromSteganoPngBase64(pngBase64) {
  const secret = getSteganographySecret();
  const imageLike = await pngBase64ToImageLike(pngBase64);
  for (const bitsPerChannel of [1, 2, 3]) {
    try {
      const text = await decode(imageLike, {
        password: secret,
        bitsPerChannel,
        channels: DEFAULT_ENCODE_CHANNELS,
        scatterPayload: true,
        randomizeBitSlots: true,
      });
      return JSON.parse(text);
    } catch {
      // try next encoding variant
    }
  }
  throw new Error('Could not decode steganographic metadata (wrong secret or not a stegano-kit AES payload).');
}

export function createCoverImageLike({ width = 300, height = 300 } = {}) {
  const canvas = document.createElement('canvas');
  canvas.width = width;
  canvas.height = height;
  const ctx = canvas.getContext('2d');
  if (!ctx) throw new Error('Canvas 2D context not available.');

  const imageData = ctx.createImageData(width, height);
  const data = imageData.data;
  const rand = new Uint8Array(width * height * 4);

  const MAX = 65536;
  for (let offset = 0; offset < rand.length; offset += MAX) {
    crypto.getRandomValues(rand.subarray(offset, Math.min(rand.length, offset + MAX)));
  }

  for (let i = 0; i < data.length; i += 4) {
    data[i + 0] = rand[i + 0];
    data[i + 1] = rand[i + 1];
    data[i + 2] = rand[i + 2];
    data[i + 3] = 255;
  }
  ctx.putImageData(imageData, 0, 0);

  return ctx.getImageData(0, 0, width, height);
}

export function imageLikeToPngBase64(imageLike) {
  const { width, height, data } = imageLike;
  const canvas = document.createElement('canvas');
  canvas.width = width;
  canvas.height = height;
  const ctx = canvas.getContext('2d');
  if (!ctx) throw new Error('Canvas 2D context not available.');
  const imgData = data instanceof ImageData ? data : new ImageData(data, width, height);
  ctx.putImageData(imgData, 0, 0);
  const dataUrl = canvas.toDataURL('image/png');
  return dataUrl.replace(/^data:image\/png;base64,/, '');
}

export async function pngBase64ToImageLike(pngBase64) {
  const img = new Image();
  img.decoding = 'async';
  img.src = `data:image/png;base64,${pngBase64}`;
  await img.decode();

  const canvas = document.createElement('canvas');
  canvas.width = img.naturalWidth || img.width;
  canvas.height = img.naturalHeight || img.height;
  const ctx = canvas.getContext('2d');
  if (!ctx) throw new Error('Canvas 2D context not available.');
  ctx.drawImage(img, 0, 0);
  return ctx.getImageData(0, 0, canvas.width, canvas.height);
}
