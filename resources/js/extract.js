/**
 * Text from an uploaded file, read inside the visitor's browser only (the file is never uploaded).
 * TXT: decoded as UTF-8 (UTF-16 by its BOM; Windows-1256 if it is not valid UTF-8, for old Urdu/Arabic files).
 * DOCX: the zip is opened here and word/document.xml read (no library).
 * PDF: pdf.js (Apache-2.0), loaded only when a PDF is chosen. Scanned PDFs have no text and are not OCR'd.
 */

export const MAX_BYTES = 25 * 1024 * 1024;
const MAX_PDF_PAGES = 500;

const fail = (msg) => Object.assign(new Error(msg), { userMessage: msg });

export function decodeText(bytes) {
    const b = new Uint8Array(bytes);
    if (b[0] === 0xff && b[1] === 0xfe) return new TextDecoder('utf-16le').decode(b);
    if (b[0] === 0xfe && b[1] === 0xff) return new TextDecoder('utf-16be').decode(b);
    try {
        return new TextDecoder('utf-8', { fatal: true }).decode(b).replace(/^﻿/, '');
    } catch {
        return new TextDecoder('windows-1256').decode(b); // Urdu/Arabic files saved by old Windows editors
    }
}

const ENT = { amp: '&', lt: '<', gt: '>', quot: '"', apos: "'" };
const unescapeXml = (s) => s.replace(/&(#x[0-9a-f]+|#\d+|amp|lt|gt|quot|apos);/gi, (m, e) => {
    if (e[0] === '#') return String.fromCodePoint(e[1] === 'x' || e[1] === 'X' ? parseInt(e.slice(2), 16) : parseInt(e.slice(1), 10));
    return ENT[e.toLowerCase()] ?? m;
});

/** Plain text of a WordprocessingML document.xml: one line per paragraph, tabs and breaks kept. */
export function docxXmlToText(xml) {
    const body = xml.replace(/<w:(?:del|instrText|delText)\b[\s\S]*?<\/w:(?:del|instrText|delText)>/g, ''); // tracked deletions, field codes
    const paras = body.match(/<w:p[\s>][\s\S]*?<\/w:p>|<w:p\/>/g) || [];
    return paras.map((p) => {
        let line = '';
        for (const m of p.matchAll(/<w:t(?:\s[^>]*)?>([\s\S]*?)<\/w:t>|<w:(tab|br|cr)\b[^>]*\/>/g)) {
            line += m[1] !== undefined ? unescapeXml(m[1]) : m[2] === 'tab' ? '\t' : '\n';
        }
        return line;
    }).join('\n').replace(/\n{3,}/g, '\n\n').trim();
}

/** One file from a zip archive (stored or deflate), using the browser's own DecompressionStream. */
export async function unzipEntry(buf, name) {
    const v = new DataView(buf);
    let eocd = -1;
    for (let i = buf.byteLength - 22; i >= Math.max(0, buf.byteLength - 65557); i--) {
        if (v.getUint32(i, true) === 0x06054b50) { eocd = i; break; }
    }
    if (eocd < 0) throw fail('This is not a valid DOCX file.');
    const count = v.getUint16(eocd + 10, true);
    let p = v.getUint32(eocd + 16, true);
    const dec = new TextDecoder();
    for (let k = 0; k < count; k++) {
        if (v.getUint32(p, true) !== 0x02014b50) break;
        const method = v.getUint16(p + 10, true);
        const csize = v.getUint32(p + 20, true);
        const nlen = v.getUint16(p + 28, true);
        const xlen = v.getUint16(p + 30, true);
        const clen = v.getUint16(p + 32, true);
        const local = v.getUint32(p + 42, true);
        const fname = dec.decode(new Uint8Array(buf, p + 46, nlen));
        if (fname === name) {
            const start = local + 30 + v.getUint16(local + 26, true) + v.getUint16(local + 28, true);
            const data = new Uint8Array(buf, start, csize);
            if (method === 0) return dec.decode(data);
            if (method !== 8) throw fail('This DOCX uses a compression this browser cannot open.');
            if (typeof DecompressionStream === 'undefined') throw fail('This browser is too old to open DOCX files. Copy the text from Word and paste it here.');
            const out = new Blob([data]).stream().pipeThrough(new DecompressionStream('deflate-raw'));
            return await new Response(out).text();
        }
        p += 46 + nlen + xlen + clen;
    }
    return null;
}

export async function docxToText(buf) {
    const xml = await unzipEntry(buf, 'word/document.xml');
    if (xml === null) throw fail('This file has no Word text in it. Is it really a .docx file?');
    return docxXmlToText(xml);
}

/** Text of a PDF's text layer, page by page. */
export async function pdfToText(buf, pdfjs) {
    let doc;
    const task = pdfjs.getDocument({ data: new Uint8Array(buf), isEvalSupported: false, disableFontFace: true, useSystemFonts: false });
    try {
        doc = await task.promise;
    } catch (e) {
        await task.destroy().catch(() => {});
        if (e && e.name === 'PasswordException') throw fail('This PDF is password-protected. Open it, copy the text and paste it here.');
        throw fail('This PDF could not be opened. It may be damaged.');
    }
    const pages = Math.min(doc.numPages, MAX_PDF_PAGES);
    const out = [];
    for (let n = 1; n <= pages; n++) {
        const page = await doc.getPage(n);
        const tc = await page.getTextContent();
        out.push(pdfItemsToText(tc.items));
        page.cleanup();
    }
    const total = doc.numPages;
    await task.destroy();
    // Arabic "presentation form" letters (common in PDFs) → normal letters so voices can read them
    const text = out.filter(Boolean).join('\n\n').normalize('NFKC');
    const note = total > MAX_PDF_PAGES ? 'Only the first ' + MAX_PDF_PAGES + ' pages were read.' : '';
    return { text, note };
}

