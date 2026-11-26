// Small helpers shared by games, quizzes and the Daily page. Everything is stored in
// the visitor's own browser (localStorage); nothing is sent to the server.
const store = {
    get(k, d = null) { try { const v = localStorage.getItem('bf_' + k); return v === null ? d : JSON.parse(v); } catch { return d; } },
    set(k, v) { try { localStorage.setItem('bf_' + k, JSON.stringify(v)); } catch { /* private mode */ } },
};

// Saved lists come from localStorage: keep only same-site paths with a title, so a bad entry can't become a link elsewhere.
const safeList = (l) => (Array.isArray(l) ? l : []).filter((x) => x && typeof x.url === 'string' && x.url.startsWith('/') && !x.url.startsWith('//') && typeof x.title === 'string');

const today = () => new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD in the visitor's time zone

// GA4 event if the site tag is loaded (no-op otherwise). Only counts: no personal data.
const track = (name, params = {}) => { try { if (typeof window.gtag === 'function') window.gtag('event', name, params); } catch { /* ignore */ } };

const BF = {
    store,
    today,
    track,
    /** Save a score; returns { best, isNew }. lowerIsBetter for time/moves. */
    best(key, score, lowerIsBetter = false) {
        const old = store.get('best_' + key);
        const isNew = old === null || (lowerIsBetter ? score < old : score > old);
        if (isNew) { store.set('best_' + key, score); track('new_personal_best', { game_key: key, score }); }
        track('game_complete', { game_key: key, score });
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
        track(item.type === 'game' ? 'game_start' : 'content_start', { item_name: item.title, page_path: item.url });
        const list = store.get('recent', []).filter((x) => x.url !== item.url);
        list.unshift(item);
        store.set('recent', list.slice(0, 8));
    },
    recent() { return safeList(store.get('recent', [])); },
    /** Remember a page as recently opened without counting it as a game start (no GA event). */
    visited(item) {
        const list = store.get('recent', []).filter((x) => x.url !== item.url);
        list.unshift(item);
        store.set('recent', list.slice(0, 8));
    },
    clearRecent() { store.set('recent', []); window.dispatchEvent(new CustomEvent('bf-saved')); },
    /** Favourites (games + quizzes), newest first, max 30. Stored only in this browser. */
    favs() { return safeList(store.get('favs', [])); },
    isFav(url) { return store.get('favs', []).some((x) => x.url === url); },
    toggleFav(item) {
        let list = store.get('favs', []);
        const on = !list.some((x) => x.url === item.url);
        list = on ? [item, ...list].slice(0, 30) : list.filter((x) => x.url !== item.url);
        store.set('favs', list);
        track(on ? 'favourite_add' : 'favourite_remove', { item_name: item.title, page_path: item.url, content_type: item.type });
        window.dispatchEvent(new CustomEvent('bf-saved'));
        return on;
    },
    clearFavs() { store.set('favs', []); window.dispatchEvent(new CustomEvent('bf-saved')); },
    async share(text, url = location.href) {
        if (navigator.share) {
            try { await navigator.share({ text, url }); track('share', { method: 'native', page_path: location.pathname }); return 'shared'; } catch { /* cancelled */ }
        }
        track('share', { method: 'whatsapp', page_path: location.pathname });
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
