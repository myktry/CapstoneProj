import CryptoJS from 'crypto-js';
import { encode, decode, capacity } from 'stegano-kit';

const DEFAULT_DEV_KEY = 'dev-only-secret-key';

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
  return await encode(imageLike, cipherText, options);
}

export async function extractCiphertext(imageLike, options) {
  return await decode(imageLike, options);
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

export function createCoverImageLike({ width = 300, height = 300 } = {}) {
  const canvas = document.createElement('canvas');
  canvas.width = width;
  canvas.height = height;
  const ctx = canvas.getContext('2d');
  if (!ctx) throw new Error('Canvas 2D context not available.');

  // Fill with opaque pseudo-random noise so the carrier image isn’t a flat color.
  const imageData = ctx.createImageData(width, height);
  const data = imageData.data;
  const rand = new Uint8Array(width * height * 4);

  // Browsers cap getRandomValues to 65536 bytes per call.
  const MAX = 65536;
  for (let offset = 0; offset < rand.length; offset += MAX) {
    crypto.getRandomValues(rand.subarray(offset, Math.min(rand.length, offset + MAX)));
  }

  for (let i = 0; i < data.length; i += 4) {
    data[i + 0] = rand[i + 0]; // r
    data[i + 1] = rand[i + 1]; // g
    data[i + 2] = rand[i + 2]; // b
    data[i + 3] = 255; // a
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