const RTL_CHAR = /[\u0590-\u08FF\uFB1D-\uFDFF\uFE70-\uFEFF]/u;
const LTR_CHAR = /[A-Za-z0-9\u00C0-\u024F]/;
const MARKS_ONLY = /^[\s\u064B-\u065F\u0670\u06D6-\u06ED]*$/u;

/**
 * pdf.js text items of one page → text in reading order. PDFs place words by position, and Urdu/Arabic
 * words often come out left-to-right (reversed). Lines are rebuilt from x positions: fragments of one word
 * are joined, then right-to-left runs are put back in reading order (a simple word-level bidi).
 */
export function pdfItemsToText(items) {
    const lines = [];
    let cur = [];
    let y = null;
    for (const it of items) {
        if (typeof it.str !== 'string') continue;
        const iy = it.transform ? it.transform[5] : 0;
        if (cur.length && y !== null && Math.abs(iy - y) > 3 && it.str.trim() && !MARKS_ONLY.test(it.str)) { lines.push(cur); cur = []; }
        if (it.str.trim() && !MARKS_ONLY.test(it.str)) { cur.push({ s: it.str, x: it.transform ? it.transform[4] : 0, w: it.width || 0 }); y = iy; }
        if (it.hasEOL) { lines.push(cur); cur = []; y = null; }
    }
    lines.push(cur);
    // base direction from the whole page (a mostly-Urdu page is right-to-left even on a mixed line)
    const all = lines.flat().map((t) => t.s).join(' ');
    const base = (all.match(new RegExp(RTL_CHAR, 'gu')) || []).length > (all.match(/[A-Za-z\u00C0-\u024F]/g) || []).length ? 'r' : 'l';
    return lines.map((l) => orderLine(l, base)).join('\n').replace(/\n{3,}/g, '\n\n').trim();
}

function orderLine(tokens, base) {
    if (!tokens.length) return '';
    const t = [...tokens].sort((a, b) => a.x - b.x);
    // join touching fragments into one visual piece; a space or a gap starts a new piece.
    // An item's own text is already in reading order (pdf.js fixes that), so items are never split.
    const words = [];
    t.forEach((tok, k) => {
        const prev = t[k - 1];
        const gap = prev ? tok.x - (prev.x + prev.w) : Infinity;
        if (prev && gap < 0.8 && !/\s$/.test(prev.s) && !/^\s/.test(tok.s) && words.length) words[words.length - 1].push(tok.s.trim());
        else words.push([tok.s.trim()]);
    });
    const ws = words.map((frags) => {
        const rtl = frags.some((f) => RTL_CHAR.test(f));
        return { text: (rtl ? [...frags].reverse() : frags).join(''), dir: rtl ? 'r' : LTR_CHAR.test(frags.join('')) ? 'l' : 'n' };
    });
    // neutral words (numbers-only punctuation, dashes) follow the word before them, else the base direction
    ws.forEach((w, k) => { if (w.dir === 'n') w.dir = (ws[k - 1] && ws[k - 1].dir) || base; });
    const runs = [];
    for (const w of ws) {
        if (runs.length && runs[runs.length - 1].dir === w.dir) runs[runs.length - 1].words.push(w.text);
        else runs.push({ dir: w.dir, words: [w.text] });
    }
    const ordered = (base === 'r' ? runs.reverse() : runs).map((r) => (r.dir === 'r' ? r.words.reverse() : r.words).join(' '));
    return ordered.join(' ');
}

let pdfjsPromise = null;
async function loadPdfjs() {
    pdfjsPromise ||= (async () => {
        const pdfjs = await import('pdfjs-dist');
        const worker = (await import('pdfjs-dist/build/pdf.worker.min.mjs?url')).default;
        pdfjs.GlobalWorkerOptions.workerSrc = worker;
        return pdfjs;
    })();
    return pdfjsPromise;
}

export async function extractText(file) {
    const name = (file.name || '').toLowerCase();
    if (file.size > MAX_BYTES) throw fail('This file is larger than 25 MB. Try a smaller file or paste the text.');
    const ext = name.split('.').pop();
    if (ext === 'doc') throw fail('Old Word .doc files cannot be read in the browser. Save it as .docx (File → Save As) or paste the text.');
    if (!['txt', 'pdf', 'docx'].includes(ext)) throw fail('Choose a .txt, .pdf or .docx file.');
    const buf = await file.arrayBuffer();
    if (ext === 'txt') return { text: decodeText(buf), note: '' };
    if (ext === 'docx') return { text: await docxToText(buf), note: '' };

    const r = await pdfToText(buf, await loadPdfjs());
    if (!r.text.trim()) throw fail('No text found in this PDF. It is probably a scanned image; this page cannot read scanned pages (no OCR).');
    const notes = [r.note];
    // Urdu PDFs made with InPage often come out as wrong letters or reversed words: say so.
    if (/[؀-ۿ]/.test(r.text)) notes.push('Urdu/Arabic PDFs sometimes come out with wrong or reversed letters. Check the text below before listening.');
    return { text: r.text, note: notes.filter(Boolean).join(' ') };
}
