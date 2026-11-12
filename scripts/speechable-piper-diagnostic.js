/**
 * Speechable / Piper diagnostic: isolate the shortest failing text span.
 * Standalone browser module; deliberately not wired into production.
 * Usage:
 *   import { isolatePiperFailure } from './speechable-piper-diagnostic.js';
 *   const result = await isolatePiperFailure(text, part =>
 *     tts.predict({ text: part, voiceId: 'de_DE-eva_k-x_low' }));
 *   console.log(result);
 *
 * Every probe uses the SAME model and voice. Do not silently delete words
 * or publish synthesized audio until the complete text has been verified.
 */
export async function isolatePiperFailure(text, predict, maxProbes = 48) {
  if (typeof text !== 'string' || typeof predict !== 'function') {
    throw new TypeError('Expected text and an async Piper predict function');
  }
  const normalized = text.normalize('NFC').replace(/\s+/gu, ' ').trim();
  let probes = 0;
  const test = async part => {
    if (++probes > maxProbes) throw new Error('Diagnostic probe limit reached');
    try { await predict(part); return { ok: true }; }
    catch (error) { return { ok: false, error: String(error?.message || error) }; }
  };
  if (!normalized) return { status: 'empty', probes };
  const original = await test(normalized);
  if (original.ok) return { status: 'passed', probes };
  let words = normalized.split(' ');
  let lastError = original.error;
  while (words.length > 1) {
    const mid = Math.ceil(words.length / 2);
    const left = words.slice(0, mid);
    const right = words.slice(mid);
    const l = await test(left.join(' '));
    if (!l.ok) { words = left; lastError = l.error; continue; }
    const r = await test(right.join(' '));
    if (!r.ok) { words = right; lastError = r.error; continue; }
    return {
      status: 'interaction-or-length-dependent',
      failingText: words.join(' '),
      error: lastError,
      probes
    };
  }
  return { status: 'isolated', failingText: words[0], error: lastError, probes };
}
