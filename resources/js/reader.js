/**
 * Listen to Text (/listen): splits text into short pieces, reads them one by one with the visitor's
 * own device voice (Web Speech API) and highlights the piece being read. Nothing is sent to a server.
 *
 * Why one short utterance at a time: Chrome stops long utterances after ~15 s, Android caps the text
 * length, and pause()/resume() are unreliable on Android and some Safari versions. So "Pause" cancels
 * and "Resume" re-reads the current sentence, which works the same everywhere.
 */

import { URDU_ENGINE_ID, URDU_ENGINE_LABEL, audioSupported, unlockAudio, speakUrdu, stopUrdu } from './urdu-espeak.js';

export const MAX_CHARS = 300000; // longer texts are cut (a 300 KB text is ~8 hours of listening)
const PIECE = 220; // max characters per utterance

// Arabic-script letters used in Urdu but not in standard Arabic (ٹ ڈ ڑ ں ھ ہ ۂ ۃ ی ے ۓ ک گ چ پ ژ).
const URDU_ONLY = /[ٹڈڑںھہۂۃیےۓکگچپژ]/u;
const ARABIC_SCRIPT = /[؀-ۿݐ-ݿࢠ-ࣿﭐ-﷿ﹰ-﻿]/u;
const LATIN = /[A-Za-zÀ-ɏ]/;
// Letters / marks used in Arabic but not in normal Urdu writing (ي ك ة ى, tanween, sukun).
const ARABIC_ONLY = /[\u064A\u0643\u0629\u0649\u064B-\u064D\u0652]/u;

/**
 * 'ur' | 'ar' | 'en' | null for a piece of text, by script. Urdu-only letters mean Urdu, Arabic-only letters
 * or tanween mean Arabic; a short piece with neither (e.g. "احمد") takes `hint` (the text's main language).
 */
export function detectLang(s, hint = null) {
    const t = String(s || '');
    const ar = (t.match(new RegExp(ARABIC_SCRIPT, 'gu')) || []).length;
    const la = (t.match(/[A-Za-z\u00C0-\u024F]/g) || []).length;
    if (!ar && !la) return null;
    if (ar < la) return 'en';
    if (URDU_ONLY.test(t)) return 'ur';
    if (ARABIC_ONLY.test(t)) return 'ar';
    return hint === 'ur' || hint === 'ar' ? hint : 'ar';
}

/** Split one sentence into runs of the same script, so "Eid Mubarak! عید مبارک" uses two voices. */
function scriptRuns(s) {
    const out = [];
    let cur = '';
    let curKind = null;
    for (const ch of s) {
        const kind = ARABIC_SCRIPT.test(ch) ? 'a' : LATIN.test(ch) ? 'l' : null;
        if (kind && curKind && kind !== curKind && cur.trim()) {
            // keep trailing spaces/punctuation with the run they follow
            out.push(cur);
            cur = '';
        }
        if (kind) curKind = kind;
        cur += ch;
    }
    if (cur.trim()) out.push(cur);
    // merge tiny runs (one or two letters, e.g. "a" or "ﷺ") into a neighbour
    return out.reduce((acc, r) => {
        if (acc.length && r.replace(/[^\p{L}]/gu, '').length <= 2) acc[acc.length - 1] += r;
        else acc.push(r);
        return acc;
    }, []);
}

/** Cut a long sentence at commas / Urdu-Arabic commas / spaces so each piece is ≤ PIECE characters. */
function chunk(s) {
    if (s.length <= PIECE) return [s];
    const out = [];
    let rest = s;
    while (rest.length > PIECE) {
        const win = rest.slice(0, PIECE);
        let cut = Math.max(win.lastIndexOf(', '), win.lastIndexOf('، '), win.lastIndexOf('; '), win.lastIndexOf(': '));
        if (cut < PIECE * 0.4) cut = win.lastIndexOf(' ');
        if (cut < PIECE * 0.3) cut = PIECE - 1;
        out.push(rest.slice(0, cut + 1));
        rest = rest.slice(cut + 1);
    }
    if (rest.trim()) out.push(rest);
    return out;
}

