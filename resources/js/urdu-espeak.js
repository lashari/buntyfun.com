/**
 * Built-in Urdu voice for devices that have none (most iPhones, Chrome on computers).
 * eSpeak NG (GPL-3.0) compiled to WebAssembly, Urdu data only (~0.8 MB + 0.8 MB, cached by the browser),
 * loaded the first time Urdu has to be read. Runs in the visitor's browser; no text leaves the device.
 * It is a basic, robotic computer voice, labelled as such; a device Urdu voice is always preferred.
 */

export const URDU_ENGINE_ID = 'bf-espeak-ur';
export const URDU_ENGINE_LABEL = 'BuntyFun basic Urdu voice (built in)';

const BASE = '/vendor/espeak-ur/';
let workerPromise = null;

/** Load the engine once. `base` lets tests load it from disk. */
export function loadUrduEngine(base = BASE) {
    workerPromise ||= (async () => {
        const Module = (await import(/* @vite-ignore */ base + 'espeak-ur.js')).default;
        const m = await Module({ locateFile: (f) => base + f });
        const w = new m.eSpeakNGWorker();
        w.set_voice('ur');
        return w;
    })().catch((e) => { workerPromise = null; throw e; });
    return workerPromise;
}

/** Urdu text → { samples: Int16Array, sampleRate }. rate 1 = normal. */
export async function synthUrdu(text, rate = 1, base = BASE) {
    const w = await loadUrduEngine(base);
    w.set_rate(Math.round(Math.min(450, Math.max(80, 160 * rate))));
    const parts = [];
    w.synthesize(String(text || ''), (samples) => { if (samples && samples.length) parts.push(samples); return 0; });
    const n = parts.reduce((a, p) => a + p.length, 0);
    const out = new Int16Array(n);
    let o = 0;
    for (const p of parts) { out.set(p, o); o += p.length; }
    return { samples: out, sampleRate: w.get_samplerate() };
}

let ctx = null;
let current = null;
let currentDone = null;

/** Create/resume the audio output. Call it inside a click so iPhone/Safari allow sound. */
export function unlockAudio() {
    try {
        const AC = window.AudioContext || window.webkitAudioContext;
        if (!AC) return;
        ctx ||= new AC();
        if (ctx.state === 'suspended') ctx.resume();
    } catch { /* no audio */ }
}

export function stopUrdu() {
    if (current) { try { current.onended = null; current.stop(); } catch { /* already stopped */ } current = null; }
    if (currentDone) { const d = currentDone; currentDone = null; d(); }
}

/** Speak Urdu with the built-in voice; resolves when finished or stopped. */
export async function speakUrdu(text, rate = 1, isCurrent = () => true, onStart = () => {}) {
    unlockAudio();
    if (!ctx) return;
    const { samples, sampleRate } = await synthUrdu(text, rate);
    if (!samples.length || !isCurrent()) return; // stopped while the voice was loading
    const buf = ctx.createBuffer(1, samples.length, sampleRate);
    const ch = buf.getChannelData(0);
    for (let i = 0; i < samples.length; i++) ch[i] = samples[i] / 32768;
    stopUrdu();
    const src = ctx.createBufferSource();
    src.buffer = buf;
    src.connect(ctx.destination);
    current = src;
    return new Promise((resolve) => {
        currentDone = resolve;
        onStart();
        src.onended = () => { if (current === src) { current = null; currentDone = null; } resolve(); };
        src.start();
    });
}

export const audioSupported = () => typeof window !== 'undefined' && !!(window.AudioContext || window.webkitAudioContext) && typeof WebAssembly !== 'undefined';
