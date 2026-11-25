/* BuntyFun Daily Word — original code and word list. Guess the 5-letter word in 6 tries.
   Everyone gets the same word each day (by date); Practice mode gives a random word. */
(function () {
  'use strict';
  var root = document.getElementById('dword'); if (!root) return;
  var WORDS = ('apple bread chair dream earth flame grape heart juice knife lemon mango night ocean peace queen river smile tiger water youth zebra beach cloud dance eagle field ghost honey ivory jolly karma light magic noble olive pearl quiet radio sugar table unity voice wheat yield amber blend brave candy daisy elbow fresh giant happy index jelly kitty laugh money nurse orbit plant quick rainy shine train uncle vivid world young angel badge cabin delta enjoy fancy glass habit image joint kneel large metal north offer paint quote ruler sheep thumb under value whale yacht berry bloom brick brush camel chalk chess clock coast coral crane cream crown curry dough drift drink eight empty equal extra fairy feast fever fifty flute focus forty frame frost fruit glove grain grass green guard guest guide heavy horse hotel house human humor ideal joker judge label layer lever lodge lucky lunar maple march match mercy minor model month motor mount mouse music nerve never novel oasis onion order owner panel paper party pasta pause phone piano pilot pizza place plate point power press price pride prize proud puppy queue raise reach ready relax reply rhyme right robot round royal rural salad sauce scale scarf scene scout serve seven shade shape share shark shelf shell shirt shore short silky skill sleep slice slope smart snack snake solar solid sound south space spark spice spoon sport stack staff stage stamp stand steam steel stone storm story straw sunny super sweet swing sword teach teeth thick think three toast today topic torch total tower track trade treat trend truck trust truth tulip twist ultra urban usual valid video vital vocal wagon waste watch wheel white whole width woman worth write yummy zesty about above actor adult after again agree alarm album alert alive allow alone along amaze among ankle apron arena arrow aside award aware bacon baker basic basin batch begin bench birth black blank blaze blind block board boast bonus boost booth bound brain brief bring broad brown build bunch buyer cable carry catch cause cedar chain charm cheap check cheek chest chief child chili cider civic claim class clean clear climb close coach color count court cover craft crisp cross crowd daily dairy dealt depth diary digit dizzy donor draft drama drawn dress dried drive early ember enter entry event exact exist faith false fault fiber final first fixed flash fleet float floor flour fluid force forge found front funny gamer gauge gecko glide globe glory grace grand graph great greet grill gross group grove grown guess handy harsh haven hello hobby holly hover input irony issue jumbo kayak kebab koala lasso latch later learn least leave legal level limit linen liver local logic loose lover lower loyal lyric medal media melon merit messy might mixer moral muddy naval nifty ninja noise nylon nutty occur often opera other outer oxide patch peach penny petal photo piece plaza plumb polar porch pouch prism prose punch pupil purse quail quest quilt radar ranch range rapid rebel reign rider rival roast rocky rouge route rusty saint sandy savor scope score sense shift shiny sight silly since sixty skate skirt slate smoke snowy sober spare speak speed spend spent spine spray squad stair start state stick still sting stork stove style swamp swift syrup tasty tempo thank theme there thing those tidal timer title token tonic topaz trail trick tribe tried truly tunic unite until upper usage vapor vault venue verse vigor villa visit vista waltz weave weigh whisk wider windy wings witty worry yearn').toUpperCase().split(' ');
  var start = Date.UTC(2026, 0, 1);
  var dayNo = Math.floor((Date.UTC(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()) - start) / 864e5);
  var mode = 'daily', answer, rows, cur, done, won;
  var gridEl = root.querySelector('[data-grid]'), kbEl = root.querySelector('[data-kb]'), msgEl = root.querySelector('[data-msg]');
  function pickDaily() { var i = ((dayNo * 7919) % WORDS.length + WORDS.length) % WORDS.length; return WORDS[i]; }
  function score(guess, ans) {
    var res = Array(5).fill('x'), left = {}, i;
    for (i = 0; i < 5; i++) { if (guess[i] === ans[i]) res[i] = 'g'; else left[ans[i]] = (left[ans[i]] || 0) + 1; }
    for (i = 0; i < 5; i++) if (res[i] !== 'g' && left[guess[i]]) { res[i] = 'y'; left[guess[i]]--; }
    return res;
  }
  function key() { return 'dword_' + (window.BF ? BF.today() : dayNo); }
  function newGame(m) {
    mode = m || mode;
    answer = mode === 'daily' ? pickDaily() : WORDS[Math.floor(Math.random() * WORDS.length)];
    rows = []; cur = ''; done = false; won = false;
    if (mode === 'daily' && window.BF) {
      var saved = BF.store.get(key());
      if (saved && saved.answer === answer) { rows = saved.rows; done = saved.done; won = saved.won; }
    }
    root.querySelectorAll('[data-m]').forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.m === mode); });
    msg(done ? (won ? 'Solved! Come back tomorrow for a new word.' : 'The word was ' + answer + '. New word tomorrow.') : (mode === 'daily' ? 'Word #' + (dayNo + 1) + ' — same word for everyone today.' : 'Practice word — play as many as you like.'));
    render();
    if (window.BF) BF.played({ url: location.pathname, title: 'Daily Word', type: 'game' });
  }
  function msg(t) { msgEl.textContent = t; }
  function save() { if (mode === 'daily' && window.BF) BF.store.set(key(), { answer: answer, rows: rows, done: done, won: won }); }
  function submit() {
    if (done) return;
    if (cur.length < 5) { msg('Type 5 letters first.'); return; }
    rows.push(cur); cur = '';
    if (rows[rows.length - 1] === answer) { done = won = true; finish(); }
    else if (rows.length === 6) { done = true; finish(); }
    save(); render();
  }
  function finish() {
    if (!window.BF) return;
    if (mode === 'daily') {
      var st = BF.store.get('dword_stats', { played: 0, wins: 0, dist: [0, 0, 0, 0, 0, 0] });
      st.played++; if (won) { st.wins++; st.dist[rows.length - 1]++; BF.markDay('dword'); }
      BF.store.set('dword_stats', st);
    }
    msg(won ? ['Genius!', 'Brilliant!', 'Great!', 'Well done!', 'Nice!', 'Phew!'][rows.length - 1] + ' Solved in ' + rows.length + '/6.' : 'The word was ' + answer + '.');
  }
  function letterState() {
    var st = {};
    rows.forEach(function (r) { var s = score(r, answer); for (var i = 0; i < 5; i++) { var p = st[r[i]]; if (s[i] === 'g' || (s[i] === 'y' && p !== 'g') || (!p)) st[r[i]] = s[i] === 'x' && p ? p : s[i]; } });
    return st;
  }
  var COL = { g: 'background:#16a34a;color:#fff;border-color:#16a34a', y: 'background:#eab308;color:#fff;border-color:#eab308', x: 'background:#64748b;color:#fff;border-color:#64748b' };
  function render() {
    var h = '';
    for (var r = 0; r < 6; r++) {
      var word = rows[r] || (r === rows.length ? cur : ''), s = rows[r] ? score(rows[r], answer) : null;
      for (var c = 0; c < 5; c++) h += '<div class="dw-cell" style="' + (s ? COL[s[c]] : (word[c] ? 'border-color:#475569' : '')) + '">' + (word[c] || '') + '</div>';
    }
    gridEl.innerHTML = h;
    var st = letterState(), k = '';
    ['QWERTYUIOP', 'ASDFGHJKL', '+ZXCVBNM-'].forEach(function (row) {
      k += '<div class="dw-row">';
      row.split('').forEach(function (l) {
        var lab = l === '+' ? 'Enter' : l === '-' ? '⌫' : l;
        k += '<button type="button" data-k="' + l + '" class="dw-key' + (l === '+' || l === '-' ? ' dw-wide' : '') + '" style="' + (COL[st[l]] || '') + '">' + lab + '</button>';
      });
      k += '</div>';
    });
    kbEl.innerHTML = k;
    var end = root.querySelector('[data-end]'); end.hidden = !done;
    if (window.BF) {
      var s2 = BF.store.get('dword_stats', { played: 0, wins: 0, dist: [0, 0, 0, 0, 0, 0] });
      root.querySelector('[data-stats]').textContent = 'Daily played ' + s2.played + ' · Won ' + (s2.played ? Math.round(100 * s2.wins / s2.played) : 0) + '% · Streak 🔥 ' + BF.streak('dword');
    }
  }
  function press(l) {
    if (done) return;
    if (l === '+') return submit();
    if (l === '-') { cur = cur.slice(0, -1); return render(); }
    if (/^[A-Z]$/.test(l) && cur.length < 5) { cur += l; render(); }
  }
  kbEl.addEventListener('click', function (e) { var b = e.target.closest('[data-k]'); if (b) press(b.dataset.k); });
  document.addEventListener('keydown', function (e) {
    if (e.ctrlKey || e.metaKey || e.altKey || /INPUT|TEXTAREA/.test(document.activeElement.tagName)) return;
    if (e.key === 'Enter') press('+'); else if (e.key === 'Backspace') press('-'); else if (/^[a-z]$/i.test(e.key)) press(e.key.toUpperCase());
  });
  root.querySelectorAll('[data-m]').forEach(function (b) { b.addEventListener('click', function () { newGame(b.dataset.m); }); });
  root.querySelector('[data-next]').addEventListener('click', function () { newGame('practice'); });
  root.querySelector('[data-share]').addEventListener('click', function () {
    var grid = rows.map(function (r) { return score(r, answer).map(function (x) { return x === 'g' ? '🟩' : x === 'y' ? '🟨' : '⬜'; }).join(''); }).join('\n');
    var t = 'BuntyFun Daily Word ' + (mode === 'daily' ? '#' + (dayNo + 1) + ' ' : '') + (won ? rows.length : 'X') + '/6\n' + grid;
    if (window.BF) BF.share(t);
  });
  newGame('daily');
})();
