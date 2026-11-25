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

if (audio) {
    audio.preload = 'auto';
    audio.addEventListener('timeupdate', () => { store.time = audio.currentTime; });
    audio.addEventListener('durationchange', () => { store.duration = audio.duration; });
    audio.addEventListener('playing', () => { store.playing = true; store.loading = false; store.emit(); });
    audio.addEventListener('pause', () => { store.playing = false; store.emit(); });
    audio.addEventListener('waiting', () => { store.loading = true; });
    audio.addEventListener('ended', () => store.ended());
    audio.addEventListener('error', () => {
        if (!audio.getAttribute('src')) return;
        store.loading = false;
        store.playing = false;
        store.error = 'This recording could not be loaded. Check your connection and press play to try again.';
        store.emit();
    });
    if ('mediaSession' in navigator) {
        const ms = navigator.mediaSession;
        ms.setActionHandler('play', () => store.toggle());
        ms.setActionHandler('pause', () => store.toggle());
        ms.setActionHandler('previoustrack', () => store.prev());
        ms.setActionHandler('nexttrack', () => store.next());
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.store('player', store);
    window.BFPlayer = Alpine.store('player');
});

/**
 * Name pronunciation fallback when no reviewed recording exists: the visitor's own
 * device voice (Web Speech API). Returns false when the device has no Arabic/Urdu voice.
 */
export function say(text, lang = 'ar') {
    if (!('speechSynthesis' in window)) return false;
    const voices = window.speechSynthesis.getVoices();
    const voice = voices.find((v) => v.lang && v.lang.toLowerCase().startsWith(lang));
    if (!voice && voices.length) return false;
    window.speechSynthesis.cancel();
    const u = new SpeechSynthesisUtterance(text);
    u.lang = voice ? voice.lang : (lang === 'ar' ? 'ar-SA' : 'ur-PK');
    if (voice) u.voice = voice;
    u.rate = 0.8;
    window.speechSynthesis.speak(u);
    return true;
}

if ('speechSynthesis' in window) window.speechSynthesis.getVoices(); // warm the voice list (loads async in Chrome)

export default store;
