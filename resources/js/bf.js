// Small helpers shared by games, quizzes and the Daily page. Everything is stored in
// the visitor's own browser (localStorage); nothing is sent to the server.
const store = {
    get(k, d = null) { try { const v = localStorage.getItem('bf_' + k); return v === null ? d : JSON.parse(v); } catch { return d; } },
    set(k, v) { try { localStorage.setItem('bf_' + k, JSON.stringify(v)); } catch { /* private mode */ } },
};

const today = () => new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD in the visitor's time zone

const BF = {
    store,
    today,
    /** Save a score; returns { best, isNew }. lowerIsBetter for time/moves. */
    best(key, score, lowerIsBetter = false) {
        const old = store.get('best_' + key);
        const isNew = old === null || (lowerIsBetter ? score < old : score > old);
        if (isNew) store.set('best_' + key, score);
        return { best: isNew ? score : old, isNew };
    },
    getBest(key) { return store.get('best_' + key); },
    /** Daily streak: call when today's challenge is done. Returns the streak length. */
    markDay(key) {
        const s = store.get('streak_' + key, { last: null, count: 0 });
        const t = today();
        if (s.last === t) return s.count;
        const y = new Date(); y.setDate(y.getDate() - 1);
        s.count = s.last === y.toLocaleDateString('en-CA') ? s.count + 1 : 1;
        s.last = t;
        store.set('streak_' + key, s);
        return s.count;
    },
    streak(key) {
        const s = store.get('streak_' + key, { last: null, count: 0 });
        const y = new Date(); y.setDate(y.getDate() - 1);
        return (s.last === today() || s.last === y.toLocaleDateString('en-CA')) ? s.count : 0;
    },
    doneToday(key) { return store.get('streak_' + key, {}).last === today(); },
    /** Remember a played game/quiz for the "recently played" row. */
    played(item) {
        const list = store.get('recent', []).filter((x) => x.url !== item.url);
        list.unshift(item);
        store.set('recent', list.slice(0, 8));
    },
    recent() { return store.get('recent', []); },
    async share(text, url = location.href) {
        if (navigator.share) {
            try { await navigator.share({ text, url }); return 'shared'; } catch { /* cancelled */ }
        }
        window.open('https://wa.me/?text=' + encodeURIComponent(text + ' ' + url), '_blank', 'noopener');
        return 'whatsapp';
    },
    async copy(text) {
        try { await navigator.clipboard.writeText(text); return true; } catch { return false; }
    },
    shuffle(a) {
        const b = [...a];
        for (let i = b.length - 1; i > 0; i--) { const j = Math.floor(Math.random() * (i + 1)); [b[i], b[j]] = [b[j], b[i]]; }
        return b;
    },
};

window.BF = BF;
export default BF;
