// One audio player for the whole site (Quran, naats, name pronunciations).
// Pages call window.BFPlayer.load(queue, startIndex, options); the sticky bar
// (resources/views/components/audio-player.blade.php) renders this Alpine store.
// Recordings are played exactly as served; nothing is generated or altered.
import Alpine from 'alpinejs';

const audio = typeof Audio !== 'undefined' ? new Audio() : null;
let preloader = null;

const fmt = (s) => {
    if (!isFinite(s) || s < 0) return '0:00';
    const m = Math.floor(s / 60);
    return m + ':' + String(Math.floor(s % 60)).padStart(2, '0');
};

const store = {
    queue: [],          // [{ src, title, subtitle, key }]
    index: -1,
    playing: false,
    loading: false,
    error: '',
    time: 0,
    duration: 0,
    rate: 1,
    volume: 1,
    muted: false,
    visible: false,
    allowSpeed: true,   // false for Quran recitation
    repeatEach: 1,      // play each track N times (0 = endlessly)
    loopAll: false,     // start the queue again at the end
    played: 0,          // times the current track has finished
    get current() { return this.queue[this.index] || null; },
    get timeText() { return fmt(this.time); },
    get durationText() { return fmt(this.duration); },
    get progress() { return this.duration ? (this.time / this.duration) * 100 : 0; },

    load(queue, start = 0, opts = {}) {
        this.queue = queue.filter((t) => t && t.src);
        this.allowSpeed = opts.allowSpeed !== false;
        if (!this.allowSpeed) this.setRate(1);
        if (opts.repeatEach !== undefined) this.repeatEach = opts.repeatEach;
        if (opts.loopAll !== undefined) this.loopAll = opts.loopAll;
        this.visible = true;
        this.play(start);
    },
    play(i) {
        if (!audio || i < 0 || i >= this.queue.length) return;
        this.index = i;
        this.played = 0;
        this.error = '';
        this.loading = true;
        audio.src = this.queue[i].src;
        audio.playbackRate = this.rate;
        audio.play().catch(() => { this.loading = false; this.playing = false; });
        this.announce();
        this.preloadNext();
    },
    toggle() {
        if (!audio || !this.current) return;
        if (this.error) { this.play(this.index); return; } // retry the same track after a load error
        audio.paused ? audio.play().catch(() => {}) : audio.pause();
    },
    next() { if (this.index + 1 < this.queue.length) this.play(this.index + 1); else if (this.loopAll) this.play(0); },
    prev() { if (audio && audio.currentTime > 3) { audio.currentTime = 0; return; } if (this.index > 0) this.play(this.index - 1); },
    seek(pct) { if (audio && this.duration) audio.currentTime = (pct / 100) * this.duration; },
    skip(sec) { if (audio) audio.currentTime = Math.max(0, Math.min(this.duration || 0, audio.currentTime + sec)); },
    setRate(r) { this.rate = Number(r) || 1; if (audio) audio.playbackRate = this.rate; },
    setVolume(v) { this.volume = Number(v); if (audio) { audio.volume = this.volume; audio.muted = this.muted = this.volume === 0; } },
    toggleMute() { if (!audio) return; this.muted = !this.muted; audio.muted = this.muted; },
    close() { if (audio) { audio.pause(); audio.removeAttribute('src'); audio.load(); } this.visible = false; this.playing = false; this.index = -1; this.emit(); },
    ended() {
        this.played++;
        if (this.repeatEach === 0 || this.played < this.repeatEach) {
            audio.currentTime = 0;
            audio.play().catch(() => {});
            return;
        }
        if (this.index + 1 < this.queue.length) this.play(this.index + 1);
        else if (this.loopAll) this.play(0);
        else { this.playing = false; this.emit(); }
    },
    preloadNext() {
        const n = this.queue[this.index + 1];
        if (!n || typeof Audio === 'undefined') return;
        preloader = new Audio();
        preloader.preload = 'auto';
        preloader.src = n.src;
    },
    emit() {
        window.dispatchEvent(new CustomEvent('bf-player', { detail: { key: this.current ? this.current.key : null, playing: this.playing } }));
    },
    announce() {
        this.emit();
        const t = this.current;
        if (t && 'mediaSession' in navigator && window.MediaMetadata) {
            navigator.mediaSession.metadata = new window.MediaMetadata({ title: t.title, artist: t.subtitle || 'BuntyFun', album: t.album || '' });
        }
    },
};

