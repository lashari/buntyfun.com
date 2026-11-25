/* BuntyFun Chess — original code. Full rules (castling, en passant, promotion to queen,
   check, checkmate, stalemate, 50-move draw), play a friend or the computer (3 levels). */
(function () {
  'use strict';
  var VAL = { p: 100, n: 320, b: 330, r: 500, q: 900, k: 0 };
  var root = document.getElementById('chess');
  if (!root) return;
  var boardEl = root.querySelector('[data-board]');
  var statusEl = root.querySelector('[data-status]');
  var movesEl = root.querySelector('[data-moves]');
  var GLYPH = { K: '♔', Q: '♕', R: '♖', B: '♗', N: '♘', P: '♙', k: '♚', q: '♛', r: '♜', b: '♝', n: '♞', p: '♟' };
  var S, sel = null, targets = [], mode = 'cpu', level = 2, thinking = false, history = [], flipped = false;

  function white(p) { return p && p === p.toUpperCase(); }
  function colorOf(p) { return p ? (white(p) ? 'w' : 'b') : null; }
  function start() {
    var b = [];
    var back = 'rnbqkbnr';
    for (var r = 0; r < 8; r++) for (var c = 0; c < 8; c++) {
      var p = null;
      if (r === 0) p = back[c]; else if (r === 1) p = 'p'; else if (r === 6) p = 'P'; else if (r === 7) p = back[c].toUpperCase();
      b.push(p);
    }
    return { b: b, turn: 'w', castle: { K: true, Q: true, k: true, q: true }, ep: -1, half: 0, moveNo: 1 };
  }
  function clone(s) { return { b: s.b.slice(), turn: s.turn, castle: Object.assign({}, s.castle), ep: s.ep, half: s.half, moveNo: s.moveNo }; }
  function sq(r, c) { return r * 8 + c; }
  function on(r, c) { return r >= 0 && r < 8 && c >= 0 && c < 8; }

  function attacked(s, idx, by) {
    var r = idx >> 3, c = idx & 7, i, d, rr, cc, p;
    var pawnDir = by === 'w' ? 1 : -1;
    for (i = -1; i <= 1; i += 2) { rr = r + pawnDir; cc = c + i; if (on(rr, cc)) { p = s.b[sq(rr, cc)]; if (p && colorOf(p) === by && p.toLowerCase() === 'p') return true; } }
    var N = [[1, 2], [2, 1], [-1, 2], [-2, 1], [1, -2], [2, -1], [-1, -2], [-2, -1]];
    for (i = 0; i < 8; i++) { rr = r + N[i][0]; cc = c + N[i][1]; if (on(rr, cc)) { p = s.b[sq(rr, cc)]; if (p && colorOf(p) === by && p.toLowerCase() === 'n') return true; } }
    for (rr = -1; rr <= 1; rr++) for (cc = -1; cc <= 1; cc++) { if (!rr && !cc) continue; if (on(r + rr, c + cc)) { p = s.b[sq(r + rr, c + cc)]; if (p && colorOf(p) === by && p.toLowerCase() === 'k') return true; } }
    var DIRS = [[1, 0, 'rq'], [-1, 0, 'rq'], [0, 1, 'rq'], [0, -1, 'rq'], [1, 1, 'bq'], [1, -1, 'bq'], [-1, 1, 'bq'], [-1, -1, 'bq']];
    for (d = 0; d < 8; d++) {
      rr = r + DIRS[d][0]; cc = c + DIRS[d][1];
      while (on(rr, cc)) {
        p = s.b[sq(rr, cc)];
        if (p) { if (colorOf(p) === by && DIRS[d][2].indexOf(p.toLowerCase()) >= 0) return true; break; }
        rr += DIRS[d][0]; cc += DIRS[d][1];
      }
    }
    return false;
  }
  function kingAt(s, col) { var k = col === 'w' ? 'K' : 'k'; return s.b.indexOf(k); }
  function inCheck(s, col) { return attacked(s, kingAt(s, col), col === 'w' ? 'b' : 'w'); }

  function pseudo(s) {
    var out = [], col = s.turn, opp = col === 'w' ? 'b' : 'w';
    for (var i = 0; i < 64; i++) {
      var p = s.b[i]; if (!p || colorOf(p) !== col) continue;
      var r = i >> 3, c = i & 7, t = p.toLowerCase(), rr, cc, j, q;
      var add = function (to) { out.push({ from: i, to: to }); };
      if (t === 'p') {
        var dir = col === 'w' ? -1 : 1, startRow = col === 'w' ? 6 : 1;
        if (on(r + dir, c) && !s.b[sq(r + dir, c)]) {
          add(sq(r + dir, c));
          if (r === startRow && !s.b[sq(r + 2 * dir, c)]) add(sq(r + 2 * dir, c));
        }
        for (j = -1; j <= 1; j += 2) {
          if (!on(r + dir, c + j)) continue;
          q = sq(r + dir, c + j);
          if ((s.b[q] && colorOf(s.b[q]) === opp) || q === s.ep) add(q);
        }
      } else if (t === 'n' || t === 'k') {
        var D = t === 'n' ? [[1, 2], [2, 1], [-1, 2], [-2, 1], [1, -2], [2, -1], [-1, -2], [-2, -1]] : [[1, 0], [-1, 0], [0, 1], [0, -1], [1, 1], [1, -1], [-1, 1], [-1, -1]];
        for (j = 0; j < 8; j++) { rr = r + D[j][0]; cc = c + D[j][1]; if (on(rr, cc) && colorOf(s.b[sq(rr, cc)]) !== col) add(sq(rr, cc)); }
        if (t === 'k') {
          var home = col === 'w' ? 60 : 4, K = col === 'w' ? 'K' : 'k', Q = col === 'w' ? 'Q' : 'q';
          if (i === home && !inCheck(s, col)) {
            if (s.castle[K] && !s.b[home + 1] && !s.b[home + 2] && !attacked(s, home + 1, opp) && !attacked(s, home + 2, opp)) add(home + 2);
            if (s.castle[Q] && !s.b[home - 1] && !s.b[home - 2] && !s.b[home - 3] && !attacked(s, home - 1, opp) && !attacked(s, home - 2, opp)) add(home - 2);
          }
        }
      } else {
        var DD = [];
        if (t !== 'b') DD.push([1, 0], [-1, 0], [0, 1], [0, -1]);
        if (t !== 'r') DD.push([1, 1], [1, -1], [-1, 1], [-1, -1]);
        for (j = 0; j < DD.length; j++) {
          rr = r + DD[j][0]; cc = c + DD[j][1];
          while (on(rr, cc)) {
            q = sq(rr, cc);
            if (!s.b[q]) add(q); else { if (colorOf(s.b[q]) === opp) add(q); break; }
            rr += DD[j][0]; cc += DD[j][1];
          }
        }
      }
    }
    return out;
  }
  function apply(s, m) {
    var n = clone(s), p = n.b[m.from], t = p.toLowerCase(), col = s.turn;
    var captured = n.b[m.to];
    if (t === 'p' && m.to === s.ep) { captured = n.b[m.to + (col === 'w' ? 8 : -8)]; n.b[m.to + (col === 'w' ? 8 : -8)] = null; }
    n.b[m.to] = p; n.b[m.from] = null;
    if (t === 'p' && (m.to < 8 || m.to >= 56)) n.b[m.to] = col === 'w' ? 'Q' : 'q';
    if (t === 'k' && Math.abs(m.to - m.from) === 2) {
      if (m.to > m.from) { n.b[m.from + 1] = n.b[m.from + 3]; n.b[m.from + 3] = null; } else { n.b[m.from - 1] = n.b[m.from - 4]; n.b[m.from - 4] = null; }
    }
    if (t === 'k') { if (col === 'w') { n.castle.K = n.castle.Q = false; } else { n.castle.k = n.castle.q = false; } }
    [[63, 'K'], [56, 'Q'], [7, 'k'], [0, 'q']].forEach(function (x) { if (m.from === x[0] || m.to === x[0]) n.castle[x[1]] = false; });
    n.ep = (t === 'p' && Math.abs(m.to - m.from) === 16) ? (m.from + m.to) / 2 : -1;
    n.half = (t === 'p' || captured) ? 0 : s.half + 1;
    if (col === 'b') n.moveNo++;
    n.turn = col === 'w' ? 'b' : 'w';
    n.captured = captured || null;
    return n;
  }
  function legal(s) {
    return pseudo(s).filter(function (m) { var n = apply(s, m); return !inCheck(n, s.turn); });
  }
  function evaluate(s) {
    var score = 0;
    for (var i = 0; i < 64; i++) {
      var p = s.b[i]; if (!p) continue;
      var t = p.toLowerCase(), r = i >> 3, c = i & 7, v = VAL[t];
      var centre = 3.5 - Math.max(Math.abs(3.5 - r), Math.abs(3.5 - c));
      if (t === 'n' || t === 'b') v += centre * 8;
      if (t === 'p') v += (white(p) ? 6 - r : r - 1) * 6 + (c > 1 && c < 6 ? centre * 3 : 0);
      score += white(p) ? v : -v;
    }
    return s.turn === 'w' ? score : -score;
  }
  function order(s, ms) {
    return ms.map(function (m) { var cap = s.b[m.to]; return { m: m, k: cap ? VAL[cap.toLowerCase()] * 10 - VAL[s.b[m.from].toLowerCase()] : 0 }; })
      .sort(function (a, b) { return b.k - a.k; }).map(function (x) { return x.m; });
  }
  function search(s, depth, alpha, beta) {
    var ms = legal(s);
    if (!ms.length) return inCheck(s, s.turn) ? -100000 - depth : 0;
    if (depth === 0) return evaluate(s);
    ms = order(s, ms);
    for (var i = 0; i < ms.length; i++) {
      var v = -search(apply(s, ms[i]), depth - 1, -beta, -alpha);
      if (v >= beta) return beta;
      if (v > alpha) alpha = v;
    }
    return alpha;
  }
  function bestMove(s) {
    var ms = legal(s); if (!ms.length) return null;
    if (level === 1) {
      var caps = ms.filter(function (m) { return s.b[m.to]; });
      var pool = caps.length && Math.random() < 0.6 ? caps : ms;
      return pool[Math.floor(Math.random() * pool.length)];
    }
    var depth = level === 2 ? 2 : 3, best = null, bestV = -Infinity;
    ms = order(s, ms);
    for (var i = 0; i < ms.length; i++) {
      var v = -search(apply(s, ms[i]), depth - 1, -Infinity, -bestV) + Math.random() * 6;
      if (v > bestV) { bestV = v; best = ms[i]; }
    }
    return best;
  }
  var FILES = 'abcdefgh';
  function name(i) { return FILES[i & 7] + (8 - (i >> 3)); }
  function notation(s, m) {
    var p = s.b[m.from].toUpperCase();
    if (p === 'K' && Math.abs(m.to - m.from) === 2) return m.to > m.from ? 'O-O' : 'O-O-O';
    var cap = s.b[m.to] || (p === 'P' && m.to === s.ep);
    return (p === 'P' ? (cap ? FILES[m.from & 7] : '') : p) + (cap ? 'x' : '') + name(m.to) + (p === 'P' && (m.to < 8 || m.to >= 56) ? '=Q' : '');
  }
  function play(m) {
    var txt = notation(S, m);
    history.push(S);
    S = apply(S, m);
    var ms = legal(S);
    if (inCheck(S, S.turn)) txt += ms.length ? '+' : '#';
    S.last = m; S.san = (S.san || []).concat(); 
    log.push(txt);
    sel = null; targets = [];
    render();
    if (!over() && mode === 'cpu' && S.turn === 'b') cpu();
  }
  var log = [];
  function over() {
    var ms = legal(S);
    if (!ms.length) return inCheck(S, S.turn) ? (S.turn === 'w' ? 'Checkmate — Black wins' : 'Checkmate — White wins') : 'Stalemate — draw';
    if (S.half >= 100) return 'Draw by the 50-move rule';
    var pieces = S.b.filter(Boolean).map(function (p) { return p.toLowerCase(); }).sort().join('');
    if (pieces === 'kk' || pieces === 'bkk' || pieces === 'kkn') return 'Draw — not enough pieces to checkmate';
    return null;
  }
  function cpu() {
    thinking = true; render();
    setTimeout(function () { var m = bestMove(S); thinking = false; if (m) play(m); else render(); }, 250);
  }
  function render() {
    var res = over();
    var html = '';
    for (var vi = 0; vi < 64; vi++) {
      var i = flipped ? 63 - vi : vi;
      var r = i >> 3, c = i & 7, p = S.b[i];
      var dark = (r + c) % 2 === 1;
      var cls = 'cq ' + (dark ? 'cq-d' : 'cq-l');
      if (sel === i) cls += ' cq-sel';
      if (S.last && (S.last.from === i || S.last.to === i)) cls += ' cq-last';
      if (p && p.toLowerCase() === 'k' && colorOf(p) === S.turn && inCheck(S, S.turn)) cls += ' cq-check';
      var tgt = targets.indexOf(i) >= 0;
      html += '<button type="button" class="' + cls + '" data-i="' + i + '" aria-label="' + name(i) + (p ? ' ' + (white(p) ? 'white ' : 'black ') + ({ k: 'king', q: 'queen', r: 'rook', b: 'bishop', n: 'knight', p: 'pawn' })[p.toLowerCase()] : '') + '">' +
        (p ? '<span class="' + (white(p) ? 'pw' : 'pb') + '">' + GLYPH[p] + '</span>' : '') + (tgt ? '<i class="' + (p ? 'cq-cap' : 'cq-dot') + '"></i>' : '') + '</button>';
    }
    boardEl.innerHTML = html;
    statusEl.textContent = res || (thinking ? 'Computer is thinking…' : (S.turn === 'w' ? 'White' : 'Black') + ' to move' + (inCheck(S, S.turn) ? ' — check!' : ''));
    var mv = '';
    for (var k = 0; k < log.length; k += 2) mv += (k / 2 + 1) + '. ' + log[k] + (log[k + 1] ? ' ' + log[k + 1] : '') + '  ';
    movesEl.textContent = mv || 'No moves yet.';
    root.querySelector('[data-result]').hidden = !res;
    if (res && !root.dataset.saved) {
      root.dataset.saved = '1';
      if (mode === 'cpu' && /White wins/.test(res) && window.BF) {
        var key = 'chess_wins_l' + level; BF.store.set(key, BF.store.get(key, 0) + 1);
      }
      root.querySelector('[data-result-text]').textContent = res;
    }
    var w = window.BF ? [1, 2, 3].map(function (l) { return BF.store.get('chess_wins_l' + l, 0); }) : [0, 0, 0];
    root.querySelector('[data-wins]').textContent = 'Your wins vs computer — Easy ' + w[0] + ' · Medium ' + w[1] + ' · Hard ' + w[2];
  }
  boardEl.addEventListener('click', function (e) {
    var b = e.target.closest('[data-i]'); if (!b || thinking || over()) return;
    if (mode === 'cpu' && S.turn === 'b') return;
    var i = +b.dataset.i;
    if (sel !== null && targets.indexOf(i) >= 0) { play({ from: sel, to: i }); return; }
    if (S.b[i] && colorOf(S.b[i]) === S.turn) {
      sel = i; targets = legal(S).filter(function (m) { return m.from === i; }).map(function (m) { return m.to; });
    } else { sel = null; targets = []; }
    render();
  });
  function reset() { S = start(); log = []; history = []; sel = null; targets = []; delete root.dataset.saved; render(); if (window.BF) BF.played({ url: location.pathname, title: 'Chess', type: 'game' }); }
  root.querySelectorAll('[data-mode]').forEach(function (b) {
    b.addEventListener('click', function () { mode = b.dataset.mode; root.querySelectorAll('[data-mode]').forEach(function (x) { x.setAttribute('aria-pressed', x === b); }); root.querySelector('[data-levels]').hidden = mode !== 'cpu'; reset(); });
  });
  root.querySelectorAll('[data-level]').forEach(function (b) {
    b.addEventListener('click', function () { level = +b.dataset.level; root.querySelectorAll('[data-level]').forEach(function (x) { x.setAttribute('aria-pressed', x === b); }); reset(); });
  });
  root.querySelector('[data-new]').addEventListener('click', reset);
  root.querySelector('[data-again]').addEventListener('click', reset);
  root.querySelector('[data-undo]').addEventListener('click', function () {
    if (thinking || !history.length) return;
    var steps = mode === 'cpu' && history.length >= 2 ? 2 : 1;
    while (steps-- && history.length) { S = history.pop(); log.pop(); }
    delete root.dataset.saved; sel = null; targets = []; render();
  });
  root.querySelector('[data-flip]').addEventListener('click', function () { flipped = !flipped; render(); });
  root.querySelector('[data-share]').addEventListener('click', function () {
    var t = (root.querySelector('[data-result-text]').textContent || 'I played chess') + ' on BuntyFun ♟️ Can you beat the computer?';
    if (window.BF) BF.share(t);
  });
  reset();
})();
