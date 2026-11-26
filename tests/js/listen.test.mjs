// Listen to Text: logic tests without a browser.  Run: node --test tests/js/
import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { segment, detectLang, voiceList, voiceRegion, listenPlayer, MAX_CHARS } from '../../resources/js/reader.js';
import { decodeText, docxToText, docxXmlToText, pdfToText, pdfItemsToText, extractText } from '../../resources/js/extract.js';

const fx = (n) => readFileSync(new URL('./fixtures/' + n, import.meta.url));
const ab = (b) => b.buffer.slice(b.byteOffset, b.byteOffset + b.byteLength);

test('language by script: Urdu vs Arabic vs English', () => {
    assert.equal(detectLang('یہ اردو کا جملہ ہے'), 'ur');
    assert.equal(detectLang('اللَّهُمَّ بَارِكْ لَنَا فِيمَا رَزَقْتَنَا'), 'ar');
    assert.equal(detectLang('Hello there'), 'en');
    assert.equal(detectLang('123 ...'), null);
});

test('sentences keep their end marks, Urdu ۔ and ؟ split', () => {
    const s = segment('یہ پہلا جملہ ہے۔ کیا یہ دوسرا ہے؟ Yes. No!');
    assert.deepEqual(s.map((x) => x.text.trim()), ['یہ پہلا جملہ ہے۔', 'کیا یہ دوسرا ہے؟', 'Yes.', 'No!']);
    assert.deepEqual(s.map((x) => x.lang), ['ur', 'ur', 'en', 'en']);
});

test('mixed-language sentence gets two voices in auto mode, one in fixed mode', () => {
    const auto = segment('Eid Mubarak! عید مبارک');
    assert.deepEqual(auto.map((x) => x.lang), ['en', 'ur']);
    // a word with no Urdu-only letters takes the text's language: Urdu text around it → Urdu
    assert.deepEqual(segment('میرا نام ہے۔\nMy name is احمد and I live here.').map((x) => x.lang), ['ur', 'en', 'ur', 'en']);
    assert.deepEqual(segment('My name is احمد.').map((x) => x.lang), ['en', 'ar']);
    assert.equal(segment('My name is احمد and I live here.').map((x) => x.text).join(''), 'My name is احمد and I live here.');
    assert.deepEqual(segment('Eid Mubarak! عید مبارک', 'ur').map((x) => x.lang), ['ur', 'ur']);
});

test('tiny runs like ﷺ stay with their sentence', () => {
    const s = segment('The Prophet ﷺ said this.');
    assert.equal(s.length, 1);
    assert.equal(s[0].lang, 'en');
});

test('long sentence is cut into pieces ≤ 220 chars at commas/spaces, nothing lost', () => {
    const long = Array.from({ length: 60 }, (_, i) => 'word' + i).join(', ') + '.';
    const s = segment(long);
    assert.ok(s.length > 1);
    assert.ok(s.every((x) => x.text.length <= 220));
    assert.equal(s.map((x) => x.text).join(''), long);
    const noSpaces = 'x'.repeat(1000);
    assert.ok(segment(noSpaces).every((x) => x.text.length <= 220));
    assert.equal(segment(noSpaces).map((x) => x.text).join(''), noSpaces);
});

test('paragraph numbers, CRLF, very long text capped', () => {
    const s = segment('One.\r\n\r\nTwo.\nThree.');
    assert.deepEqual(s.map((x) => x.para), [0, 1, 2]);
    const huge = 'a. '.repeat(MAX_CHARS);
    const t = segment(huge);
    assert.ok(t.map((x) => x.text).join('').length <= MAX_CHARS);
    assert.deepEqual(segment(''), []);
    assert.deepEqual(segment('   \n\n  '), []);
});

