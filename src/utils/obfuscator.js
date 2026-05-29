const SALT = 0x7e39a1f2; // 32-bit key for XOR cipher

export function encodeId(id) {
  if (id === null || id === undefined) return '';
  const num = parseInt(id, 10);
  if (isNaN(num)) return id;
  const unsigned = (num ^ SALT) >>> 0;
  return unsigned.toString(36);
}

export function decodeId(hash) {
  if (!hash) return null;
  const parsed = parseInt(hash, 36);
  if (isNaN(parsed)) return hash;
  const original = (parsed ^ SALT) >>> 0;
  return original;
}