/** Sentences of a paragraph; keeps the end mark ( . ! ? ؟ ۔ ! … ) with its sentence. */
function sentences(p) {
    return p.match(/[^.!?؟۔…\n]+(?:[.!?؟۔…]+["'”’)\]]*|$)/gu) || [p];
}

/**
 * Text → pieces to read: [{ text, lang, para }]. lang is 'auto' (by script, per piece) or a fixed language.
 * Joining the pieces' text gives back the original text (minus blank lines), so the page can show it as is.
 */
export function segment(text, lang = 'auto') {
    const clean = String(text || '').replace(/\r\n?/g, '\n').replace(/[​﻿]/g, '').slice(0, MAX_CHARS);
    const paras = clean.split(/\n\s*\n|\n/).map((p) => p.replace(/[ \t]+/g, ' ').trim()).filter(Boolean);
    // Urdu or Arabic for Arabic-script words that could be either: the paragraph's, else the whole text's
    const textHint = URDU_ONLY.test(clean) ? 'ur' : 'ar';
    const out = [];
    paras.forEach((p, para) => {
        const pHint = URDU_ONLY.test(p) ? 'ur' : ARABIC_ONLY.test(p) ? 'ar' : textHint;
        // a paragraph's main language, used for pieces with no letters of their own (numbers, "…")
        const main = detectLang(p, pHint) || 'en';
        for (const s of sentences(p)) {
            if (!s.trim()) continue;
            let runs = [s];
            if (lang === 'auto') {
                // one voice per script run; neighbouring runs that end up in the same language are joined again
                runs = scriptRuns(s).map((r) => ({ r, l: detectLang(r, pHint) || main }))
                    .reduce((acc, x) => {
                        const last = acc[acc.length - 1];
                        if (last && last.l === x.l) last.r += x.r; else acc.push({ ...x });
                        return acc;
                    }, []).map((x) => x.r);
            }
            for (const r of runs) {
                for (const c of chunk(r)) {
                    if (!c.trim()) continue;
                    out.push({ text: c, lang: lang === 'auto' ? (detectLang(c, pHint) || main) : lang, para });
                }
            }
        }
    });
    return out;
}

/** Text direction for a piece: Urdu and Arabic are right-to-left. */
export const dirOf = (lang) => (lang === 'ur' || lang === 'ar' ? 'rtl' : 'ltr');

export const LANG_NAMES = { en: 'English', ur: 'Urdu', ar: 'Arabic' };

/** Where a voice says it is from, from its language tag only (never guessed from the name). */
export function voiceRegion(langTag) {
    const t = String(langTag || '').replace('_', '-').toUpperCase();
    const region = t.split('-')[1] || '';
    const names = { PK: 'Pakistan', IN: 'India', SA: 'Saudi Arabia', EG: 'Egypt', AE: 'UAE', US: 'US', GB: 'UK', AU: 'Australia', CA: 'Canada', IE: 'Ireland', ZA: 'South Africa', JO: 'Jordan', KW: 'Kuwait', QA: 'Qatar', MA: 'Morocco', DZ: 'Algeria', TN: 'Tunisia', LB: 'Lebanon', SY: 'Syria', IQ: 'Iraq', OM: 'Oman', YE: 'Yemen', BH: 'Bahrain', LY: 'Libya', NZ: 'New Zealand', SG: 'Singapore', NG: 'Nigeria', KE: 'Kenya', PH: 'Philippines' };
    return names[region] || (region ? region : 'region not stated');
}

const QUALITY = /natural|neural|online|premium|enhanced|wavenet|google|siri/i;

/** All device voices of one language, best first: [{ id, label, lang, local }]. */
export function voiceList(all, lang) {
    const list = all.filter((v) => (v.lang || '').toLowerCase().replace('_', '-').startsWith(lang));
    // Urdu: a voice tagged ur-PK first, then the rest (ur-IN …); never relabelled.
    const score = (v) => (lang === 'ur' && /-PK$/i.test(v.lang.replace('_', '-')) ? 0 : 2) + (QUALITY.test(v.name) ? 0 : 1) + (v.localService ? 0.5 : 0);
    return list.sort((a, b) => score(a) - score(b) || a.name.localeCompare(b.name))
        .map((v) => ({
            id: v.voiceURI || v.name,
            label: v.name.replace(/^Microsoft |^Google /, '').replace(/ Online \(Natural\)/, ' (natural)') + ' — ' + voiceRegion(v.lang),
            lang: v.lang,
            local: !!v.localService,
        }));
}

function loadVoices() {
    return new Promise((resolve) => {
        if (typeof window === 'undefined' || !('speechSynthesis' in window)) { resolve([]); return; }
        const now = window.speechSynthesis.getVoices();
        if (now.length) { resolve(now); return; }
        const done = () => resolve(window.speechSynthesis.getVoices());
        window.speechSynthesis.addEventListener('voiceschanged', done, { once: true });
        setTimeout(done, 2000); // some browsers never fire the event
    });
}

const saved = (k, d) => { try { const v = localStorage.getItem('bf_' + k); return v === null ? d : JSON.parse(v); } catch { return d; } };
const save = (k, v) => { try { localStorage.setItem('bf_' + k, JSON.stringify(v)); } catch { /* private mode */ } };

export const RATES = [0.75, 1, 1.25, 1.5, 2];

/** Alpine component for the /listen page. */
export function listenPlayer(initial = {}) {
    return {
        supported: typeof window !== 'undefined' && 'speechSynthesis' in window && 'SpeechSynthesisUtterance' in window,
        text: initial.text || '',
        lang: initial.lang || 'auto', // always automatic by default
        rate: RATES.includes(saved('listen_rate', 1)) ? saved('listen_rate', 1) : 1,
        rates: RATES,
        segs: [],
        i: 0,
        status: 'idle', // idle | playing | paused | done
        voices: { en: [], ur: [], ar: [] },
        chosen: { en: '', ur: '', ar: '' },
        missing: [],
        notice: initial.notice || '',
        error: '',
        loadingUrdu: false,
        engineFailed: false,
        busy: false,
        fileName: '',
        token: 0,
        _all: [],
        _watch: null,

        async init() {
            if (!this.supported) return;
            await this.refreshVoices();
            // Chrome/Edge fill the list later; refresh when it changes (e.g. after installing a voice).
            window.speechSynthesis.addEventListener('voiceschanged', () => this.refreshVoices());
            if (this.text) this.prepare();
            window.addEventListener('pagehide', () => this.stop());
        },

        async refreshVoices() {
            this._all = await loadVoices();
            for (const l of ['en', 'ur', 'ar']) {
                this.voices[l] = voiceList(this._all, l);
                // no Urdu voice on many devices: the built-in one is added (device voices stay first)
                if (l === 'ur' && audioSupported() && !this.engineFailed) this.voices.ur.push({ id: URDU_ENGINE_ID, label: URDU_ENGINE_LABEL, lang: 'ur', local: true });
                const want = saved('voice_' + l);
                this.chosen[l] = this.voices[l].some((v) => v.id === want) ? want : (this.voices[l][0]?.id || '');
            }
        },

        /** Languages in the text and whether this device has a voice for each. */
        get langsInText() {
            return [...new Set(this.segs.map((s) => s.lang))];
        },
        get chars() { return this.text.length; },
        get minutes() {
            // ~14 characters per second at 1x for English; Urdu/Arabic voices are a little slower
            return Math.max(1, Math.round(this.text.replace(/\s+/g, ' ').length / 14 / 60 / this.rate));
        },

        prepare() {
            this.stop();
            this.error = '';
            if (this.text.length > MAX_CHARS) this.notice = 'This text is very long, so only the first ' + MAX_CHARS.toLocaleString() + ' characters will be read.';
            this.segs = segment(this.text, this.lang);
            this.i = 0;
            this.missing = this.langsInText.filter((l) => !this.voices[l]?.length);
        },

        voiceFor(lang) {
            const id = this.chosen[lang];
            if (id === URDU_ENGINE_ID && lang === 'ur' && !this.engineFailed) return { engine: true, lang: 'ur' };
            return this._all.find((v) => (v.voiceURI || v.name) === id) || null;
        },

        setVoice(lang) { save('voice_' + lang, this.chosen[lang]); if (this.status === 'playing') this.play(this.i); },
        setRate() { save('listen_rate', this.rate); if (this.status === 'playing') this.play(this.i); },

        toggle() {
            if (this.status === 'playing') this.pause();
            else this.play(this.status === 'done' ? 0 : this.i);
        },

        play(from = this.i) {
            if (!this.supported) return;
            if (!this.segs.length) this.prepare();
            if (!this.segs.length) { this.error = 'Type, paste or upload some text first.'; return; }
            if (!this.segs.some((s) => this.voiceFor(s.lang))) {
                this.error = 'This device has no ' + this.langsInText.map((l) => LANG_NAMES[l]).join(' or ') + ' voice, so this text cannot be read aloud here.';
                return;
            }
            this.error = '';
            this.token++;
            window.speechSynthesis.cancel();
            stopUrdu();
            if (this.segs.some((s) => this.voiceFor(s.lang)?.engine)) unlockAudio(); // inside the click, for iPhone
            this.i = Math.max(0, Math.min(from, this.segs.length - 1));
            this.status = 'playing';
            this.speakCurrent(this.token);
        },

        speakCurrent(tok) {
            if (tok !== this.token || this.status !== 'playing') return;
            // skip pieces whose language has no voice here (never read them in another accent)
            while (this.i < this.segs.length && !this.voiceFor(this.segs[this.i].lang)) this.i++;
            if (this.i >= this.segs.length) { this.finish(); return; }
            const seg = this.segs[this.i];
            const voice = this.voiceFor(seg.lang);
            if (voice.engine) { this.speakEngine(seg, tok); return; }
            const u = new SpeechSynthesisUtterance(seg.text);
            u.voice = voice;
            u.lang = voice.lang;
            u.rate = this.rate * (seg.lang === 'en' ? 1 : 0.9); // Urdu/Arabic device voices are clearer a little slower
            let ended = false;
            const next = () => {
                if (ended || tok !== this.token) return;
                ended = true;
                clearInterval(this._watch);
                this.i++;
                this.speakCurrent(tok);
            };
            u.onend = next;
            u.onerror = (e) => {
                if (e.error === 'interrupted' || e.error === 'canceled') return; // our own stop/pause
                if (e.error === 'not-allowed') { this.status = 'paused'; this.error = 'The browser blocked speech. Press Play again.'; return; }
                next();
            };
            this.scrollTo(this.i);
            window.speechSynthesis.speak(u);
            // Some voices (Chrome's online ones, Safari) occasionally never send "end": move on if speech stopped.
            clearInterval(this._watch);
            let idle = 0;
            this._watch = setInterval(() => {
                if (tok !== this.token) { clearInterval(this._watch); return; }
                idle = window.speechSynthesis.speaking || window.speechSynthesis.pending ? 0 : idle + 1;
                if (idle >= 3) next();
            }, 1000);
        },

        /** Urdu with the built-in voice (first use downloads it once, ~1.6 MB). */
        speakEngine(seg, tok) {
            clearInterval(this._watch);
            this.scrollTo(this.i);
            this.loadingUrdu = !this._urduReady; // only the first time (download)
            this._urduReady = true;
            speakUrdu(seg.text, this.rate * 0.9, () => tok === this.token, () => { this.loadingUrdu = false; }).then(() => {
                this.loadingUrdu = false;
                if (tok !== this.token) return;
                this.i++;
                this.speakCurrent(tok);
            }).catch(() => {
                this.loadingUrdu = false;
                if (tok !== this.token) return;
                this.engineFailed = true;
                this.voices.ur = this.voices.ur.filter((v) => v.id !== URDU_ENGINE_ID);
                this.chosen.ur = this.voices.ur[0]?.id || '';
                this.missing = this.langsInText.filter((l) => !this.voices[l]?.length);
                this.error = 'The Urdu voice could not be loaded. Check your connection and press Play again.';
                this.speakCurrent(tok); // carry on with the other languages
            });
        },

        pause() {
            if (this.status !== 'playing') return;
            this.token++;
            clearInterval(this._watch);
            window.speechSynthesis.cancel();
            stopUrdu();
            this.status = 'paused';
        },

        stop() {
            this.token++;
            clearInterval(this._watch);
            if (this.supported) window.speechSynthesis.cancel();
            stopUrdu();
            if (this.status !== 'idle') this.i = 0;
            this.status = 'idle';
        },

        finish() {
            clearInterval(this._watch);
            this.status = 'done';
            this.i = this.segs.length - 1;
        },

        prev() { this.play(Math.max(0, this.i - 1)); },
        next() { if (this.i < this.segs.length - 1) this.play(this.i + 1); },
        jump(n) { this.play(n); },

        scrollTo(n) {
            const el = this.$refs?.pane?.querySelector('[data-seg="' + n + '"]');
            if (el && this.$refs.pane) {
                const pane = this.$refs.pane;
                const top = el.offsetTop - pane.offsetTop;
                if (top < pane.scrollTop || top > pane.scrollTop + pane.clientHeight - 40) pane.scrollTo({ top: top - 60, behavior: 'smooth' });
            }
        },

        /** Paragraphs of pieces for the reading pane: [[{ n, text, lang }], …]. */
        get paras() {
            const out = [];
            this.segs.forEach((s, n) => { (out[s.para] ||= []).push({ n, text: s.text, lang: s.lang }); });
            return out.filter(Boolean);
        },

        async load(file) {
            if (!file) return;
            this.error = '';
            this.notice = '';
            this.busy = true;
            this.fileName = file.name;
            try {
                const { extractText } = await import('./extract.js');
                const r = await extractText(file);
                this.text = r.text;
                this.notice = r.note || '';
                this.prepare();
                if (!this.text.trim()) this.error = 'No text was found in this file.';
            } catch (e) {
                this.error = e && e.userMessage ? e.userMessage : 'This file could not be read. Try copying its text and pasting it here.';
            } finally {
                this.busy = false;
                if (this.$refs?.file) this.$refs.file.value = '';
            }
        },

        clear() { this.stop(); this.text = ''; this.segs = []; this.fileName = ''; this.notice = ''; this.error = ''; this.missing = []; },
    };
}