test('voices: Urdu ur-PK preferred, region only from the tag, never invented', () => {
    const all = [
        { name: 'Google اردو', lang: 'ur-IN', voiceURI: 'g-ur-in', localService: false },
        { name: 'Microsoft Uzma Online (Natural) - Urdu (Pakistan)', lang: 'ur-PK', voiceURI: 'ms-uzma', localService: false },
        { name: 'Samantha', lang: 'en-US', voiceURI: 'sam', localService: true },
        { name: 'Maged', lang: 'ar_SA', voiceURI: 'maged', localService: true },
    ];
    const ur = voiceList(all, 'ur');
    assert.equal(ur[0].id, 'ms-uzma');
    assert.match(ur[0].label, /Pakistan$/);
    assert.match(ur[1].label, /India$/);
    assert.doesNotMatch(ur[1].label, /Pakistan/);
    assert.equal(voiceList(all, 'ar')[0].label, 'Maged — Saudi Arabia');
    assert.equal(voiceRegion('ur'), 'region not stated');
    assert.equal(voiceList(all, 'fr').length, 0);
});

test('TXT: UTF-8, UTF-16 BOM and Windows-1256 fallback', () => {
    assert.match(decodeText(ab(fx('mixed.txt'))), /یہ اردو کا ایک جملہ ہے/);
    assert.match(decodeText(ab(fx('utf16.txt'))), /Eid Mubarak! عید مبارک/);
    assert.equal(decodeText(ab(fx('cp1256.txt'))), 'Café العربية');
});

test('DOCX (made by macOS textutil) → paragraphs', async () => {
    const t = await docxToText(ab(fx('mixed.docx')));
    assert.match(t, /Hello friends\. This is a test of the listening page!/);
    assert.match(t, /یہ اردو کا ایک جملہ ہے۔/);
    assert.match(t, /بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ/);
    assert.match(t, /Eid Mubarak! عید مبارک/);
});

test('PDF line order: LTR line with an RTL run, RTL line with an English word', () => {
    const it = (s, x, w, y = 700) => ({ str: s, transform: [1, 0, 0, 1, x, y], width: w });
    // visual: "Hello [ دنیا پیاری ]" with the Urdu words placed left-to-right on the page
    assert.equal(pdfItemsToText([it('Hello', 10, 30), it('پیاری', 50, 20), it('دنیا', 80, 20), it('An English page here', 10, 90, 680)]), 'Hello دنیا پیاری\nAn English page here');
    // Urdu line containing "BuntyFun com": the English stays left-to-right inside it
    assert.equal(pdfItemsToText([it('ہے', 10, 10), it('BuntyFun', 30, 40), it('com', 75, 15), it('یہ', 100, 10), it('اردو صفحہ کی دوسری لائن یہاں ہے', 10, 120, 680)]), 'یہ BuntyFun com ہے\nاردو صفحہ کی دوسری لائن یہاں ہے');
    // one word split in two fragments that touch
    assert.equal(pdfItemsToText([it('Hel', 10, 15), it('lo', 25, 10), it('world', 45, 25)]), 'Hello world');
    // two lines by y position
    assert.equal(pdfItemsToText([it('One', 10, 20, 700), it('Two', 10, 20, 680)]), 'One\nTwo');
});

test('DOCX XML: entities, tabs, breaks, tracked deletions dropped', () => {
    const xml = '<w:body><w:p><w:r><w:t>A &amp; B</w:t><w:tab/><w:t xml:space="preserve">C </w:t></w:r><w:del><w:r><w:delText>gone</w:delText></w:r></w:del></w:p><w:p><w:r><w:t>D</w:t><w:br/><w:t>E&#1575;</w:t></w:r></w:p></w:body>';
    assert.equal(docxXmlToText(xml), 'A & B\tC \nD\nEا');
});

test('wrong / broken files give a clear message', async () => {
    const f = (name, bytes) => ({ name, size: bytes.length, arrayBuffer: async () => ab(Buffer.from(bytes)) });
    await assert.rejects(extractText(f('a.doc', 'x')), /\.docx/);
    await assert.rejects(extractText(f('a.png', 'x')), /\.txt, \.pdf or \.docx/);
    await assert.rejects(extractText(f('a.docx', 'not a zip at all, sorry')), /not a valid DOCX/);
    await assert.rejects(extractText({ name: 'big.pdf', size: 30 * 1024 * 1024 }), /25 MB/);
});