// Audio events must change the reactive Alpine store (window.BFPlayer), not the plain object:
// writes to the plain object never reach the player bar (it stayed on "verse 3, 0:00" while verse 7 played).
const live = () => window.BFPlayer || store;

if (audio) {
    audio.preload = 'auto';
    audio.addEventListener('timeupdate', () => { live().time = audio.currentTime; });
    audio.addEventListener('durationchange', () => { live().duration = audio.duration; });
    audio.addEventListener('playing', () => { const s = live(); s.playing = true; s.loading = false; s.emit(); });
    audio.addEventListener('pause', () => { const s = live(); s.playing = false; s.emit(); });
    audio.addEventListener('waiting', () => { live().loading = true; });
    audio.addEventListener('ended', () => live().ended());
    audio.addEventListener('error', () => {
        if (!audio.getAttribute('src')) return;
        const s = live();
        s.loading = false;
        s.playing = false;
        s.error = 'This recording could not be loaded. Check your connection and press play to try again.';
        s.emit();
    });
    if ('mediaSession' in navigator) {
        const ms = navigator.mediaSession;
        ms.setActionHandler('play', () => live().toggle());
        ms.setActionHandler('pause', () => live().toggle());
        ms.setActionHandler('previoustrack', () => live().prev());
        ms.setActionHandler('nexttrack', () => live().next());
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.store('player', store);
    window.BFPlayer = Alpine.store('player');
});

/**
 * Device-voice fallback when no reviewed recording exists (Web Speech API).
 * Only ever uses a voice of the right language: never reads Arabic with an English voice.
 * Natural / neural voices are preferred; the visitor can pick another one (saved on this device).
 */
import { audioSupported, unlockAudio, speakUrdu, stopUrdu } from './urdu-espeak.js';

const QUALITY = /natural|neural|online|premium|enhanced|wavenet|google|siri|hamed|zariyah|majed|maged|laila|tarik|mariam|shakir|hoda|naayf/i;

function loadVoices() {
    return new Promise((resolve) => {
        if (!('speechSynthesis' in window)) { resolve([]); return; }
        const now = window.speechSynthesis.getVoices();
        if (now.length) { resolve(now); return; }
        const done = () => resolve(window.speechSynthesis.getVoices());
        window.speechSynthesis.addEventListener('voiceschanged', done, { once: true });
        setTimeout(done, 1500); // some browsers never fire the event
    });
}

/** Voices for a language, best first: [{ id, label }]. */
export async function voicesFor(lang = 'ar') {
    const list = (await loadVoices()).filter((v) => (v.lang || '').toLowerCase().replace('_', '-').startsWith(lang));
    const score = (v) => (QUALITY.test(v.name) ? 0 : 1) + (v.localService ? 0.5 : 0);
    return list.sort((a, b) => score(a) - score(b) || a.name.localeCompare(b.name))
        .map((v) => ({ id: v.voiceURI || v.name, label: v.name.replace(/^Microsoft |^Google /, '').replace(/ Online \(Natural\)/, ' (natural)') + ' · ' + v.lang }));
}

async function pickVoice(lang) {
    const all = (await loadVoices()).filter((v) => (v.lang || '').toLowerCase().replace('_', '-').startsWith(lang));
    if (!all.length) return null;
    let saved = null;
    try { saved = JSON.parse(localStorage.getItem('bf_voice_' + lang)); } catch { /* ignore */ }
    const best = (await voicesFor(lang))[0];
    return all.find((v) => (v.voiceURI || v.name) === saved) || all.find((v) => (v.voiceURI || v.name) === best.id);
}

/**
 * Speak text, or a list of parts in different languages one after another:
 * say([{ text: 'الرَّحْمَٰنُ', lang: 'ar' }, { text: 'The Most Merciful', lang: 'en' }]).
 * A part whose language has no voice on this device is skipped (never read in another accent).
 * Returns 'voice' if anything was spoken, else 'none'.
 */
