/* BuntyFun Ludo — original code. 2–4 players, each a person or the computer.
   Rules: roll a 6 to leave the base, a 6 gives another roll (three 6s in a row lose the turn),
   landing on an opponent sends it home (not on safe ★ squares), exact roll needed to finish. */
(function () {
  'use strict';
  var root = document.getElementById('ludo'); if (!root) return;
  var C = [
    { name: 'Red', hex: '#dc2626', light: '#fecaca', start: 0, home: [[7, 1], [7, 2], [7, 3], [7, 4], [7, 5]], base: [[1, 1], [1, 4], [4, 1], [4, 4]], area: [0, 0] },
    { name: 'Green', hex: '#16a34a', light: '#bbf7d0', start: 13, home: [[1, 7], [2, 7], [3, 7], [4, 7], [5, 7]], base: [[1, 10], [1, 13], [4, 10], [4, 13]], area: [0, 9] },
    { name: 'Yellow', hex: '#ca8a04', light: '#fef08a', start: 26, home: [[7, 13], [7, 12], [7, 11], [7, 10], [7, 9]], base: [[10, 10], [10, 13], [13, 10], [13, 13]], area: [9, 9] },
    { name: 'Blue', hex: '#2563eb', light: '#bfdbfe', start: 39, home: [[13, 7], [12, 7], [11, 7], [10, 7], [9, 7]], base: [[10, 1], [10, 4], [13, 1], [13, 4]], area: [9, 0] }
  ];
  var T = [];
  (function () {
    var i;
    for (i = 1; i <= 5; i++) T.push([6, i]);
    for (i = 5; i >= 0; i--) T.push([i, 6]);
    T.push([0, 7]);
    for (i = 0; i <= 5; i++) T.push([i, 8]);
    for (i = 9; i <= 14; i++) T.push([6, i]);
    T.push([7, 14]);
    for (i = 14; i >= 9; i--) T.push([8, i]);
    for (i = 9; i <= 14; i++) T.push([i, 8]);
    T.push([14, 7]);
    for (i = 14; i >= 9; i--) T.push([i, 6]);
    for (i = 5; i >= 0; i--) T.push([8, i]);
    T.push([7, 0]);
    T.push([6, 0]);
  })();
  T = T.filter(function (p, k) { return T.findIndex(function (q) { return q[0] === p[0] && q[1] === p[1]; }) === k; });
  var SAFE = [0, 8, 13, 21, 26, 34, 39, 47];
  var players = [], tokens = [], turn = 0, dice = 0, rolled = false, sixes = 0, busy = false, winner = [], setup = { n: 4, cpu: [false, true, true, true] };
  var boardEl = root.querySelector('[data-board]'), msgEl = root.querySelector('[data-msg]'), rollBtn = root.querySelector('[data-roll]'), diceEl = root.querySelector('[data-dice]');

  function seats(n) { return n === 2 ? [0, 2] : n === 3 ? [0, 1, 2] : [0, 1, 2, 3]; }
  function newGame() {
    players = seats(setup.n).map(function (c, k) { return { c: c, cpu: k === 0 ? setup.cpu[0] : setup.cpu[k] }; });
    tokens = players.map(function () { return [-1, -1, -1, -1]; });
    turn = 0; dice = 0; rolled = false; sixes = 0; busy = false; winner = [];
    root.querySelector('[data-end]').hidden = true;
    say(); render(); next();
    if (window.BF) BF.played({ url: location.pathname, title: 'Ludo', type: 'game' });
  }
  function abs(pi, p) { return (C[players[pi].c].start + p) % 52; }
  function cellOf(pi, ti) {
    var p = tokens[pi][ti], col = C[players[pi].c];
    if (p < 0) return col.base[ti];
    if (p <= 50) return T[abs(pi, p)];
    if (p <= 55) return col.home[p - 51];
    return null;
  }
  function canMove(pi, ti, d) { var p = tokens[pi][ti]; if (p === 56) return false; if (p < 0) return d === 6; return p + d <= 56; }
  function movable(pi, d) { return [0, 1, 2, 3].filter(function (ti) { return canMove(pi, ti, d); }); }
  function say(t) { var pl = players[turn]; msgEl.innerHTML = t || ('<b style="color:' + C[pl.c].hex + '">' + C[pl.c].name + '</b> ' + (pl.cpu ? '(computer) is playing…' : '— tap Roll')); }
  function roll() {
    if (rolled || busy || winner.length >= players.length - 1) return;
    dice = 1 + Math.floor(Math.random() * 6); rolled = true; diceEl.textContent = '⚀⚁⚂⚃⚄⚅'[dice - 1]; diceEl.style.color = C[players[turn].c].hex;
    if (dice === 6) sixes++; else sixes = 0;
    if (sixes === 3) { say('Three 6s in a row — turn lost.'); sixes = 0; return later(endTurn, 900); }
    var ms = movable(turn, dice);
    if (!ms.length) { say(C[players[turn].c].name + ' rolled ' + dice + ' — no move.'); return later(endTurn, 900); }
    if (players[turn].cpu) return later(function () { move(turn, choose(turn, ms)); }, 600);
    if (ms.length === 1 && tokens[turn][ms[0]] >= 0) return later(function () { move(turn, ms[0]); }, 400);
    say('<b style="color:' + C[players[turn].c].hex + '">' + C[players[turn].c].name + '</b> rolled ' + dice + ' — tap a glowing token.');
    render();
  }
  function later(fn, ms) { busy = true; render(); setTimeout(function () { busy = false; fn(); }, ms); }
  function choose(pi, ms) {
    var best = ms[0], bestS = -1e9;
    ms.forEach(function (ti) {
      var p = tokens[pi][ti], np = p < 0 ? 0 : p + dice, s = np;
      if (np === 56) s += 1000;
      if (p < 0) s += 400;
      if (np <= 50) {
        var a = abs(pi, np);
        if (SAFE.indexOf(a) < 0) players.forEach(function (pl, pj) { if (pj !== pi) tokens[pj].forEach(function (q) { if (q >= 0 && q <= 50 && abs(pj, q) === a) s += 800; }); });
        if (SAFE.indexOf(a) >= 0) s += 120;
      } else s += 150;
      if (s > bestS) { bestS = s; best = ti; }
    });
    return best;
  }
  function move(pi, ti) {
    var p = tokens[pi][ti];
    tokens[pi][ti] = p < 0 ? 0 : p + dice;
    var np = tokens[pi][ti], extra = dice === 6, note = '';
    if (np <= 50) {
      var a = abs(pi, np);
      if (SAFE.indexOf(a) < 0) players.forEach(function (pl, pj) {
        if (pj === pi) return;
        tokens[pj].forEach(function (q, tj) { if (q >= 0 && q <= 50 && abs(pj, q) === a) { tokens[pj][tj] = -1; extra = true; note = ' Captured ' + C[pl.c].name + '! 💥'; } });
      });
    }
    if (np === 56) { extra = true; note = ' Token home! 🏠'; }
    if (tokens[pi].every(function (q) { return q === 56; }) && winner.indexOf(pi) < 0) { winner.push(pi); extra = false; }
    rolled = false;
    if (winner.length >= players.length - 1 || (winner.length && !players[winner[0]].cpu && players.filter(function (x) { return !x.cpu; }).length === 1)) return finish();
    if (extra && winner.indexOf(pi) < 0) { say('<b style="color:' + C[players[pi].c].hex + '">' + C[players[pi].c].name + '</b> rolls again.' + note); render(); return next(); }
    say(note ? C[players[pi].c].name + ':' + note : undefined); endTurn(true);
  }
  function endTurn(keepMsg) {
    rolled = false; sixes = 0;
    do { turn = (turn + 1) % players.length; } while (winner.indexOf(turn) >= 0);
    if (keepMsg !== true) say(); else setTimeout(function () { say(); }, 700);
    render(); next();
  }
  function next() { if (players[turn].cpu && winner.length < players.length - 1) later(roll, 700); else render(); }
  function finish() {
    render();
    var w = players[winner[0]], human = !w.cpu;
    var text = C[w.c].name + (w.cpu ? ' (computer)' : '') + ' wins! 🎉';
    if (human && window.BF) { var k = 'ludo_wins'; BF.store.set(k, BF.store.get(k, 0) + 1); }
    root.querySelector('[data-final]').textContent = text;
    root.querySelector('[data-end]').hidden = false; msgEl.textContent = text;
  }
  function render() {
    var grid = [];
    for (var r = 0; r < 15; r++) { grid.push([]); for (var c = 0; c < 15; c++) grid[r].push({ bg: '#fff', tok: [], star: false, cls: '' }); }
    C.forEach(function (col) { for (var r2 = 0; r2 < 6; r2++) for (var c2 = 0; c2 < 6; c2++) grid[col.area[0] + r2][col.area[1] + c2].bg = col.light; col.home.forEach(function (h) { grid[h[0]][h[1]].bg = col.light; }); grid[T[col.start][0]][T[col.start][1]].bg = col.light; });
    SAFE.forEach(function (a) { grid[T[a][0]][T[a][1]].star = true; });
    for (var r3 = 6; r3 <= 8; r3++) for (var c3 = 6; c3 <= 8; c3++) grid[r3][c3].bg = '#e2e8f0';
    var canPick = rolled && !busy && !players[turn].cpu ? movable(turn, dice) : [];
    players.forEach(function (pl, pi) {
      tokens[pi].forEach(function (p, ti) {
        var cell = p === 56 ? [7, 7] : cellOf(pi, ti);
        grid[cell[0]][cell[1]].tok.push({ pi: pi, ti: ti, hex: C[pl.c].hex, glow: pi === turn && canPick.indexOf(ti) >= 0 });
      });
    });
    var h = '';
    grid.forEach(function (row, r4) {
      row.forEach(function (cell, c4) {
        var track = T.some(function (t) { return t[0] === r4 && t[1] === c4; });
        var inner = cell.star && !cell.tok.length ? '<span class="lu-star">★</span>' : '';
        var toks = cell.tok.slice(0, 4);
        if (toks.length) inner += '<span class="lu-toks lu-n' + toks.length + '">' + toks.map(function (t) {
          return '<button type="button" class="lu-tok' + (t.glow ? ' lu-glow' : '') + '" style="background:' + t.hex + '" data-pi="' + t.pi + '" data-ti="' + t.ti + '" aria-label="' + C[players[t.pi].c].name + ' token ' + (t.ti + 1) + '"></button>';
        }).join('') + '</span>';
        h += '<div class="lu-c" style="background:' + cell.bg + (track || (r4 >= 6 && r4 <= 8) || (c4 >= 6 && c4 <= 8) ? ';outline:1px solid #cbd5e1' : '') + '">' + inner + '</div>';
      });
    });
    boardEl.innerHTML = h;
    rollBtn.disabled = rolled || busy || players[turn].cpu || winner.length >= players.length - 1;
    rollBtn.style.background = C[players[turn].c].hex;
    if (window.BF) root.querySelector('[data-wins]').textContent = 'Your wins: ' + BF.store.get('ludo_wins', 0);
  }
  boardEl.addEventListener('click', function (e) {
    var b = e.target.closest('[data-pi]'); if (!b || !rolled || busy) return;
    var pi = +b.dataset.pi, ti = +b.dataset.ti;
    if (pi !== turn || players[turn].cpu || !canMove(pi, ti, dice)) return;
    move(pi, ti);
  });
  rollBtn.addEventListener('click', roll);
  document.addEventListener('keydown', function (e) { if ((e.code === 'Space' || e.key === 'Enter') && !rollBtn.disabled && document.activeElement === document.body) { e.preventDefault(); roll(); } });
  root.querySelectorAll('[data-n]').forEach(function (b) { b.addEventListener('click', function () { setup.n = +b.dataset.n; root.querySelectorAll('[data-n]').forEach(function (x) { x.setAttribute('aria-pressed', x === b); }); newGame(); }); });
  root.querySelectorAll('[data-vs]').forEach(function (b) { b.addEventListener('click', function () { var cpu = b.dataset.vs === 'cpu'; setup.cpu = [false, cpu, cpu, cpu]; root.querySelectorAll('[data-vs]').forEach(function (x) { x.setAttribute('aria-pressed', x === b); }); newGame(); }); });
  root.querySelector('[data-new]').addEventListener('click', newGame);
  root.querySelector('[data-again]').addEventListener('click', newGame);
  root.querySelector('[data-share]').addEventListener('click', function () { if (window.BF) BF.share(root.querySelector('[data-final]').textContent + ' Play Ludo free on BuntyFun 🎲'); });
  newGame();
})();