test('PDF text layer: English (cupsfilter) and Urdu/Arabic (CoreText)', async () => {
    const pdfjs = await import('pdfjs-dist/legacy/build/pdf.mjs');
    const en = await pdfToText(ab(fx('mixed.pdf')), pdfjs);
    assert.match(en.text, /Hello friends/);
    const ur = await pdfToText(ab(fx('urdu.pdf')), pdfjs);
    console.log('   urdu.pdf →', JSON.stringify(ur.text));
    assert.match(ur.text, /^یہ اردو کا ایک جملہ ہے۔ کیا آپ سن سکتے ہیں؟\n/, 'Urdu words back in reading order');
    assert.match(ur.text, /Hello world\./);
    assert.match(ur.text, /اللَّهُم بَارِك لَنَا/, 'Arabic words in reading order (lone sukun/shadda marks are dropped)');
    assert.ok(/[؀-ۿ]/.test(ur.text), 'Arabic-script text found');
    assert.ok(!/[ﭐ-﻿]/.test(ur.text), 'presentation forms normalised');
    await assert.rejects(pdfToText(ab(Buffer.from('%PDF-1.4 broken')), pdfjs), /could not be opened/);
});

// --- player with a fake speechSynthesis --------------------------------------------------------
function fakeSpeech(voices, { neverEnd = false } = {}) {
    const said = [];
    const q = [];
    const ss = {
        speaking: false, pending: false,
        getVoices: () => voices,
        addEventListener() {},
        speak(u) { said.push(u); q.push(u); this.speaking = true; if (!neverEnd) setTimeout(() => { if (q.includes(u)) { q.splice(q.indexOf(u), 1); this.speaking = q.length > 0; u.onend?.(); } }, 5); },
        cancel() { const c = q.splice(0); this.speaking = false; c.forEach((u) => u.onerror?.({ error: 'interrupted' })); },
    };
    globalThis.window = { speechSynthesis: ss, SpeechSynthesisUtterance: class { constructor(t) { this.text = t; } }, addEventListener() {} };
    globalThis.SpeechSynthesisUtterance = window.SpeechSynthesisUtterance;
    const mem = {};
    globalThis.localStorage = { getItem: (k) => (k in mem ? mem[k] : null), setItem: (k, v) => { mem[k] = String(v); } };
    return { ss, said };
}
const wait = (ms) => new Promise((r) => setTimeout(r, ms));
const V = { en: { name: 'Samantha', lang: 'en-US', voiceURI: 'sam' }, ar: { name: 'Maged', lang: 'ar-SA', voiceURI: 'maged' }, ur: { name: 'Uzma', lang: 'ur-PK', voiceURI: 'uzma' } };

test('player: plays all pieces in order with the right voice and speed', async () => {
    const { said } = fakeSpeech([V.en, V.ar, V.ur]);
    const p = listenPlayer({ text: 'One. Two. یہ تین ہے۔' });
    p.lang = 'auto';
    await p.init();
    p.rate = 1.5;
    p.play(0);
    await wait(80);
    assert.equal(p.status, 'done');
    assert.deepEqual(said.map((u) => u.text.trim()), ['One.', 'Two.', 'یہ تین ہے۔']);
    assert.deepEqual(said.map((u) => u.voice.lang), ['en-US', 'en-US', 'ur-PK']);
    assert.equal(said[0].rate, 1.5);
    assert.equal(said[2].rate, 1.5 * 0.9);
});

test('player: pause, resume re-reads the current sentence, stop resets', async () => {
    const { said } = fakeSpeech([V.en], { neverEnd: true });
    const p = listenPlayer({ text: 'One. Two. Three.' });
    p.lang = 'auto';
    await p.init();
    p.play(1);
    assert.equal(said.at(-1).text.trim(), 'Two.');
    p.pause();
    assert.equal(p.status, 'paused');
    assert.equal(p.i, 1);
    p.toggle(); // resume
    assert.equal(p.status, 'playing');
    assert.equal(said.at(-1).text.trim(), 'Two.');
    p.stop();
    assert.equal(p.status, 'idle');
    assert.equal(p.i, 0);
    clearInterval(p._watch);
});

test('player: missing Urdu voice → Urdu skipped (never another accent) and reported', async () => {
    const { said } = fakeSpeech([V.en]);
    const p = listenPlayer({ text: 'Hello. یہ اردو ہے۔ Bye.' });
    p.lang = 'auto';
    await p.init();
    assert.deepEqual(p.missing, ['ur']);
    p.play(0);
    await wait(60);
    assert.deepEqual(said.map((u) => u.text.trim()), ['Hello.', 'Bye.']);
    assert.ok(said.every((u) => u.voice.lang === 'en-US'));
});