export async function say(text, lang = 'ar') {
    const parts = Array.isArray(text) ? text : [{ text, lang }];
    // Urdu with no device voice → the built-in Urdu voice (via speakSeq, which reads part by part)
    if (parts.some((p) => p && p.lang === 'ur') && audioSupported()) {
        unlockAudio();
        if (!(await pickVoice('ur'))) return speakSeq(parts);
    }
    if (!('speechSynthesis' in window)) return 'none';
    const ready = [];
    for (const p of parts) {
        if (!p || !String(p.text || '').trim()) continue;
        const voice = await pickVoice(p.lang || 'en');
        if (voice) ready.push([p.text, voice]);
    }
    if (!ready.length) return 'none';
    window.speechSynthesis.cancel();
    for (const [t, voice] of ready) {
        const u = new SpeechSynthesisUtterance(t);
        u.voice = voice;
        u.lang = voice.lang;
        u.rate = voice.lang.toLowerCase().startsWith('en') ? 0.95 : 0.85;
        window.speechSynthesis.speak(u); // the browser queues these, so they play in order
    }
    return 'voice';
}

let seqId = 0;
let seqAudio = null;

export function stopVoice() {
    seqId++;
    if ('speechSynthesis' in window) window.speechSynthesis.cancel();
    if (seqAudio) { seqAudio.pause(); seqAudio = null; }
    stopUrdu();
}

/** Female or male for pre-recorded Urdu voices (saved site-wide). */
export function urduVoice(v) {
    if (v) { try { localStorage.setItem('bf_urdu_voice', JSON.stringify(v)); } catch { /* ignore */ } return v; }
    try { return JSON.parse(localStorage.getItem('bf_urdu_voice')) || 'female'; } catch { return 'female'; }
}

const speakOne = (text, voice) => new Promise((resolve) => {
    const u = new SpeechSynthesisUtterance(text);
    u.voice = voice; u.lang = voice.lang;
    u.rate = voice.lang.toLowerCase().startsWith('en') ? 0.95 : 0.85;
    u.onend = resolve; u.onerror = resolve;
    window.speechSynthesis.speak(u);
});

const playOne = (src) => new Promise((resolve) => {
    if (typeof Audio === 'undefined') { resolve(); return; }
    seqAudio = new Audio(src);
    seqAudio.onended = resolve; seqAudio.onerror = resolve;
    seqAudio.play().catch(resolve);
});

/**
 * Read parts one after another: [{ text, lang, src: { female, male } }].
 * A part with a recording plays it (chosen voice, else the other one); otherwise the device voice of that
 * language is used; a part with neither is skipped. Returns 'audio' | 'voice' | 'none'.
 */
export async function speakSeq(parts) {
    stopVoice();
    if (parts.some((p) => p && p.lang === 'ur') && audioSupported()) unlockAudio(); // still inside the click
    const id = seqId;
    const plan = [];
    for (const p of parts) {
        if (!p || !String(p.text || '').trim()) continue;
        if (p.src && (p.src.female || p.src.male)) { const g = urduVoice(); plan.push(['audio', p.src[g] || p.src.female || p.src.male]); continue; }
        const v = 'speechSynthesis' in window ? await pickVoice(p.lang || 'en') : null;
        if (v) plan.push(['voice', p.text, v]);
        else if ((p.lang || 'en') === 'ur' && audioSupported()) plan.push(['urdu', p.text]); // built-in Urdu voice
    }
    if (!plan.length) return 'none';
    const kind = plan.some((x) => x[0] === 'audio') ? 'audio' : 'voice';
    (async () => {
        for (const step of plan) {
            if (id !== seqId) return;
            if (step[0] === 'audio') await playOne(step[1]);
            else if (step[0] === 'urdu') await speakUrdu(step[1], 0.9, () => id === seqId).catch(() => {});
            else await speakOne(step[1], step[2]);
        }
    })();
    return kind;
}

if ('speechSynthesis' in window) window.speechSynthesis.getVoices(); // warm the voice list (loads async in Chrome)

export default store;