test('player: no voice at all for the text → clear error, nothing spoken', async () => {
    const { said } = fakeSpeech([V.en]);
    const p = listenPlayer({ text: 'یہ اردو ہے۔' });
    p.lang = 'auto';
    await p.init();
    p.play(0);
    assert.equal(said.length, 0);
    assert.match(p.error, /no Urdu voice/);
    const q = listenPlayer({ text: '' });
    await q.init();
    q.play();
    assert.match(q.error, /Type, paste or upload/);
});

test('player: voice that never sends "end" does not hang (watchdog moves on)', async () => {
    const { ss, said } = fakeSpeech([V.en], { neverEnd: true });
    const p = listenPlayer({ text: 'One. Two.' });
    p.lang = 'auto';
    await p.init();
    p.play(0);
    ss.speaking = false; // speech engine went quiet without an end event
    await wait(3300);
    assert.ok(said.length >= 2, 'moved on to the second sentence');
    p.stop();
});

test('player: speed change while playing restarts the current sentence at the new rate', async () => {
    const { said } = fakeSpeech([V.en], { neverEnd: true });
    const p = listenPlayer({ text: 'One. Two.' });
    p.lang = 'auto';
    await p.init();
    p.play(0);
    p.rate = 2;
    p.setRate();
    assert.equal(said.at(-1).rate, 2);
    assert.equal(said.at(-1).text.trim(), 'One.');
    p.stop();
});

// --- built-in Urdu voice (eSpeak NG, files in public/vendor/espeak-ur) ------------------------
test('built-in Urdu voice: shipped files synthesize Urdu audio', async () => {
    const { synthUrdu, loadUrduEngine } = await import('../../resources/js/urdu-espeak.js');
    const base = new URL('../../public/vendor/espeak-ur/', import.meta.url).pathname;
    // load once in Node mode (the earlier fake `window` would make the engine think it is in a browser)
    const w = globalThis.window; delete globalThis.window;
    try { await loadUrduEngine(base); } finally { globalThis.window = w; }
    const a = await synthUrdu('یہ اردو کا ایک جملہ ہے۔', 1, base);
    assert.equal(a.sampleRate, 22050);
    assert.ok(a.samples.length > 22050, 'more than a second of audio');
    assert.ok(a.samples.some((s) => Math.abs(s) > 1000), 'not silence');
    const fast = await synthUrdu('یہ اردو کا ایک جملہ ہے۔', 2, base);
    assert.ok(fast.samples.length < a.samples.length * 0.75, '2x is shorter');
});

test('player: no device Urdu voice + audio available → Urdu read by built-in voice, not skipped', async () => {
    const { said } = fakeSpeech([V.en]);
    const played = [];
    globalThis.window.AudioContext = class {
        constructor() { this.state = 'running'; this.destination = {}; }
        createBuffer(c, n, sr) { const d = new Float32Array(n); return { getChannelData: () => d, length: n, sampleRate: sr }; }
        createBufferSource() { const s = { connect() {}, start() { played.push(s.buffer.length); setTimeout(() => s.onended?.(), 5); }, stop() {} }; return s; }
    };
    globalThis.WebAssembly ||= {};
    const eng = await import('../../resources/js/urdu-espeak.js');
    await eng.loadUrduEngine(new URL('../../public/vendor/espeak-ur/', import.meta.url).pathname); // preload from disk
    const p = listenPlayer({ text: 'Hello. یہ اردو ہے۔ Bye.' });
    await p.init();
    assert.deepEqual(p.missing, []);
    assert.equal(p.chosen.ur, eng.URDU_ENGINE_ID);
    p.play(0);
    await wait(400);
    assert.equal(p.status, 'done');
    assert.deepEqual(said.map((u) => u.text.trim()), ['Hello.', 'Bye.']);
    assert.equal(played.length, 1, 'Urdu sentence played through the built-in voice');
    assert.ok(played[0] > 10000);
    delete globalThis.window.AudioContext;
});
